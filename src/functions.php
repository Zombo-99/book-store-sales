<?php
require_once 'db.php';
// import json function
function importSalesData(string $filePath, PDO $pdo): void
{
    $jsonData = file_get_contents($filePath);
    $sales = json_decode($jsonData, true);
    foreach ($sales as $sale) {
        // Check if the customer exists
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = :email");
        $stmt->execute(['email' => $sale['customer_mail']]);
        $customerId = $stmt->fetchColumn();

        if (!$customerId) {
            // Insert new customer
            $stmt = $pdo->prepare("INSERT INTO customers (name, email) VALUES (:name, :email)");
            $stmt->execute(['name' => $sale['customer_name'], 'email' => $sale['customer_mail']]);
            $customerId = $pdo->lastInsertId();
        }

        // Check if the product exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE name = :name");
        $stmt->execute(['name' => $sale['product_name']]);
        $productId = $stmt->fetchColumn();

        if (!$productId) {
            // Insert new product
            $stmt = $pdo->prepare("INSERT INTO products (name, price) VALUES (:name, :price)");
            $stmt->execute(['name' => $sale['product_name'], 'price' => $sale['product_price']]);
            $productId = $pdo->lastInsertId();
        }

        // Check if the sale exists
        $stmt = $pdo->prepare("SELECT * FROM sales WHERE customer_id = :customer_id AND product_id = :product_id AND sale_date = :sale_date");
        $stmt->execute([
            'customer_id' => $customerId,
            'product_id' => $productId,
            'sale_date' => $sale['sale_date']
        ]);
        if ($stmt->rowCount() == 0) {
            // Insert the sale only if it doesn't exist
            $stmt = $pdo->prepare("INSERT INTO sales (customer_id, product_id, sale_date)
                                   VALUES (:customer_id, :product_id, :sale_date)");
            $stmt->execute([
                'customer_id' => $customerId,
                'product_id' => $productId,
                'sale_date' => $sale['sale_date']
            ]);
        }
    }
}
// filters logique
function getFilteredSales(array $filters): array
{
    global $pdo;

    $query = "SELECT sales.id, customers.name AS customer, products.name AS product, products.price, sales.sale_date
              FROM sales
              JOIN customers ON sales.customer_id = customers.id
              JOIN products ON sales.product_id = products.id
              WHERE 1=1";

    $params = [];

    if (!empty($filters['customer'])) {
        $query .= " AND sales.customer_id = :customer_id";
        $params['customer_id'] = $filters['customer'];
    }
    if (!empty($filters['product'])) {
        $query .= " AND sales.product_id = :product_id";
        $params['product_id'] = $filters['product'];
    }
    if (!empty($filters['price'])) {
        $query .= " AND products.price = :price";
        $params['price'] = $filters['price'];
    }
    if (!empty($filters['min_price'])) {
        $query .= " AND products.price >= :min_price";
        $params['min_price'] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $query .= " AND products.price <= :max_price";
        $params['max_price'] = $filters['max_price'];
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
