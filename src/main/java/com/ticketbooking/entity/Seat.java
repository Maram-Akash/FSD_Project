package com.ticketbooking.entity;

import jakarta.persistence.*;
import lombok.*;

@Entity
@Table(name = "seats")
@Builder
public class Seat {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "screen_id", nullable = false)
    private Screen screen;

    @Column(nullable = false)
    private String seatNumber; // e.g. A1, A2

    @Column(nullable = false)
    private String seatType; // REGULAR, PREMIUM, VIP

    public Seat() {}

    public Seat(Long id, Screen screen, String seatNumber, String seatType) {
        this.id = id;
        this.screen = screen;
        this.seatNumber = seatNumber;
        this.seatType = seatType;
    }
    // Getters and Setters
    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public Screen getScreen() { return screen; }
    public void setScreen(Screen screen) { this.screen = screen; }
    public String getSeatNumber() { return seatNumber; }
    public void setSeatNumber(String seatNumber) { this.seatNumber = seatNumber; }
    public String getSeatType() { return seatType; }
    public void setSeatType(String seatType) { this.seatType = seatType; }
}
