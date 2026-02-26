<?php
// api/owner/get_pet_data.php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php'; 

if (!isset($_SESSION['user_id'])) { echo json_encode(["status" => "error", "message" => "Unauthorized"]); exit(); }

$user_id = $_SESSION['user_id'];

// 1. Get Owner
$owner_q = $conn->query("SELECT Owner_ID, First_name FROM owners WHERE User_ID = $user_id");
$owner = $owner_q->fetch_assoc();
$owner_id = $owner['Owner_ID'];

// 2. FETCH ALL BREEDS (For the dropdown)
$breeds = [];
$breed_q = $conn->query("SELECT Breed_ID, Species_ID, Breed_Name FROM breeds WHERE Status = 'active'");
while($b = $breed_q->fetch_assoc()) {
    $breeds[] = $b; 
}

// 3. CHECK REQUEST TYPE
if (isset($_GET['id'])) {
    // --- DETAIL VIEW ---
    $pet_id = intval($_GET['id']);
    $sql = "SELECT p.*, b.Breed_Name, s.Species_Name FROM pets p 
            LEFT JOIN breeds b ON p.Breed_ID = b.Breed_ID
            LEFT JOIN species s ON p.Species_ID = s.Species_ID
            WHERE p.Pet_ID = $pet_id AND p.Owner_ID = $owner_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $pet = $result->fetch_assoc();
        
        // Age Calc
        $age_display = "Unknown";
        if ($pet['Birthdate']) {
            $dob = new DateTime($pet['Birthdate']);
            $now = new DateTime();
            $age_display = $now->diff($dob)->y . " Years";
        }
        $pet['Age_Display'] = $age_display;
        $pet['Birthdate_Formatted'] = date("F j, Y", strtotime($pet['Birthdate']));

        // Notes
        $notes = [];
        $notes_q = $conn->query("SELECT * FROM pet_notes WHERE Pet_ID = $pet_id ORDER BY Created_At DESC");
        while ($n = $notes_q->fetch_assoc()) {
            $n['Date_Formatted'] = date("M j, Y", strtotime($n['Created_At']));
            $notes[] = $n;
        }

        echo json_encode([
            "status" => "success", 
            "mode" => "detail", 
            "owner_name" => $owner['First_name'], 
            "pet" => $pet, 
            "notes" => $notes,
            "all_breeds" => $breeds // FIX: Added this line so the Edit Modal has the breeds list!
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Pet not found"]);
    }

} else {
    // --- LIST VIEW ---
    $pets = [];
    // FIX: Added p.Gender and p.Profile_Pic to the SELECT statement
    $sql = "SELECT p.Pet_ID, p.Name, p.Status, p.Gender, p.Profile_Pic, b.Breed_Name, s.Species_Name FROM pets p 
            LEFT JOIN breeds b ON p.Breed_ID = b.Breed_ID 
            LEFT JOIN species s ON p.Species_ID = s.Species_ID
            WHERE p.Owner_ID = $owner_id";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) { $pets[] = $row; }

    echo json_encode([
        "status" => "success", 
        "mode" => "list", 
        "owner_name" => $owner['First_name'], 
        "pets" => $pets,
        "all_breeds" => $breeds 
    ]);
}
?>