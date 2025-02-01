# Tourism Booking Management Project

## Introduction

Welcome to the tourism booking management project. This project aims to assess your ability to develop and improve an existing Laravel application. Below, you will find the necessary enhancements and new functionalities you are expected to implement. Additionally, we have listed some appreciated features that can add value to your solution.

We hope this project allows you to demonstrate your technical skills and your ability to write clean, efficient, and well-structured code. If you have any questions or need further clarification, please feel free to ask. Good luck!

## Requested Features

1. **Send an Email to the User with Booking Information at the Time of Creation (Mail + Blade):**
   - The email should include the client's name, tour name, hotel name, booking date, and number of people.
2. **Allow Searches by Tour Name, Hotel Name, or Client Name.**
3. **Allow Searches by Date Range.**
4. **Allow Data Sorting, Both Ascending and Descending.**
5. **Create an Endpoint `/api/bookings/export` that Generates a CSV File with All Bookings:**
   - The use of the `laravel-excel` library is recommended.
6. **Add Pagination to the Listings of Tour, Hotel, and Booking.**
7. **Add Statuses to Bookings and Create an Endpoint `/api/bookings/{id}/cancel` that Changes the Booking Status to Canceled.**

## Appreciated Features

1. **Generation of Tests:** Create tests to verify the new features and improve existing ones.
2. **Use of Eloquent Resources:** Utilize Eloquent Resources for data transformation.
3. **Use of Eloquent Scopes:** Utilize Eloquent Scopes for data searches.
4. **Use of Eloquent Events:** Utilize Eloquent Events for sending emails.
5. **Use of Jobs:** Utilize Jobs for CSV file generation.
6. **Use of FormRequests:** Utilize FormRequests for data validation.
7. **Use of Traits:** Utilize Traits for code reuse where necessary.
8. **Use of Enums:** Utilize Enums to improve code readability where possible.
