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

CREATE TABLE custom_pages
(
    id      INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name    VARCHAR(255) NOT NULL,
    content TEXT         NOT NULL
);

CREATE TABLE addresses
(
    id         INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id    INT          NULL REFERENCES users (id),
    name       VARCHAR(255) NOT NULL,
    phone      VARCHAR(255) NOT NULL,
    line       VARCHAR(255) NOT NULL,
    city       VARCHAR(255) NOT NULL,
    zip_code   VARCHAR(255) NOT NULL,
    is_deleted boolean      NOT NULL DEFAULT FALSE
);

CREATE TABLE orders
(
    id                  INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id             INT          NULL REFERENCES users (id),
    payment_method_id   INT          NOT NULL REFERENCES payment_methods (id),
    delivery_method_id  INT          NOT NULL REFERENCES delivery_methods (id),
    address_id          INT          NOT NULL REFERENCES addresses (id),
    delivery_address_id INT          NOT NULL REFERENCES addresses (id),
    status              VARCHAR(255) NOT NULL,
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted          boolean      NOT NULL DEFAULT FALSE
);

CREATE TABLE order_items
(
    id         INT           NOT NULL PRIMARY KEY AUTO_INCREMENT,
    order_id   INT           NOT NULL REFERENCES orders (id),
    product_id INT           NOT NULL REFERENCES products (id),
    quantity   INT           NOT NULL,
    price      DECIMAL(6, 2) NOT NULL,
    is_deleted boolean       NOT NULL DEFAULT FALSE
);