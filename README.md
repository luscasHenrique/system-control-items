CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,             -- Coluna id como chave primária
    qrcode VARCHAR(255) UNIQUE NOT NULL,           -- Coluna qrcode única
    name VARCHAR(255) NOT NULL,                    -- Coluna name como texto (varchar)
    price DECIMAL(10, 2) NOT NULL,                 -- Coluna price como número decimal
    company VARCHAR(255) NOT NULL,                 -- Coluna company como texto (varchar)
    description TEXT NULL,                         -- Coluna description como texto, aceita valores nulos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL, -- Coluna created_at com timestamp, aceita nulo
    quantity INT NULL                              -- Coluna quantity como inteiro, aceita valores nulos
);
