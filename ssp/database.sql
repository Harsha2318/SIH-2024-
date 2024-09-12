create database scholarship_portal;
CREATE TABLE users (
    user_id VARCHAR(12) PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    aadhaar_number VARCHAR(12) UNIQUE,
    phone_number VARCHAR(10),
    gender VARCHAR(10),
    password_hash VARCHAR(255)
);
