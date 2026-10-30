<?php
require_once "db.php";

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Invalid request.");
}

// Get claim ID and status
$id = filter_input(INPUT_POST, 'claim_id', FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';

// Validate the submitted data
if (!$id || !in_array($status, ['Approved', 'Rejected'], true)) {
    exit("Invalid claim details.");
}

// Update the claim status
$stmt = $conn->prepare(
    "UPDATE claims
     SET claim_status = ?
     WHERE id = ? AND claim_status = 'Pending'"
);

$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    header("Location: claims.php");
    exit;
} else {
    echo "Unable to update claim status.";
}

$stmt->close();
$conn->close();
?>
