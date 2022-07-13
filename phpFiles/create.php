<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] !== 'POST') :
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Invalid Request Method. HTTP method should be POST',
    ]);
    exit;
endif;

require 'Database.php';
$database = new Database();
$conn = $database->dbConnection();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->product_name) || !isset($data->category) || !isset($data->price) || !isset($data->available_stock)) {

    echo json_encode([
        'success' => 0,
        'message' => 'Please fill all the required fields product_name, category, price, available_stock.',
    ]);

} else if (empty(trim($data->product_name)) || empty(trim($data->category)) || empty(trim($data->price)) || empty(trim($data->available_stock))) {

    echo json_encode([
        'success' => 0,
        'message' => 'Empty field detected.',
    ]);
    exit;

}

try {
    //isolating the parameters sent after validating that they exist.
    $productName = htmlspecialchars(trim($data->product_name));
    $category = htmlspecialchars(trim($data->category));
    $price = htmlspecialchars(trim($data->price));
    $available_stock = htmlspecialchars(trim($data->available_stock));

    //INSERT INTO products (name, category, price, available_stock) VALUES ('Latte', 'Espresso Drinks', '2.22', 50);
    //$queryToInsert = "INSERT INTO products (product_name, category, price, available_stock) VALUES ($name,:category,:price,:available_stock)";
    $queryToInsert = "INSERT INTO products (product_name, category, price, available_stock) VALUES (:productname,:category,:price,:available_stock) RETURNING product_id";

    $stmt = $conn->prepare($queryToInsert);

    $stmt->bindValue(':productname', $productName, PDO::PARAM_STR);
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    $stmt->bindValue(':price', $price, PDO::PARAM_STR);
    $stmt->bindValue(':available_stock', $available_stock, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        $idProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        
        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Data Inserted Successfully with id '.$idProduct['product_id']
        ]);
        exit;
    }
    http_response_code(404);
    echo json_encode([
        'success' => 0,
        'message' => 'Data not Inserted.'
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => $e->getMessage()
    ]);
    exit;
}