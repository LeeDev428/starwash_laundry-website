<?php
require_once '../../includes/config.php';
require_once '../layouts/main.php';

startUserLayout('Settings - Profile');

// Ensure user is logged in
requireLogin();

// Helper: check if a column exists in a table
function columnExists($pdo, $table, $column) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table AND COLUMN_NAME = :col");
        $stmt->execute([':db' => DB_NAME, ':table' => $table, ':col' => $column]);
        return (bool) $stmt->fetchColumn();
    } catch (Exception $e) {
        // If INFORMATION_SCHEMA is not accessible, be conservative and return false
        return false;
    }
}

$userId = $_SESSION['user_id'];

$message = '';
$error = '';

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle avatar deletion request
    if (!empty($_POST['delete_avatar'])) {
        try {
            // Fetch current avatar path
            $stmt = $pdo->prepare('SELECT avatar FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $cur = $stmt->fetch();
            if (!empty($cur['avatar'])) {
                // Try to delete file if exists on disk (convert relative to absolute)
                $filePath = __DIR__ . '/../../' . ltrim(str_replace('..\/', '', $cur['avatar']), '/');
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            // Update DB to null avatar if column exists
            if (columnExists($pdo, 'users', 'avatar')) {
                $stmt = $pdo->prepare('UPDATE users SET avatar = NULL WHERE id = ?');
                $stmt->execute([$userId]);
            }

            unset($_SESSION['avatar']);
            $message = 'Avatar deleted.';
        } catch (Exception $e) {
            $error = 'Failed to delete avatar.';
        }
    }
    // Sanitize inputs
    $raw_name = $_POST['full_name'] ?? '';
    $raw_email = $_POST['email'] ?? '';
    $raw_phone = $_POST['phone'] ?? '';
    $raw_password = $_POST['password'] ?? '';

    $full_name = sanitizeInput($raw_name);
    $email = sanitizeInput($raw_email);
    $phone = sanitizeInput($raw_phone);

    // Basic validation
    if (empty($full_name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    }

    // Handle avatar upload if provided
    $avatarPath = null;
    if (empty($error) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $avatar = $_FILES['avatar'];
        if ($avatar['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
            if (!in_array($avatar['type'], $allowed)) {
                $error = 'Avatar must be a PNG, JPG or WEBP image.';
            } elseif ($avatar['size'] > 2 * 1024 * 1024) {
                $error = 'Avatar must be smaller than 2MB.';
            } else {
                $ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
                $targetDir = '../../assets/images/avatars/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
                $dest = $targetDir . $filename;
                if (move_uploaded_file($avatar['tmp_name'], $dest)) {
                    // Store web-accessible relative path
                    $avatarPath = '../../assets/images/avatars/' . $filename;
                } else {
                    $error = 'Failed to save uploaded avatar.';
                }
            }
        } else {
            $error = 'Avatar upload error.';
        }
    }

    // If no errors, perform update
    if (empty($error)) {
        try {
            $fields = ['full_name' => $full_name, 'email' => $email, 'phone' => $phone];

            // If password provided, hash and include
            if (!empty($raw_password)) {
                $passwordHash = password_hash($raw_password, PASSWORD_DEFAULT);
                $fields['password_hash'] = $passwordHash;
            }

            if ($avatarPath) {
                // Ensure the avatar column exists; if not, try to create it.
                if (!columnExists($pdo, 'users', 'avatar')) {
                    try {
                        $pdo->exec("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL");
                    } catch (Exception $e) {
                        // If we cannot add the column (permissions/schema restrictions), skip saving avatar
                        $avatarPath = null;
                    }
                }

                if ($avatarPath && columnExists($pdo, 'users', 'avatar')) {
                    $fields['avatar'] = $avatarPath;
                }
            }

            // Build SET clause
            $sets = [];
            $params = [];
            foreach ($fields as $col => $val) {
                $sets[] = "`$col` = :$col";
                $params[":$col"] = $val;
            }
            $params[':id'] = $userId;

            $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $message = 'Profile updated successfully.';

            // Refresh session name if changed
            $_SESSION['full_name'] = $full_name;
            // Refresh session avatar if updated
            if (!empty($fields['avatar'])) {
                $_SESSION['avatar'] = $fields['avatar'];
            }

        } catch (Exception $e) {
            $error = 'Failed to update profile: ' . $e->getMessage();
        }
    }
}

// Fetch current user data, but only select columns that exist to avoid SQL errors
$selectCols = ['id', 'full_name', 'email', 'phone'];
if (columnExists($pdo, 'users', 'avatar')) {
    $selectCols[] = 'avatar';
}
$sqlSelect = 'SELECT ' . implode(', ', $selectCols) . ' FROM users WHERE id = ? LIMIT 1';
$stmt = $pdo->prepare($sqlSelect);
$stmt->execute([$userId]);
$user = $stmt->fetch();

// If avatar exists in DB result and session is not set, populate session for immediate header display
if (!empty($user['avatar']) && empty($_SESSION['avatar'])) {
    $_SESSION['avatar'] = $user['avatar'];
}

?>

<div class="profile-page profile-layout">
    <?php if (!empty($message)): ?>
        <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="profile-grid">
        <div class="profile-grid-left">
            <form method="post" enctype="multipart/form-data" class="profile-form">
                <div class="form-row">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label for="password">New Password (leave blank to keep current)</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password">
                </div>

                <div class="form-row actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>

        <aside class="profile-grid-right">
            <div class="profile-card">
                <div class="profile-card-avatar">
                    <?php if (!empty($user['avatar'])): ?>
                        <img class="profile-preview-img" src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                    <?php else: ?>
                        <div class="profile-avatar-placeholder">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profile-card-actions">
                    <label class="btn btn-outline" for="avatar">Change picture</label>
                    <form method="post" enctype="multipart/form-data" id="avatarUploadForm" style="display:inline-block">
                        <input type="file" id="avatar" name="avatar" accept="image/*" style="display:none" onchange="document.getElementById('avatarUploadForm').submit();">
                    </form>
                    <form method="post" style="display:inline-block;margin-left:.5rem">
                        <button name="delete_avatar" value="1" class="btn btn-secondary">Delete picture</button>
                    </form>
                </div>

                <div class="profile-card-meta">
                    <h4><?php echo htmlspecialchars($user['full_name'] ?? ($_SESSION['full_name'] ?? '')); ?></h4>
                    <p class="muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php
endUserLayout();

?>
<script>
// Auto-hide profile page success alerts
document.addEventListener('DOMContentLoaded', function(){
    const alertEl = document.querySelector('.profile-page .alert.success');
    if (alertEl) {
        setTimeout(()=>{
            alertEl.classList.add('hidden');
            setTimeout(()=> alertEl.remove(), 500);
        }, 3500);
    }
});
</script>
