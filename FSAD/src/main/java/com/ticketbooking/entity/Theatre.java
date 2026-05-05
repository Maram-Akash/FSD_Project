package com.ticketbooking.entity;

import jakarta.persistence.*;
import lombok.*;

@Entity
@Table(name = "theatres")
@Builder
public class Theatre {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false)
    private String name;

    @Column(nullable = false)
    private String location;

    public Theatre() {}

    public Theatre(Long id, String name, String location) {
        this.id = id;
        this.name = name;
        this.location = location;
    }
    // Getters and Setters
    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }
    public String getLocation() { return location; }
    public void setLocation(String location) { this.location = location; }
}
