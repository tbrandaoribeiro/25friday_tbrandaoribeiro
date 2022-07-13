<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') :
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Invalid Request Method. HTTP method should be DELETE',
    ]);
    exit;
endif;

require './Database.php';
$database = new Database();
$conn = $database->dbConnection();

$data = json_decode(file_get_contents("php://input"));


if (!isset($data->product_id)) {
    http_response_code(404);
    echo json_encode([
        'success' => 0, 
        'message' => 'You must provide an id'
    ]);
    exit;
}


try {
    //First checking if the product_id is valid
    $checkQuery = "SELECT * FROM products WHERE product_id=:product_id";
    
    $fetch_stmt = $conn->prepare($checkQuery);
    $fetch_stmt->bindValue(':product_id', $data->product_id, PDO::PARAM_INT);
    $fetch_stmt->execute();

    if ($fetch_stmt->rowCount() > 0) {

        $deleteQuery = "DELETE FROM products WHERE product_id=:product_id";
        
        $delete_product_stmt = $conn->prepare($deleteQuery);
        $delete_product_stmt->bindValue(':product_id', $data->product_id,PDO::PARAM_INT);

        if ($delete_product_stmt->execute()) {
            http_response_code(200);
            echo json_encode([
                'success' => 1,
                'message' => 'Product deleted successfully'
            ]);
            exit;
        }
        http_response_code(404);
        echo json_encode([
            'success' => 0,
            'message' => 'Product not deleted. Something went wrong.'
        ]);
        exit;

    } else {
        http_response_code(404);
        echo json_encode([
            'success' => 0,
            'message' => 'The provided product_id is not valid. Please, provide an existing id.'
        ]);
        exit;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => $e->getMessage()
    ]);
    exit;
}