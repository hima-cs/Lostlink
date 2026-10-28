<?php

require_once "db.php";

$keyword = trim($_GET['keyword'] ?? '');
$category = trim($_GET['category'] ?? '');
$colour = trim($_GET['colour'] ?? '');
$location = trim($_GET['location'] ?? '');
$date = trim($_GET['date'] ?? '');

$sql = "SELECT * FROM items
        WHERE status IN ('Lost', 'Found')";

$params = [];
$types = "";

if ($keyword !== '') {
    $sql .= " AND (item_name LIKE ? OR description LIKE ?)";

    $term = "%" . $keyword . "%";

    $params[] = $term;
    $params[] = $term;

    $types .= "ss";
}

if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if ($colour !== '') {
    $sql .= " AND colour = ?";
    $params[] = $colour;
    $types .= "s";
}

if ($location !== '') {
    $sql .= " AND location LIKE ?";
    $params[] = "%" . $location . "%";
    $types .= "s";
}

if ($date !== '') {
    $sql .= " AND item_date = ?";
    $params[] = $date;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Unable to prepare search query.");
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

function e($value) {
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Search Items | LostLink</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>LostLink</h1>
    <a href="index.php">Home</a>
</header>

<main>

    <h2>Search Lost & Found Items</h2>

    <form action="search.php" method="GET">

        <input
            type="text"
            name="keyword"
            placeholder="Search item name"
            value="<?= e($keyword) ?>"
        >

        <select name="category">
            <option value="">All Categories</option>
            <option value="Electronics">Electronics</option>
            <option value="Books">Books</option>
            <option value="ID Cards">ID Cards</option>
            <option value="Accessories">Accessories</option>
            <option value="Other">Other</option>
        </select>

        <select name="colour">
            <option value="">All Colours</option>
            <option value="Black">Black</option>
            <option value="Blue">Blue</option>
            <option value="White">White</option>
            <option value="Red">Red</option>
            <option value="Green">Green</option>
        </select>

        <input
            type="text"
            name="location"
            placeholder="Enter location"
            value="<?= e($location) ?>"
        >

        <input
            type="date"
            name="date"
            value="<?= e($date) ?>"
        >

        <button type="submit">Search</button>

    </form>

    <h2>Search Results</h2>

    <p><?= $result->num_rows ?> item(s) found.</p>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($item = $result->fetch_assoc()): ?>

            <div class="item-card">

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
                    <strong>Status:</strong>
                    <?= e($item['status']) ?>
                </p>

                <p><?= e($item['description']) ?></p>

                <a href="matching.php?id=<?= (int)$item['id'] ?>">
                    Check Possible Matches
                </a>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No items found. Try different search filters.</p>

    <?php endif; ?>

</main>

</body>
</html>

<?php

$stmt->close();
$conn->close();

?>
