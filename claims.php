<?php
require_once "db.php";

$sql = "SELECT claims.*, items.item_name
        FROM claims
        JOIN items ON claims.item_id = items.id
        ORDER BY claims.created_at DESC";

$result = $conn->query($sql);

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Status | LostLink</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="navbar">
    <a href="index.php" class="logo">LostLink</a>
    <nav>
        <a href="index.php">Home</a>
        <a href="search.php">Search Items</a>
    </nav>
</header>

<main>
    <section>
        <h2>Claim Status</h2>

        <?php if (isset($_GET['success'])): ?>
            <p>Your claim was submitted successfully!</p>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($claim = $result->fetch_assoc()): ?>

                <div class="feature-card">

                    <h3><?= e($claim['item_name']) ?></h3>

                    <p><strong>Claimant Name:</strong>
                        <?= e($claim['claimant_name']) ?></p>

                    <p><strong>Contact:</strong>
                        <?= e($claim['contact']) ?></p>

                    <p><strong>Reason:</strong>
                        <?= e($claim['reason']) ?></p>

                    <p><strong>Claim Status:</strong>
                        <?= e($claim['claim_status']) ?></p>

                    <p><strong>Date:</strong>
                        <?= e($claim['created_at']) ?></p>

                    <?php if ($claim['claim_status'] === 'Approved'): ?>
                        <a href="recover.php?id=<?= (int)$claim['item_id'] ?>"
                           class="nav-button">
                            Mark as Recovered
                        </a>
                    <?php endif; ?>

                    <?php if ($claim['claim_status'] === 'Pending'): ?>
                        <form action="update-claim.php" method="POST">
                            <input type="hidden" name="claim_id"
                                   value="<?= (int)$claim['id'] ?>">

                            <button type="submit" name="status"
                                    value="Approved">Approve</button>

                            <button type="submit" name="status"
                                    value="Rejected">Reject</button>
                        </form>
                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        <?php else: ?>
            <p>No claims submitted yet.</p>
        <?php endif; ?>

    </section>
</main>

</body>
</html>

<?php
$conn->close();
?>
