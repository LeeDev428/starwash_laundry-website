<?php
require_once '../../includes/config.php';

// Check if user is logged in and has user role
if (!isLoggedIn()) {
    redirectTo('../../pages/login.php');
}
if (isSeller()) {
    redirectTo('../../admin/pages/dashboard.php');
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$email = $_SESSION['email'];

// Get user stats
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_orders = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$user_id]);
    $pending_orders = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) as completed_orders FROM orders WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$user_id]);
    $completed_orders = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT SUM(total_price) as total_spent FROM orders WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$user_id]);
    $total_spent = $stmt->fetchColumn() ?: 0;
    $stmt = $pdo->prepare("SELECT o.*, s.name as service_name, s.description FROM orders o JOIN services s ON o.service_id = s.id WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_orders = $stmt->fetchAll();
    $stmt = $pdo->prepare("SELECT * FROM services WHERE status = 'active' ORDER BY name LIMIT 6");
    $stmt->execute();
    $featured_services = $stmt->fetchAll();
} catch (PDOException $e) {
    $total_orders = $pending_orders = $completed_orders = $total_spent = 0;
    $recent_orders = [];
    $featured_services = [];
}

// Use main layout and components
$page_title = 'My Dashboard';
$current_page = 'dashboard';
require_once '../layouts/main.php';
startUserLayout($page_title, $current_page);
?>
<div class="user-dashboard">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>Welcome back, <?php echo htmlspecialchars($full_name); ?>!</h1>
            <p>Ready for fresh, clean laundry? We're here to help!</p>
        </div>
        <div class="welcome-action">
            <a href="#services" class="btn user-btn-primary">
                <i class="fas fa-plus"></i>
                Book New Service
            </a>
        </div>
    </div>

    <!-- User Stats -->
    <div class="user-stats-grid">
        <div class="user-stat-card">
            <div class="user-stat-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="user-stat-value"><?php echo number_format($total_orders); ?></div>
            <div class="user-stat-label">Total Orders</div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="user-stat-value"><?php echo number_format($pending_orders); ?></div>
            <div class="user-stat-label">Pending Orders</div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="user-stat-value"><?php echo number_format($completed_orders); ?></div>
            <div class="user-stat-label">Completed</div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="user-stat-value">$<?php echo number_format($total_spent, 2); ?></div>
            <div class="user-stat-label">Total Spent</div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        <!-- Recent Orders -->
        <div class="user-card">
            <div class="user-card-header">
                <h3 class="user-card-title">Recent Orders</h3>
                <a href="#" class="view-all-link">View All</a>
            </div>
            <div class="user-card-body">
                <?php if (empty($recent_orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h4>No orders yet</h4>
                        <p>Ready to get started? Book your first laundry service!</p>
                        <a href="#services" class="user-btn user-btn-primary">Browse Services</a>
                    </div>
                <?php else: ?>
                    <div class="orders-timeline">
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="order-timeline-item">
                                <div class="timeline-marker status-<?php echo $order['status']; ?>"></div>
                                <div class="timeline-content">
                                    <div class="order-header">
                                        <h4><?php echo htmlspecialchars($order['service_name']); ?></h4>
                                        <span class="order-status status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <p class="order-description"><?php echo htmlspecialchars($order['description']); ?></p>
                                    <div class="order-footer">
                                        <span class="order-price">$<?php echo number_format($order['total_price'], 2); ?></span>
                                        <span class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Featured Services -->
        <div class="user-card" id="services">
            <div class="user-card-header">
                <h3 class="user-card-title">Featured Services</h3>
                <a href="#" class="view-all-link">View All</a>
            </div>
            <div class="user-card-body">
                <?php if (empty($featured_services)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tshirt"></i>
                        <p>No services available at the moment</p>
                    </div>
                <?php else: ?>
                    <div class="services-grid">
                        <?php foreach ($featured_services as $service): ?>
                            <div class="service-card">
                                <div class="service-card-header">
                                    <h4 class="service-title"><?php echo htmlspecialchars($service['name']); ?></h4>
                                    <div class="service-price">$<?php echo number_format($service['price'], 2); ?></div>
                                </div>
                                <div class="service-card-body">
                                    <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                                    <div class="service-meta">
                                        <div class="service-rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <span>4.9</span>
                                        </div>
                                    </div>
                                    <div class="service-actions">
                                        <button class="user-btn user-btn-primary">Book Now</button>
                                        <button class="user-btn user-btn-outline">Learn More</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="user-card">
            <div class="user-card-header">
                <h3 class="user-card-title">Quick Actions</h3>
            </div>
            <div class="user-card-body">
                <div class="quick-actions-grid">
                    <a href="#" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="action-content">
                            <h4>Schedule Pickup</h4>
                            <p>Book a convenient pickup time</p>
                        </div>
                    </a>
                    
                    <a href="#" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="action-content">
                            <h4>Track Order</h4>
                            <p>Monitor your laundry status</p>
                        </div>
                    </a>
                    
                    <a href="#" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="action-content">
                            <h4>Favorites</h4>
                            <p>Your preferred services</p>
                        </div>
                    </a>
                    
                    <a href="#" class="quick-action-card">
                        <div class="action-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="action-content">
                            <h4>Support</h4>
                            <p>Get help when you need it</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* User Dashboard Specific Styles */
.user-dashboard {
    padding: 0;
}

.welcome-section {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.welcome-content h1 {
    margin: 0 0 0.5rem;
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
}

.welcome-content p {
    margin: 0;
    color: #718096;
    font-size: 1.125rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.view-all-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: color 0.2s ease;
}

.view-all-link:hover {
    color: #764ba2;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #a0aec0;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #cbd5e0;
}

.empty-state h4 {
    margin: 0 0 0.5rem;
    color: #4a5568;
    font-size: 1.25rem;
}

.empty-state p {
    margin: 0 0 1.5rem;
    color: #718096;
}

/* Orders Timeline */
.orders-timeline {
    position: relative;
}

.order-timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
    position: relative;
}

.order-timeline-item::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 2rem;
    bottom: -1.5rem;
    width: 2px;
    background: #e2e8f0;
}

.order-timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    z-index: 1;
}

.timeline-marker.status-pending {
    background: #fbbf24;
}

.timeline-marker.status-processing {
    background: #3b82f6;
}

.timeline-marker.status-completed {
    background: #10b981;
}

.timeline-content {
    flex: 1;
    background: #f8fafc;
    padding: 1.25rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.order-header h4 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #2d3748;
}

.order-status {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.order-status.status-pending {
    background: #fef3cd;
    color: #92400e;
}

.order-status.status-processing {
    background: #dbeafe;
    color: #1e40af;
}

.order-status.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.order-description {
    margin: 0 0 1rem;
    color: #718096;
    font-size: 0.875rem;
    line-height: 1.5;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-price {
    font-weight: 700;
    color: #667eea;
    font-size: 1.125rem;
}

.order-date {
    color: #a0aec0;
    font-size: 0.875rem;
}

/* Quick Actions Grid */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.quick-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 0.75rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.quick-action-card:hover {
    background: white;
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.quick-action-card .action-icon {
    width: 3rem;
    height: 3rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.quick-action-card h4 {
    margin: 0 0 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
}

.quick-action-card p {
    margin: 0;
    color: #718096;
    font-size: 0.875rem;
    line-height: 1.4;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .welcome-section {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .order-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

@media (max-width: 480px) {
    .welcome-content h1 {
        font-size: 1.5rem;
    }
    
    .welcome-content p {
        font-size: 1rem;
    }
    
    .timeline-content {
        padding: 1rem;
    }
}
</style>

<?php
endUserLayout();
?>