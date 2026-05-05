package com.ticketbooking.config;

import com.ticketbooking.entity.Movie;
import com.ticketbooking.entity.Screen;
import com.ticketbooking.entity.Seat;
import com.ticketbooking.entity.Show;
import com.ticketbooking.entity.Theatre;
import com.ticketbooking.repository.MovieRepository;
import com.ticketbooking.repository.ScreenRepository;
import com.ticketbooking.repository.SeatRepository;
import com.ticketbooking.repository.ShowRepository;
import com.ticketbooking.repository.TheatreRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.CommandLineRunner;
import org.springframework.stereotype.Component;

import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

@Component
public class DataInitializer implements CommandLineRunner {

    @Autowired
    MovieRepository movieRepository;

    @Autowired
    TheatreRepository theatreRepository;

    @Autowired
    ScreenRepository screenRepository;

    @Autowired
    SeatRepository seatRepository;

    @Autowired
    ShowRepository showRepository;

    @Override
    public void run(String... args) throws Exception {
        if (movieRepository.count() == 0) {
            preloadMovies();
        }
        if (theatreRepository.count() == 0) {
            preloadTheatresAndShows();
        }
    }

    private void preloadMovies() {
        List<Movie> movies = new ArrayList<>();

        // ========== ENGLISH MOVIES ==========
        // MCU
        movies.add(new Movie(null, "Avengers: Endgame", "MCU", "English", 181, "Action/Sci-Fi",
                "https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg"));
        movies.add(new Movie(null, "Iron Man", "MCU", "English", 126, "Action/Sci-Fi",
                "https://image.tmdb.org/t/p/w500/78lPtwv72eTNqFW9COBYI0dWDJa.jpg"));
        movies.add(new Movie(null, "Thor: Ragnarok", "MCU", "English", 130, "Action/Adventure",
                "https://image.tmdb.org/t/p/w500/rzRwTcFvttcN1ZpX2xv4j3tSdJu.jpg"));
        movies.add(new Movie(null, "Spider-Man: No Way Home", "MCU", "English", 148, "Action/Adventure",
                "https://image.tmdb.org/t/p/w500/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg"));
        movies.add(new Movie(null, "Black Panther", "MCU", "English", 134, "Action/Sci-Fi",
                "https://image.tmdb.org/t/p/w500/uxzzxijgPIY7slzFvMotPv8wjKA.jpg"));
        movies.add(new Movie(null, "Doctor Strange", "MCU", "English", 115, "Action/Fantasy",
                "https://image.tmdb.org/t/p/w500/uGBVj3bEbCoZbDjjl9wTxcygko1.jpg"));

        // DCU
        movies.add(new Movie(null, "The Batman", "DCU", "English", 176, "Action/Crime",
                "https://image.tmdb.org/t/p/w500/74xTEgt7R36Fpooo50r9T25onhq.jpg"));
        movies.add(new Movie(null, "Wonder Woman", "DCU", "English", 141, "Action/Adventure",
                "https://image.tmdb.org/t/p/w500/gfJGkfI47EGHXoa7cTl5mMbAQPB.jpg"));
        movies.add(new Movie(null, "Aquaman", "DCU", "English", 143, "Action/Adventure",
                "https://image.tmdb.org/t/p/w500/xLPffWMhMj1l50ND3KchMjYoKmE.jpg"));
        movies.add(new Movie(null, "The Flash", "DCU", "English", 144, "Action/Sci-Fi",
                "https://image.tmdb.org/t/p/w500/rktDFPbfHfUbArZ6OOOKsXcv0Bm.jpg"));

        // X-MEN
        movies.add(new Movie(null, "Logan", "X-MEN", "English", 137, "Action/Drama",
                "https://image.tmdb.org/t/p/w500/fnbjcRDYn6YviCcePDnGdyAkYsB.jpg"));
        movies.add(new Movie(null, "Deadpool", "X-MEN", "English", 108, "Action/Comedy",
                "https://image.tmdb.org/t/p/w500/fSRb7vyIP8rQpL0I47P3qUsEKX3.jpg"));

        // ========== TELUGU MOVIES ==========
        movies.add(new Movie(null, "RRR", "Tollywood", "Telugu", 187, "Action/Drama",
                "https://image.tmdb.org/t/p/w500/nEufeZYpR9ivtsEeEFTrqSCORJq.jpg"));
        movies.add(new Movie(null, "Baahubali 2", "Tollywood", "Telugu", 167, "Action/Drama",
                "https://image.tmdb.org/t/p/w500/qqkGFi6kiaPCBxmUjOs7btDI2CE.jpg"));
        movies.add(new Movie(null, "Pushpa: The Rise", "Tollywood", "Telugu", 179, "Action/Thriller",
                "https://image.tmdb.org/t/p/w500/q9jEzTEyJwnpVBiNQvKEC08Htmn.jpg"));
        movies.add(new Movie(null, "Pushpa 2: The Rule", "Tollywood", "Telugu", 200, "Action/Thriller",
                "https://image.tmdb.org/t/p/w500/jx3FPGrVPgHqQVOvBODLOnahs7I.jpg"));
        movies.add(new Movie(null, "Salaar: Part 1", "Tollywood", "Telugu", 175, "Action/Thriller",
                "https://image.tmdb.org/t/p/w500/bKa0fT2rjWQBL1FnsPcO5IjuHIm.jpg"));
        movies.add(new Movie(null, "KGF: Chapter 2", "Tollywood", "Telugu", 168, "Action/Drama",
                "https://image.tmdb.org/t/p/w500/y4V3TCVa1r30iJHWJlxQa5XnPsE.jpg"));
        movies.add(new Movie(null, "Ala Vaikunthapurramuloo", "Tollywood", "Telugu", 163, "Action/Comedy",
                "https://image.tmdb.org/t/p/w500/bJCVcqQ7q8BT3lFycfjYAdIULbH.jpg"));
        movies.add(new Movie(null, "Devara: Part 1", "Tollywood", "Telugu", 176, "Action/Thriller",
                "https://image.tmdb.org/t/p/w500/g5kMKFVZ3c7rCqyVgPnNlMc5rj8.jpg"));

        // ========== HINDI MOVIES ==========
        movies.add(new Movie(null, "Jawan", "Bollywood", "Hindi", 169, "Action/Thriller",
                "https://image.tmdb.org/t/p/w500/jFpSRiHSfDkAE3wNQFPoZp0JnXn.jpg"));
        movies.add(new Movie(null, "Pathaan", "Bollywood", "Hindi", 146, "Action/Thriller",
                "https://image.tmdb.org/t/p/w500/y4MYQMwSaFCDaUGtuXOD5vqxNtZ.jpg"));
        movies.add(new Movie(null, "Dangal", "Bollywood", "Hindi", 161, "Drama/Sports",
                "https://image.tmdb.org/t/p/w500/fxRgY5JfUFfcQNqVN8g1z2xS5sH.jpg"));
        movies.add(new Movie(null, "3 Idiots", "Bollywood", "Hindi", 170, "Comedy/Drama",
                "https://image.tmdb.org/t/p/w500/66A9MqXOyVFCssoloscw79z8Tew.jpg"));
        movies.add(new Movie(null, "Animal", "Bollywood", "Hindi", 201, "Action/Drama",
                "https://image.tmdb.org/t/p/w500/lzz5LlErhZMjfHnqSHSha08JdUr.jpg"));
        movies.add(new Movie(null, "War", "Bollywood", "Hindi", 154, "Action/Thriller",
                "https://image.tmdb.org/t/p/w500/aQOWBkCj45spGTkBkJIvA2KSJzR.jpg"));
        movies.add(new Movie(null, "Fighter", "Bollywood", "Hindi", 166, "Action/Drama",
                "https://image.tmdb.org/t/p/w500/hOTb9aOfMfAQWjmugfZzDzKbATV.jpg"));
        movies.add(new Movie(null, "Stree 2", "Bollywood", "Hindi", 150, "Horror/Comedy",
                "https://image.tmdb.org/t/p/w500/qyQF7IwJecOw33IUjhJRlctrJSj.jpg"));

        movieRepository.saveAll(movies);
    }

    private void preloadTheatresAndShows() {
        String[] cities = {
                "Chennai", "Bangalore", "Mumbai", "Delhi",
                "Hyderabad", "Kolkata", "Pune", "Jaipur",
                "Kochi", "Vizag", "Lucknow", "Ahmedabad"
        };
        String[] theatreNames = { "PVR Cinemas", "Inox Leisure", "SPI Cinemas", "Cinepolis", "Miraj Cinemas" };
        List<Movie> movies = movieRepository.findAll();

        for (String city : cities) {
            for (int i = 0; i < 4; i++) { // 4 theatres per city
                Theatre theatre = new Theatre(null, theatreNames[i] + " " + city, city);
                theatre = theatreRepository.save(theatre);

                Screen screen1 = new Screen(null, theatre, "Screen 1", 64);
                screen1 = screenRepository.save(screen1);

                Screen screen2 = new Screen(null, theatre, "Screen 2", 64);
                screen2 = screenRepository.save(screen2);

                // Preload Seats for both screens (8x8 Grid)
                for (Screen screen : List.of(screen1, screen2)) {
                    List<Seat> seats = new ArrayList<>();
                    char rowChar = 'A';
                    for (int r = 0; r < 8; r++) {
                        String type = (r < 2) ? "FRONT" : (r < 5) ? "MIDDLE" : "BACK";
                        for (int c = 1; c <= 8; c++) {
                            seats.add(new Seat(null, screen, "" + rowChar + c, type));
                        }
                        rowChar++;
                    }
                    seatRepository.saveAll(seats);
                }

                // Seed shows for all movies in this theatre
                for (Movie movie : movies) {
                    // Morning show - Screen 1
                    showRepository.save(new Show(null, movie, screen1,
                            LocalDateTime.now().plusDays(1).withHour(10).withMinute(0), 100.0));
                    // Afternoon show - Screen 1
                    showRepository.save(new Show(null, movie, screen1,
                            LocalDateTime.now().plusDays(1).withHour(14).withMinute(30), 150.0));
                    // Evening show - Screen 2
                    showRepository.save(new Show(null, movie, screen2,
                            LocalDateTime.now().plusDays(1).withHour(18).withMinute(0), 180.0));
                    // Night show - Screen 2
                    showRepository.save(new Show(null, movie, screen2,
                            LocalDateTime.now().plusDays(1).withHour(22).withMinute(15), 200.0));
                }
            }
        }
    }
}
