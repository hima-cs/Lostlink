<?php
require_once "db.php";

$keyword = trim($_GET['keyword'] ?? '');
$category = trim($_GET['category'] ?? '');
$colour = trim($_GET['colour'] ?? '');
$location = trim($_GET['location'] ?? '');
$date = trim($_GET['date'] ?? '');

$sql = "SELECT * FROM items
        WHERE status IN ('Lost', 'Found')
        AND (
            item_name LIKE CONCAT('%', ?, '%')
            OR description LIKE CONCAT('%', ?, '%')
        )
        AND (? = '' OR category = ?)
        AND (? = '' OR colour = ?)
        AND (? = '' OR location = ?)
        AND (? = '' OR item_date = ?)
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssssssss",
    $keyword, $keyword,
    $category, $category,
    $colour, $colour,
    $location, $location,
    $date, $date
);

$stmt->execute();
$result = $stmt->get_result();

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Search Items | LostLink</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="navbar">
    <a href="index.php" class="logo">LostLink</a>

    <nav>
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="search.php">Search Items</a>
    </nav>
</header>

<main>

    <section>
        <h2>Search Lost and Found Items</h2>

        <div class="feature-card">

            <form method="GET" action="search.php">

                <label>Keyword</label>
                <input type="text" name="keyword"
                       value="<?= e($keyword) ?>"
                       placeholder="Enter item name">

                <br><br>

                <label>Category</label>
                <input type="text" name="category"
                       value="<?= e($category) ?>"
                       placeholder="Enter category">

                <br><br>

                <label>Colour</label>
                <input type="text" name="colour"
                       value="<?= e($colour) ?>"
                       placeholder="Enter colour">

                <br><br>

                <label>Location</label>
                <input type="text" name="location"
                       value="<?= e($location) ?>"
                       placeholder="Enter location">

                <br><br>

                <label>Date</label>
                <input type="date" name="date"
                       value="<?= e($date) ?>">

                <br><br>

                <button type="submit" class="nav-button">
                    Search
                </button>

                <a href="search.php">Clear</a>

            </form>

        </div>
    </section>

    <section>

        <h2>Search Results</h2>

        <?php if ($result->num_rows > 0): ?>

            <?php while ($item = $result->fetch_assoc()): ?>

                <div class="feature-card">

                    <h3><?= e($item['item_name']) ?></h3>

                    <p>
                        <strong>Category:</strong>
                        <?= e($item['category']) ?>
                    </p>

                    <p>
                        <strong>Colour:</strong>
                        <?= e($item['colour']) ?>
                    </p>

                    <p>
                        <strong>Location:</strong>
                        <?= e($item['location']) ?>
                    </p>

                    <p>
                        <strong>Date:</strong>
                        <?= e($item['item_date']) ?>
                    </p>

                    <p>
                        <strong>Description:</strong>
                        <?= e($item['description']) ?>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <?= e($item['status']) ?>
                    </p>

                    <a href="item.php?id=<?= (int)$item['id'] ?>"
                       class="nav-button">
                        View Item Details
                    </a>

                    <br><br>

                    <a href="matching.php?id=<?= (int)$item['id'] ?>">
                        Check Possible Matches
                    </a>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No matching items found.</p>

        <?php endif; ?>

    </section>

</main>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
