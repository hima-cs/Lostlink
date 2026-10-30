CREATE DATABASE IF NOT EXISTS lostlink;

USE lostlink;

CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    colour VARCHAR(50),
    location VARCHAR(150),
    item_date DATE,
    description TEXT,
    status ENUM('Lost', 'Found', 'Recovered')
        NOT NULL DEFAULT 'Lost',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO items
(item_name, category, colour, location,
 item_date, description, status)
VALUES
(
    'Water Bottle',
    'Accessories',
    'Blue',
    'Library',
    '2026-10-25',
    'Blue bottle with a white sticker',
    'Lost'
),
(
    'Water Bottle',
    'Accessories',
    'Blue',
    'Library',
    '2026-10-25',
    'Blue bottle with a white sticker',
    'Found'
),
(
    'Notebook',
    'Books',
    'Black',
    'Classroom',
    '2026-10-24',
    'Black notebook with class notes',
    'Lost'
),
(
    'ID Card',
    'ID Cards',
    'White',
    'Canteen',
    '2026-10-23',
    'College identity card',
    'Found'
);
-- Claims Table

CREATE TABLE IF NOT EXISTS claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    claimant_name VARCHAR(150) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    reason TEXT NOT NULL,
    claim_status ENUM('Pending', 'Approved', 'Rejected')
        DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (item_id) REFERENCES items(id)
);
