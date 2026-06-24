CREATE TABLE product_stock_backup AS
SELECT * FROM product_stock;

CREATE TABLE shared_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    category_id INT NOT NULL,
    size_id INT NOT NULL,
    qty INT NOT NULL,
    threshhold INT NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO product_stock (branch_id, product_id, size_id, created_at)
SELECT branch_id, product_id, size_id, created_at
FROM product_stock_backup;


ALTER TABLE table_name
ADD CONSTRAINT constraint_name UNIQUE (column1, column2);





ALTER TABLE product_stock
ADD CONSTRAINT unique_branch_product_size
UNIQUE (branch_id, product_id, size_id);


INSERT INTO product_stock (...)
VALUES (...)
ON DUPLICATE KEY UPDATE qty = VALUES(qty);