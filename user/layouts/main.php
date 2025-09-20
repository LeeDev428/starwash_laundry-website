<?php
// User Main Layout
require_once '../../includes/config.php';

// Check if user is logged in and is regular user
if (!isLoggedIn() || !isUser()) {
    redirectTo('../../pages/login.php');
}

function startUserLayout($pageTitle = 'My Dashboard', $currentPage = '', $additionalCSS = [], $additionalJS = []) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - StarWash</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Core Styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/user/user.css">
    
    <!-- Additional CSS -->
    <?php foreach ($additionalCSS as $css): ?>
        <link rel="stylesheet" href="<?php echo $css; ?>">
    <?php endforeach; ?>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="user-body">
    <div class="user-layout">
        <?php
        require_once '../components/header.php';
        renderUserHeader($pageTitle);
        ?>
        
        <div class="user-main">
            <?php
            require_once '../components/sidebar.php';
            renderUserSidebar($currentPage);
            ?>
            
            <main class="user-content">
<?php
}

function endUserLayout($additionalJS = []) {
?>
            </main>
        </div>
    </div>
    
    <!-- Core Scripts -->
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/user/user.js"></script>
    
    <!-- Additional JS -->
    <?php foreach ($additionalJS as $js): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
</body>
</html>
<?php
}
?>