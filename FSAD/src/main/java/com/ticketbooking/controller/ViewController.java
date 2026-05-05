package com.ticketbooking.controller;

import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;

@Controller
public class ViewController {

    @GetMapping(value = "/{path:[^\\.]*}")
    public String redirect() {
        System.out.println("Redirecting path to index.html");
        return "forward:/index.html";
    }
}
