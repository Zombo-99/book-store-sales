<?php
require_once '../src/functions.php';
require_once '../src/db.php';

$filters = $_POST ?? [];
$sales = getFilteredSales($filters);
$totalPrice = array_sum(array_column($sales, 'price'));


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['json_file'])) {
    $uploadDir = __DIR__ . '/../storage/';
    $uploadedFile = $uploadDir . basename($_FILES['json_file']['name']);

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($_FILES['json_file']['tmp_name'], $uploadedFile)) {
        importSalesData($uploadedFile, $pdo);
    } else {
        echo "<h1>Failed to upload file. <h1>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bouhadjeb mohamed</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <h1>Bookshop Sales Management</h1>
    </header>
    <h1>Import Sales Data</h1>
      <form method="POST" enctype="multipart/form-data">
        <label for="json_file">Select JSON File:</label>
        <input type="file" name="json_file" id="json_file" accept=".json" required>
        <button type="submit">Upload and Import</button>
      </form>
    <h1>Bookshop Sales </h1>

    <form method="POST">
        <select name="customer">
            <option value="">Select Customer</option>
            <?php
            $customers = $pdo->query("SELECT id, name FROM customers")->fetchAll();
            foreach ($customers as $customer) {
                echo "<option value='{$customer['id']}'>{$customer['name']}</option>";
            }
            ?>
        </select>
        <select name="product">
            <option value="">Select Product</option>
            <?php
            $products = $pdo->query("SELECT id, name FROM products")->fetchAll();
            foreach ($products as $product) {
                echo "<option value='{$product['id']}'>{$product['name']}</option>";
            }
            ?>
        </select>
        <label for="price">Price:</label>
        <input type="number" step="0.01" name="price" id="price">

        <label for="min_price">Min Price:</label>
        <input type="number" step="0.01" name="min_price" id="min_price">

        <label for="max_price">Max Price:</label>
        <input type="number" step="0.01" name="max_price" id="max_price">
        <button type="submit">Filter</button>
    </form>

    <table border="2">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Product</th>
                <th>Price</th>
                <th>Sale Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $sale): ?>
            <tr>
                <td><?= $sale['customer'] ?></td>
                <td><?= $sale['product'] ?></td>
                <td><?= number_format($sale['price'], 2) ?></td>
                <td><?= $sale['sale_date'] ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2"><strong>Total</strong></td>
                <td colspan="2"><?= number_format($totalPrice, 2) ?></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
