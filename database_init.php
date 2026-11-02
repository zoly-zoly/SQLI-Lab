<?php
// SQLITE DATABASE INITIALIZER FOR SQLI LAB
// Creates a local SQLite database file automatically with pre-seeded vulnerable tables and records.

$db_file = __DIR__ . '/database.sqlite';
$is_new = !file_exists($db_file);

try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($is_new) {
        // Create Users Table
        $pdo->exec("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            password TEXT NOT NULL,
            email TEXT NOT NULL,
            role TEXT NOT NULL
        )");

        // Create Products Table
        $pdo->exec("CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT NOT NULL,
            price REAL NOT NULL,
            stock INTEGER NOT NULL
        )");

        // Create Secrets Table (Target for UNION/Exfiltration)
        $pdo->exec("CREATE TABLE secrets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            secret_name TEXT NOT NULL,
            secret_value TEXT NOT NULL
        )");

        // Seed Users
        $pdo->exec("INSERT INTO users (username, password, email, role) VALUES ('alice', 'password123', 'alice@securesite.local', 'user')");
        $pdo->exec("INSERT INTO users (username, password, email, role) VALUES ('bob', 'qwerty456', 'bob@securesite.local', 'user')");
        $pdo->exec("INSERT INTO users (username, password, email, role) VALUES ('admin', 'SuperSecretAdminP@ss99!', 'admin@securesite.local', 'administrator')");

        // Seed Products
        $pdo->exec("INSERT INTO products (name, description, price, stock) VALUES ('Wireless Mechanical Keyboard', 'RGB Backlit mechanical keyboard with brown switches.', 89.99, 45)");
        $pdo->exec("INSERT INTO products (name, description, price, stock) VALUES ('Ergonomic Vertical Mouse', 'Wireless optical mouse designed for wrist health.', 45.50, 12)");
        $pdo->exec("INSERT INTO products (name, description, price, stock) VALUES ('UltraWide 34-Inch Monitor', '4K QHD Curved IPS Display for developers.', 499.00, 8)");
        $pdo->exec("INSERT INTO products (name, description, price, stock) VALUES ('Noise-Canceling Headphones', 'Active ambient noise reduction over-ear headset.', 199.99, 20)");

        // Seed Secrets
        $pdo->exec("INSERT INTO secrets (secret_name, secret_value) VALUES ('AWS Master Key', 'AKIA_SAMPLE_MASTER_KEY_9981273')");
        $pdo->exec("INSERT INTO secrets (secret_name, secret_value) VALUES ('Database Root Password', 'root_secure_db_pass_2026!')");
        $pdo->exec("INSERT INTO secrets (secret_name, secret_value) VALUES ('Stripe Production API Token', 'rk_live_sample_stripe_token_55819')");
    }
} catch (PDOException $e) {
    die("Database Initialization Failed: " . $e->getMessage());
}
?>