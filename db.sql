CREATE TABLE branch (
    id TINYINT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(45) NOT NULL,
    location VARCHAR(100) DEFAULT NULL,
    logo_path VARCHAR(100) NOT NULL,
    ticket_number INT(11) UNSIGNED NULL DEFAULT 1,
    phone_number CHAR(12) NOT NULL,
    note TEXT DEFAULT NULL,
    admin_id TINYINT(3) NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO branch (name, location, logo_path, ticket_number, phone_number, note) VALUES
    ('Pollo Rey', 'Santa Cruz', 'img/logo/1.jpg', 1, '333 333 3333', 'Write notes here!'),
    ('Pollos Arriaga', 'J. Barrera', 'img/logo/1.jpg', 1, '333 333 3333', 'Write notes here!'),
    ('Pollos Arriaga', 'Ocotlán', 'img/logo/1.jpg', 1, '333 333 3333', 'Write notes here!');

CREATE TABLE category (
    id TINYINT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(30) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO category (category) VALUES
    ('Platillos'),
    ('Paquete'),
    ('Bebidas'),
    ('Extras');


CREATE TABLE food (
    id SMALLINT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    quantity FLOAT(6,2) NOT NULL,
    quantity_notif TINYINT(3) UNSIGNED NULL DEFAULT 0,
    is_notif_sent  BOOLEAN NULL DEFAULT 0,
    cost FLOAT(6,2) NOT NULL,
    is_showed_in_index BOOLEAN NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    category_id TINYINT(3) UNSIGNED NOT NULL,
    branch_id TINYINT(3) UNSIGNED NOT NULL,
    FOREIGN KEY (branch_id)
    REFERENCES branch (id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (category_id)
    REFERENCES category (id) 
    ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO food (id, name, quantity, quantity_notif, is_notif_sent, cost, is_showed_in_index, category_id, branch_id) VALUES
    (1, 'Pollo', 44.00, 30, 0, 130.00, 1, 1, 1),
    (2, 'Costilla', 42.16, 0, 0, 149.70, 1, 1, 1),
    (3, 'Chorizo', 431, 0, 0, 48.00, 1, 1, 1),
    (4, 'Refresco 1.5L', 84.00, 0, 0, 30.00, 1, 3, 1),
    (5, 'Refresco vidrio ', 223, 0, 0, 20.00, 0, 3, 1),
    (6, 'Refresco taparrosca', 17.00, 0, 0, 20.00, 0, 3, 1),
    (7, 'Arroz', 8129.00, 0, 0, 0.00, 0, 5, 1),
    (8, 'Agua natural', 33.00, 0, 0, 20.00, 0, 3, 1),
    (10, 'Desechable', 9304.00, 0, 0, 0.00, 0, 5, 1);

CREATE TABLE dish (
    id SMALLINT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(45) NOT NULL,
    price FLOAT(6,2) NOT NULL,
    `portion` FLOAT(3,2) NOT NULL,
    description VARCHAR(100) NULL,
    is_showed_in_sales BOOLEAN NULL DEFAULT 1,
    is_combo BOOLEAN NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    food_id SMALLINT(5) UNSIGNED NOT NULL,
        FOREIGN KEY (food_id)
            REFERENCES food (id)
            ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO dish (id, name, price, portion, description, is_showed_in_sales, is_combo, food_id) VALUES
    (1, '1 Pollo', 120.00, 1, '', 1, 0, 1),
    (2, '1/2 Pollo', 60.00, .5, '', 1, 0, 1),
    (3, '1/4 Pollo', 30.00, .25, '', 1, 0, 1),
    (4, 'All Pollos', 210.00, 0, '1 pollo y 3/4', 1, 1, 1);

CREATE TABLE dishes_in_combo (
    combo_id SMALLINT(5) UNSIGNED NOT NULL,
    dish_id SMALLINT(5) UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (combo_id)
        REFERENCES dish (id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (dish_id)
        REFERENCES dish (id) 
        ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO dishes_in_combo (combo_id, dish_id) VALUES
    (4, 1),
    (4, 2),
    (4, 3);

CREATE TABLE product (
    id SMALLINT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    quantity FLOAT(6,2) NOT NULL,
    quantity_notif TINYINT(3) UNSIGNED NULL DEFAULT 0,
    is_notif_sent BOOLEAN NULL DEFAULT 0,
    cost FLOAT(6,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP DEFAULT NULL,
    branch_id TINYINT(3) UNSIGNED NOT NULL,
    FOREIGN KEY (branch_id)
    REFERENCES branch (id) 
    ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO product (id, name, quantity, quantity_notif, cost, branch_id) VALUES
    (1, 'Carbón', 25, 5, 60, 1),
    (2, 'Rollo ticket', 10, 2, 10, 1),
    (3, 'Charola', 10, 3, 20, 1);

CREATE TABLE user (
    id TINYINT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(45) NOT NULL,
    last_name VARCHAR(45) NOT NULL,
    email VARCHAR(75) NOT NULL,
    phone_number VARCHAR(25) NOT NULL,
    address VARCHAR(50) NOT NULL,
    hash VARCHAR(255) NOT NULL,
    photo_path VARCHAR(100) NOT NULL,
    root BOOLEAN NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    branch_id TINYINT(3) UNSIGNED NOT NULL,
    FOREIGN KEY (branch_id)
        REFERENCES branch (id) 
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE expense (
    id SMALLINT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amount FLOAT(7,2) NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reason VARCHAR(100) NOT NULL,
    branch_id TINYINT(3) UNSIGNED NOT NULL,
    user_id TINYINT(3) UNSIGNED NOT NULL,
    FOREIGN KEY (user_id)
        REFERENCES user (id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id)
        REFERENCES branch (id) 
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE used_product (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id SMALLINT(5) UNSIGNED NOT NULL,
    quantity TINYINT(3) UNSIGNED NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id TINYINT(3) UNSIGNED NOT NULL,
    branch_id TINYINT(3) UNSIGNED NOT NULL,
    FOREIGN KEY (product_id)
        REFERENCES product (id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id)
        REFERENCES user (id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id)
        REFERENCES branch (id) 
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE supplied_food (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    food_id SMALLINT(5) UNSIGNED NOT NULL,
    quantity FLOAT(6,2) NOT NULL,
    new_quantity FLOAT(6,2) NOT NULL,
    cost FLOAT(7,2) NOT NULL,
    date TIMESTAMP NOT NULL,
    user_id TINYINT(3) UNSIGNED NOT NULL,
    branch_id TINYINT(3) UNSIGNED NOT NULL,
    FOREIGN KEY (food_id)
    REFERENCES food (id) 
    ON DELETE CASCADE  ON UPDATE CASCADE,
    FOREIGN KEY (user_id)
    REFERENCES user (id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id)
    REFERENCES branch (id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE altered_food (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    food_id SMALLINT(5) UNSIGNED NOT NULL,
    quantity FLOAT(6,2) NOT NULL,
    reason VARCHAR(100) NOT NULL,
    new_quantity FLOAT(6,2) NOT NULL,
    cost FLOAT(7,2) NOT NULL,
    date TIMESTAMP NOT NULL,
    user_id TINYINT(3) UNSIGNED NOT NULL,
    branch_id TINYINT(3) UNSIGNED NOT NULL,
    FOREIGN KEY (food_id)
    REFERENCES food (id) 
    ON DELETE CASCADE  ON UPDATE CASCADE,
    FOREIGN KEY (user_id)
    REFERENCES user (id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id)
    REFERENCES branch (id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE courtesy (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dish_id SMALLINT(5) UNSIGNED NOT NULL,
    quantity TINYINT(3) UNSIGNED NOT NULL,
    price FLOAT(7,2) NOT NULL,
    date TIMESTAMP NOT NULL,
    reason VARCHAR(100) NOT NULL,
    user_id TINYINT(3) UNSIGNED NOT NULL,
    branch_id TINYINT(3) UNSIGNED NOT NULL,
    FOREIGN KEY (dish_id)
    REFERENCES dish (id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id)
    REFERENCES user (id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id)
    REFERENCES branch (id) 
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE sale (
     id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
     ticket_number INT(11) NOT NULL,
     branch_id TINYINT(3) UNSIGNED NOT NULL,
     FOREIGN KEY (branch_id)
         REFERENCES branch (id)
);

INSERT INTO sale (id, ticket_number, branch_id) VALUES
    (1, 1, 1);

CREATE TABLE ticket (
   ticket_number INT(11) NOT NULL,
   dish_id SMALLINT(5) UNSIGNED NOT NULL,
   quantity TINYINT(3) UNSIGNED NOT NULL,
   price FLOAT(7,2) NOT NULL,
   date TIMESTAMP NULL DEFAULT current_TIMESTAMP() ON UPDATE current_TIMESTAMP(),
   branch_id TINYINT(3) UNSIGNED NOT NULL,
   user_id TINYINT(3) UNSIGNED NOT NULL,
   FOREIGN KEY (user_id)
       REFERENCES user (id)
       ON DELETE CASCADE ON UPDATE CASCADE,
   FOREIGN KEY (branch_id)
       REFERENCES branch (id)
       ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO ticket (ticket_number, dish_id, quantity, price, date, branch_id, user_id) VALUES
    (1, 1, 3, 130, '2022-07-03 11:40:32', 1, 1),
    (1, 2, 1, 130, '2022-07-03 11:40:32', 1, 1),
    (1, 3, 1, 130, '2022-07-03 11:40:32', 1, 1);

CREATE TABLE summary (
   id INT(11) NOT NULL,
   sales FLOAT(7,2) NOT NULL,
   expenses FLOAT(7,2) NULL DEFAULT 0.00,
   money_received FLOAT(7,2) NULL DEFAULT 0.00,
   date date NULL DEFAULT current_TIMESTAMP(),
   branch_id TINYINT(3) UNSIGNED NOT NULL,
   FOREIGN KEY (branch_id)
       REFERENCES branch (id)
       ON DELETE CASCADE ON UPDATE CASCADE
);