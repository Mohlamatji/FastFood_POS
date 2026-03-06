# Fast Food POS System 🍔

A simple PHP-based Point of Sale (POS) system designed for fast food businesses.  
This project currently includes **secure user authentication** with registration and login functionality.

---

## ✨ Features
- User registration with **bcrypt password hashing** (`password_hash`)
- Secure login using **password verification** (`password_verify`)
- Unique email validation (no duplicate accounts)
- Session management for logged-in users
- Bootstrap styling for a clean, responsive UI

---

## 📂 Project Structure
- `register.php` → Handles new account creation
- `login.php` → Handles user login
- `config.php` → Database connection settings
- `dashboard.php` → Example landing page after login
- `users` table → Stores `id`, `username`, `email`, `password`, `created_at`

---

## 🛠️ Requirements
- PHP 8+
- MySQL/MariaDB
- Web server (Apache/Nginx)
- Composer (optional, for dependency management)

---

## 🚀 Getting Started
1. Clone or download this repository.
2. Import the `users` table schema:

   ```sql
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(100) NOT NULL UNIQUE,
       email VARCHAR(255) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

