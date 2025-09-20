<?php
require_once '../includes/config.php';

// Check if user is logged in and is a regular user
if (!isLoggedIn() || !isUser()) {
    redirectTo('login.php');
}

// Get user's orders
try {
    $stmt = $pdo->prepare("
        SELECT o.*, s.service_name, s.price, u.full_name as seller_name 
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        JOIN users u ON o.seller_id = u.id 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

// Get available services
try {
    $stmt = $pdo->prepare("
        SELECT s.*, u.full_name as seller_name 
        FROM services s 
        JOIN users u ON s.seller_id = u.id 
        WHERE s.status = 'active' 
        ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - StarWash</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>StarWash</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active" data-section="overview">
                    <i class="fas fa-home"></i>
                    <span>Overview</span>
                </a>
                <a href="#" class="nav-item" data-section="services">
                    <i class="fas fa-list"></i>
                    <span>Browse Services</span>
                </a>
                <a href="#" class="nav-item" data-section="orders">
                    <i class="fas fa-shopping-bag"></i>
                    <span>My Orders</span>
                </a>
                <a href="#" class="nav-item" data-section="profile">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="header-left">
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                    <p>Manage your laundry orders and explore our services</p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                            <span class="user-role">Customer</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Overview Section -->
            <section id="overview" class="dashboard-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($orders); ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'pending'; })); ?></h3>
                            <p>Pending Orders</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'completed'; })); ?></h3>
                            <p>Completed Orders</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format(array_sum(array_column($orders, 'total_amount')), 2); ?></h3>
                            <p>Total Spent</p>
                        </div>
                    </div>
                </div>

                <div class="recent-orders">
                    <h2>Recent Orders</h2>
                    <?php if (empty($orders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <h3>No orders yet</h3>
                            <p>Browse our services and place your first order!</p>
                            <button class="btn btn-primary" onclick="showSection('services')">Browse Services</button>
                        </div>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <h4><?php echo htmlspecialchars($order['service_name']); ?></h4>
                                        <p>Provider: <?php echo htmlspecialchars($order['seller_name']); ?></p>
                                        <span class="order-date"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="order-status">
                                        <span class="status status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                        <span class="order-amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Services Section -->
            <section id="services" class="dashboard-section">
                <div class="section-header">
                    <h2>Available Services</h2>
                    <p>Choose from our range of professional laundry services</p>
                </div>
                <div class="services-grid">
                    <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <div class="service-header">
                                <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
                                <span class="service-price">$<?php echo number_format($service['price'], 2); ?></span>
                            </div>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="service-details">
                                <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($service['duration']); ?></span>
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($service['seller_name']); ?></span>
                            </div>
                            <button class="btn btn-primary btn-full" onclick="bookService(<?php echo $service['id']; ?>)">
                                Book Now
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Orders Section -->
            <section id="orders" class="dashboard-section">
                <div class="section-header">
                    <h2>My Orders</h2>
                    <p>Track and manage your laundry orders</p>
                </div>
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>No orders found</h3>
                        <p>You haven't placed any orders yet.</p>
                    </div>
                <?php else: ?>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Service</th>
                                    <th>Provider</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['seller_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="status status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-small" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Profile Section -->
            <section id="profile" class="dashboard-section">
                <div class="section-header">
                    <h2>Profile Settings</h2>
                    <p>Manage your account information</p>
                </div>
                <div class="profile-form">
                    <form>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>