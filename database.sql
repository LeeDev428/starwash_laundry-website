-- StarWash Laundry Website Database Schema
-- For use with HeidiSQL/MySQL in Laragon

-- Create database
CREATE DATABASE IF NOT EXISTS starwash_laundry;
USE starwash_laundry;

-- Users table with role-based access
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'seller') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Services table (for sellers to add their services)
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders table (for users to place orders)
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    seller_id INT NOT NULL,
    service_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pickup_date DATE,
    delivery_date DATE,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Insert default admin/seller account
INSERT INTO users (username, email, password, full_name, phone, role) VALUES 
('admin', 'admin@starwash.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'StarWash Admin', '+1234567890', 'seller');

-- Insert sample regular user
INSERT INTO users (username, email, password, full_name, phone, role) VALUES 
('customer1', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Customer', '+1234567891', 'user');

-- Insert sample services
INSERT INTO services (seller_id, service_name, description, price, duration) VALUES 
(1, 'Wash & Fold', 'Professional washing and folding service', 15.99, '24 hours'),
(1, 'Dry Cleaning', 'Premium dry cleaning for delicate items', 25.99, '48 hours'),
(1, 'Express Wash', 'Quick turnaround washing service', 19.99, '6 hours'),
(1, 'Ironing Service', 'Professional ironing and pressing', 12.99, '12 hours');

-- Time slots table for appointment scheduling
CREATE TABLE time_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slot_time TIME NOT NULL,
    slot_label VARCHAR(20) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert available time slots
INSERT INTO time_slots (slot_time, slot_label) VALUES 
('08:00:00', '8:00 AM'),
('09:00:00', '9:00 AM'),
('10:00:00', '10:00 AM'),
('11:00:00', '11:00 AM'),
('12:00:00', '12:00 PM'),
('13:00:00', '1:00 PM'),
('14:00:00', '2:00 PM'),
('15:00:00', '3:00 PM'),
('16:00:00', '4:00 PM'),
('17:00:00', '5:00 PM');

-- Enhanced appointments table with better slot management
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    time_slot_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_datetime DATETIME NOT NULL,
    pickup_address TEXT NOT NULL,
    delivery_address TEXT,
    special_instructions TEXT,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    total_price DECIMAL(10,2) NOT NULL,
    confirmed_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (time_slot_id) REFERENCES time_slots(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_appointment_slot (appointment_date, time_slot_id)
);

-- Insert sample appointments with proper slot references
INSERT INTO appointments (user_id, service_id, time_slot_id, appointment_date, appointment_datetime, pickup_address, delivery_address, special_instructions, total_price, status) VALUES 
(2, 1, 3, '2025-09-25', '2025-09-25 10:00:00', '123 Main Street, City', '123 Main Street, City', 'Handle with care', 15.99, 'confirmed'),
(2, 2, 7, '2025-09-26', '2025-09-26 14:00:00', '456 Oak Avenue, City', '456 Oak Avenue, City', 'Delicate fabrics', 25.99, 'pending'),
(2, 3, 5, '2025-09-27', '2025-09-27 12:00:00', '789 Pine Street, City', '789 Pine Street, City', 'Express service needed', 19.99, 'pending');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_services_seller ON services(seller_id);
CREATE INDEX idx_services_status ON services(status);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_seller ON orders(seller_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_appointments_user ON appointments(user_id);
CREATE INDEX idx_appointments_service ON appointments(service_id);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_appointments_datetime ON appointments(appointment_datetime);