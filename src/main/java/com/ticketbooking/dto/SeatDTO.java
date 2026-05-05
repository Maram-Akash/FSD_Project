package com.ticketbooking.dto;

import lombok.AllArgsConstructor;
import lombok.Data;

@AllArgsConstructor
public class SeatDTO {
    private Long id;
    private String seatNumber;
    private String seatType;
    private boolean isBooked;

    public SeatDTO() {}


    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public String getSeatNumber() { return seatNumber; }
    public void setSeatNumber(String seatNumber) { this.seatNumber = seatNumber; }
    public String getSeatType() { return seatType; }
    public void setSeatType(String seatType) { this.seatType = seatType; }
    public boolean isBooked() { return isBooked; }
    public void setBooked(boolean isBooked) { this.isBooked = isBooked; }
}
