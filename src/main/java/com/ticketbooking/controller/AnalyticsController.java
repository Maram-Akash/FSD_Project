package com.ticketbooking.controller;

import com.ticketbooking.entity.Movie;
import com.ticketbooking.repository.BookingRepository;
import com.ticketbooking.repository.MovieRepository;
import com.ticketbooking.repository.ShowRepository;
import com.ticketbooking.repository.BookedSeatRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.domain.PageRequest;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api/analytics")
public class AnalyticsController {

    @Autowired
    private BookingRepository bookingRepository;

    @Autowired
    private ShowRepository showRepository;

    @Autowired
    private BookedSeatRepository bookedSeatRepository;

    @Autowired
    private MovieRepository movieRepository;

    @GetMapping("/dashboard")
    public ResponseEntity<Map<String, Object>> getDashboardAnalytics() {
        Map<String, Object> analytics = new HashMap<>();

        // 1. Total Bookings
        analytics.put("totalBookings", bookingRepository.count());

        // 2. Most Booked Movie & City
        List<Object[]> movieResult = bookingRepository.findMostBookedMovie(PageRequest.of(0, 1));
        if (!movieResult.isEmpty() && movieResult.get(0) != null) {
            analytics.put("mostBookedMovie", movieResult.get(0)[0]);
            analytics.put("mostBookedMovieCount", movieResult.get(0)[1]);
        } else {
            analytics.put("mostBookedMovie", "N/A");
            analytics.put("mostBookedMovieCount", 0);
        }

        List<Object[]> cityResult = bookingRepository.findMostPopularCity(PageRequest.of(0, 1));
        if (!cityResult.isEmpty() && cityResult.get(0) != null) {
            analytics.put("mostPopularCity", cityResult.get(0)[0]);
            analytics.put("mostPopularCityCount", cityResult.get(0)[1]);
        } else {
            analytics.put("mostPopularCity", "N/A");
            analytics.put("mostPopularCityCount", 0);
        }

        // 2.b Pie Chart Data (Movie Distribution)
        List<Object[]> top5Movies = bookingRepository.findMostBookedMovie(PageRequest.of(0, 5));
        List<String> pieLabels = top5Movies.stream().map(m -> m[0].toString()).collect(Collectors.toList());
        List<Long> pieData = top5Movies.stream().map(m -> ((Number) m[1]).longValue()).collect(Collectors.toList());
        analytics.put("pieLabels", pieLabels);
        analytics.put("pieData", pieData);

        // 3. Seat Occupancy Overall
        long totalBookedSeats = bookedSeatRepository.count();
        long totalAvailableSeats = showRepository.findAll().stream()
                .filter(s -> s.getScreen() != null)
                .mapToLong(s -> s.getScreen().getTotalSeats())
                .sum();
        
        analytics.put("totalAvailableSeats", totalAvailableSeats - totalBookedSeats); // Available remaining
        analytics.put("totalBookedSeats", totalBookedSeats);

        double occupancyPercentage = totalAvailableSeats > 0 ? ((double) totalBookedSeats / totalAvailableSeats) * 100 : 0.0;
        analytics.put("occupancyPercentage", String.format("%.1f", occupancyPercentage) + "%");
        analytics.put("occupancyRaw", occupancyPercentage);

        // 4. Booking Trends (Chart Data)
        List<Object[]> trends = bookingRepository.findBookingTrends();
        List<String> trendDates = trends.stream().map(t -> t[0].toString()).collect(Collectors.toList());
        List<Long> trendCounts = trends.stream().map(t -> ((Number) t[1]).longValue()).collect(Collectors.toList());
        analytics.put("trendDates", trendDates);
        analytics.put("trendCounts", trendCounts);

        // 5. Overall Top Movie (Full Entity)
        List<Object[]> topMovieEntity = bookingRepository.findTopMovieEntityOverall(PageRequest.of(0, 1));
        if (!topMovieEntity.isEmpty() && topMovieEntity.get(0) != null) {
            analytics.put("topMovieOverall", topMovieEntity.get(0)[0]);
        }

        // 6. User Recommendation Engine
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        if (authentication != null && authentication.getPrincipal() instanceof UserDetails) {
            UserDetails userDetails = (UserDetails) authentication.getPrincipal();
            String email = userDetails.getUsername();

            List<Object[]> topGenreRow = bookingRepository.findTopGenreByEmail(email, PageRequest.of(0, 1));
            if (!topGenreRow.isEmpty() && topGenreRow.get(0) != null) {
                String preferredGenre = (String) topGenreRow.get(0)[0];
                List<Movie> recommendedMovies = movieRepository.findByGenre(preferredGenre);
                if (!recommendedMovies.isEmpty()) {
                    analytics.put("recommendedMovie", recommendedMovies.get(0));
                    analytics.put("recommendedReason", "Because you watched " + preferredGenre + " movies");
                }
            }
        }

        return ResponseEntity.ok(analytics);
    }
}
