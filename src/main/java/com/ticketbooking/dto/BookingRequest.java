package com.ticketbooking.dto;

import lombok.Data;
import java.util.List;

public class BookingRequest {
    private Long showId;
    private List<Long> seatIds;
    private String paymentMethod; // WALLET

    public Long getShowId() { return showId; }
    public void setShowId(Long showId) { this.showId = showId; }
    public List<Long> getSeatIds() { return seatIds; }
    public void setSeatIds(List<Long> seatIds) { this.seatIds = seatIds; }
    public String getPaymentMethod() { return paymentMethod; }
    public void setPaymentMethod(String paymentMethod) { this.paymentMethod = paymentMethod; }
}
