package com.ticketbooking.controller;

import com.ticketbooking.dto.BookingRequest;
import com.ticketbooking.entity.Booking;
import com.ticketbooking.repository.BookingRepository;
import com.ticketbooking.security.UserDetailsImpl;
import com.ticketbooking.service.BookingService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api")
public class BookingController {

    @Autowired
    private BookingService bookingService;

    @Autowired
    private BookingRepository bookingRepository;

    @PostMapping({"/book", "/bookings"})
    public ResponseEntity<?> bookTickets(@RequestBody BookingRequest request) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        UserDetailsImpl userDetails = (UserDetailsImpl) authentication.getPrincipal();

        try {
            Booking booking = bookingService.bookTickets(request, userDetails.getUsername(), userDetails.getName());
            return ResponseEntity.ok(booking);
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(e.getMessage());
        }
    }

    @PostMapping("/cancel/{bookingId}")
    public ResponseEntity<?> cancelBooking(@PathVariable("bookingId") Long bookingId) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        UserDetailsImpl userDetails = (UserDetailsImpl) authentication.getPrincipal();

        try {
            String result = bookingService.cancelBooking(bookingId, userDetails.getUsername());
            return ResponseEntity.ok(result);
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(e.getMessage());
        }
    }

    @GetMapping("/bookings/user/{email}")
    public ResponseEntity<List<Booking>> getUserBookings(@PathVariable("email") String email) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        UserDetailsImpl userDetails = (UserDetailsImpl) authentication.getPrincipal();

        if (!userDetails.getUsername().equals(email)) {
            return ResponseEntity.status(403).build();
        }

        return ResponseEntity.ok(bookingRepository.findByEmail(email));
    }
}
