package com.ticketbooking.entity;

import jakarta.persistence.*;
import lombok.*;
import com.fasterxml.jackson.annotation.JsonIgnore;

@Entity
@Table(name = "booked_seats")
@Builder
public class BookedSeat {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "booking_id", nullable = false)
    @JsonIgnore
    private Booking booking;

    @ManyToOne(fetch = FetchType.EAGER)
    @JoinColumn(name = "seat_id", nullable = false)
    private Seat seat;

    public BookedSeat() {}

    public BookedSeat(Long id, Booking booking, Seat seat) {
        this.id = id;
        this.booking = booking;
        this.seat = seat;
    }
    // Getters and Setters
    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public Booking getBooking() { return booking; }
    public void setBooking(Booking booking) { this.booking = booking; }
    public Seat getSeat() { return seat; }
    public void setSeat(Seat seat) { this.seat = seat; }
}
