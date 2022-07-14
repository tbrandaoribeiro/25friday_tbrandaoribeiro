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


//Get data from the "frontend"
$data = json_decode(file_get_contents("php://input"));


if (!isset($data->product_id)) {
    http_response_code(406);
    echo json_encode([
        'success' => 0, 
        'message' => 'You must provide an id'
    ]);
    exit;
}


try {
    //First checking if the product_id is valid
    $checkQuery = "SELECT * FROM products WHERE product_id=:product_id";
    
    $getStmt = $conn->prepare($checkQuery);
    $getStmt->bindValue(':product_id', $data->product_id, PDO::PARAM_INT);
    $getStmt->execute();

    if ($getStmt->rowCount() > 0) {

        $deleteQuery = "DELETE FROM products WHERE product_id=:product_id";
        
        $deleteProductStmt = $conn->prepare($deleteQuery);
        $deleteProductStmt->bindValue(':product_id', $data->product_id,PDO::PARAM_INT);

        if ($deleteProductStmt->execute()) {
            http_response_code(200);
            echo json_encode([
                'success' => 1,
                'message' => 'Product deleted successfully'
            ]);
            exit;
        }
        //HTTP Response for some internal error
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'message' => 'Product not deleted. Something went wrong.'
        ]);
        exit;

    } else {
        //HTTP Response for Not Acceptable
        http_response_code(406);
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