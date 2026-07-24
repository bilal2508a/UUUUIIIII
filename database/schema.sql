-- Mehmaan Hub Database Schema
-- MySQL Database for Property Booking Platform

CREATE DATABASE IF NOT EXISTS mehmaan_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mehmaan_hub;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('tenant', 'owner', 'admin') DEFAULT 'tenant',
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    property_type ENUM('apartment', 'house', 'room', 'studio', 'villa') DEFAULT 'apartment',
    address VARCHAR(255),
    city VARCHAR(100),
    area VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    price_period ENUM('per_day','per_month','both') NOT NULL DEFAULT 'per_month',
    price_per_day DECIMAL(10,2) DEFAULT NULL,
    bedrooms INT DEFAULT 1,
    bathrooms INT DEFAULT 1,
    area_sqft INT,
    is_furnished TINYINT(1) DEFAULT 0,
    has_parking TINYINT(1) DEFAULT 0,
    has_wifi TINYINT(1) DEFAULT 0,
    has_ac TINYINT(1) DEFAULT 0,
    has_generator TINYINT(1) DEFAULT 0,
    has_kitchen TINYINT(1) DEFAULT 0,
    has_swimming_pool TINYINT(1) DEFAULT 0,
    has_gym TINYINT(1) DEFAULT 0,
    has_security TINYINT(1) DEFAULT 0,
    has_elevator TINYINT(1) DEFAULT 0,
    has_garden TINYINT(1) DEFAULT 0,
    has_heating TINYINT(1) DEFAULT 0,
    has_cctv TINYINT(1) DEFAULT 0,
    status ENUM('available', 'rented', 'inactive') DEFAULT 'available',
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Property images table
CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    tenant_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    commission_rate DECIMAL(5,2) DEFAULT 10.00,
    commission_amount DECIMAL(10,2) DEFAULT 0.00,
    owner_payout DECIMAL(10,2) DEFAULT 0.00,
    refund_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded', 'partial_refund') DEFAULT 'unpaid',
    notes TEXT,
    cancelled_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, property_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Password resets (OTP-based)
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_expires (user_id, expires_at)
);

-- =============================================
-- SAMPLE DATA
-- =============================================

-- Admin user (password: admin123)
INSERT IGNORE INTO users (id, name, email, password, role) VALUES
(1, 'Admin', 'admin@mehmaanhub.com', '$2y$12$HZklDQtjGhSVmeD60gkp9.xmJSUcNUpAgZC/4ZzKSmGOwIO.OA1TC', 'admin');

-- Owner 1 (password: owner123)
INSERT IGNORE INTO users (id, name, email, password, phone, role) VALUES
(2, 'Ahmed Khan', 'owner@mehmaanhub.com', '$2y$12$jGIlyrN7moE/Lv5VhTpWI.Ck6jAUEN/i2U8tXzhVAdiSGJTUmpYra', '03001234567', 'owner');

-- Owner 2 (password: owner123)
INSERT IGNORE INTO users (id, name, email, password, phone, role) VALUES
(3, 'Bilal Raza', 'bilal@mehmaanhub.com', '$2y$12$jGIlyrN7moE/Lv5VhTpWI.Ck6jAUEN/i2U8tXzhVAdiSGJTUmpYra', '03019876543', 'owner');

-- Owner 3 (password: owner123)
INSERT IGNORE INTO users (id, name, email, password, phone, role) VALUES
(4, 'Fatima Sheikh', 'fatima@mehmaanhub.com', '$2y$12$jGIlyrN7moE/Lv5VhTpWI.Ck6jAUEN/i2U8tXzhVAdiSGJTUmpYra', '03214567890', 'owner');

-- Tenant (password: tenant123)
INSERT IGNORE INTO users (id, name, email, password, phone, role) VALUES
(5, 'Sara Ali', 'tenant@mehmaanhub.com', '$2y$12$ay2eVDz5IR4PhmTkSwBDPeZeyIvWW8UfZIQVDC84ckfeuvZyUCtum', '03009876543', 'tenant');

-- =============================================
-- PROPERTIES
-- =============================================

INSERT IGNORE INTO properties (id, owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, has_kitchen, has_swimming_pool, has_gym, has_security, has_elevator, has_garden, has_heating, has_cctv, status, featured) VALUES
(1, 2, 'Luxury 2 Bed Apartment in Gulberg', 'Beautifully furnished apartment in the heart of Gulberg. Spacious living room, modern kitchen, and balcony with city view. Close to restaurants, shopping malls, and public transport. 24/7 security and backup generator.', 'apartment', 'Main Boulevard, Gulberg III', 'Lahore', 'Gulberg', 65000.00, 'per_month', NULL, 2, 2, 1200, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 0, 1, 1, 'available', 1),
(2, 2, 'Spacious 3 Bed House in DHA Phase 5', 'A beautiful family house in DHA Phase 5 with lush green garden, car porch, and modern amenities. Located in a peaceful and secure neighborhood. Close to schools, parks, and commercial area.', 'house', 'Block H, DHA Phase 5', 'Lahore', 'DHA', 120000.00, 'per_month', NULL, 3, 3, 2500, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 0, 1, 'available', 1),
(3, 3, 'Modern Studio Apartment in Bahria Town', 'Compact and modern studio apartment perfect for singles or couples. Fully furnished with modern appliances, located in Bahria Town with access to all amenities including gym, swimming pool, and shopping center.', 'studio', 'Sector C, Bahria Town', 'Rawalpindi', 'Bahria Town', 35000.00, 'both', 2000.00, 1, 1, 600, 1, 1, 1, 1, 0, 1, 1, 1, 0, 0, 0, 1, 0, 'available', 0),
(4, 3, '5 Bed Luxury Villa in DHA Phase 6', 'Stunning luxury villa with 5 bedrooms, private swimming pool, landscaped garden, and double car garage. Premium location in DHA Phase 6 with easy access to main roads and commercial areas.', 'villa', 'Block B, DHA Phase 6', 'Lahore', 'DHA', 250000.00, 'per_month', NULL, 5, 5, 5000, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 0, 1, 1, 'available', 1),
(5, 4, 'Furnished Single Room in Johar Town', 'Clean and furnished single room available for rent in Johar Town. Shared kitchen and bathroom. Ideal for students or working professionals. Close to universities and public transport.', 'room', 'Block A2, Johar Town', 'Lahore', 'Johar Town', 15000.00, 'per_day', NULL, 1, 1, 200, 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(6, 4, '2 Bed Apartment in Model Town', 'Well-maintained 2 bedroom apartment in Model Town with spacious rooms, attached bathrooms, and a modern kitchen. Balcony with garden view. Walking distance to Model Town Park and commercial market.', 'apartment', 'Block B, Model Town', 'Lahore', 'Model Town', 45000.00, 'per_month', NULL, 2, 2, 1100, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, 0, 0, 'available', 0),
(7, 2, '4 Bed Family House in Johar Town', 'Spacious 4 bedroom house with large drawing room, dining area, and kitchen. Car porch for 2 vehicles. Located in a prime location of Johar Town near Block A2 commercial area.', 'house', 'Block A3, Johar Town', 'Lahore', 'Johar Town', 95000.00, 'both', 4000.00, 4, 3, 3000, 0, 1, 0, 1, 1, 1, 0, 0, 1, 0, 0, 1, 1, 'available', 0),
(8, 3, 'Cozy Studio in Gulberg II', 'A cozy and affordable studio apartment in Gulberg II. Perfect for bachelors or students. Walking distance to Liberty Market and Main Boulevard. Fully furnished with AC and WiFi.', 'studio', 'Gulberg II, near Liberty Market', 'Lahore', 'Gulberg', 28000.00, 'per_day', NULL, 1, 1, 500, 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(9, 2, 'Modern 3 Bed Apartment in Clifton', 'Elegant apartment in Clifton with sea breeze, modern fittings, and spacious rooms. Close to Sea View, restaurants, and shopping malls. Building has elevator and 24/7 security.', 'apartment', 'Block 2, Clifton', 'Karachi', 'Clifton', 85000.00, 'per_month', NULL, 3, 2, 1500, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 0, 1, 'available', 1),
(10, 3, 'Luxury Villa in DHA Karachi', 'Stunning 6 bed villa in DHA Karachi with private pool, gym, garden, and smart home features. Prime location near DHA Phase 8 commercial.', 'villa', 'Phase 8, DHA', 'Karachi', 'DHA', 350000.00, 'per_month', NULL, 6, 6, 6000, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'available', 1),
(11, 4, 'Furnished 2 Bed Flat in Gulshan', 'Nicely furnished 2 bedroom flat in Gulshan-e-Iqbal. Close to university, markets, and transport. WiFi, AC, and generator included.', 'apartment', 'Block 7, Gulshan-e-Iqbal', 'Karachi', 'Gulshan', 40000.00, 'per_month', NULL, 2, 2, 1000, 1, 0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(12, 2, 'Single Room in F-11 Islamabad', 'Comfortable single room in F-11 Islamabad. Ideal for students or professionals. Shared kitchen and bath. Near metro station and markets.', 'room', 'F-11/3', 'Islamabad', 'F-11', 18000.00, 'per_day', NULL, 1, 1, 250, 1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 0, 0, 1, 'available', 0),
(13, 3, '3 Bed House in Bahria Town Islamabad', 'Beautiful 3 bedroom house in Bahria Town Phase 7 Islamabad. Modern kitchen, garden, parking for 2 cars. Near school and commercial center.', 'house', 'Phase 7, Bahria Town', 'Islamabad', 'Bahria Town', 110000.00, 'both', 5000.00, 3, 3, 2200, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 1, 'available', 0),
(14, 4, 'Studio in F-7 Markaz Islamabad', 'Modern studio in the heart of F-7 Markaz. Walking distance to cafes, shops, and Centaurus Mall shuttle. Fully furnished with all amenities.', 'studio', 'F-7 Markaz', 'Islamabad', 'F-7', 30000.00, 'per_month', NULL, 1, 1, 550, 1, 0, 1, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1, 'available', 0),
(15, 2, '4 Bed Villa in E-11 Islamabad', 'Spacious 4 bedroom villa in E-11 with garden, parking, and modern amenities. Quiet and secure neighborhood near Margalla Hills.', 'villa', 'E-11/2', 'Islamabad', 'E-11', 200000.00, 'per_month', NULL, 4, 4, 3500, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 0, 1, 1, 'available', 1),
(16, 3, '2 Bed Apartment in Saddar Karachi', 'Affordable 2 bed apartment in Saddar near Empress Market. Great for families. Close to transport and commercial areas.', 'apartment', 'Saddar', 'Karachi', 'Saddar', 35000.00, 'per_month', NULL, 2, 1, 900, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(17, 4, 'Furnished Room in G-9 Islamabad', 'Clean furnished room in G-9/4 Islamabad. Shared kitchen and bath. Near Melody Market and metro bus. Good for students.', 'room', 'G-9/4', 'Islamabad', 'G-9', 12000.00, 'per_day', NULL, 1, 1, 200, 1, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(18, 2, '3 Bed House in Township Lahore', 'Spacious 3 bed house in Township Lahore. Large drawing room, kitchen, and car porch. Near main road and commercial market.', 'house', 'Township, Block C', 'Lahore', 'Township', 70000.00, 'per_month', NULL, 3, 2, 2000, 0, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 1, 0, 'available', 0),
(19, 3, 'Modern Studio in Tariq Road Karachi', 'Stylish studio apartment on Tariq Road. Fully furnished, AC, WiFi, and elevator in building. Great for singles or couples.', 'studio', 'Tariq Road', 'Karachi', 'Tariq Road', 25000.00, 'both', 1500.00, 1, 1, 450, 1, 0, 1, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1, 'available', 0),
(20, 4, '5 Bed Luxury House in DHA Phase 4 Lahore', 'Premium 5 bedroom house in DHA Phase 4. Modern architecture, garden, swimming pool, and gym. Gated community with 24/7 security.', 'house', 'Block E, DHA Phase 4', 'Lahore', 'DHA', 300000.00, 'per_month', NULL, 5, 5, 4500, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 'available', 1);

-- =============================================
-- PROPERTY IMAGES (using Pexels stock photos)
-- =============================================

INSERT IGNORE INTO property_images (id, property_id, image_path, is_primary, sort_order) VALUES
(1, 1, 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(2, 1, 'https://images.pexels.com/photos/1571468/pexels-photo-1571468.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(3, 1, 'https://images.pexels.com/photos/1571453/pexels-photo-1571453.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(4, 1, 'https://images.pexels.com/photos/1571463/pexels-photo-1571463.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 3),
(5, 2, 'https://images.pexels.com/photos/106399/pexels-photo-106399.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(6, 2, 'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(7, 2, 'https://images.pexels.com/photos/1396132/pexels-photo-1396132.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(8, 2, 'https://images.pexels.com/photos/1396128/pexels-photo-1396128.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 3),
(9, 3, 'https://images.pexels.com/photos/3935350/pexels-photo-3935350.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(10, 3, 'https://images.pexels.com/photos/3935352/pexels-photo-3935352.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(11, 3, 'https://images.pexels.com/photos/3935354/pexels-photo-3935354.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(12, 4, 'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(13, 4, 'https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(14, 4, 'https://images.pexels.com/photos/1396132/pexels-photo-1396132.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(15, 4, 'https://images.pexels.com/photos/106399/pexels-photo-106399.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 3),
(16, 4, 'https://images.pexels.com/photos/1396128/pexels-photo-1396128.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 4),
(17, 5, 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(18, 5, 'https://images.pexels.com/photos/271639/pexels-photo-271639.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(19, 5, 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(20, 6, 'https://images.pexels.com/photos/7587425/pexels-photo-7587425.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(21, 6, 'https://images.pexels.com/photos/7587426/pexels-photo-7587426.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(22, 6, 'https://images.pexels.com/photos/7587427/pexels-photo-7587427.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(23, 6, 'https://images.pexels.com/photos/7587428/pexels-photo-7587428.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 3),
(24, 7, 'https://images.pexels.com/photos/259588/pexels-photo-259588.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(25, 7, 'https://images.pexels.com/photos/1396132/pexels-photo-1396132.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(26, 7, 'https://images.pexels.com/photos/106399/pexels-photo-106399.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(27, 7, 'https://images.pexels.com/photos/259588/pexels-photo-259588.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 3),
(28, 8, 'https://images.pexels.com/photos/3935350/pexels-photo-3935350.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(29, 8, 'https://images.pexels.com/photos/3935352/pexels-photo-3935352.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(30, 8, 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2);

INSERT IGNORE INTO properties (id, owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, has_kitchen, has_swimming_pool, has_gym, has_security, has_elevator, has_garden, has_heating, has_cctv, status, featured) VALUES
(21, 2, 'Modern 3 Bed Apartment in Faisal Town', 'Spacious 3 bedroom apartment in Faisal Town with modern fittings, attached bathrooms, and a large living area. Close to Canal Road and commercial markets.', 'apartment', 'Faisal Town, near Canal Road', 'Faisalabad', 'Faisal Town', 55000.00, 'per_month', NULL, 3, 2, 1400, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 0, 1, 'available', 0),
(22, 3, 'Luxury 4 Bed House in People Colony', 'Beautiful 4 bedroom house in People Colony B-block with large drawing room, dining area, and landscaped garden. Car porch for 2 vehicles. Prime location.', 'house', 'People Colony, Block B', 'Faisalabad', 'People Colony', 90000.00, 'per_month', NULL, 4, 3, 2800, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 0, 'available', 0),
(23, 4, 'Furnished Studio in D Ground', 'Compact and fully furnished studio apartment in D Ground market area. Perfect for bachelors or working professionals. Walking distance to restaurants and shops.', 'studio', 'D Ground, People Colony', 'Faisalabad', 'D Ground', 25000.00, 'per_day', NULL, 1, 1, 450, 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(24, 2, '5 Bed Luxury Villa in Citi Housing', 'Stunning 5 bed villa in Citi Housing Faisalabad with private swimming pool, gym, garden, and smart home features. Premium location with 24/7 security.', 'villa', 'Citi Housing, Phase 1', 'Faisalabad', 'Citi Housing', 280000.00, 'per_month', NULL, 5, 5, 5500, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 'available', 1),
(25, 3, 'Single Room in Madina Town', 'Clean and affordable single room for rent in Madina Town. Shared kitchen and bathroom. Ideal for students. Near bus stand and markets.', 'room', 'Madina Town, Block C', 'Faisalabad', 'Madina Town', 12000.00, 'per_day', NULL, 1, 1, 180, 1, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(26, 4, '2 Bed Apartment in Model Town Faisalabad', 'Well-maintained 2 bedroom apartment in Model Town with spacious rooms and modern kitchen. Balcony with street view. Near Model Town Bazaar.', 'apartment', 'Model Town, Block A', 'Faisalabad', 'Model Town', 38000.00, 'per_month', NULL, 2, 2, 950, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, 0, 0, 'available', 0),
(27, 2, '3 Bed House in Kohinoor City', 'Spacious 3 bedroom house in Kohinoor City with modern amenities, car porch, and garden. Near Kohinoor Square and Faisalabad Motorway.', 'house', 'Kohinoor City, Block D', 'Faisalabad', 'Kohinoor City', 75000.00, 'both', 3500.00, 3, 2, 2200, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 1, 0, 'available', 0),
(28, 3, 'Modern 2 Bed Apartment in Multan Cantt', 'Elegant 2 bedroom apartment in Multan Cantt with modern fittings and spacious rooms. Close to Multan Airport and commercial area.', 'apartment', 'Cantt, near Airport Road', 'Multan', 'Cantt', 48000.00, 'per_month', NULL, 2, 2, 1100, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 0, 1, 'available', 0),
(29, 4, '4 Bed Family House in Gulgasht Colony', 'Beautiful 4 bedroom house in Gulgasht Colony with large drawing room, dining area, kitchen, and garden. Car porch for 2 vehicles.', 'house', 'Gulgasht Colony, Block A', 'Multan', 'Gulgasht', 85000.00, 'per_month', NULL, 4, 3, 2600, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 0, 'available', 0),
(30, 2, 'Furnished Studio in Bosan Road', 'Compact and fully furnished studio apartment near Bosan Road. Perfect for students of NUST Multan campus. WiFi and AC included.', 'studio', 'Bosan Road, near NUST', 'Multan', 'Bosan Road', 22000.00, 'per_day', NULL, 1, 1, 400, 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(31, 3, '5 Bed Luxury Villa in DHA Multan', 'Stunning 5 bed villa in DHA Multan with private swimming pool, landscaped garden, gym, and double car garage. Premium location with 24/7 security.', 'villa', 'DHA Multan, Phase 1', 'Multan', 'DHA', 220000.00, 'per_month', NULL, 5, 4, 4800, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 'available', 1),
(32, 4, 'Single Room in Shah Rukn-e-Alam', 'Clean and affordable single room near Shah Rukn-e-Alam Colony. Shared kitchen and bath. Near university and markets.', 'room', 'Shah Rukn-e-Alam Colony', 'Multan', 'Shah Rukn-e-Alam', 10000.00, 'per_day', NULL, 1, 1, 200, 1, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(33, 2, '2 Bed Apartment in WAPDA Town Multan', 'Well-maintained 2 bedroom apartment in WAPDA Town with spacious rooms and modern kitchen. Near WAPDA Town commercial market.', 'apartment', 'WAPDA Town, Block B', 'Multan', 'WAPDA Town', 35000.00, 'per_month', NULL, 2, 2, 900, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, 0, 0, 'available', 0),
(34, 3, 'Modern 3 Bed Apartment in Latifabad', 'Spacious 3 bedroom apartment in Latifabad Unit 7 with modern fittings and attached bathrooms. Close to Hyderabad main bazaar.', 'apartment', 'Latifabad, Unit 7', 'Hyderabad', 'Latifabad', 42000.00, 'per_month', NULL, 3, 2, 1300, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 0, 1, 'available', 0),
(35, 4, '4 Bed House in Qasimabad', 'Beautiful 4 bedroom house in Qasimabad with large drawing room, dining area, and garden. Car porch for 2 vehicles. Near Hyderabad bypass.', 'house', 'Qasimabad, Block C', 'Hyderabad', 'Qasimabad', 70000.00, 'per_month', NULL, 4, 3, 2400, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 0, 'available', 0),
(36, 2, 'Furnished Studio in Auto Bhan', 'Compact and fully furnished studio apartment near Auto Bhan Road. Perfect for working professionals. WiFi and AC included.', 'studio', 'Auto Bhan, near Niaz Stadium', 'Hyderabad', 'Auto Bhan', 20000.00, 'per_day', NULL, 1, 1, 420, 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(37, 3, 'Single Room in Kohsar Hyderabad', 'Clean and affordable single room in Kohsar area. Shared kitchen and bath. Ideal for students. Near markets and transport.', 'room', 'Kohsar, near Thandi Sarak', 'Hyderabad', 'Kohsar', 9000.00, 'per_day', NULL, 1, 1, 180, 1, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(38, 4, '3 Bed Apartment in Gulistan-e-Jauhar', 'Well-maintained 3 bedroom apartment in Gulistan-e-Jauhar with spacious rooms and modern kitchen. Near university and commercial area.', 'apartment', 'Gulistan-e-Jauhar, Block 18', 'Hyderabad', 'Gulistan-e-Jauhar', 38000.00, 'both', 1800.00, 3, 2, 1200, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, 0, 0, 'available', 0),
(39, 2, 'Modern 3 Bed Apartment in Hayatabad', 'Spacious 3 bedroom apartment in Hayatabad Phase 3 with modern fittings and attached bathrooms. Close to PIMS hospital and commercial center.', 'apartment', 'Hayatabad, Phase 3, Sector P-3', 'Peshawar', 'Hayatabad', 52000.00, 'per_month', NULL, 3, 2, 1350, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 0, 1, 'available', 0),
(40, 3, '4 Bed Family House in University Town', 'Beautiful 4 bedroom house in University Town Peshawar with large drawing room, dining area, and garden. Near Peshawar University.', 'house', 'University Town, Street 4', 'Peshawar', 'University Town', 80000.00, 'per_month', NULL, 4, 3, 2500, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 0, 'available', 0),
(41, 4, 'Furnished Studio in Saddar Peshawar', 'Compact and fully furnished studio apartment in Saddar area. Perfect for bachelors or working professionals. Near cantt and commercial area.', 'studio', 'Saddar, near Cantt', 'Peshawar', 'Saddar', 24000.00, 'per_day', NULL, 1, 1, 450, 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(42, 2, '5 Bed Luxury Villa in DHA Peshawar', 'Stunning 5 bed villa in DHA Peshawar with private swimming pool, gym, garden, and smart home features. Premium location with 24/7 security.', 'villa', 'DHA Peshawar, Phase 1', 'Peshawar', 'DHA', 200000.00, 'per_month', NULL, 5, 5, 5000, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 'available', 1),
(43, 3, 'Single Room in Tehkal', 'Clean and affordable single room in Tehkal area near University Road. Shared kitchen and bath. Ideal for students.', 'room', 'Tehkal, near University Road', 'Peshawar', 'Tehkal', 11000.00, 'per_day', NULL, 1, 1, 200, 1, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(44, 4, '2 Bed Apartment in Cantt Quetta', 'Well-maintained 2 bedroom apartment in Quetta Cantt with spacious rooms and modern kitchen. Near cantt and commercial area.', 'apartment', 'Cantt, near Airport Road', 'Quetta', 'Cantt', 32000.00, 'per_month', NULL, 2, 2, 850, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, 0, 0, 'available', 0),
(45, 2, '3 Bed House in Jinnah Town Quetta', 'Spacious 3 bedroom house in Jinnah Town with modern amenities, car porch, and garden. Near Jinnah Town commercial market.', 'house', 'Jinnah Town, Block A', 'Quetta', 'Jinnah Town', 60000.00, 'both', 2500.00, 3, 2, 2000, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 1, 0, 'available', 0),
(46, 3, 'Furnished Studio in Brewery Road', 'Compact and fully furnished studio apartment near Brewery Road. Perfect for working professionals. WiFi and AC included.', 'studio', 'Brewery Road, near BMC', 'Quetta', 'Brewery Road', 18000.00, 'per_day', NULL, 1, 1, 380, 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(47, 4, 'Single Room in Pashtun Bagh', 'Clean and affordable single room in Pashtun Bagh area. Shared kitchen and bath. Near markets and transport.', 'room', 'Pashtun Bagh, near Jail Road', 'Quetta', 'Pashtun Bagh', 8000.00, 'per_day', NULL, 1, 1, 160, 1, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0),
(48, 2, 'Modern 3 Bed Apartment in Citi Housing Gujranwala', 'Spacious 3 bedroom apartment in Citi Housing Gujranwala with modern fittings and attached bathrooms. Close to GT Road and commercial area.', 'apartment', 'Citi Housing, Phase 2', 'Gujranwala', 'Citi Housing', 45000.00, 'per_month', NULL, 3, 2, 1250, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 0, 1, 'available', 0),
(49, 3, '4 Bed House in Wapda Town Gujranwala', 'Beautiful 4 bedroom house in WAPDA Town Gujranwala with large drawing room, dining area, and garden. Near WAPDA Town commercial market.', 'house', 'WAPDA Town, Block B', 'Gujranwala', 'WAPDA Town', 68000.00, 'per_month', NULL, 4, 3, 2300, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 0, 'available', 0),
(50, 4, 'Furnished 2 Bed Apartment in Sialkot Cantt', 'Nicely furnished 2 bedroom apartment in Sialkot Cantt. Close to Sialkot Airport, markets, and transport. WiFi, AC, and generator included.', 'apartment', 'Cantt, near Paris Road', 'Sialkot', 'Cantt', 40000.00, 'both', 2000.00, 2, 2, 1000, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 'available', 0);

-- =============================================
-- SAMPLE REVIEWS
-- =============================================

INSERT IGNORE INTO reviews (id, property_id, user_id, rating, comment) VALUES
(1, 1, 5, 5, 'Excellent apartment! Very clean and well-maintained. The owner is very cooperative.'),
(2, 1, 5, 4, 'Great location and nice facilities. WiFi and AC work perfectly.'),
(3, 3, 5, 5, 'Perfect studio for a single person. Everything you need in one place.'),
(4, 4, 5, 5, 'Amazing villa with beautiful pool. Highly recommended for families.');
