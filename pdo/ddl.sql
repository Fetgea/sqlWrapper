DROP TABLE IF EXISTS Vendors;
CREATE TABLE Vendors (
    vend_id VARCHAR(5) PRIMARY KEY,
    vend_name VARCHAR(200) NOT NULL,
    vend_address VARCHAR(250),
    vend_city VARCHAR(100),
    vend_state VARCHAR(100),
    vend_zip VARCHAR(20),
    vend_country VARCHAR(100)
);
DROP TABLE IF EXISTS Products;
CREATE TABLE Products (
    prod_id INTEGER PRIMARY KEY AUTOINCREMENT,
    vend_id VARCHAR(5),
    prod_name VARCHAR(250),
    prod_price DECIMAL(19,4),
    prod_desc VARCHAR(1000),
    FOREIGN KEY (vend_id) REFERENCES Vendors (vend_id)
);
DROP TABLE IF EXISTS Customers;
CREATE TABLE Customers (
    cust_id INTEGER PRIMARY KEY AUTOINCREMENT,
    cust_name VARCHAR(250),
    cust_address VARCHAR(250),
    cust_city VARCHAR(100),
    cust_state VARCHAR(100),
    cust_zip VARCHAR(20),
    cust_country VARCHAR(100),
    cust_contact VARCHAR(250),
    cust_email VARCHAR(250)
);
DROP TABLE IF EXISTS Orders;
CREATE TABLE Orders (
    order_num INTEGER PRIMARY KEY AUTOINCREMENT,
    order_date DATE,
    cust_id INT,
    FOREIGN KEY (cust_id) REFERENCES Customers (cust_id) ON DELETE SET NULL
);
DROP TABLE IF EXISTS OrderItems;
CREATE TABLE OrderItems (
    order_num INT,
    order_item INT,
    prod_id INT,
    quantity INT,
    item_price DECIMAL(19,4),
    PRIMARY KEY (order_num, order_item),
    FOREIGN KEY (order_num) REFERENCES Orders (order_num) ON DELETE CASCADE,
    FOREIGN KEY (prod_id) REFERENCES Products (prod_id) ON DELETE CASCADE
);
