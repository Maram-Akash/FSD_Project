# Skill Learning Academy Marketplace

A full-stack web application for a skill-based learning platform with secure authentication, course management, and marketplace features.

## Tech Stack

- **Frontend:** HTML5, CSS3, ES6 JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Server:** XAMPP (Apache + MySQL + PHP)

## Features

### Public Pages
- **Home** – Featured courses, categories, testimonials
- **About** – Mission, vision, team
- **Course Catalog** – Browse with search & filters (category, price, level)
- **Course Detail** – Description, syllabus, instructor, reviews, enroll
- **Contact** – Contact form

### Authentication
- Register (Learner / Instructor)
- Login / Logout
- Forgot Password / Reset Password

### Learner
- Dashboard – Enrolled courses, progress tracker
- Course Page – Lessons, mark complete, embedded videos + links
- Course Test – Quiz/test after enrolling
- Profile – Update info, change password, enrollment history
- Wishlist – Save courses for later
- Checkout – Enroll (free or dummy payment for paid courses)
- Reviews – Rate and review enrolled courses

### Instructor
- Dashboard – Course stats, students, earnings
- Upload Course – Create new courses
- Edit Course – Update details, add/remove lessons
- Students List – View enrolled students per course

### Admin
- Dashboard – Users, courses, enrollments, revenue, contact messages

## Installation (XAMPP)

1. **Copy project** to `htdocs` folder (e.g. `htdocs/skill-academy`)

2. **Start XAMPP** – Apache and MySQL

3. **Create database:**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Import `database/schema.sql` or run it in the SQL tab

4. **Configure** `config/db.php` if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'skill_academy_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. **Base path**: `config/config.php` auto-detects `BASE_PATH` from your XAMPP `DOCUMENT_ROOT`.  
   If you use a custom Apache vhost or unusual folder layout, you can still manually define `BASE_PATH` before loading the config.

6. **Run setup** to create demo users + sample content:  
   http://localhost/skill-academy/setup.php  
   - Admin: **admin@skillacademy.com** / **admin123**
   - Learner: **learner@skillacademy.com** / **learner123**
   - Instructor: **instructor@skillacademy.com** / **instructor123**

7. **Delete setup.php** after setup

8. **Access the app:**  
   http://localhost/skill-academy/

## Default Credentials

| Role      | Email                  | Password  |
|-----------|------------------------|-----------|
| Admin     | admin@skillacademy.com | admin123  |
| Learner   | learner@skillacademy.com | learner123 |
| Instructor | instructor@skillacademy.com | instructor123 |

## Project Structure

```
├── config/          # db.php, config.php
├── includes/        # header.php, footer.php, auth.php
├── public/          # about, catalog, course-detail, contact
├── auth/            # login, register, forgot-password, reset-password
├── learner/         # dashboard, course, profile, wishlist, checkout
├── instructor/      # dashboard, upload-course, edit-course, students
├── admin/           # dashboard
├── api/             # wishlist.php, review.php
├── assets/          # css/style.css, js/main.js
├── database/        # schema.sql
├── index.php        # Home
└── setup.php        # One-time admin setup (delete after use)
```

## Payments

Currently uses a **dummy payment** flow. To integrate real payments:
- Replace logic in `learner/checkout.php` with Stripe/PayPal SDK
- Update `config` with API keys

## Security Notes

- Passwords hashed with `password_hash()` (bcrypt)
- Prepared statements for all SQL
- Session-based authentication
- Role-based access (learner, instructor, admin)
