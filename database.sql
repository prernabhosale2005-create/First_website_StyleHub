CREATE DATABASE IF NOT EXISTS style_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE style_hub;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS remember_tokens, wishlist, deliveries, delivery_persons, payments, order_items, orders, cart_items, addresses, products, categories, users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    image VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(500) NOT NULL,
    amazon_url VARCHAR(1000) NULL,
    stock INT NOT NULL DEFAULT 20,
    tab_name ENUM('trending','recent') NOT NULL DEFAULT 'recent',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(50) NOT NULL DEFAULT 'Home',
    address_line TEXT NOT NULL,
    city VARCHAR(80) NOT NULL,
    state VARCHAR(80) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart_user_product (user_id, product_id),
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(40) NOT NULL UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cod','upi','card','netbanking','wallet') NOT NULL,
    payment_status VARCHAR(40) NOT NULL DEFAULT 'Pending',
    order_status VARCHAR(60) NOT NULL DEFAULT 'Confirmed',
    shipping_address TEXT NOT NULL,
    phone VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method ENUM('cod','upi','card','netbanking','wallet') NOT NULL,
    status VARCHAR(40) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_reference VARCHAR(100) NULL,
    paid_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE delivery_persons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    vehicle_number VARCHAR(30) NULL,
    status ENUM('Available','Assigned','Inactive') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    delivery_person_id INT NULL,
    delivery_address TEXT NOT NULL,
    delivery_status ENUM('Preparing','Packed','Out for Delivery','Delivered') NOT NULL DEFAULT 'Preparing',
    estimated_date DATE NULL,
    delivered_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_deliveries_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_deliveries_person FOREIGN KEY (delivery_person_id) REFERENCES delivery_persons(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist (user_id, product_id),
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO categories (name, description, image) VALUES
('Men','Modern clothing and everyday essentials for men','https://images.unsplash.com/photo-1617127365659-c47fa864d8bc?auto=format&fit=crop&w=900&q=80'),
('Women','Trendy outfits and fashion essentials for women','https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=900&q=80'),
('Kids','Comfortable and fun styles for kids','https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=900&q=80'),
('Accessories','Complete your look with stylish accessories','https://images.unsplash.com/photo-1523779917675-b6ed3a42a561?auto=format&fit=crop&w=900&q=80');

INSERT INTO products (category_id,name,description,price,image,amazon_url,stock,tab_name) VALUES
(1,'Classic Denim Jacket','Classic blue denim jacket for casual outfits.',1999,'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',25,'trending'),
(1,'Slim Fit Casual Shirt','Comfortable slim-fit shirt for everyday wear.',999,'https://images.unsplash.com/photo-1603252110481-7ba873bf42ab?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',30,'trending'),
(1,'Cotton Polo T-Shirt','Soft cotton polo with a clean casual look.',799,'https://images.unsplash.com/photo-1625910513413-5fc45e0a3c1b?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',40,'recent'),
(1,'Classic Chinos','Versatile chinos for casual and semi-formal styling.',1299,'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',22,'recent'),
(2,'Floral Summer Dress','Lightweight floral dress for summer outings.',1499,'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',20,'trending'),
(2,'Oversized Blazer','Smart oversized blazer for a modern look.',2499,'https://images.unsplash.com/photo-1591369822096-ffd140ec948f?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',15,'trending'),
(2,'Everyday Kurti','Comfortable printed kurti for everyday wear.',1199,'https://images.unsplash.com/photo-1583391733956-6c78276477e2?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',35,'recent'),
(2,'Wide Leg Trousers','Relaxed-fit trousers for effortless styling.',1399,'https://images.unsplash.com/photo-1506629905607-d9c297d99b3e?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',18,'recent'),
(3,'Kids Hoodie','Warm and comfortable hoodie for kids.',899,'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',28,'recent'),
(3,'Kids Casual T-Shirt','Fun everyday t-shirt for active kids.',599,'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',32,'trending'),
(3,'Kids Denim Jacket','Mini denim jacket for stylish occasions.',1299,'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',16,'recent'),
(4,'Classic Sunglasses','Everyday sunglasses with a timeless frame.',699,'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',50,'trending'),
(4,'Leather Handbag','Elegant handbag for daily use.',1799,'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',14,'recent'),
(4,'Minimal Watch','Minimal design watch for a polished look.',2199,'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',12,'trending'),
(4,'Canvas Backpack','Lightweight backpack for college and travel.',1299,'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=700&q=80','https://www.amazon.in/',24,'recent');

INSERT INTO delivery_persons (name,phone,vehicle_number,status) VALUES
('Rahul Patil','9876543210','MH12AB1234','Available'),
('Amit Sharma','9876501234','MH14CD5678','Available');
