CREATE TABLE users
(
    id                       INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    login                    VARCHAR(255) NOT NULL UNIQUE,
    email                    VARCHAR(255) NOT NULL UNIQUE,
    password                 VARCHAR(255) NOT NULL,
    is_admin                 BOOLEAN      NOT NULL DEFAULT FALSE,
    email_confirmation_token VARCHAR(255) UNIQUE   DEFAULT NULL,
    password_reset_token     VARCHAR(255) UNIQUE   DEFAULT NULL
);

CREATE TABLE categories
(
    id         INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(255) NOT NULL,
    is_deleted boolean      NOT NULL DEFAULT FALSE
);

CREATE TABLE products
(
    id          INT           NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255)  NOT NULL,
    description TEXT          NOT NULL,
    price       DECIMAL(6, 2) NOT NULL,
    image       VARCHAR(255)  NOT NULL,
    category_id INT           NOT NULL REFERENCES categories (id),
    is_deleted  boolean       NOT NULL DEFAULT FALSE
);

CREATE TABLE payment_methods
(
    id         INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(255) NOT NULL,
    is_deleted boolean      NOT NULL DEFAULT FALSE
);

CREATE TABLE delivery_methods
(
    id         INT           NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(255)  NOT NULL,
    price      DECIMAL(6, 2) NOT NULL,
    is_deleted boolean       NOT NULL DEFAULT FALSE
);