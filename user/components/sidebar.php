<?php
// User Sidebar Component
function renderUserSidebar($currentPage = '') {
?>
    <aside class="user-sidebar" id="userSidebar">
        <div class="sidebar-content">
            <div class="user-welcome">
                <div class="welcome-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="welcome-text">
                    <h3>Welcome back!</h3>
                    <p><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Customer'); ?></p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-group">
                    <a href="../pages/dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="../pages/services.php" class="nav-item <?php echo $currentPage === 'services' ? 'active' : ''; ?>">
                        <i class="fas fa-search"></i>
                        <span>Browse Services</span>
                    </a>
                </div>
                
                <div class="nav-group">
                    <span class="nav-group-title">My Account</span>
                    
                    <a href="../pages/orders.php" class="nav-item <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-bag"></i>
                        <span>My Orders</span>
                        <span class="nav-badge">2</span>
                    </a>
                    
                    <a href="../pages/favorites.php" class="nav-item <?php echo $currentPage === 'favorites' ? 'active' : ''; ?>">
                        <i class="fas fa-heart"></i>
                        <span>Favorites</span>
                    </a>
                    
                    <a href="../pages/history.php" class="nav-item <?php echo $currentPage === 'history' ? 'active' : ''; ?>">
                        <i class="fas fa-history"></i>
                        <span>Order History</span>
                    </a>
                </div>
                
                <div class="nav-group">
                    <span class="nav-group-title">Account</span>
                    
                    <a href="../pages/profile.php" class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i>
                        <span>Profile Settings</span>
                    </a>
                    
                    <a href="../pages/addresses.php" class="nav-item <?php echo $currentPage === 'addresses' ? 'active' : ''; ?>">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>My Addresses</span>
                    </a>
                    
                    <a href="../pages/payments.php" class="nav-item <?php echo $currentPage === 'payments' ? 'active' : ''; ?>">
                        <i class="fas fa-credit-card"></i>
                        <span>Payment Methods</span>
                    </a>
                </div>
                
                <div class="nav-group">
                    <span class="nav-group-title">Support</span>
                    
                    <a href="../pages/help.php" class="nav-item <?php echo $currentPage === 'help' ? 'active' : ''; ?>">
                        <i class="fas fa-question-circle"></i>
                        <span>Help & FAQ</span>
                    </a>
                    
                    <a href="../pages/contact.php" class="nav-item <?php echo $currentPage === 'contact' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i>
                        <span>Contact Support</span>
                    </a>
                </div>
            </nav>
            
            <div class="sidebar-promotion">
                <div class="promo-card">
                    <div class="promo-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="promo-content">
                        <h4>Premium Member</h4>
                        <p>Upgrade for exclusive benefits and faster service!</p>
                        <button class="btn btn-small btn-primary">Upgrade Now</button>
                    </div>
                </div>
            </div>
        </div>
    </aside>
<?php
}
?>