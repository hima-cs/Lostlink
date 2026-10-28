<?php

require_once "db.php";

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    exit("Invalid item ID.");
}

$stmt = $conn->prepare(
    "SELECT * FROM items
     WHERE id = ?
     AND status IN ('Lost', 'Found')"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$current = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$current) {
    exit("Item not found.");
}

$oppositeStatus = (
    $current['status'] === 'Lost'
) ? 'Found' : 'Lost';

$stmt = $conn->prepare(
    "SELECT * FROM items
     WHERE status = ?
     AND id != ?"
);

$stmt->bind_param(
    "si",
    $oppositeStatus,
    $id
);

$stmt->execute();

$result = $stmt->get_result();

function sameValue($a, $b) {
    return trim((string)$a) !== ''
        && trim((string)$b) !== ''
        && strcasecmp(
            trim((string)$a),
            trim((string)$b)
        ) === 0;
}

function calculateScore($a, $b) {

    $score = 0;

    if (sameValue($a['item_name'], $b['item_name'])) {
        $score += 30;
    }

    if (sameValue($a['category'], $b['category'])) {
        $score += 25;
    }

    if (sameValue($a['colour'], $b['colour'])) {
        $score += 20;
    }

    if (sameValue($a['location'], $b['location'])) {
        $score += 15;
    }

    if (
        !empty($a['item_date']) &&
        !empty($b['item_date']) &&
        $a['item_date'] === $b['item_date']
    ) {
        $score += 10;
    }

    return $score;
}

function e($value) {
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$matches = [];

while ($item = $result->fetch_assoc()) {

    $score = calculateScore($current, $item);

    if ($score >= 50) {
        $item['match_score'] = $score;
        $matches[] = $item;
    }
}

usort($matches, function ($a, $b) {
    return $b['match_score'] <=> $a['match_score'];
});

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Possible Matches | LostLink</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>LostLink</h1>
    <a href="index.php">Home</a>
</header>

<main>

    <h2>Possible Matches</h2>

    <p>
        Selected item:
        <strong><?= e($current['item_name']) ?></strong>
    </p>

    <?php if (!empty($matches)): ?>

        <?php foreach ($matches as $item): ?>

            <div class="item-card">

                <h3><?= e($item['item_name']) ?></h3>

                <p>
                    <strong>Match Score:</strong>
                    <?= (int)$item['match_score'] ?>/100
                </p>

                <p>
                    <strong>Status:</strong>
                    <?= e($item['status']) ?>
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

                <p><?= e($item['description']) ?></p>

                <p>
                    This is a possible match.
                    Verify the details before claiming the item.
                </p>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p>No possible matches found.</p>

    <?php endif; ?>

    <a href="search.php">Back to Search</a>

</main>

</body>
</html>

<?php

$conn->close();

?>
