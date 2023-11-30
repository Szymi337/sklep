CREATE TABLE users
(
    id                       INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    login                    VARCHAR(255) NOT NULL UNIQUE,
    email                    VARCHAR(255) NOT NULL UNIQUE,
    password                 VARCHAR(255) NOT NULL,
    is_admin                 BOOLEAN      NOT NULL DEFAULT FALSE,
    email_confirmation_token VARCHAR(255) UNIQUE,
    password_reset_token     VARCHAR(255) UNIQUE
);