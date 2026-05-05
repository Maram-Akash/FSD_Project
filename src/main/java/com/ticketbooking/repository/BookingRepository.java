package com.ticketbooking.repository;

import com.ticketbooking.entity.Booking;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface BookingRepository extends JpaRepository<Booking, Long> {
    List<Booking> findByEmail(String email);

    List<Booking> findByShowId(Long showId);

    @Query("SELECT b.show.movie.title, COUNT(b) FROM Booking b GROUP BY b.show.movie.title ORDER BY COUNT(b) DESC")
    List<Object[]> findMostBookedMovie(org.springframework.data.domain.Pageable pageable);

    @Query("SELECT b.show.screen.theatre.location, COUNT(b) FROM Booking b GROUP BY b.show.screen.theatre.location ORDER BY COUNT(b) DESC")
    List<Object[]> findMostPopularCity(org.springframework.data.domain.Pageable pageable);

    @Query(value = "SELECT CAST(booking_time AS DATE) as bdate, COUNT(*) FROM bookings b WHERE b.status = 'BOOKED' GROUP BY CAST(booking_time AS DATE) ORDER BY bdate ASC LIMIT 7", nativeQuery = true)
    List<Object[]> findBookingTrends();

    @Query("SELECT m.genre, COUNT(b) FROM Booking b JOIN b.show s JOIN s.movie m WHERE b.email = :email GROUP BY m.genre ORDER BY COUNT(b) DESC")
    List<Object[]> findTopGenreByEmail(@Param("email") String email, org.springframework.data.domain.Pageable pageable);

    @Query("SELECT b.show.movie, COUNT(b) FROM Booking b GROUP BY b.show.movie ORDER BY COUNT(b) DESC")
    List<Object[]> findTopMovieEntityOverall(org.springframework.data.domain.Pageable pageable);
}
