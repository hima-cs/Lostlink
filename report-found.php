
<?php
require_once "db.php";

$message = "";

$item = [
    "item_name" => "",
    "category" => "",
    "colour" => "",
    "location" => "",
    "item_date" => "",
    "description" => ""
];

function escapeFound($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($item as $key => $value) {
        $item[$key] = trim($_POST[$key] ?? "");
    }

    if (in_array("", $item, true)) {
        $message = "Please fill in all required fields.";
    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $item["item_date"]) ||
        $item["item_date"] > date("Y-m-d")
    ) {
        $message = "Please enter a valid date that is not in the future.";
    } else {
        $status = "Found";

        $sql = "INSERT INTO items
            (item_name, category, colour, location,
             item_date, description, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param(
                "sssssss",
                $item["item_name"],
                $item["category"],
                $item["colour"],
                $item["location"],
                $item["item_date"],
                $item["description"],
                $status
            );

            if ($stmt->execute()) {
                $message = "Found item reported successfully!";

                foreach ($item as $key => $value) {
                    $item[$key] = "";
                }
            } else {
                $message = "Unable to save the report. Please try again.";
            }

            $stmt->close();
        } else {
            $message = "Unable to prepare the database query.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Found Item | LostLink</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Lost<span>Link</span></a>
    <nav>
        <a href="index.php">Home</a>
        <a href="search.php">Search Items</a>
        <a href="report-lost.php">Report Lost</a>
    </nav>
</header>

<main>
    <section style="max-width:760px;margin:40px auto;padding:30px;background:white;border-radius:16px;">
        <h1>Report a Found Item</h1>
        <p>Help return a found belonging to its owner by submitting its details.</p>

        <?php if ($message !== ""): ?>
            <p role="status">
                <?= escapeFound($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="report-found.php"
              style="display:grid;gap:14px;">

            <label for="item_name">Item Name</label>
            <input type="text" id="item_name" name="item_name"
                   maxlength="150" required
                   value="<?= escapeFound($item["item_name"]) ?>"
                   placeholder="e.g. Blue Water Bottle">

            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Select a category</option>
                <?php
                $categories = [
                    "Books", "Electronics", "ID Cards",
                    "Accessories", "Clothing", "Stationery", "Other"
                ];
                foreach ($categories as $option):
                ?>
                    <option value="<?= escapeFound($option) ?>"
                        <?= $item["category"] === $option ? "selected" : "" ?>>
                        <?= escapeFound($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="colour">Colour</label>
            <input type="text" id="colour" name="colour"
                   maxlength="50" required
                   value="<?= escapeFound($item["colour"]) ?>"
                   placeholder="e.g. Blue">

            <label for="location">Found Location</label>
            <input type="text" id="location" name="location"
                   maxlength="150" required
                   value="<?= escapeFound($item["location"]) ?>"
                   placeholder="e.g. College Library">

            <label for="item_date">Date Found</label>
            <input type="date" id="item_date" name="item_date"
                   max="<?= date("Y-m-d") ?>" required
                   value="<?= escapeFound($item["item_date"]) ?>">

            <label for="description">Description</label>
            <textarea id="description" name="description"
                      required rows="4"
                      placeholder="Describe the item and identifying features"><?= escapeFound($item["description"]) ?></textarea>

            <button type="submit">Submit Found Item Report</button>
        </form>
    </section>
</main>

<footer class="footer">
    <p>LostLink — Smart Campus Lost and Found System</p>
    <p>© 2026 LostLink</p>
</footer>

</body>
</html>
