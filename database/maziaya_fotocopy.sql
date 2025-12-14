-- Buat database
CREATE DATABASE IF NOT EXISTS maziaya_fotocopy;
USE maziaya_fotocopy;

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'kasir', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel services
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_page DECIMAL(10,2) NOT NULL,
    color_print BOOLEAN DEFAULT FALSE,
    min_pages INT DEFAULT 1,
    max_pages INT DEFAULT 1000,
    turnaround_time VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    service_id INT NOT NULL,
    file_path VARCHAR(255),
    original_filename VARCHAR(255),
    page_count INT NOT NULL,
    color_print BOOLEAN DEFAULT FALSE,
    binding_type VARCHAR(50),
    special_instructions TEXT,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_proof VARCHAR(255),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estimated_completion DATETIME,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- Tabel payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Insert data admin default
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@maziaya.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'),
('kasir', 'kasir@maziaya.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Maziaya', 'kasir');

-- Insert layanan default
INSERT INTO services (name, description, price_per_page, color_print, turnaround_time) VALUES 
('Fotokopi Hitam Putih', 'Fotokopi dokumen hitam putih dengan kualitas tinggi', 300.00, FALSE, '1-2 jam'),
('Fotokopi Berwarna', 'Fotokopi dokumen berwarna dengan hasil yang tajam', 1000.00, TRUE, '1-2 jam'),
('Print Hitam Putih', 'Print dokumen hitam putih dari file digital', 500.00, FALSE, '1-2 jam'),
('Print Berwarna', 'Print dokumen berwarna dari file digital', 1500.00, TRUE, '1-2 jam'),
('Jilid Soft Cover', 'Jilid dokumen dengan soft cover', 5000.00, FALSE, '2-3 jam'),
('Jilid Hard Cover', 'Jilid dokumen dengan hard cover', 10000.00, FALSE, '1 hari'),
('Laminasi', 'Laminasi dokumen dengan berbagai ukuran', 3000.00, FALSE, '1 jam'),
('Scan Dokumen', 'Scan dokumen ke format digital', 1000.00, FALSE, '30 menit');