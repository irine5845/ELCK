
CREATE DATABASE IF NOT EXISTS elck_kisumu
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE elck_kisumu;

-- Administrators
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin','editor') DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Church information
CREATE TABLE church_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    church_name VARCHAR(150) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(30),
    email VARCHAR(150),
    mission TEXT,
    vision TEXT,
    about_text TEXT,
    paybill VARCHAR(50),
    till_number VARCHAR(50),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

-- Church leadership
CREATE TABLE leaders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    position VARCHAR(150) NOT NULL,
    biography TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sermons
CREATE TABLE sermons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    preacher VARCHAR(150),
    description TEXT,
    video_url VARCHAR(500),
    sermon_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATETIME,
    location VARCHAR(200),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Gallery
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    image VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Membership registration
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    phone VARCHAR(30) NOT NULL,
    gender VARCHAR(30),
    date_of_birth DATE,
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Prayer requests
CREATE TABLE prayer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150),
    email VARCHAR(150),
    phone VARCHAR(30),
    request TEXT NOT NULL,
    is_private TINYINT(1) DEFAULT 1,
    status ENUM('pending','prayed_for','completed')
        DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Announcements
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial church information
INSERT INTO church_info
(church_name, address, mission, vision, about_text)
VALUES
(
 'ELCK KISUMU',
 'Kisumu, Kenya',
 'To be updated with the official church mission.',
 'To be updated with the official church vision.',
 'Welcome to ELCK KISUMU. We are committed to worship,
 fellowship, spiritual growth and serving our community.'
);
