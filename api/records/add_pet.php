<?php
header("Content-Type: application/json");

echo json_encode([
    "status" => "error",
    "message" => "This endpoint is not used. Use api/owner/add_pet.php for pet registration."
]);
?>
