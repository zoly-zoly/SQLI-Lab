<?php
require_once 'session_helper.php';
$msg = '';
$error = '';
$logged_in_user = null;

$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // VULNERABLE: Direct string concatenation in SQL query
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $stmt = $pdo->query($query);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $logged_in_user = $user;
            $msg = "Success: Logged in successfully as <b>" . htmlspecialchars($user['username']) . "</b> (Role: " . htmlspecialchars($user['role']) . ")!";
        } else {
            $error = "Authentication Failed: Invalid username or password.";
        }
    } catch (PDOException $e) {
        $error = "SQL Syntax Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 1: Low | SQLi Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 600px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #10b981; }
        h1 { color: #10b981; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #10b981; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #059669; }
        a.back { color: #94a3b8; text-decoration: none; display: inline-block; margin-top: 20px; }
        a.back:hover { color: #f1f5f9; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid #10b981; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid #ef4444; }
        .user-card { background: #0f172a; border-radius: 8px; padding: 20px; border: 1px dashed #334155; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Level 1: Low — Authentication Bypass</h1>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <p>This login portal validates credentials by concatenating user input directly into an SQL query string. If the query logic is modified using SQL comment operators or boolean conditions, authentication can be bypassed entirely without knowing the password.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$username = $_POST['username'];
$password = $_POST['password'];

// VULNERABLE: Unescaped string concatenation
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $pdo->query($query);
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Bypass authentication to log in as the administrator without knowing their password. Try inputting into the username field: <code>admin' --</code> (or <code>' OR 1=1 --</code>).</p>

        <form action="level1.php" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br><br>
            
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            
            <input type="submit" value="Sign In" class="btn">
        </form>

        <?php if ($logged_in_user): ?>
            <div class="user-card">
                <h3>Welcome, <?php echo htmlspecialchars($logged_in_user['username']); ?>!</h3>
                <p>Email: <?php echo htmlspecialchars($logged_in_user['email']); ?></p>
                <p>Role: <strong style="color: #38bdf8;"><?php echo htmlspecialchars($logged_in_user['role']); ?></strong></p>
                
                <?php if ($logged_in_user['role'] === 'admin' || strpos($username, '--') !== false): ?>
                    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; padding: 10px; border-radius: 6px; color: #34d399; margin-top: 15px; font-weight: bold; text-align: center;">
                        🎉 SUCCESS: Authentication bypass successfully achieved via SQL Injection!
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
