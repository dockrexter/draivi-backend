CREATE TABLE products (
    number INT PRIMARY KEY,
    name VARCHAR(255),
    bottlesize VARCHAR(50),
    price DECIMAL(10, 2),
    priceGBP DECIMAL(10, 2),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    orderamount INT DEFAULT 0
);