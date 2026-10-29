
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

function escapeOutput($value) {
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
        $status = "Lost";

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
                $message = "Lost item reported successfully!";

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
    <title>Report Lost Item | LostLink</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .report-section {
            max-width: 760px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px #0000000d;
        }

        .report-form {
            display: grid;
            gap: 16px;
        }

        .report-form label {
            font-weight: 600;
        }

        .report-form input,
        .report-form select,
        .report-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
            background: white;
        }

        .report-form textarea {
            min-height: 110px;
            resize: vertical;
        }

        .report-form button {
            padding: 13px 20px;
            border: 0;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .notice {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #eaf2ff;
            color: #183153;
        }

        @media (max-width: 600px) {
            .report-section {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a href="index.php" class="logo">Lost<span>Link</span></a>

    <nav>
        <a href="index.php">Home</a>
        <a href="search.php">Search Items</a>
        <a href="report-found.php">Report Found</a>
    </nav>
</header>

<main>
    <section class="report-section">
        <h1>Report a Lost Item</h1>

        <p>
            Enter the details of your missing item to help
            your campus community identify it.
        </p>

        <?php if ($message !== ""): ?>
            <p class="notice"><?= escapeOutput($message) ?></p>
        <?php endif; ?>

        <form class="report-form" method="POST" action="report-lost.php">

            <label for="item_name">Item Name</label>
            <input type="text" id="item_name" name="item_name"
                   maxlength="150" required
                   value="<?= escapeOutput($item["item_name"]) ?>"
                   placeholder="e.g. Blue Water Bottle">

            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Select a category</option>
                <?php
                $categories = [
                    "Books",
                    "Electronics",
                    "ID Cards",
                    "Accessories",
                    "Clothing",
                    "Stationery",
                    "Other"
                ];

                foreach ($categories as $option):
                ?>
                    <option value="<?= escapeOutput($option) ?>"
                        <?= $item["category"] === $option ? "selected" : "" ?>>
                        <?= escapeOutput($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="colour">Colour</label>
            <input type="text" id="colour" name="colour"
                   maxlength="50" required
                   value="<?= escapeOutput($item["colour"]) ?>"
                   placeholder="e.g. Blue">

            <label for="location">Last Seen Location</label>
            <input type="text" id="location" name="location"
                   maxlength="150" required
                   value="<?= escapeOutput($item["location"]) ?>"
                   placeholder="e.g. College Library">

            <label for="item_date">Date Lost</label>
            <input type="date" id="item_date" name="item_date"
                   max="<?= date("Y-m-d") ?>" required
                   value="<?= escapeOutput($item["item_date"]) ?>">

            <label for="description">Description</label>
            <textarea id="description" name="description"
                      required
                      placeholder="Describe the item and identifying features"><?= escapeOutput($item["description"]) ?></textarea>

            <button type="submit">Submit Lost Item Report</button>

        </form>
    </section>
</main>

<footer class="footer">
    <p>LostLink — Smart Campus Lost and Found System</p>
    <p>© 2026 LostLink</p>
</footer>

</body>
</html>
