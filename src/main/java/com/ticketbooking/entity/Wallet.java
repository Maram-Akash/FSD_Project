package com.ticketbooking.entity;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "wallets")
public class Wallet {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false, unique = true)
    private String email;

    @Column(nullable = false)
    private Double balance;

    @Column(nullable = false)
    private LocalDateTime lastUpdated;

    public Wallet() {}

    public Wallet(String email, Double balance) {
        this.email = email;
        this.balance = balance;
        this.lastUpdated = LocalDateTime.now();
    }

    public Long getId() { return id; }
    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }
    public Double getBalance() { return balance; }
    public void setBalance(Double balance) { this.balance = balance; this.lastUpdated = LocalDateTime.now(); }
    public LocalDateTime getLastUpdated() { return lastUpdated; }
    public void setLastUpdated(LocalDateTime lastUpdated) { this.lastUpdated = lastUpdated; }
}
