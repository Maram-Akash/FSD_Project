package com.ticketbooking.entity;

import jakarta.persistence.*;
import lombok.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "shows")
@Builder
public class Show {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.EAGER)
    @JoinColumn(name = "movie_id", nullable = false)
    private Movie movie;

    @ManyToOne(fetch = FetchType.EAGER)
    @JoinColumn(name = "screen_id", nullable = false)
    private Screen screen;

    @Column(nullable = false)
    private LocalDateTime showTime;

    @Column(nullable = false)
    private Double basePrice;

    public Show() {}

    public Show(Long id, Movie movie, Screen screen, LocalDateTime showTime, Double basePrice) {
        this.id = id;
        this.movie = movie;
        this.screen = screen;
        this.showTime = showTime;
        this.basePrice = basePrice;
    }
    // Getters and Setters
    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public Movie getMovie() { return movie; }
    public void setMovie(Movie movie) { this.movie = movie; }
    public Screen getScreen() { return screen; }
    public void setScreen(Screen screen) { this.screen = screen; }
    public LocalDateTime getShowTime() { return showTime; }
    public void setShowTime(LocalDateTime showTime) { this.showTime = showTime; }
    public Double getBasePrice() { return basePrice; }
    public void setBasePrice(Double basePrice) { this.basePrice = basePrice; }
}
