<?php
header('Content-Type: application/json');

$jsonFile = __DIR__ . '/../classes/json/item.json';

// Check if file exists
if (!file_exists($jsonFile)) {
    echo json_encode(["items" => []]);
    exit;
}

// Read and decode JSON file
$jsonContent = file_get_contents($jsonFile);
$data = json_decode($jsonContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["error" => "Invalid JSON format"]);
    exit;
}

$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

$results = [];

if (!empty($searchTerm)) {
    $searchTermLower = strtolower($searchTerm);

    foreach ($data['items'] as $product) {
        if (
            stripos($product['code'], $searchTermLower) !== false ||
            stripos($product['product_group'], $searchTermLower) !== false ||
            stripos($product['product_description'], $searchTermLower) !== false
        ) {
            $results[] = [
                "id" => $product["id"],
                "price" => $product["price"],
                "text" => "<span style='color:red;'>" . htmlspecialchars($product["code"]) . "</span> " .
                          "<b>" . htmlspecialchars($product["product_description"]) . "</b> " .
                          "<span style='color:green;'>" . htmlspecialchars($product["pack_size"]) . "</span>"
            ];
        }
    }
}

// Return JSON response
echo json_encode(["items" => $results]);
?>
