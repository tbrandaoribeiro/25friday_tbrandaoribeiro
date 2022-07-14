<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Invalid Request Method. HTTP method should be POST',
    ]);
    exit;
}

require './Database.php';
$database = new Database();
$conn = $database->dbConnection();

//Data obtained from the "Frontend"
$data = json_decode(file_get_contents("php://input"));

if ( !isset($data->product_id) || !isset($data->product_name) || !isset($data->category) || !isset($data->price) || !isset($data->available_stock) ) {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'It is mandatory to provide all details about the product, such as product_id, product_name, category, price, available_stock'
    ]);
} else {
    try{
        //check if product exists
        $updateProductQuery = "UPDATE products SET product_name=:product_name, category=:category, price=:price, available_stock=:available_stock WHERE product_id=:product_id";
        
        $updateStmt = $conn->prepare($updateProductQuery);
        $updateStmt->bindValue(':product_id', $data->product_id, PDO::PARAM_INT);
        $updateStmt->bindValue(':product_name', $data->product_name, PDO::PARAM_INT);
        $updateStmt->bindValue(':category', $data->category, PDO::PARAM_INT);
        $updateStmt->bindValue(':price', $data->price, PDO::PARAM_INT);
        $updateStmt->bindValue(':available_stock', $data->available_stock, PDO::PARAM_INT);
        $updateStmt->execute();
        http_response_code(200);
        echo json_encode([
            'success' => 1,
            'message' => 'The product '.$data->product_name.' was updated successfully'
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}