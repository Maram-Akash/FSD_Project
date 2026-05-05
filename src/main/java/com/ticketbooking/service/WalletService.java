package com.ticketbooking.service;

import com.ticketbooking.entity.Wallet;
import com.ticketbooking.entity.WalletTransaction;
import com.ticketbooking.repository.WalletRepository;
import com.ticketbooking.repository.WalletTransactionRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;

@Service
public class WalletService {

    @Autowired
    private WalletRepository walletRepository;

    @Autowired
    private WalletTransactionRepository walletTransactionRepository;

    // Get or create wallet for user
    public Wallet getOrCreateWallet(String email) {
        return walletRepository.findByEmail(email)
                .orElseGet(() -> {
                    Wallet w = new Wallet(email, 10000.0); // Seed wallet with 10k for easy testing
                    return walletRepository.save(w);
                });
    }

    // Add funds to wallet
    @Transactional
    public Wallet addFunds(String email, Double amount) {
        if (amount <= 0) throw new RuntimeException("Amount must be greater than 0");
        if (amount > 50000) throw new RuntimeException("Cannot add more than ₹50,000 at once");

        Wallet wallet = getOrCreateWallet(email);
        wallet.setBalance(wallet.getBalance() + amount);
        walletRepository.save(wallet);

        walletTransactionRepository.save(
            new WalletTransaction(email, amount, "CREDIT", "Added ₹" + String.format("%.2f", amount) + " to wallet")
        );

        return wallet;
    }

    // Deduct funds from wallet (called during booking)
    @Transactional
    public void deductFunds(String email, Double amount, String description) {
        Wallet wallet = getOrCreateWallet(email);

        if (wallet.getBalance() < amount) {
            throw new RuntimeException("Insufficient wallet balance. Required: ₹" +
                    String.format("%.2f", amount) + ", Available: ₹" + String.format("%.2f", wallet.getBalance()));
        }

        wallet.setBalance(wallet.getBalance() - amount);
        walletRepository.save(wallet);

        walletTransactionRepository.save(
            new WalletTransaction(email, amount, "DEBIT", description)
        );
    }

    // Refund to wallet (called during cancellation)
    @Transactional
    public void refundToWallet(String email, Double amount, String description) {
        Wallet wallet = getOrCreateWallet(email);
        wallet.setBalance(wallet.getBalance() + amount);
        walletRepository.save(wallet);

        walletTransactionRepository.save(
            new WalletTransaction(email, amount, "CREDIT", description)
        );
    }

    // Get transaction history
    public List<WalletTransaction> getTransactions(String email) {
        return walletTransactionRepository.findByEmailOrderByCreatedAtDesc(email);
    }
}
