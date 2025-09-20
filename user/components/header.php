<?php
// User Header Component
function renderUserHeader($pageTitle = 'My Dashboard', $user = null) {
    $user = $user ?: $_SESSION;
?>
    <header class="user-header">
        <div class="header-container">
            <div class="header-left">
                <button class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo">
                    <i class="fas fa-tshirt"></i>
                    <span>StarWash</span>
                </div>
            </div>
            
            <div class="header-center">
                <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
            </div>
            
            <div class="header-right">
                <div class="header-actions">
                    <button class="action-btn" title="Quick Order">
                        <i class="fas fa-plus"></i>
                    </button>
                    
                    <button class="action-btn" title="My Orders" onclick="showSection('orders')">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="badge">2</span>
                    </button>
                    
                    <button class="action-btn" title="Notifications">
                        <i class="fas fa-bell"></i>
                    </button>
                </div>
                
                <div class="user-profile">
                    <div class="profile-avatar">
                        <img src="../../assets/images/default-avatar.png" alt="Profile" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="avatar-fallback">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name"><?php echo htmlspecialchars($user['full_name'] ?? 'Customer'); ?></span>
                        <span class="profile-status">Active Member</span>
                    </div>
                    <div class="profile-dropdown">
                        <button class="dropdown-toggle" onclick="toggleUserProfileMenu()">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" id="userProfileMenu">
                            <a href="../pages/profile.php"><i class="fas fa-user"></i> My Profile</a>
                            <a href="../pages/orders.php"><i class="fas fa-list"></i> My Orders</a>
                            <a href="../pages/favorites.php"><i class="fas fa-heart"></i> Favorites</a>
                            <hr>
                            <a href="../pages/help.php"><i class="fas fa-question-circle"></i> Help & Support</a>
                            <a href="../../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
<?php
}
?>