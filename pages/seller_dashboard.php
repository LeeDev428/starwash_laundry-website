<?php
require_once '../includes/config.php';

// Check if user is logged in and is a seller
if (!isLoggedIn() || !isSeller()) {
    redirectTo('login.php');
}

// Get seller's services and orders
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE seller_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $services = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT o.*, s.service_name, u.full_name as customer_name 
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        JOIN users u ON o.user_id = u.id 
        WHERE o.seller_id = ? 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - StarWash</title>
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
                <span class="badge">Seller</span>
            </div>
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active" data-section="overview">
                    <i class="fas fa-chart-line"></i>
                    <span>Overview</span>
                </a>
                <a href="#" class="nav-item" data-section="services">
                    <i class="fas fa-cogs"></i>
                    <span>My Services</span>
                </a>
                <a href="#" class="nav-item" data-section="orders">
                    <i class="fas fa-list-alt"></i>
                    <span>Orders</span>
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
                    <h1>Seller Dashboard</h1>
                    <p>Manage your services and orders, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary" onclick="showAddServiceModal()">
                        <i class="fas fa-plus"></i>
                        Add Service
                    </button>
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="user-details">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                            <span class="user-role">Service Provider</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Overview Section -->
            <section id="overview" class="dashboard-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($services); ?></h3>
                            <p>Total Services</p>
                        </div>
                    </div>
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
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format(array_sum(array_column($orders, 'total_amount')), 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <h2>Recent Orders</h2>
                    <?php if (empty($orders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <h3>No orders yet</h3>
                            <p>Add services to start receiving orders from customers!</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <h4><?php echo htmlspecialchars($order['service_name']); ?></h4>
                                        <p>Customer: <?php echo htmlspecialchars($order['customer_name']); ?></p>
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
                    <h2>My Services</h2>
                    <p>Manage your laundry services and pricing</p>
                    <button class="btn btn-primary" onclick="showAddServiceModal()">
                        <i class="fas fa-plus"></i>
                        Add New Service
                    </button>
                </div>
                <?php if (empty($services)): ?>
                    <div class="empty-state">
                        <i class="fas fa-cogs"></i>
                        <h3>No services added</h3>
                        <p>Add your first service to start receiving orders.</p>
                        <button class="btn btn-primary" onclick="showAddServiceModal()">Add Service</button>
                    </div>
                <?php else: ?>
                    <div class="services-grid">
                        <?php foreach ($services as $service): ?>
                            <div class="service-card">
                                <div class="service-header">
                                    <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
                                    <div class="service-actions">
                                        <button class="btn btn-small" onclick="editService(<?php echo $service['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-small btn-danger" onclick="deleteService(<?php echo $service['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <p><?php echo htmlspecialchars($service['description']); ?></p>
                                <div class="service-details">
                                    <span class="service-price">$<?php echo number_format($service['price'], 2); ?></span>
                                    <span class="service-duration">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo htmlspecialchars($service['duration']); ?>
                                    </span>
                                </div>
                                <div class="service-status">
                                    <span class="status status-<?php echo $service['status']; ?>">
                                        <?php echo ucfirst($service['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Orders Section -->
            <section id="orders" class="dashboard-section">
                <div class="section-header">
                    <h2>Customer Orders</h2>
                    <p>Manage and track customer orders</p>
                </div>
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-list-alt"></i>
                        <h3>No orders yet</h3>
                        <p>Orders will appear here when customers book your services.</p>
                    </div>
                <?php else: ?>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Service</th>
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
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <select class="status-select" onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="in_progress" <?php echo $order['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
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
                    <h2>Business Profile</h2>
                    <p>Manage your business information</p>
                </div>
                <div class="profile-form">
                    <form>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Business Name</label>
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
                        <div class="form-group">
                            <label>Business Description</label>
                            <textarea rows="4" placeholder="Tell customers about your laundry business..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <!-- Add Service Modal -->
    <div id="addServiceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Service</h2>
                <span class="close">&times;</span>
            </div>
            <form class="modal-form">
                <div class="form-group">
                    <label>Service Name</label>
                    <input type="text" name="service_name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" name="duration" placeholder="e.g., 24 hours" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Service</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script src="../assets/js/seller.js"></script>
</body>
</html>