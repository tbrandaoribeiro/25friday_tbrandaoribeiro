<?php

//Setting up the HTTP Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//Only allow POST Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') :
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Invalid Request Method. HTTP method should be POST',
    ]);
    exit;
endif;

require 'Database.php';
// Creating a object to connect to the database
$database = new Database();
$conn = $database->dbConnection();

//Obtaining the input from the frontend
$data = json_decode(file_get_contents("php://input"));

//Verifications to see if user sent the parameters
if (!isset($data->product_name) || !isset($data->category) || !isset($data->price) || !isset($data->available_stock)) {
    //HTTP Response for not Acceptable
    http_response_code(406);
    echo json_encode([
        'success' => 0,
        'message' => 'Please fill all the required fields product_name, category, price, available_stock.',
    ]);

//Verification to see if not empty    
} else if (empty(trim($data->product_name)) || empty(trim($data->category)) || empty(trim($data->price)) || empty(trim($data->available_stock))) {
    //HTTP Response for not Acceptable
    http_response_code(406);
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

    $queryToInsert = "INSERT INTO products (product_name, category, price, available_stock) VALUES (:productname,:category,:price,:available_stock) RETURNING product_id";

    $insertStmt = $conn->prepare($queryToInsert);
    //Binding the parameters from $data to column values
    $insertStmt->bindValue(':productname', $productName, PDO::PARAM_STR);
    $insertStmt->bindValue(':category', $category, PDO::PARAM_STR);
    $insertStmt->bindValue(':price', $price, PDO::PARAM_STR);
    $insertStmt->bindValue(':available_stock', $available_stock, PDO::PARAM_STR);
    
    if ($insertStmt->execute()) {
        $idProduct = $insertStmt->fetch(PDO::FETCH_ASSOC);
        //HTTP Response for successful creation
        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Data Inserted Successfully with id '.$idProduct['product_id']
        ]);
        exit;
    }
    ////HTTP Response for successful creation
    http_response_code(500);
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