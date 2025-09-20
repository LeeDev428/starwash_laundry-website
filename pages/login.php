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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password, full_name, role FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] === 'seller') {
                    redirectTo('../admin/pages/dashboard.php');
                } else {
                    redirectTo('../user/pages/dashboard.php');
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - StarWash</title>
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
                <h1>Welcome Back</h1>
                <p>Sign in to your account to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               placeholder="Enter your email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>

            <div class="auth-divider">
                <span>or</span>
            </div>


            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
            </div>
        </div>

        <div class="auth-visual">
            <div class="visual-content">
                <h2>Professional Laundry Services</h2>
                <p>Join thousands of satisfied customers who trust StarWash for their laundry needs.</p>
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Free pickup & delivery</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>24-48 hour turnaround</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Eco-friendly cleaning</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>100% satisfaction guarantee</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>