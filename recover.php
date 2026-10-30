<?php
require_once "db.php";

// Get item ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid item ID.");
}

// Check whether the item has an approved claim
$stmt = $conn->prepare(
    "SELECT id FROM claims
     WHERE item_id = ? AND claim_status = 'Approved'
     LIMIT 1"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$approved = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$approved) {
    exit("No approved claim found.");
}

// Update item status to Recovered
$stmt = $conn->prepare(
    "UPDATE items
     SET status = 'Recovered'
     WHERE id = ? AND status = 'Found'"
);

$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo "Item successfully marked as recovered!";
} else {
    echo "Unable to recover this item.";
}

$stmt->close();
$conn->close();
?>

<br><br>
<a href="claims.php">Back to Claims</a>
