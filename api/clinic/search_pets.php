<?php
// search_pets.php - Search for pets by name or owner name (first or last)
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$search = isset($_GET['query']) ? "%" . $_GET['query'] . "%" : "%%";

$sql = "SELECT p.Pet_ID, p.Name as Pet_Name, o.First_name, o.Last_name, s.Species_Name 
        FROM pets p
        JOIN owners o ON p.Owner_ID = o.Owner_ID
        LEFT JOIN species s ON p.Species_ID = s.Species_ID
        WHERE o.First_name LIKE ? OR o.Last_name LIKE ? OR p.Name LIKE ?
        LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $search, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>