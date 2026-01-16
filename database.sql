CREATE DATABASE wedding_rsvp;

USE wedding_rsvp;

CREATE TABLE rsvps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    attending ENUM('Yes','No') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
