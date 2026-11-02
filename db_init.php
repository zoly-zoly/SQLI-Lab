<?php
// SQLITE DATABASE INITIALIZER FOR SQLI LAB
$db_file = __DIR__ . '/database.sqlite';
$init_db = !file_exists($db_file);

try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($init_db) {
        // Create users table
        $pdo->exec("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            password TEXT NOT NULL,
            email TEXT NOT NULL,
            role TEXT NOT NULL
        )");
        
        // Create products table
        $pdo->exec("CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT NOT NULL,
            price REAL NOT NULL,
            category TEXT NOT NULL
        )");
        
        // Create secrets table (for high-level challenges)
        $pdo->exec("CREATE TABLE secrets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            secret_name TEXT NOT NULL,
            secret_value TEXT NOT NULL
        )");
        
        // Populate mock users
        $pdo->exec("INSERT INTO users (username, password, email, role) VALUES ('alice', 'alice_password_123', 'alice@securesite.local', 'user')");
        $pdo->exec("INSERT INTO users (username, password, email, role) VALUES ('bob', 'bob_secure_pass_456', 'bob@securesite.local', 'user')");
        $pdo->exec("INSERT INTO users (username, password, email, role) VALUES ('admin', 'admin_super_secret_flag_9988', 'admin@securesite.local', 'administrator')");
        
        // Populate mock products
        $pdo->exec("INSERT INTO products (name, description, price, category) VALUES ('Enterprise Firewall', 'Advanced network security appliance', 1200.00, 'Security')");
        $pdo->exec("INSERT INTO products (name, description, price, category) VALUES ('Secure Cloud Storage', 'Encrypted multi-tenant cloud drive', 45.00, 'Cloud')");
        $pdo->exec("INSERT INTO products (name, description, price, category) VALUES ('Vulnerability Scanner', 'Automated web app security crawler', 499.00, 'Security')");
        $pdo->exec("INSERT INTO products (name, description, price, category) VALUES ('Database Audit Tool', 'Deep inspection compliance checker', 250.00, 'Database')");
        
        // Populate mock secrets
        $pdo->exec("INSERT INTO secrets (secret_name, secret_value) VALUES ('Master Encryption Key', 'KEY-SEC-99887766-AZURE')");
        $pdo->exec("INSERT INTO secrets (secret_name, secret_value) VALUES ('AWS Root Credentials', 'AKIAIOSFODNN7EXAMPLE:wJalrXUtnFEMI/K7MDENG')");
    }
} catch (PDOException $e) {
    die("Database Initialization Error: " . $e->getMessage());
}
?>