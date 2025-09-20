<?php
// Admin Header Component
function renderAdminHeader($pageTitle = 'Admin Dashboard', $user = null) {
    $user = $user ?: $_SESSION;
?>
    <header class="admin-header">
        <div class="header-left">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-title">
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                <span class="breadcrumb">Admin Panel</span>
            </div>
        </div>
        
        <div class="header-right">
            <div class="header-actions">
                <button class="action-btn" onclick="showNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </button>
                
                <button class="action-btn" onclick="showMessages()">
                    <i class="fas fa-envelope"></i>
                    <span class="badge">5</span>
                </button>
                
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search orders, users...">
                </div>
            </div>
            
            <div class="admin-profile">
                <div class="profile-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="profile-info">
                    <span class="profile-name"><?php echo htmlspecialchars($user['full_name'] ?? 'Admin'); ?></span>
                    <span class="profile-role">Administrator</span>
                </div>
                <div class="profile-dropdown">
                    <button class="dropdown-toggle" onclick="toggleProfileMenu()">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="profileMenu">
                        <a href="../pages/profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="../pages/settings.php"><i class="fas fa-cog"></i> Settings</a>
                        <a href="../../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
<?php
}
?>