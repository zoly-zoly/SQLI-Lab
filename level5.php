<?php
require_once 'session_helper.php';
$message = '';
$user_checked = isset($_GET['username']) ? $_GET['username'] : '';

if (!empty($user_checked)) {
    try {
        // VULNERABLE: Blind boolean-based SQLi. The application only returns a generic true/false message.
        $query = "SELECT * FROM users WHERE username = '" . $user_checked . "'";
        $stmt = $pdo->query($query);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $message = "✅ User account exists in database.";
        } else {
            $message = "❌ User account does not exist.";
        }
    } catch (PDOException $e) {
        $message = "❌ User account does not exist."; // Hides errors, forcing boolean inference
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 5: High | SQLi Lab</title>
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
        .result-box { background: #0f172a; border-radius: 8px; padding: 15px; border: 1px dashed #334155; margin-top: 15px; font-size: 1.1rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Level 5: High — Blind Boolean-Based SQLi</h1>

        <p>When an application does not return data or database errors, but changes its behavior based on whether a query evaluates to true or false, you must perform **Blind Boolean-Based SQL Injection**, extracting data character by character.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$username = $_GET['username'];
// VULNERABLE: String query returning only binary UI state (Exists vs Not Exists)
$query = "SELECT * FROM users WHERE username = '" . $username . "'";
$result = $pdo->query($query);
if ($result->fetch()) {
    echo "User exists";
} else {
    echo "User does not exist";
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Infer admin password characters using boolean logic.<br>
        <em>Payload example:</em> <code>admin' AND (SELECT SUBSTR(password,1,1) FROM users WHERE username='admin')='a' --</code></p>

        <form action="level5.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <label for="username">Check Username:</label><br>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_checked); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Verify User" class="btn">
        </form>

        <?php if (!empty($message)): ?>
            <div class="result-box">
                <div><?php echo $message; ?></div>
                
                <?php if (strpos($user_checked, 'AND') !== false || strpos($user_checked, 'SUBSTR') !== false): ?>
                    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; padding: 10px; border-radius: 6px; color: #f87171; margin-top: 15px; font-weight: bold; text-align: center;">
                        🎉 SUCCESS: You successfully evaluated boolean conditions blindly!
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
