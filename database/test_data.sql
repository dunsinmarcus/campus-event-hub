-- Campus Event Hub - Sample Test Data
-- Run this after importing schema.sql to populate test data

-- Insert sample students
INSERT INTO users (student_id, name, email, password, phone, department) VALUES
('STU001', 'John Doe', 'john@student.edu', '$2y$10$..', '555-0001', 'Computer Science'),
('STU002', 'Jane Smith', 'jane@student.edu', '$2y$10$..', '555-0002', 'Engineering'),
('STU003', 'Mike Johnson', 'mike@student.edu', '$2y$10$..', '555-0003', 'Business'),
('STU004', 'Sarah Williams', 'sarah@student.edu', '$2y$10$..', '555-0004', 'Arts');

-- Insert sample organizers
INSERT INTO users (student_id, name, email, password, phone, department) VALUES
('ORG001', 'Event Admin', 'admin@campus.edu', '$2y$10$..', '555-9001', 'Administration'),
('ORG002', 'Club Leader', 'leader@campus.edu', '$2y$10$..', '555-9002', 'Student Affairs');

-- Insert sample events (dates should be in the future for testing)
INSERT INTO events (title, description, category_id, location, event_date, end_date, capacity, organizer_id, status) VALUES
('Annual Tech Summit 2026', 
 'Join us for the biggest technology conference of the year. Learn from industry experts, network with peers, and explore the latest innovations in tech. Sessions include AI, cloud computing, cybersecurity, and more.',
 6, 'Main Auditorium', '2026-06-15 09:00:00', '2026-06-15 17:00:00', 500, 5, 'upcoming'),

('Spring Sports Day',
 'Participate in various sports competitions including basketball, football, volleyball, and track & field. All students welcome. Prizes for winners!',
 2, 'Sports Complex', '2026-05-20 08:00:00', '2026-05-20 18:00:00', 300, 6, 'upcoming'),

('Career Fair 2026',
 'Meet representatives from top companies and explore career opportunities. Bring your resume and network with recruiters. Companies include Google, Microsoft, Amazon, and more.',
 5, 'Convention Center', '2026-06-01 10:00:00', '2026-06-01 16:00:00', 1000, 5, 'upcoming'),

('Cultural Night: Around the World',
 'Experience cultures from around the globe. Enjoy traditional food, music, dance, and art from different countries. A celebration of diversity and international friendship.',
 3, 'Student Union Hall', '2026-05-25 18:00:00', '2026-05-25 22:00:00', 200, 6, 'upcoming'),

('Introduction to Machine Learning',
 'Learn the basics of machine learning and AI. This seminar covers supervised learning, unsupervised learning, neural networks, and practical applications. Perfect for beginners.',
 6, 'Science Building Room 201', '2026-05-18 14:00:00', '2026-05-18 16:00:00', 100, 5, 'upcoming'),

('Welcome Social for New Students',
 'Meet fellow students and get to know the campus community. Enjoy refreshments, games, and fun activities. A perfect way to make new friends!',
 4, 'Outdoor Lawn', '2026-05-22 16:00:00', '2026-05-22 19:00:00', 150, 6, 'upcoming'),

('Academic Excellence Seminar',
 'Tips and tricks for academic success. Learn study techniques, time management, and how to succeed in college. Featuring successful alumni and faculty.',
 1, 'Library Auditorium', '2026-05-19 13:00:00', '2026-05-19 14:30:00', 75, 5, 'upcoming'),

('Chess Tournament Finals',
 'Compete in our annual chess tournament. Round-robin format with prizes for top finalists. All skill levels welcome.',
 4, 'Recreation Center', '2026-06-05 10:00:00', '2026-06-05 17:00:00', 50, 6, 'upcoming');

-- Insert sample RSVPs
INSERT INTO rsvps (event_id, user_id, status) VALUES
(1, 1, 'confirmed'),
(1, 2, 'confirmed'),
(1, 3, 'interested'),
(2, 1, 'confirmed'),
(2, 4, 'confirmed'),
(3, 2, 'confirmed'),
(3, 3, 'confirmed'),
(4, 4, 'confirmed'),
(4, 1, 'interested'),
(5, 2, 'confirmed'),
(6, 1, 'confirmed'),
(6, 3, 'confirmed'),
(6, 4, 'confirmed'),
(7, 2, 'confirmed'),
(8, 1, 'confirmed'),
(8, 2, 'confirmed'),
(8, 3, 'confirmed');

-- Insert sample reminders
INSERT INTO reminders (event_id, user_id, reminder_type, reminder_time, reminder_unit) VALUES
(1, 1, 'email', 24, 'hours'),
(1, 2, 'email', 12, 'hours'),
(2, 1, 'email', 24, 'hours'),
(3, 2, 'email', 48, 'hours'),
(4, 4, 'email', 24, 'hours'),
(5, 2, 'email', 24, 'hours'),
(6, 1, 'email', 12, 'hours'),
(8, 1, 'email', 24, 'hours');

-- Note: Password hashes above are placeholders
-- To create real password hashes, use PHP:
-- echo password_hash('password123', PASSWORD_BCRYPT);

-- Verify data insertion
SELECT 'Users:' as 'Category', COUNT(*) as 'Count' FROM users
UNION ALL
SELECT 'Events' as 'Category', COUNT(*) as 'Count' FROM events
UNION ALL
SELECT 'RSVPs' as 'Category', COUNT(*) as 'Count' FROM rsvps
UNION ALL
SELECT 'Reminders' as 'Category', COUNT(*) as 'Count' FROM reminders;
