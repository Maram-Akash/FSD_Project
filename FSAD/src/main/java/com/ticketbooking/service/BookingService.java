package com.ticketbooking.service;

import com.ticketbooking.dto.BookingRequest;
import com.ticketbooking.entity.*;
import com.ticketbooking.repository.*;
import com.ticketbooking.service.WalletService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;
import java.time.temporal.ChronoUnit;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

@Service
public class BookingService {

    @Autowired
    private BookingRepository bookingRepository;

    @Autowired
    private BookedSeatRepository bookedSeatRepository;

    @Autowired
    private ShowRepository showRepository;

    @Autowired
    private SeatRepository seatRepository;

    @Autowired
    private WalletService walletService;

    @Transactional
    public Booking bookTickets(BookingRequest request, String email, String userName) {
        Show show = showRepository.findById(request.getShowId())
                .orElseThrow(() -> new RuntimeException("Show not found"));

        // Fetch requested seats with PESSIMISTIC_WRITE lock
        List<Seat> requestedSeats = seatRepository.findByIdsWithPessimisticLock(request.getSeatIds());

        if (requestedSeats.size() != request.getSeatIds().size()) {
            throw new RuntimeException("Some seats are invalid");
        }

        // Check if any seat is already booked for this show
        List<BookedSeat> alreadyBooked = bookedSeatRepository.findByBookingShowId(show.getId());
        List<Long> bookedSeatIds = alreadyBooked.stream()
                .filter(bs -> "BOOKED".equals(bs.getBooking().getStatus()))
                .map(bs -> bs.getSeat().getId())
                .collect(Collectors.toList());

        for (Seat seat : requestedSeats) {
            if (bookedSeatIds.contains(seat.getId())) {
                throw new RuntimeException("Seat " + seat.getSeatNumber() + " is already booked.");
            }
        }

        // Pricing logic
        double totalAmount = 0.0;
        double basePrice = show.getBasePrice();

        // Calculate current booking percentage
        int totalSeatsInScreen = show.getScreen().getTotalSeats();
        long currentBookedCount = bookedSeatIds.size();

        double demandMultiplier = 1.0;
        if ((double) currentBookedCount / totalSeatsInScreen > 0.7) {
            demandMultiplier = 1.25; // 25% extra if >70% booked
        }

        for (Seat seat : requestedSeats) {
            double seatPrice = basePrice;
            if ("PREMIUM".equalsIgnoreCase(seat.getSeatType())) {
                seatPrice *= 1.3;
            } else if ("VIP".equalsIgnoreCase(seat.getSeatType())) {
                seatPrice *= 1.6;
            }
            totalAmount += seatPrice * demandMultiplier;
        }

        // If wallet payment, deduct from wallet
        if ("WALLET".equalsIgnoreCase(request.getPaymentMethod())) {
            walletService.deductFunds(email, totalAmount,
                "Booking: " + show.getMovie().getTitle() + " (" + requestedSeats.size() + " seats)");
        }

        // Create Booking
        Booking booking = new Booking();
        booking.setEmail(email);
        booking.setUserName(userName);
        booking.setShow(show);
        booking.setBookingTime(LocalDateTime.now());
        booking.setTotalAmount(totalAmount);
        booking.setStatus("BOOKED");
        booking.setPaymentMethod(request.getPaymentMethod() != null ? request.getPaymentMethod() : "WALLET");

        booking = bookingRepository.save(booking);

        // Save Booked Seats
        List<BookedSeat> bookedSeats = new ArrayList<>();
        for (Seat seat : requestedSeats) {
            BookedSeat bs = new BookedSeat(null, booking, seat);
            bookedSeats.add(bs);
        }
        bookedSeatRepository.saveAll(bookedSeats);

        booking.setBookedSeats(bookedSeats);
        return booking;
    }

    @Transactional
    public String cancelBooking(Long bookingId, String email) {
        Booking booking = bookingRepository.findById(bookingId)
                .orElseThrow(() -> new RuntimeException("Booking not found"));

        if (!booking.getEmail().equals(email)) {
            throw new RuntimeException("Unauthorized cancellation attempt");
        }

        if ("CANCELLED".equals(booking.getStatus())) {
            throw new RuntimeException("Booking is already cancelled");
        }

        LocalDateTime showTime = booking.getShow().getShowTime();
        LocalDateTime now = LocalDateTime.now();

        if (now.isAfter(showTime)) {
            throw new RuntimeException("Cannot cancel past shows");
        }

        long hoursToMenu = ChronoUnit.HOURS.between(now, showTime);
        double refundPercentage = 0;

        if (hoursToMenu > 6) {
            refundPercentage = 100.0;
        } else if (hoursToMenu >= 2) {
            refundPercentage = 90.0;
        } else {
            refundPercentage = 0.0;
        }

        booking.setStatus("CANCELLED");
        bookingRepository.save(booking);

        double refundAmount = (booking.getTotalAmount() * refundPercentage) / 100.0;

        // Refund to wallet if original payment was wallet
        if (refundAmount > 0 && "WALLET".equalsIgnoreCase(booking.getPaymentMethod())) {
            walletService.refundToWallet(email, refundAmount,
                "Refund for cancelled booking: " + booking.getShow().getMovie().getTitle());
        }

        return String.format("Booking Cancelled. Refund: %.1f%% = ₹%.2f credited to your wallet.", refundPercentage, refundAmount);
    }
}
