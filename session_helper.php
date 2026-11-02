<?php
// MOCK SQLITE DATABASE & SESSION HELPER FOR LOCAL SQLI LAB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_file = __DIR__ . '/database.db';
$db_exists = file_exists($db_file);

try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Initialize tables if database is fresh
    if (!$db_exists) {
        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT,
                password TEXT,
                role TEXT,
                email TEXT
            );
            
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                category TEXT,
                price REAL,
                stock INTEGER
            );
            
            CREATE TABLE credit_cards (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                card_number TEXT,
                cvv TEXT,
                balance TEXT
            );
            
            CREATE TABLE secrets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                owner TEXT,
                flag TEXT
            );
            
            -- Insert Mock Data
            INSERT INTO users (username, password, role, email) VALUES ('alice', 'alice123', 'user', 'alice@securesite.local');
            INSERT INTO users (username, password, role, email) VALUES ('bob', 'bobpass456', 'user', 'bob@securesite.local');
            INSERT INTO users (username, password, role, email) VALUES ('admin', 'SuperSecretAdminP@ss99!', 'admin', 'admin@securesite.local');
            
            INSERT INTO products (name, category, price, stock) VALUES ('Wireless Mechanical Keyboard', 'Electronics', 89.99, 42);
            INSERT INTO products (name, category, price, stock) VALUES ('Ergonomic Vertical Mouse', 'Electronics', 49.99, 15);
            INSERT INTO products (name, category, price, stock) VALUES ('UltraWide 34-inch Monitor', 'Displays', 499.99, 7);
            
            INSERT INTO credit_cards (user_id, card_number, cvv, balance) VALUES (1, '4111-2222-3333-4242', '123', '$5,400.00');
            INSERT INTO credit_cards (user_id, card_number, cvv, balance) VALUES (2, '5555-4444-3333-1111', '987', '$1,200.00');
            INSERT INTO credit_cards (user_id, card_number, cvv, balance) VALUES (3, '9999-8888-7777-6666', '000', '$999,999.00');
            
            INSERT INTO secrets (owner, flag) VALUES ('Alice', 'FLAG{sqli_master_alice_789}');
            INSERT INTO secrets (owner, flag) VALUES ('Admin', 'FLAG{SQLI_MASTERCLASS_ELITE_ADMIN_FLAG_2026}');
        ");
    }
} catch (PDOException $e) {
    die("Database Initialization Error: " . $e->getMessage());
}

// Reset database helper
if (isset($_GET['reset_db'])) {
    if (file_exists($db_file)) {
        unlink($db_file);
    }
    header("Location: index.php");
    exit();
}

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$lab_root = $base_url . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>