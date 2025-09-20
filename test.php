<?php
require_once 'includes/config.php';

echo "<h2>System Test</h2>";

// Test database connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $user_count = $stmt->fetchColumn();
    echo "<p>✅ Database connected successfully</p>";
    echo "<p>📊 Total users in database: $user_count</p>";
    
    // Get admin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'seller' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>👨‍💼 Admin user found: " . $admin['email'] . "</p>";
        echo "<p>🔐 Password hash length: " . strlen($admin['password']) . " characters</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Test Login</h3>";
echo "<p>Try logging in with:</p>";
echo "<ul>";
echo "<li><strong>Email:</strong> admin@starwash.com</li>";
echo "<li><strong>Password:</strong> password</li>";
echo "</ul>";

echo "<a href='pages/login.php' style='background: #4299e1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
</style>