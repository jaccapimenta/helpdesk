CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
 
CREATE TABLE tech_support (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
 
CREATE TABLE status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(50) NOT NULL
);

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tickets_type VARCHAR(50) NOT NULL,
    openningdate DATETIME NOT NULL,
    conclusiondate DATETIME NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NULL,
    users_id INT,
    status_id INT,
    tech_support_id INT,

    FOREIGN KEY (users_id) REFERENCES users(id),
    FOREIGN KEY (status_id) REFERENCES status(id),
    FOREIGN KEY (tech_support_id) REFERENCES tech_support(id)
);
 
CREATE TABLE feedback_histories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    Date DATETIME NOT NULL,
    status_id INT,
    tech_support_id INT,
    FOREIGN KEY (status_id) REFERENCES status(id),
    FOREIGN KEY (tech_support_id) REFERENCES tech_support(id)
);