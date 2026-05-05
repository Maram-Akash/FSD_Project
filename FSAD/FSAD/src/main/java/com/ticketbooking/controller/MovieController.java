package com.ticketbooking.controller;

import com.ticketbooking.entity.Movie;
import com.ticketbooking.repository.MovieRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/movies")
public class MovieController {

    @Autowired
    private MovieRepository movieRepository;

    @GetMapping
    public ResponseEntity<List<Movie>> getAllMovies() {
        return ResponseEntity.ok(movieRepository.findAll());
    }

    @GetMapping("/universe/{type}")
    public ResponseEntity<List<Movie>> getMoviesByUniverse(@PathVariable("type") String type) {
        return ResponseEntity.ok(movieRepository.findByUniverse(type.toUpperCase()));
    }

    @GetMapping("/language/{lang}")
    public ResponseEntity<List<Movie>> getMoviesByLanguage(@PathVariable("lang") String lang) {
        // Capitalize first letter for matching: "english" -> "English"
        String normalized = lang.substring(0, 1).toUpperCase() + lang.substring(1).toLowerCase();
        return ResponseEntity.ok(movieRepository.findByLanguage(normalized));
    }
}
