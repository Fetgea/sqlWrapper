/*
Вставка данных
*/
INSERT INTO Customers(cust_name, cust_address, cust_city, cust_state, cust_zip, cust_country, cust_contact, cust_email)
VALUES
    ('Village Toys', '200 Maple Lane', 'Detroit', 'MI', '44444', 'USA', 'John Smith', 'sales@villagetoys.com'),
    ('Kids Place', '333 South Lake Drive', 'Columbus', 'OH', '43333', 'USA', 'Michelle Green', NULL),
    ('Fun4All', '1 Sunny Place', 'Muncie', 'IN', '42222', 'USA', 'Jim Jones', 'jjones@fun4all.com'),
    ('Fun4All', '829 Riverside Drive', 'Phoenix', 'AZ', '88888', 'USA', 'Denise L. Stephens', 'dstephens@fun4all.com'),
    ('The Toy Store', '4545 53rd Street', 'Chicago', 'IL', '54545', 'USA', 'Kim Howard', NULL);

INSERT INTO Vendors(vend_id, vend_name, vend_address, vend_city, vend_state, vend_zip, vend_country)
VALUES
    ('BRS01', 'Bears R Us', '123 Main Street', 'Bear Town', 'MI', '44444', 'USA'),
    ('BRE02', 'Bear Emporium', '500 Park Street', 'Anytown', 'OH', '44333', 'USA'),
    ('DLL01', 'Doll House Inc.', '555 High Street', 'Dollsville', 'CA', '99999', 'USA'),
    ('FRB01', 'Furball Inc.', '1000 5th Avenue', 'New York', 'NY', '11111', 'USA'),
    ('FNG01', 'Fun and Games', '42 Galaxy Road', 'London', NULL, 'N16 6PS', 'England'),
    ('JTS01', 'Jouets et ours', '1 Rue Amusement', 'Paris', NULL, '45678', 'France');

INSERT INTO Products(vend_id, prod_name, prod_price, prod_desc)
VALUES
    ('BRS01', '8 inch teddy bear', 5.99, '8 inch teddy bear, comes with cap and jacket'),
    ('BRS01', '12 inch teddy bear', 8.99, '12 inch teddy bear, comes with cap and jacket'),
    ('BRS01', '18 inch teddy bear', 11.99, '18 inch teddy bear, comes with cap and jacket'),
    ('DLL01', 'Fish bean bag toy', 3.49, 'Fish bean bag toy, complete with bean bag worms with which to feed it'),
    ('DLL01', 'Bird bean bag toy', 3.49, 'Bird bean bag toy, eggs are not included'),
    ('DLL01', 'Rabbit bean bag toy', 3.49, 'Rabbit bean bag toy, comes with bean bag carrots'),
    ('DLL01', 'Raggedy Ann', 4.99, '18 inch Raggedy Ann doll'),
    ('FNG01', 'King doll', 9.49, '12 inch king doll with royal garments and crown'),
    ('FNG01', 'Queen doll', 9.49, '12 inch queen doll with royal garments and crown');

INSERT INTO Orders(order_date, cust_id)
VALUES
    ('2012-05-01', 1),
    ('2012-01-12', 3),
    ('2012-01-30', 4),
    ('2012-02-03', 5),
    ('2012-02-08', 1);

INSERT INTO OrderItems(order_num, order_item, prod_id, quantity, item_price)
VALUES
    (1, 1, 1, 100, 5.49),
    (1, 2, 3, 100, 10.99),
    (2, 1, 1, 20, 5.99),
    (2, 2, 2, 10, 8.99),
    (2, 3, 3, 10, 11.99),
    (3, 1, 3, 50, 11.49),
    (3, 2, 4, 100, 2.99),
    (3, 3, 5, 100, 2.99),
    (3, 4, 6, 100, 2.99),
    (3, 5, 7, 50, 4.49),
    (4, 1, 7, 5, 4.99),
    (4, 2, 3, 5, 11.99),
    (4, 3, 4, 10, 3.49),
    (4, 4, 5, 10, 3.49),
    (4, 5, 6, 10, 3.49),
    (5, 1, 4, 250, 2.49),
    (5, 2, 5, 250, 2.49),
    (5, 3, 6, 250, 2.49);
