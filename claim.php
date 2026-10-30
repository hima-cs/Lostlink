<?php
require_once "db.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid item ID.");
}

$stmt = $conn->prepare(
    "SELECT * FROM items WHERE id = ? AND status = 'Found'"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    exit("Found item not available for claiming.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['claimant_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($name === '' || $contact === '' || $reason === '') {
        $message = "Please fill all fields.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO claims
            (item_id, claimant_name, contact, reason)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param("isss", $id, $name, $contact, $reason);

        if ($stmt->execute()) {
            header("Location: claims.php?success=1");
            exit;
        } else {
            $message = "Unable to submit claim.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Item | LostLink</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="navbar">
    <a href="index.php" class="logo">LostLink</a>
    <a href="search.php">Search Items</a>
</header>

<main>
    <section>
        <h2>Claim This Item</h2>

        <div class="feature-card">

            <h3><?= htmlspecialchars($item['item_name']) ?></h3>

            <?php if ($message): ?>
                <p><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form method="POST">

                <label>Your Name</label>
                <input type="text" name="claimant_name"
                       required maxlength="150">

                <br><br>

                <label>Contact Number</label>
                <input type="tel" name="contact"
                       required maxlength="20">

                <br><br>

                <label>Why are you claiming this item?</label>
                <textarea name="reason" required></textarea>

                <br><br>

                <button type="submit" class="nav-button">
                    Submit Claim
                </button>

            </form>
        </div>
    </section>
</main>

</body>
</html>

<?php $conn->close(); ?>
