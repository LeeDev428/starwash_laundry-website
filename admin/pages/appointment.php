<?php
require_once '../../includes/config.php';

// Check if user is logged in and has admin/seller role
if (!isLoggedIn()) {
    redirectTo('../../pages/login.php');
}

if (isUser()) {
    redirectTo('../../user/pages/dashboard.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $appointment_id]);
        $message = 'Appointment status updated successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to update appointment status.';
    }
}

// Get appointments for the current month
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$month_start = $current_month . '-01';
$month_end = date('Y-m-t', strtotime($month_start));

try {
    $stmt = $pdo->prepare("
        SELECT a.*, s.service_name, s.price as service_price, 
               u.full_name as customer_name, u.phone as customer_phone, u.email as customer_email,
               ts.slot_label, ts.slot_time
        FROM appointments a
        JOIN services s ON a.service_id = s.id
        JOIN users u ON a.user_id = u.id
        JOIN time_slots ts ON a.time_slot_id = ts.id
        WHERE a.appointment_date BETWEEN ? AND ?
        ORDER BY a.appointment_datetime ASC
    ");
    $stmt->execute([$month_start, $month_end]);
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $appointments = [];
}

// Get appointment statistics
try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_appointments,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(total_price) as total_revenue
        FROM appointments 
        WHERE appointment_date BETWEEN ? AND ?
    ");
    $stmt->execute([$month_start, $month_end]);
    $stats = $stmt->fetch();
} catch (PDOException $e) {
    $stats = [
        'total_appointments' => 0,
        'pending' => 0,
        'confirmed' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'cancelled' => 0,
        'total_revenue' => 0
    ];
}

// Group appointments by date for calendar display
$calendar_appointments = [];
foreach ($appointments as $appointment) {
    $date = $appointment['appointment_date'];
    if (!isset($calendar_appointments[$date])) {
        $calendar_appointments[$date] = [];
    }
    $calendar_appointments[$date][] = $appointment;
}

$page_title = 'Appointment Calendar';
$current_page = 'appointment';
require_once '../layouts/main.php';
startAdminLayout($page_title, $current_page);
?>

<div class="admin-content">
    <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Header with Stats -->
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-calendar-alt"></i> Appointment Calendar</h1>
            <p>Manage and track all customer appointments</p>
        </div>
        <div class="header-actions">
            <div class="month-navigation">
                <button class="btn btn-outline nav-btn" onclick="changeMonth(-1)">
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </button>
                <span class="current-month" id="currentMonthDisplay">
                    <?php echo date('F Y', strtotime($current_month . '-01')); ?>
                </span>
                <button class="btn btn-outline nav-btn" onclick="changeMonth(1)">
                    Next
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_appointments']; ?></h3>
                <p>Total Appointments</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['pending']; ?></h3>
                <p>Pending</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon confirmed">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['confirmed']; ?></h3>
                <p>Confirmed</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon completed">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['completed']; ?></h3>
                <p>Completed</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
    </div>

    <!-- Calendar and List Layout -->
    <div class="calendar-layout">
        <!-- Calendar View -->
        <div class="calendar-section">
            <div class="calendar-container">
                <div class="calendar-grid">
                    <div class="calendar-weekdays">
                        <div class="weekday">Sun</div>
                        <div class="weekday">Mon</div>
                        <div class="weekday">Tue</div>
                        <div class="weekday">Wed</div>
                        <div class="weekday">Thu</div>
                        <div class="weekday">Fri</div>
                        <div class="weekday">Sat</div>
                    </div>
                    <div class="calendar-days" id="calendarDays">
                        <!-- Calendar days will be generated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments List -->
        <div class="appointments-section">
            <h2><i class="fas fa-list"></i> Appointments for <?php echo date('F Y', strtotime($current_month . '-01')); ?></h2>
            
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No appointments this month</h3>
                    <p>When customers book appointments, they will appear here.</p>
                </div>
            <?php else: ?>
                <div class="appointments-list">
                    <?php foreach ($appointments as $appointment): ?>
                        <div class="appointment-card status-<?php echo $appointment['status']; ?>">
                            <div class="appointment-header">
                                <div class="appointment-info">
                                    <h4><?php echo htmlspecialchars($appointment['service_name']); ?></h4>
                                    <span class="appointment-id">#<?php echo str_pad($appointment['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <div class="appointment-status">
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $appointment['status'])); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="appointment-details">
                                <div class="customer-info">
                                    <h5><i class="fas fa-user"></i> <?php echo htmlspecialchars($appointment['customer_name']); ?></h5>
                                    <div class="contact-info">
                                        <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($appointment['customer_phone']); ?></span>
                                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($appointment['customer_email']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="appointment-meta">
                                    <div class="meta-row">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('l, F j, Y', strtotime($appointment['appointment_date'])); ?></span>
                                    </div>
                                    <div class="meta-row">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $appointment['slot_label']; ?></span>
                                    </div>
                                    <div class="meta-row">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($appointment['pickup_address']); ?></span>
                                    </div>
                                    <div class="meta-row">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>$<?php echo number_format($appointment['total_price'], 2); ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($appointment['special_instructions'])): ?>
                                    <div class="special-instructions">
                                        <strong><i class="fas fa-sticky-note"></i> Special Instructions:</strong>
                                        <p><?php echo htmlspecialchars($appointment['special_instructions']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="appointment-actions">
                                <button class="btn btn-small btn-outline" onclick="viewAppointment(<?php echo $appointment['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <?php if ($appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                                    <select class="status-select" onchange="updateAppointmentStatus(<?php echo $appointment['id']; ?>, this.value)">
                                        <option value="">Change Status</option>
                                        <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'disabled' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $appointment['status'] === 'confirmed' ? 'disabled' : ''; ?>>Confirmed</option>
                                        <option value="in_progress" <?php echo $appointment['status'] === 'in_progress' ? 'disabled' : ''; ?>>In Progress</option>
                                        <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'disabled' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'disabled' : ''; ?>>Cancelled</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div id="appointmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-calendar-alt"></i> Appointment Details</h2>
            <button class="modal-close" onclick="closeAppointmentModal()">&times;</button>
        </div>
        
        <div class="modal-body" id="appointmentDetails">
            <!-- Details will be loaded via JavaScript -->
        </div>
        
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeAppointmentModal()">Close</button>
        </div>
    </div>
</div>

<style>
.admin-content {
    padding: 2rem;
}

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.header-content h1 {
    margin: 0 0 0.5rem;
    color: #1f2937;
    font-size: 2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header-content p {
    margin: 0;
    color: #6b7280;
    font-size: 1.125rem;
}

.month-navigation {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.current-month {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    min-width: 150px;
    text-align: center;
}

.nav-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Statistics Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
    background: #3b82f6;
}

.stat-icon.pending {
    background: #f59e0b;
}

.stat-icon.confirmed {
    background: #10b981;
}

.stat-icon.completed {
    background: #8b5cf6;
}

.stat-icon.revenue {
    background: #ef4444;
}

.stat-content h3 {
    margin: 0 0 0.25rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

.stat-content p {
    margin: 0;
    color: #6b7280;
    font-size: 0.875rem;
}

/* Calendar Layout */
.calendar-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: start;
}

.calendar-section {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.calendar-container {
    padding: 2rem;
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.weekday {
    text-align: center;
    font-weight: 600;
    color: #6b7280;
    padding: 0.75rem 0;
    font-size: 0.875rem;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
    position: relative;
    border: 2px solid transparent;
    padding: 0.5rem 0.25rem;
    background: #f9fafb;
}

.calendar-day:hover {
    background: #f3f4f6;
}

.calendar-day.other-month {
    color: #d1d5db;
    background: transparent;
}

.calendar-day.today {
    background: #dbeafe;
    color: #1d4ed8;
    font-weight: 600;
    border-color: #3b82f6;
}

.calendar-day.has-appointments {
    background: #f0fdf4;
    border-color: #22c55e;
}

.calendar-day.has-appointments .day-number {
    color: #15803d;
    font-weight: 600;
}

.appointment-count {
    font-size: 0.75rem;
    background: #22c55e;
    color: white;
    border-radius: 9999px;
    padding: 0.125rem 0.375rem;
    margin-top: 0.25rem;
    min-width: 1.25rem;
    text-align: center;
}

/* Appointments Section */
.appointments-section {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    padding: 2rem;
    max-height: 800px;
    overflow-y: auto;
}

.appointments-section h2 {
    margin: 0 0 2rem;
    color: #1f2937;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #d1d5db;
}

.empty-state h3 {
    margin: 0 0 0.5rem;
    color: #6b7280;
    font-size: 1.25rem;
}

.empty-state p {
    margin: 0;
    font-size: 1rem;
}

.appointments-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.appointment-card {
    background: #f9fafb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.appointment-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.appointment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.appointment-info h4 {
    margin: 0 0 0.25rem;
    color: #1f2937;
    font-size: 1.125rem;
    font-weight: 600;
}

.appointment-id {
    font-size: 0.875rem;
    color: #6b7280;
    font-family: monospace;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-confirmed {
    background: #d1fae5;
    color: #065f46;
}

.status-in-progress {
    background: #dbeafe;
    color: #1e40af;
}

.status-completed {
    background: #e0e7ff;
    color: #5b21b6;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.appointment-details {
    display: grid;
    gap: 1rem;
}

.customer-info {
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

.customer-info h5 {
    margin: 0 0 0.5rem;
    color: #1f2937;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.contact-info span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.appointment-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.meta-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.meta-row i {
    width: 1rem;
    color: #9ca3af;
}

.special-instructions {
    background: #fffbeb;
    border: 1px solid #fbbf24;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}

.special-instructions strong {
    color: #92400e;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.special-instructions p {
    margin: 0;
    color: #78350f;
    line-height: 1.5;
}

.appointment-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    align-items: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.status-select {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    background: white;
    cursor: pointer;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    margin: 5% auto;
    border-radius: 1rem;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    padding: 2rem 2rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: #1f2937;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 2rem;
    color: #9ca3af;
    cursor: pointer;
    padding: 0;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #6b7280;
}

.modal-body {
    padding: 2rem;
}

.modal-actions {
    padding: 1rem 2rem 2rem;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-outline {
    background: transparent;
    color: #3b82f6;
    border: 1px solid #3b82f6;
}

.btn-outline:hover {
    background: #3b82f6;
    color: white;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
}

@media (max-width: 1024px) {
    .calendar-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
}

@media (max-width: 768px) {
    .admin-content {
        padding: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .appointment-meta {
        grid-template-columns: 1fr;
    }
    
    .appointment-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .modal-content {
        margin: 2% auto;
        width: 95%;
    }
    
    .calendar-container,
    .appointments-section {
        padding: 1rem;
    }
}
</style>

<script>
// Calendar variables
let currentMonth = '<?php echo $current_month; ?>';
const appointmentData = <?php echo json_encode($calendar_appointments); ?>;

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    renderCalendar();
});

function renderCalendar() {
    const [year, month] = currentMonth.split('-').map(Number);
    const date = new Date(year, month - 1, 1);
    
    // Get first day of month and number of days
    const firstDayOfWeek = date.getDay();
    const daysInMonth = new Date(year, month, 0).getDate();
    const daysInPrevMonth = new Date(year, month - 1, 0).getDate();
    
    const calendarDays = document.getElementById('calendarDays');
    calendarDays.innerHTML = '';
    
    // Add previous month's trailing days
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const dayElement = createDayElement(daysInPrevMonth - i, 'other-month');
        calendarDays.appendChild(dayElement);
    }
    
    // Add current month's days
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = createDayElement(day, 'current-month');
        calendarDays.appendChild(dayElement);
    }
    
    // Add next month's leading days
    const totalCells = calendarDays.children.length;
    const remainingCells = 42 - totalCells; // 6 rows × 7 days
    for (let day = 1; day <= remainingCells; day++) {
        const dayElement = createDayElement(day, 'other-month');
        calendarDays.appendChild(dayElement);
    }
}

function createDayElement(day, monthType) {
    const dayElement = document.createElement('div');
    dayElement.className = 'calendar-day';
    
    if (monthType === 'other-month') {
        dayElement.classList.add('other-month');
        dayElement.innerHTML = `<span class="day-number">${day}</span>`;
        return dayElement;
    }
    
    const [year, month] = currentMonth.split('-').map(Number);
    const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const today = new Date().toISOString().split('T')[0];
    
    // Check if it's today
    if (dateStr === today) {
        dayElement.classList.add('today');
    }
    
    // Check if there are appointments
    if (appointmentData[dateStr]) {
        dayElement.classList.add('has-appointments');
        const appointmentCount = appointmentData[dateStr].length;
        dayElement.innerHTML = `
            <span class="day-number">${day}</span>
            <span class="appointment-count">${appointmentCount}</span>
        `;
    } else {
        dayElement.innerHTML = `<span class="day-number">${day}</span>`;
    }
    
    return dayElement;
}

function changeMonth(direction) {
    const [year, month] = currentMonth.split('-').map(Number);
    const newDate = new Date(year, month - 1 + direction, 1);
    const newMonth = String(newDate.getMonth() + 1).padStart(2, '0');
    const newYear = newDate.getFullYear();
    
    currentMonth = `${newYear}-${newMonth}`;
    
    // Update URL and reload page
    window.location.href = `?month=${currentMonth}`;
}

function updateAppointmentStatus(appointmentId, newStatus) {
    if (!newStatus) return;
    
    if (confirm(`Are you sure you want to change the status to "${newStatus.replace('_', ' ')}"?`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="appointment_id" value="${appointmentId}">
            <input type="hidden" name="status" value="${newStatus}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function viewAppointment(appointmentId) {
    // In a real implementation, you would fetch appointment details via AJAX
    // For now, we'll just show a placeholder modal
    document.getElementById('appointmentDetails').innerHTML = `
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading appointment details...</p>
        </div>
    `;
    document.getElementById('appointmentModal').style.display = 'block';
    
    // Simulate loading delay
    setTimeout(() => {
        document.getElementById('appointmentDetails').innerHTML = `
            <p>Appointment details would be loaded here via AJAX.</p>
            <p>Appointment ID: ${appointmentId}</p>
        `;
    }, 1000);
}

function closeAppointmentModal() {
    document.getElementById('appointmentModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('appointmentModal');
    if (event.target === modal) {
        closeAppointmentModal();
    }
}
</script>

<?php
endAdminLayout();
?>
