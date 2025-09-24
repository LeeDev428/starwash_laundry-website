<?php
require_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isSeller()) {
        redirectTo('../admin/pages/dashboard.php');
    } else {
        redirectTo('../user/pages/dashboard.php');
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $role = sanitizeInput($_POST['role']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!in_array($role, ['user', 'seller'])) {
        $error = 'Please select a valid role.';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = 'Username or email already exists.';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $role])) {
                    // Redirect to login after successful registration so the form is cleared
                    redirectTo('login.php?registered=1');
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - StarWash</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="../index.php" class="logo">
                    <h2>StarWash</h2>
                </a>
                <h1>Create Account</h1>
                <p>Join us for professional laundry services</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="full_name" name="full_name" required 
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                   placeholder="Enter your full name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username *</label>
                        <div class="input-group">
                            <i class="fas fa-at"></i>
                            <input type="text" id="username" name="username" required 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   placeholder="Choose a username">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               placeholder="Enter your email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                               placeholder="Enter your phone number">
                    </div>
                </div>

                <div class="form-group">
                    <label for="role">I am a *</label>
                    <div class="role-selection">
                        <label class="role-option">
                            <input type="radio" name="role" value="user" 
                                   <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') ? 'checked' : ''; ?>>
                            <div class="role-card">
                                <i class="fas fa-user-circle"></i>
                                <h4>Customer</h4>
                                <p>I want to use laundry services</p>
                            </div>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="seller" 
                                   <?php echo (isset($_POST['role']) && $_POST['role'] === 'seller') ? 'checked' : ''; ?>>
                            <div class="role-card">
                                <i class="fas fa-store"></i>
                                <h4>Service Provider</h4>
                                <p>I want to offer laundry services</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" required 
                                   placeholder="Create a password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   placeholder="Confirm your password">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox-container">
                        <input id="terms" type="checkbox" name="terms" required>
                        <label class="checkmark" for="terms"></label>
                        <span class="checkbox-text">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>

            <div class="auth-divider">
                <span>or</span>
            </div>


            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>

        <div class="auth-visual">
            <div class="visual-content">
                <h2>Join StarWash Today</h2>
                <p>Whether you're a customer looking for quality laundry services or a provider wanting to grow your business.</p>
                <div class="stats">
                    <div class="stat">
                        <h3>1000+</h3>
                        <p>Happy Customers</p>
                    </div>
                    <div class="stat">
                        <h3>50+</h3>
                        <p>Service Providers</p>
                    </div>
                    <div class="stat">
                        <h3>24/7</h3>
                        <p>Support Available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>