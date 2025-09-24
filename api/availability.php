<?php
require_once '../includes/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get availability for a specific date or month
        if (isset($_GET['date'])) {
            // Get availability for a specific date
            $date = $_GET['date'];
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                throw new Exception('Invalid date format. Use YYYY-MM-DD');
            }
            
            // Get all time slots with availability for the date
            $stmt = $pdo->prepare("
                SELECT ts.id, ts.slot_time, ts.slot_label,
                       COUNT(a.id) as booked_count,
                       CASE 
                           WHEN COUNT(a.id) > 0 THEN 0 
                           ELSE 1 
                       END as available
                FROM time_slots ts
                LEFT JOIN appointments a ON ts.id = a.time_slot_id 
                    AND a.appointment_date = ? 
                    AND a.status != 'cancelled'
                WHERE ts.is_active = 1
                GROUP BY ts.id, ts.slot_time, ts.slot_label
                ORDER BY ts.slot_time
            ");
            $stmt->execute([$date]);
            $slots = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'date' => $date,
                'slots' => $slots
            ]);
            
        } elseif (isset($_GET['month'])) {
            // Get availability overview for a month
            $month = $_GET['month'];
            
            // Validate month format
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                throw new Exception('Invalid month format. Use YYYY-MM');
            }
            
            $month_start = $month . '-01';
            $month_end = date('Y-m-t', strtotime($month_start));
            
            // Get appointment counts by date
            $stmt = $pdo->prepare("
                SELECT appointment_date as date,
                       COUNT(*) as appointment_count,
                       COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                       COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_count,
                       COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_count,
                       COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count
                FROM appointments 
                WHERE appointment_date BETWEEN ? AND ?
                    AND status != 'cancelled'
                GROUP BY appointment_date
                ORDER BY appointment_date
            ");
            $stmt->execute([$month_start, $month_end]);
            $appointments = $stmt->fetchAll();
            
            // Get total available slots per day (count of active time slots)
            $stmt = $pdo->prepare("SELECT COUNT(*) as total_slots FROM time_slots WHERE is_active = 1");
            $stmt->execute();
            $total_slots = $stmt->fetchColumn();
            
            // Format the data for calendar display
            $calendar_data = [];
            foreach ($appointments as $appointment) {
                $date = $appointment['date'];
                $appointment_count = (int) $appointment['appointment_count'];
                $available_slots = $total_slots - $appointment_count;
                
                $calendar_data[$date] = [
                    'total_slots' => $total_slots,
                    'booked_slots' => $appointment_count,
                    'available_slots' => max(0, $available_slots),
                    'is_fully_booked' => $available_slots <= 0,
                    'status_breakdown' => [
                        'pending' => (int) $appointment['pending_count'],
                        'confirmed' => (int) $appointment['confirmed_count'],
                        'in_progress' => (int) $appointment['in_progress_count'],
                        'completed' => (int) $appointment['completed_count']
                    ]
                ];
            }
            
            echo json_encode([
                'success' => true,
                'month' => $month,
                'total_slots_per_day' => $total_slots,
                'calendar_data' => $calendar_data
            ]);
            
        } else {
            throw new Exception('Please specify either date or month parameter');
        }
        
    } else {
        throw new Exception('Only GET requests are supported');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
}
?>