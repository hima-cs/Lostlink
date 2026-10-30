<?php
require_once "db.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid item ID.");
}

$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$item = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$item) {
    exit("Item not found.");
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Details | LostLink</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="navbar">
    <a href="index.php" class="logo">LostLink</a>
    <nav>
        <a href="index.php">Home</a>
        <a href="search.php">Search Items</a>
        <a href="claims.php">My Claims</a>
    </nav>
</header>

<main>
    <section>
        <h2>Item Details</h2>

        <div class="feature-card">
            <h3><?= e($item['item_name']) ?></h3>

            <p><strong>Category:</strong> <?= e($item['category']) ?></p>
            <p><strong>Colour:</strong> <?= e($item['colour']) ?></p>
            <p><strong>Location:</strong> <?= e($item['location']) ?></p>
            <p><strong>Date:</strong> <?= e($item['item_date']) ?></p>
            <p><strong>Description:</strong> <?= e($item['description']) ?></p>
            <p><strong>Status:</strong> <?= e($item['status']) ?></p>

            <?php if ($item['status'] === 'Found'): ?>
                <a href="claim.php?id=<?= (int)$item['id'] ?>"
                   class="nav-button">Claim This Item</a>

            <?php elseif ($item['status'] === 'Recovered'): ?>
                <p>This item has already been recovered.</p>

            <?php else: ?>
                <p>This item is reported as lost.</p>
            <?php endif; ?>
        </div>

        <a href="search.php">Back to Search</a>
    </section>
</main>

</body>
</html>

<?php $conn->close(); ?>
