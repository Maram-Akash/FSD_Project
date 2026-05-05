package com.ticketbooking.controller;

import com.ticketbooking.dto.SeatDTO;
import com.ticketbooking.entity.BookedSeat;
import com.ticketbooking.entity.Seat;
import com.ticketbooking.entity.Show;
import com.ticketbooking.repository.BookedSeatRepository;
import com.ticketbooking.repository.SeatRepository;
import com.ticketbooking.repository.ShowRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.*;

import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api/seats")
public class SeatController {

    @Autowired
    private SeatRepository seatRepository;

    @Autowired
    private BookedSeatRepository bookedSeatRepository;

    @Autowired
    private ShowRepository showRepository;

    @GetMapping("/{showId}")
    @Transactional(readOnly = true)
    public ResponseEntity<List<SeatDTO>> getSeatsForShow(@PathVariable("showId") Long showId) {
        Show show = showRepository.findById(showId)
                .orElseThrow(() -> new RuntimeException("Show not found"));

        List<Seat> seats = seatRepository.findByScreenId(show.getScreen().getId());
        
        List<BookedSeat> alreadyBooked = bookedSeatRepository.findByBookingShowId(showId);
        List<Long> bookedSeatIds = alreadyBooked.stream()
                .filter(bs -> "BOOKED".equals(bs.getBooking().getStatus()))
                .map(bs -> bs.getSeat().getId())
                .collect(Collectors.toList());

        List<SeatDTO> seatDTOs = new ArrayList<>();
        for (Seat seat : seats) {
            boolean isBooked = bookedSeatIds.contains(seat.getId());
            seatDTOs.add(new SeatDTO(seat.getId(), seat.getSeatNumber(), seat.getSeatType(), isBooked));
        }

        return ResponseEntity.ok(seatDTOs);
    }

    @ExceptionHandler(Exception.class)
    public ResponseEntity<String> handleException(Exception e) {
        e.printStackTrace();
        return ResponseEntity.status(500).body("Exception caught: " + e.getClass().getName() + " - " + e.getMessage());
    }
}
