-- Create database
CREATE DATABASE IF NOT EXISTS final_project;
USE final_project;

-- Users table for registration/login system
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart items table for logged-in users (temporary storage before purchase)
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    size VARCHAR(10) DEFAULT 'M',
    qty INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_product (user_id, product_name)
);

-- Inventory table for stock management (tracks available quantities)
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(200) NOT NULL,
    size VARCHAR(10) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    UNIQUE KEY unique_product_size (product_name, size)
);

-- Orders table to store completed purchases (order summary)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table to store individual items in each order (order details)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    size VARCHAR(10) NOT NULL,
    qty INT NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert initial inventory data (2 of each size for each clothing item)
INSERT INTO inventory (product_name, size, stock_quantity) VALUES
-- Elegant Blouse inventory
('Elegant Blouse', 'XS', 2), ('Elegant Blouse', 'S', 2), ('Elegant Blouse', 'M', 2), ('Elegant Blouse', 'L', 2), ('Elegant Blouse', 'XL', 2),
-- Casual T-Shirt inventory
('Casual T-Shirt', 'XS', 2), ('Casual T-Shirt', 'S', 2), ('Casual T-Shirt', 'M', 2), ('Casual T-Shirt', 'L', 2), ('Casual T-Shirt', 'XL', 2),
-- Designer Jeans inventory
('Designer Jeans', 'XS', 2), ('Designer Jeans', 'S', 2), ('Designer Jeans', 'M', 2), ('Designer Jeans', 'L', 2), ('Designer Jeans', 'XL', 2),
-- Summer Shorts inventory
('Summer Shorts', 'XS', 2), ('Summer Shorts', 'S', 2), ('Summer Shorts', 'M', 2), ('Summer Shorts', 'L', 2), ('Summer Shorts', 'XL', 2)
ON DUPLICATE KEY UPDATE stock_quantity = VALUES(stock_quantity);
