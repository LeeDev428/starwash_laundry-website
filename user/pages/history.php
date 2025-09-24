<?php
require_once '../../includes/config.php';

if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    header('Location: ../../pages/login.php');
    exit;
}

$page_title = 'History';
$current_page = 'history';
$additionalCSS = [];
$additionalJS = [];
require_once '../layouts/main.php';
if (function_exists('startUserLayout')) startUserLayout($page_title, $current_page, $additionalCSS, $additionalJS);

$user_id = $_SESSION['user_id'] ?? null;

?>

<div class="user-card">
  <div class="user-card-header">
    <h3 class="user-card-title">Your Booking History</h3>
  </div>
  <div class="user-card-body">
    <?php
    try {
        $stmt = $pdo->prepare('SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_at DESC');
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        $rows = [];
    }

    if (empty($rows)) {
        echo '<p>No bookings found.</p>';
    } else {
        echo '<ul class="appointments-list">';
        foreach ($rows as $r) {
            $dt = date('M d, Y H:i', strtotime($r['appointment_at']));
            $notes = htmlspecialchars($r['notes'] ?: 'No details');
            echo "<li><strong>{$dt}</strong> — {$notes}</li>";
        }
        echo '</ul>';
    }
    ?>
  </div>
</div>

<?php if (function_exists('endUserLayout')) endUserLayout(); ?>
