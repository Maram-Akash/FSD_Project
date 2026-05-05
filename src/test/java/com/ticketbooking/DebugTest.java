package com.ticketbooking;

import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.AutoConfigureMockMvc;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.security.test.context.support.WithMockUser;
import org.springframework.test.web.servlet.MockMvc;
import org.springframework.test.web.servlet.request.MockMvcRequestBuilders;

@SpringBootTest
@AutoConfigureMockMvc
public class DebugTest {

    @Autowired
    private MockMvc mockMvc;

    @Test
    @WithMockUser(username = "admin@test.com", roles = {"USER"})
    public void testAnalyticsEndpoint() throws Exception {
        String response = mockMvc.perform(MockMvcRequestBuilders.get("/api/analytics/dashboard"))
                .andReturn().getResponse().getContentAsString();
        System.out.println("API RESPONSE: " + response);
    }
}
