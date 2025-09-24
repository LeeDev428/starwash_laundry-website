<?php
require_once '../includes/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'] ?? 'user';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get appointments
        if (isset($_GET['id'])) {
            // Get specific appointment
            $appointment_id = (int) $_GET['id'];
            
            $stmt = $pdo->prepare("
                SELECT a.*, s.service_name, s.description as service_description, s.price as service_price,
                       u.full_name as customer_name, u.phone as customer_phone, u.email as customer_email,
                       ts.slot_label, ts.slot_time
                FROM appointments a
                JOIN services s ON a.service_id = s.id
                JOIN users u ON a.user_id = u.id
                JOIN time_slots ts ON a.time_slot_id = ts.id
                WHERE a.id = ? AND (a.user_id = ? OR ? = 'seller')
            ");
            $stmt->execute([$appointment_id, $user_id, $user_role]);
            $appointment = $stmt->fetch();
            
            if (!$appointment) {
                throw new Exception('Appointment not found');
            }
            
            echo json_encode([
                'success' => true,
                'appointment' => $appointment
            ]);
            
        } else {
            // Get appointments list
            $filter = $_GET['filter'] ?? 'all';
            $limit = min((int) ($_GET['limit'] ?? 50), 100);
            $offset = (int) ($_GET['offset'] ?? 0);
            
            // Base query
            $where_conditions = ['1=1'];
            $params = [];
            
            // Role-based filtering
            if ($user_role === 'user') {
                $where_conditions[] = 'a.user_id = ?';
                $params[] = $user_id;
            }
            
            // Status filtering
            if ($filter !== 'all') {
                $where_conditions[] = 'a.status = ?';
                $params[] = $filter;
            }
            
            // Date filtering
            if (isset($_GET['date_from'])) {
                $where_conditions[] = 'a.appointment_date >= ?';
                $params[] = $_GET['date_from'];
            }
            
            if (isset($_GET['date_to'])) {
                $where_conditions[] = 'a.appointment_date <= ?';
                $params[] = $_GET['date_to'];
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            // Get total count
            $count_stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM appointments a 
                WHERE $where_clause
            ");
            $count_stmt->execute($params);
            $total_count = $count_stmt->fetchColumn();
            
            // Get appointments
            $stmt = $pdo->prepare("
                SELECT a.*, s.service_name, s.description as service_description, s.price as service_price,
                       u.full_name as customer_name, u.phone as customer_phone, u.email as customer_email,
                       ts.slot_label, ts.slot_time
                FROM appointments a
                JOIN services s ON a.service_id = s.id
                JOIN users u ON a.user_id = u.id
                JOIN time_slots ts ON a.time_slot_id = ts.id
                WHERE $where_clause
                ORDER BY a.appointment_datetime DESC
                LIMIT ? OFFSET ?
            ");
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $appointments = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'appointments' => $appointments,
                'total_count' => (int) $total_count,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Create new appointment
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        // Validate required fields
        $required_fields = ['service_id', 'appointment_date', 'time_slot_id', 'pickup_address'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }
        
        $service_id = (int) $input['service_id'];
        $appointment_date = $input['appointment_date'];
        $time_slot_id = (int) $input['time_slot_id'];
        $pickup_address = trim($input['pickup_address']);
        $delivery_address = trim($input['delivery_address'] ?? '');
        $special_instructions = trim($input['special_instructions'] ?? '');
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointment_date)) {
            throw new Exception('Invalid date format. Use YYYY-MM-DD');
        }
        
        // Check if date is not in the past
        if ($appointment_date < date('Y-m-d')) {
            throw new Exception('Cannot book appointments for past dates');
        }
        
        // Check if slot is available
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM appointments 
            WHERE appointment_date = ? AND time_slot_id = ? AND status != 'cancelled'
        ");
        $stmt->execute([$appointment_date, $time_slot_id]);
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('This time slot is already booked');
        }
        
        // Get service details
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND status = 'active'");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();
        
        if (!$service) {
            throw new Exception('Service not found or inactive');
        }
        
        // Get time slot details
        $stmt = $pdo->prepare("SELECT slot_time FROM time_slots WHERE id = ? AND is_active = 1");
        $stmt->execute([$time_slot_id]);
        $slot_time = $stmt->fetchColumn();
        
        if (!$slot_time) {
            throw new Exception('Time slot not found or inactive');
        }
        
        // Create appointment datetime
        $appointment_datetime = $appointment_date . ' ' . $slot_time;
        $total_price = $service['price'];
        
        // Insert appointment
        $stmt = $pdo->prepare("
            INSERT INTO appointments (
                user_id, service_id, time_slot_id, appointment_date, appointment_datetime,
                pickup_address, delivery_address, special_instructions, total_price, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $user_id, $service_id, $time_slot_id, $appointment_date, $appointment_datetime,
            $pickup_address, $delivery_address, $special_instructions, $total_price
        ]);
        
        $appointment_id = $pdo->lastInsertId();
        
        // Get the created appointment
        $stmt = $pdo->prepare("
            SELECT a.*, s.service_name, ts.slot_label
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            JOIN time_slots ts ON a.time_slot_id = ts.id
            WHERE a.id = ?
        ");
        $stmt->execute([$appointment_id]);
        $appointment = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'Appointment booked successfully',
            'appointment' => $appointment
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Update appointment
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            throw new Exception('Appointment ID is required');
        }
        
        $appointment_id = (int) $input['id'];
        
        // Check if appointment exists and user has permission
        $stmt = $pdo->prepare("
            SELECT * FROM appointments 
            WHERE id = ? AND (user_id = ? OR ? = 'seller')
        ");
        $stmt->execute([$appointment_id, $user_id, $user_role]);
        $appointment = $stmt->fetch();
        
        if (!$appointment) {
            throw new Exception('Appointment not found or access denied');
        }
        
        // Update allowed fields
        $update_fields = [];
        $params = [];
        
        if (isset($input['status']) && $user_role === 'seller') {
            $allowed_statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
            if (in_array($input['status'], $allowed_statuses)) {
                $update_fields[] = 'status = ?';
                $params[] = $input['status'];
                
                // Set timestamp fields based on status
                if ($input['status'] === 'confirmed' && !$appointment['confirmed_at']) {
                    $update_fields[] = 'confirmed_at = NOW()';
                } elseif ($input['status'] === 'completed' && !$appointment['completed_at']) {
                    $update_fields[] = 'completed_at = NOW()';
                }
            }
        }
        
        if (isset($input['pickup_address']) && $user_role === 'user' && $appointment['status'] === 'pending') {
            $update_fields[] = 'pickup_address = ?';
            $params[] = trim($input['pickup_address']);
        }
        
        if (isset($input['delivery_address']) && $user_role === 'user' && $appointment['status'] === 'pending') {
            $update_fields[] = 'delivery_address = ?';
            $params[] = trim($input['delivery_address']);
        }
        
        if (isset($input['special_instructions']) && $appointment['status'] === 'pending') {
            $update_fields[] = 'special_instructions = ?';
            $params[] = trim($input['special_instructions']);
        }
        
        if (empty($update_fields)) {
            throw new Exception('No valid fields to update');
        }
        
        $update_fields[] = 'updated_at = NOW()';
        $params[] = $appointment_id;
        
        $sql = "UPDATE appointments SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Get updated appointment
        $stmt = $pdo->prepare("
            SELECT a.*, s.service_name, ts.slot_label
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            JOIN time_slots ts ON a.time_slot_id = ts.id
            WHERE a.id = ?
        ");
        $stmt->execute([$appointment_id]);
        $updated_appointment = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'Appointment updated successfully',
            'appointment' => $updated_appointment
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Cancel appointment
        $appointment_id = (int) ($_GET['id'] ?? 0);
        
        if (!$appointment_id) {
            throw new Exception('Appointment ID is required');
        }
        
        // Check if appointment exists and user has permission
        $stmt = $pdo->prepare("
            SELECT * FROM appointments 
            WHERE id = ? AND (user_id = ? OR ? = 'seller')
        ");
        $stmt->execute([$appointment_id, $user_id, $user_role]);
        $appointment = $stmt->fetch();
        
        if (!$appointment) {
            throw new Exception('Appointment not found or access denied');
        }
        
        if ($appointment['status'] === 'completed') {
            throw new Exception('Cannot cancel completed appointments');
        }
        
        // Update status to cancelled
        $stmt = $pdo->prepare("
            UPDATE appointments 
            SET status = 'cancelled', updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$appointment_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Appointment cancelled successfully'
        ]);
        
    } else {
        throw new Exception('Unsupported HTTP method');
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