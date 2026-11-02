# SQL Injection Deep-Dive: Theoretical Mastery & Secure Database Architecture

Welcome to the official Wiki for the **SQL Injection Masterclass Lab Suite**. This wiki provides advanced theoretical understanding of SQL Injection (SQLi), query parsing mechanics, database internals, and enterprise-grade defensive architecture.

---

## 🔬 Part 1: What is SQL Injection?

SQL Injection occurs when untrusted user input is concatenated directly into a database query string without proper parameterization or sanitization. 

### 1. The Confusion of Code and Data
In an insecure application, the database parser receives a single string where user input has altered the syntax of the command.
*   **Intended Query:** `SELECT * FROM users WHERE username = 'alice'`
*   **Injected Query:** `SELECT * FROM users WHERE username = 'admin' -- '`
*   The parser interprets `--` as a comment operator, ignoring the remainder of the query (the password check), leading to an authentication bypass.

### 2. The Impact: From Data Leaks to RCE
Depending on the database engine (MySQL, PostgreSQL, SQLite, MSSQL) and application permissions, successful SQL injection can lead to:
*   Exfiltration of sensitive tables (passwords, credit cards, PII).
*   Authentication bypass and privilege escalation.
*   Modification or deletion of database records.
*   Execution of operating system commands via database features (e.g., `xp_cmdshell` in MSSQL or file writing).

---

## 🎯 Part 2: Advanced SQLi Attack Archetypes

### 1. UNION-Based Exfiltration (Level 3)
When query results are displayed on the screen, attackers use the `UNION` operator to append columns from secondary tables.
*   **Requirements:** Must match the exact column count and compatible data types of the original query.
*   **Methodology:** Use `ORDER BY` or `GROUP BY` to determine the column count, then inject `UNION SELECT ...` to dump table contents.

### 2. Blind SQL Injection (Levels 5 & 6)
When the application does not return query results or error messages, attackers must infer data character by character:
*   **Boolean-Based:** The application changes state (e.g., "Welcome" vs. "Invalid login") based on whether an injected condition evaluates to true or false.
*   **Time-Based:** The application returns identical pages, but attackers force database sleep functions (like `WAITFOR DELAY` or `randomblob`) to measure response latency.

### 3. Second-Order SQL Injection (Level 7)
*   **The Flaw:** User input is safely sanitized and stored in the database (e.g., updating a profile username). However, when that data is later retrieved and executed in a secondary query (e.g., an audit log search or administrative report generator), it triggers an injection.

---

## 🛡️ Part 3: Defensive Architecture & Prepared Statements

To eliminate SQL Injection completely, developers must adopt **Parameterized Queries (Prepared Statements)**.

```
                  ┌────────────────────────────────────────┐
                  │            User Parameters             │
                  └───────────────────┬────────────────────┘
                                      │
                                      ▼
                       [ Database Query Pre-Compilation ]
                    (Send query structure separate from data)
                                      │
                                      ▼
                        [ Safe Parameter Binding ]
                      (Treat input strictly as data)
                                      │
                                      ▼
                           [ Secure Execution ]
```

### 1. Prepared Statements with PDO (The Standard)
Always separate SQL statement structure from user data using PDO or prepared statements:

```php
// SECURE: Uses parameterized prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
$stmt->execute([
    'username' => $username,
    'password' => $password
]);
$user = $stmt->fetch();
```
*Even if `$username` contains `' OR 1=1 --`, the database engine treats it strictly as literal text, preventing query restructuring.*

### 2. Principle of Least Privilege
Ensure that the database user account connected to your web application has only the permissions it needs (e.g., `SELECT`, `INSERT`, `UPDATE`), and never grant administrative privileges (`DROP`, `FILE`, `EXECUTE`) to the web application runtime.

---
*Documentation curated by **Zoly** for the advancement of secure application architecture.*
