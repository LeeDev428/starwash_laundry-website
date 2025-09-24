<?php
// Admin Main Layout
require_once '../../includes/config.php';

// Check if user is logged in and is admin/seller
if (!isLoggedIn() || !isSeller()) {
    redirectTo('../../pages/login.php');
}

function startAdminLayout($pageTitle = 'Admin Dashboard', $currentPage = '', $additionalCSS = [], $additionalJS = [], $panelMode = 'full') {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - StarWash Admin</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Core Styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin/admin.css">
    
    <!-- Additional CSS -->
    <?php foreach ($additionalCSS as $css): ?>
        <link rel="stylesheet" href="<?php echo $css; ?>">
    <?php endforeach; ?>
    
    <style>
        body {
            background: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php
        if ($panelMode === 'panel') {
            require_once '../components/sidebar-panel.php';
            renderAdminSidebarPanel($currentPage);
        } else {
            require_once '../components/sidebar.php';
            renderAdminSidebar($currentPage);
        }
        // Render orders slide-over panel so it's available on all admin pages
        require_once '../components/orders-panel.php';
        renderAdminOrdersPanel();
        ?>
        
    <main class="admin-main<?php echo $panelMode === 'panel' ? ' panel-mode' : ''; ?>">
            <?php
            require_once '../components/header.php';
            renderAdminHeader($pageTitle);
            ?>
            
            <div class="admin-content">
<?php
}

function endAdminLayout($additionalJS = []) {
?>
            </div>
        </main>
    </div>
    
    <!-- Core Scripts -->
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/admin/admin.js"></script>
    <script src="../../assets/js/admin/orders.js"></script>
    
    <!-- Additional JS -->
    <?php foreach ($additionalJS as $js): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
</body>
</html>
<?php
}
?>