package com.ticketbooking.entity;

import jakarta.persistence.*;
import lombok.*;

@Entity
@Table(name = "movies")
@Builder
public class Movie {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false)
    private String title;

    @Column(nullable = false)
    private String universe; // MCU, DCU, XMEN

    @Column(nullable = false)
    private String language;

    @Column(nullable = false)
    private Integer duration; // in minutes

    @Column(nullable = false)
    private String genre;

    @Column(nullable = false)
    private String posterUrl;

    public Movie() {}

    public Movie(Long id, String title, String universe, String language, Integer duration, String genre, String posterUrl) {
        this.id = id;
        this.title = title;
        this.universe = universe;
        this.language = language;
        this.duration = duration;
        this.genre = genre;
        this.posterUrl = posterUrl;
    }
    // Getters and Setters
    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public String getTitle() { return title; }
    public void setTitle(String title) { this.title = title; }
    public String getUniverse() { return universe; }
    public void setUniverse(String universe) { this.universe = universe; }
    public String getLanguage() { return language; }
    public void setLanguage(String language) { this.language = language; }
    public Integer getDuration() { return duration; }
    public void setDuration(Integer duration) { this.duration = duration; }
    public String getGenre() { return genre; }
    public void setGenre(String genre) { this.genre = genre; }
    public String getPosterUrl() { return posterUrl; }
    public void setPosterUrl(String posterUrl) { this.posterUrl = posterUrl; }
}
