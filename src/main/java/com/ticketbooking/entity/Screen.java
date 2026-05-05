package com.ticketbooking.entity;

import jakarta.persistence.*;
import lombok.*;

@Entity
@Table(name = "screens")
@Builder
public class Screen {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.EAGER)
    @JoinColumn(name = "theatre_id", nullable = false)
    private Theatre theatre;

    @Column(nullable = false)
    private String screenName;

    @Column(nullable = false)
    private Integer totalSeats;

    public Screen() {}

    public Screen(Long id, Theatre theatre, String screenName, Integer totalSeats) {
        this.id = id;
        this.theatre = theatre;
        this.screenName = screenName;
        this.totalSeats = totalSeats;
    }
    // Getters and Setters
    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public Theatre getTheatre() { return theatre; }
    public void setTheatre(Theatre theatre) { this.theatre = theatre; }
    public String getScreenName() { return screenName; }
    public void setScreenName(String screenName) { this.screenName = screenName; }
    public Integer getTotalSeats() { return totalSeats; }
    public void setTotalSeats(Integer totalSeats) { this.totalSeats = totalSeats; }
}
