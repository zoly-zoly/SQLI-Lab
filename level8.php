<?php
require_once 'session_helper.php';
$msg = '';
$error = '';
$file_content = '';

$file_path = isset($_GET['file']) ? $_GET['file'] : 'database.db';

if (!empty($file_path)) {
    try {
        // VULNERABLE: Insecure file read or database file exfiltration via SQLi/File functions
        // In SQLite, attackers can use readfile() or attach database files to exfiltrate data.
        if (strpos($file_path, 'SELECT') !== false || strpos($file_path, "'") !== false) {
            // Simulated SQL injection file read
            $query = "SELECT hex(quote('$file_path'))";
            $stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table'");
            $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $file_content = "Schema Table Definitions Extracted via SQLi:\n" . print_r($tables, true);
        } else {
            if (file_exists($file_path)) {
                $file_content = file_get_contents($file_path);
            } else {
                $error = "Error: File '" . htmlspecialchars($file_path) . "' not found on server.";
            }
        }
    } catch (PDOException $e) {
        $error = "SQL Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 8: High | SQLi Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 650px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #ef4444; }
        h1 { color: #ef4444; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #ef4444; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #dc2626; }
        a.back { color: #94a3b8; text-decoration: none; display: inline-block; margin-top: 20px; }
        a.back:hover { color: #f1f5f9; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid #ef4444; }
        .file-card { background: #0f172a; border-radius: 8px; padding: 20px; border: 1px dashed #334155; margin-top: 20px; font-family: monospace; color: #38bdf8; white-space: pre-wrap; word-break: break-all; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Level 8: High — File Read / Out-of-Band Extraction</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <p>In addition to extracting table records, advanced SQL injection allows attackers to interact with the underlying operating system or database engine features. In SQLite and other RDBMS, attackers can query system metadata tables (like <code>sqlite_master</code>) to map database schemas or read local configuration files.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$file_path = $_GET['file'];
// VULNERABLE: Unchecked file read / schema extraction via database metadata
$stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table'");
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Exfiltrate the entire database schema definitions by querying SQLite system metadata. Inject a query payload or request schema definitions directly to reveal secret database tables.</p>

        <form action="level8.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <label for="file">File Path / SQL Metadata Parameter:</label><br>
            <input type="text" id="file" name="file" value="<?php echo htmlspecialchars($file_path); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Read Resource" class="btn">
        </form>

        <?php if (!empty($file_content)): ?>
            <div class="file-card">
                <?php echo htmlspecialchars($file_content); ?>
                
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; padding: 10px; border-radius: 6px; color: #f87171; margin-top: 15px; font-weight: bold; text-align: center;">
                    🎉 SUCCESS: You successfully extracted internal database schema definitions!
                </div>
            </div>
        <?php endif; ?>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
