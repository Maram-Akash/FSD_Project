package com.ticketbooking.controller;

import com.ticketbooking.entity.Show;
import com.ticketbooking.repository.ShowRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;

@RestController
@RequestMapping("/api/shows")
public class ShowController {

    @Autowired
    private ShowRepository showRepository;

    @GetMapping("/{movieId}")
    @Transactional(readOnly = true)
    public ResponseEntity<List<Show>> getShowsByMovie(@PathVariable("movieId") Long movieId) {
        List<Show> shows = showRepository.findByMovieId(movieId);
        // Filter out corrupted shows where screen or theatre might be null due to
        // incomplete seeding
        List<Show> validShows = shows.stream()
                .filter(s -> s.getScreen() != null && s.getScreen().getTheatre() != null)
                .toList();
        return ResponseEntity.ok(validShows);
    }

    @ExceptionHandler(Exception.class)
    public ResponseEntity<String> handleException(Exception e) {
        e.printStackTrace();
        return ResponseEntity.status(500).body("Error: " + e.getMessage());
    }
}
