drop table if exists location;
CREATE TABLE location (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    country_id INT,
    location VARCHAR(250),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    enabled INT DEFAULT 1,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    created_by INT
);


DROP TABLE IF EXISTS inventory;
CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    country_id INT NOT NULL,
    material_id INT NOT NULL,
    location_id INT NOT NULL,
    spare_code VARCHAR(500) NOT NULL,
    spare_name VARCHAR(500) NOT NULL,
    spare_description TEXT,
    quantity VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    enabled INT DEFAULT 1,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    created_by INT
);


DROP TABLE IF EXISTS stock_transfer;
CREATE TABLE stock_transfer (
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  material_id INT DEFAULT NULL,
  inventory_id INT DEFAULT NULL,
  source_location_id INT DEFAULT NULL,
  destination_location_id INT DEFAULT NULL,
  quantity VARCHAR(500) DEFAULT NULL,
  transfer_status VARCHAR(50) DEFAULT 'PENDING',
  transfer_date DATETIME DEFAULT CURRENT_TIMESTAMP(),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
  updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
  updated_by INT,
  created_by INT
);



DROP TABLE IF EXISTS stock_transfer;
CREATE TABLE `stock_transfer` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` LONGTEXT NOT NULL,
  `brand` VARCHAR(255) NOT NULL,
  `product_type` VARCHAR(255) NOT NULL,
  `material` VARCHAR(255) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `invoice_no` VARCHAR(255) NOT NULL,
  `invoice_date` DATE DEFAULT NULL,
  `replacement_applicable` VARCHAR(10) NOT NULL,
  `replacement_reason_type` VARCHAR(50) NOT NULL,
  `replacement_reason` TEXT NOT NULL,
  `pickup_needed` VARCHAR(10) DEFAULT NULL,
  `warranty_status` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
);





DELIMITER $$

CREATE TRIGGER after_ticketproducts_insert
AFTER INSERT ON ticketproducts
FOR EACH ROW
BEGIN
    INSERT INTO stock_transfer (
        ticket_id,
        brand,
        product_type,
        material,
        quantity,
        invoice_no,
        invoice_date,
        replacement_applicable,
        replacement_reason_type,
        replacement_reason,
        pickup_needed,
        warranty_status,
        created_at,
        updated_at
    ) VALUES (
        NEW.ticket_id,
        NEW.brand,
        NEW.product_type,
        NEW.material,
        NEW.quantity,
        NEW.invoice_no,
        NEW.invoice_date,
        NEW.replacement_applicable,
        NEW.replacement_reason_type,
        NEW.replacement_reason,
        NEW.pickup_needed,
        NEW.warranty_status,
        NEW.created_at,
        NEW.updated_at
    );
END$$

DELIMITER ;



ALTER TABLE stock_transfer ADD transfer_date VARCHAR(255);
ALTER TABLE stock_transfer ADD awb_no VARCHAR(255);

ALTER TABLE ticketproducts ADD transfer_date VARCHAR(255);
ALTER TABLE ticketproducts ADD awb_no VARCHAR(255);

ALTER TABLE tickets ADD transfer_date VARCHAR(255);
ALTER TABLE tickets ADD awb_no VARCHAR(255);