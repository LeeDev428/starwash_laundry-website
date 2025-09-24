<?php
require_once '../../includes/config.php';

// Check if user is logged in and has seller role
if (!isLoggedIn()) {
    redirectTo('../../pages/login.php');
}

if (isUser()) {
    redirectTo('../../user/pages/dashboard.php');
}

// Get user info
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$email = $_SESSION['email'];

// Get dashboard stats
try {
    // Total users
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
    $stmt->execute();
    $total_users = $stmt->fetchColumn();
    
    // Total services
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_services FROM services");
    $stmt->execute();
    $total_services = $stmt->fetchColumn();
    
    // Total orders
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders");
    $stmt->execute();
    $total_orders = $stmt->fetchColumn();
    
    // Total revenue
    $stmt = $pdo->prepare("SELECT SUM(total_price) as total_revenue FROM orders WHERE status = 'completed'");
    $stmt->execute();
    $total_revenue = $stmt->fetchColumn() ?: 0;
    
    // Recent orders
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name as customer_name, s.name as service_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN services s ON o.service_id = s.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_orders = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Handle error
    $total_users = $total_services = $total_orders = $total_revenue = 0;
    $recent_orders = [];
}

// Use main layout and components
$page_title = 'Admin Dashboard';
$current_page = 'dashboard';
require_once '../layouts/main.php';
startAdminLayout($page_title, $current_page);
?>

            <div class="admin-content">
                <!-- Dashboard Header -->
                <div class="dashboard-header">
                    <div class="header-content">
                        <h1>Welcome back, <?php echo htmlspecialchars($full_name); ?>!</h1>
                        <p>Here's what's happening with your laundry business today.</p>
                    </div>
                    <!-- header-actions removed: Add New Service button -->
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo number_format($total_users); ?></div>
                            <div class="stat-label">Total Customers</div>
                        </div>
                        <div class="stat-trend">
                           
                  
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo number_format($total_services); ?></div>
                            <div class="stat-label">Active Services</div>
                        </div>
                       
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                        
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="currency-icon">₱</span>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">₱<?php echo number_format($total_revenue, 2); ?></div>
                            <div class="stat-label">Total Revenue</div>
                        </div>
                       
                    </div>
                </div>

                <!-- Dashboard Grid -->
                <div class="dashboard-grid">
                    <!-- Recent Orders -->
                    <div class="dashboard-card">
                        <div class="card-body">
                            <div class="orders-list">
                                <?php foreach ($recent_orders as $order): ?>
                                    <div class="order-item">
                                        <div class="order-info">
                                            <div class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            <div class="service-name"><?php echo htmlspecialchars($order['service_name']); ?></div>
                                        </div>
                                        <div class="order-meta">
                                            <div class="order-price">₱<?php echo number_format($order['total_price'], 2); ?></div>
                                            <div class="order-status status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions and System Status removed as requested -->
                </div>
            </div>

<?php
require_once '../layouts/main.php';
endAdminLayout();
?>
