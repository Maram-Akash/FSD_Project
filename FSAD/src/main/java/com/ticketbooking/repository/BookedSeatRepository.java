package com.ticketbooking.repository;

import com.ticketbooking.entity.BookedSeat;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface BookedSeatRepository extends JpaRepository<BookedSeat, Long> {
    List<BookedSeat> findByBookingShowId(Long showId);
}
