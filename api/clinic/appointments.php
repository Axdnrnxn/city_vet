<?php
// File: api/clinic/appointments.php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Content-Type: application/json; charset=UTF-8");

try {
    // 1. ROBUST DATABASE CONNECTION
    $root = dirname(__DIR__, 2); 
    $db_file = $root . '/config/db_connection.php';

    if (!file_exists($db_file)) {
        $db_file = $root . '/db_connection.php'; // Fallback
        if (!file_exists($db_file)) throw new Exception("Database file not found.");
    }
    require_once $db_file;
    if (isset($conn) && $conn instanceof mysqli) $conn->set_charset("utf8mb4");

    $method = $_SERVER['REQUEST_METHOD'];

    // --- GET REQUESTS ---
    if ($method === 'GET') {
        
        // A. Form Data
        if (isset($_GET['type']) && $_GET['type'] === 'form_data') {
            $response = ['owners' => [], 'services' => []];
            
            $o_res = $conn->query("SELECT Owner_ID, First_name, Last_name FROM owners WHERE Status = 'active' ORDER BY Last_name ASC");
            if($o_res) while($row = $o_res->fetch_assoc()) { $response['owners'][] = $row; }

            $s_res = $conn->query("SELECT Service_ID, Service_Name FROM services WHERE Status = 'active' ORDER BY Service_Name ASC");
            if($s_res) while($row = $s_res->fetch_assoc()) { $response['services'][] = $row; }

            echo json_encode($response);
            exit();
        }

        // B. Get Pets
        if (isset($_GET['owner_id'])) {
            $oid = intval($_GET['owner_id']);
            $sql = "SELECT Pet_ID, Name FROM pets WHERE Owner_ID = $oid AND Status = 'active'";
            $res = $conn->query($sql);
            $pets = [];
            if($res) while($row = $res->fetch_assoc()) { $pets[] = $row; }
            echo json_encode($pets);
            exit();
        }

// C. Get Complete History (NOW INCLUDES VET_ID AND PET_ID)
        $sql = "SELECT a.Appointment_ID, a.Pet_ID, a.Appointment_Date, a.Status, a.Notes,
                       o.First_name AS Owner_First, o.Last_name AS Owner_Last,
                       p.Name AS Pet_Name, sp.Species_Name,
                       sv.Service_Name,
                       st.First_name AS Vet_First, st.Last_name AS Vet_Last
                FROM appointments a
                LEFT JOIN owners o ON a.Owner_ID = o.Owner_ID
                LEFT JOIN pets p ON a.Pet_ID = p.Pet_ID
                LEFT JOIN species sp ON p.Species_ID = sp.Species_ID
                LEFT JOIN services sv ON a.Service_ID = sv.Service_ID
                LEFT JOIN staff st ON a.Vet_ID = st.Staff_ID
                ORDER BY a.Appointment_Date DESC";

        $result = $conn->query($sql);
        if (!$result) throw new Exception("SQL Error: " . $conn->error);

        $appointments = [];
        while ($row = $result->fetch_assoc()) {
            $phpdate = strtotime($row['Appointment_Date']);
            $row['Formatted_Date'] = date('M d, Y', $phpdate);
            $row['Formatted_Time'] = date('h:i A', $phpdate);
            $appointments[] = $row;
        }

        echo json_encode($appointments);
        exit();
    }

    // --- POST REQUESTS ---
    if ($method === 'POST') {
        $input = json_decode(file_get_contents("php://input"), true);
        $action = $input['action'] ?? '';

        // 1. Create Appointment
        if ($action === 'create') {
            $owner_id = $input['owner_id'];
            $pet_id = $input['pet_id'];
            $service_id = $input['service_id'];
            $datetime = $input['date'] . ' ' . $input['time']; 
            
            $stmt = $conn->prepare("INSERT INTO appointments (Owner_ID, Pet_ID, Service_ID, Appointment_Date, Status, Notes) VALUES (?, ?, ?, ?, 'Confirmed', 'Walk-in Booking')");
            $stmt->bind_param("iiis", $owner_id, $pet_id, $service_id, $datetime);
            
            if ($stmt->execute()) echo json_encode(["status" => "success", "message" => "Booked successfully"]);
            else echo json_encode(["status" => "error", "message" => "DB Error: " . $conn->error]);
        }

        // 2. Update Status (NOW SAVES VET_ID)
        if ($action === 'update_status') {
            $id = $input['appointment_id'];
            $status = $input['status'];
            $vet_id = isset($input['vet_id']) && !empty($input['vet_id']) ? $input['vet_id'] : NULL;

            if ($vet_id) {
                // If Vet ID is provided (Approval), save it!
                $stmt = $conn->prepare("UPDATE appointments SET Status = ?, Vet_ID = ? WHERE Appointment_ID = ?");
                $stmt->bind_param("sii", $status, $vet_id, $id);
            } else {
                // Otherwise just update status
                $stmt = $conn->prepare("UPDATE appointments SET Status = ? WHERE Appointment_ID = ?");
                $stmt->bind_param("si", $status, $id);
            }
            
            if ($stmt->execute()) echo json_encode(["status" => "success"]);
            else echo json_encode(["status" => "error", "message" => "DB Error: " . $conn->error]);
        }
        exit();
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit();
}
?>