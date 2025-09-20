<?php
session_start();
echo "<h2>Session Debug Information</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Data:\n";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    echo "<p>User is logged in with ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Role: " . ($_SESSION['role'] ?? 'No role set') . "</p>";
    echo "<p>Username: " . ($_SESSION['username'] ?? 'No username set') . "</p>";
} else {
    echo "<p>No user logged in</p>";
}

echo "<hr>";
echo "<a href='pages/login.php'>Go to Login</a> | ";
echo "<a href='pages/register.php'>Go to Register</a> | ";
echo "<a href='admin/pages/dashboard.php'>Admin Dashboard</a> | ";
echo "<a href='user/pages/dashboard.php'>User Dashboard</a>";
?>