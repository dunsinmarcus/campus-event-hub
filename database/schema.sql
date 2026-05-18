-- Campus Event Hub Database Schema
-- This script creates all necessary tables for the event management system

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Event Categories Table
CREATE TABLE IF NOT EXISTS event_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    color VARCHAR(7),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events Table
CREATE TABLE IF NOT EXISTS events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category_id INT,
    location VARCHAR(255) NOT NULL,
    event_date DATETIME NOT NULL,
    end_date DATETIME,
    capacity INT DEFAULT 100,
    organizer_id INT,
    image_url VARCHAR(500),
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES event_categories(id),
    FOREIGN KEY (organizer_id) REFERENCES users(id),
    INDEX idx_event_date (event_date),
    INDEX idx_status (status)
);

-- RSVPs Table
CREATE TABLE IF NOT EXISTS rsvps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('interested', 'confirmed', 'declined') DEFAULT 'interested',
    rsvp_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_event_user (event_id, user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_event_id (event_id)
);

-- Reminders Table
CREATE TABLE IF NOT EXISTS reminders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    reminder_type ENUM('email', 'sms', 'in_app') DEFAULT 'email',
    reminder_time INT DEFAULT 24,
    reminder_unit ENUM('hours', 'minutes') DEFAULT 'hours',
    is_sent BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reminder (event_id, user_id, reminder_type),
    INDEX idx_sent (is_sent),
    INDEX idx_reminder_time (reminder_time)
);

-- In-app Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    event_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
);

-- Create indexes for better query performance
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_student_id ON users(student_id);

-- Insert default event categories
INSERT INTO event_categories (name, description, color) VALUES
('Seminar', 'Educational seminars and workshops', '#3498db'),
('Sports', 'Sports events and competitions', '#e74c3c'),
('Cultural', 'Cultural events and performances', '#f39c12'),
('Social', 'Social gatherings and meet-ups', '#9b59b6'),
('Career', 'Career fairs and recruitment drives', '#27ae60'),
('Technology', 'Tech talks and coding competitions', '#34495e');

-- Create a trigger to update event status automatically
DELIMITER //

CREATE TRIGGER update_event_status_to_ongoing
BEFORE SELECT ON events
FOR EACH ROW
BEGIN
    IF NEW.event_date <= NOW() AND NEW.end_date >= NOW() THEN
        SET NEW.status = 'ongoing';
    ELSEIF NEW.end_date < NOW() THEN
        SET NEW.status = 'completed';
    ELSEIF NEW.event_date > NOW() THEN
        SET NEW.status = 'upcoming';
    END IF;
END //

DELIMITER ;
