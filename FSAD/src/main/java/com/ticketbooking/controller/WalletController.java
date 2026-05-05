package com.ticketbooking.controller;

import com.ticketbooking.entity.Wallet;
import com.ticketbooking.entity.WalletTransaction;
import com.ticketbooking.security.UserDetailsImpl;
import com.ticketbooking.service.WalletService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.web.bind.annotation.*;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/api/wallet")
public class WalletController {

    @Autowired
    private WalletService walletService;

    private String getCurrentEmail() {
        Authentication auth = SecurityContextHolder.getContext().getAuthentication();
        UserDetailsImpl userDetails = (UserDetailsImpl) auth.getPrincipal();
        return userDetails.getUsername();
    }

    // GET wallet balance
    @GetMapping
    public ResponseEntity<?> getWallet() {
        try {
            String email = getCurrentEmail();
            Wallet wallet = walletService.getOrCreateWallet(email);
            Map<String, Object> resp = new HashMap<>();
            resp.put("balance", wallet.getBalance());
            resp.put("email", wallet.getEmail());
            resp.put("lastUpdated", wallet.getLastUpdated() != null ? wallet.getLastUpdated().toString() : null);
            return ResponseEntity.ok(resp);
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }

    // POST add funds
    @PostMapping("/add")
    public ResponseEntity<?> addFunds(@RequestBody Map<String, Object> body) {
        try {
            Object rawAmount = body.get("amount");
            if (rawAmount == null) {
                return ResponseEntity.badRequest().body(Map.of("error", "Amount is required"));
            }
            double amount = ((Number) rawAmount).doubleValue();
            String email = getCurrentEmail();
            Wallet wallet = walletService.addFunds(email, amount);
            Map<String, Object> resp = new HashMap<>();
            resp.put("balance", wallet.getBalance());
            resp.put("email", wallet.getEmail());
            resp.put("message", "₹" + String.format("%.2f", amount) + " added successfully!");
            return ResponseEntity.ok(resp);
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }

    // GET transaction history
    @GetMapping("/transactions")
    public ResponseEntity<?> getTransactions() {
        try {
            String email = getCurrentEmail();
            List<WalletTransaction> txns = walletService.getTransactions(email);
            return ResponseEntity.ok(txns);
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }

    // POST pay with wallet
    @PostMapping("/pay")
    public ResponseEntity<?> payWithWallet(@RequestBody Map<String, Object> body) {
        try {
            double amount = ((Number) body.get("amount")).doubleValue();
            String description = (String) body.getOrDefault("description", "Wallet payment");
            String email = getCurrentEmail();
            walletService.deductFunds(email, amount, description);
            Wallet wallet = walletService.getOrCreateWallet(email);
            return ResponseEntity.ok(Map.of("message", "Payment successful", "newBalance", wallet.getBalance()));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }
}
