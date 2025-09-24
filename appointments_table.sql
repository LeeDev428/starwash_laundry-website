-- Appointments Table DDL for StarWash Laundry System
-- This table handles appointment bookings between users and service providers
-- Fixed to match existing database structure

DROP TABLE IF EXISTS appointments;

CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    pickup_address TEXT NOT NULL,
    delivery_address TEXT,
    special_instructions TEXT,
    estimated_price DECIMAL(10,2),
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints (matching existing table structure)
    CONSTRAINT fk_appointments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_appointments_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    
    -- Indexes for better performance
    INDEX idx_user_appointments (user_id),
    INDEX idx_service_appointments (service_id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_appointment_status (status)
);

-- Add a test user for appointments (if doesn't exist)
INSERT IGNORE INTO users (username, email, password, full_name, phone, role) VALUES 
('testuser', 'user@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', '+1234567891', 'user');

-- Add some sample appointments data (using existing service IDs 1-4)
INSERT INTO appointments (user_id, service_id, appointment_date, appointment_time, pickup_address, delivery_address, special_instructions, estimated_price, status) VALUES 
(2, 1, '2025-09-25', '10:00:00', '123 Main St, City, State 12345', '123 Main St, City, State 12345', 'Please handle delicate items carefully', 15.99, 'confirmed'),
(2, 2, '2025-09-26', '14:30:00', '456 Oak Ave, City, State 12345', '456 Oak Ave, City, State 12345', 'Dry clean only suit and dress', 25.99, 'pending'),
(2, 3, '2025-09-24', '09:15:00', '789 Pine St, City, State 12345', '789 Pine St, City, State 12345', 'Express service needed', 19.99, 'in_progress'),
(2, 4, '2025-09-27', '16:00:00', '321 Elm St, City, State 12345', '321 Elm St, City, State 12345', 'Only shirts need ironing', 12.99, 'pending');