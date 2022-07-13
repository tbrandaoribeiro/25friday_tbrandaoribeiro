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
        $updateProduct = "UPDATE products SET product_name=:product_name, category=:category, price=:price, available_stock=:available_stock WHERE product_id=:product_id";
        
        $update_stmt = $conn->prepare($updateProduct);
        $update_stmt->bindValue(':product_id', $data->product_id, PDO::PARAM_INT);
        $update_stmt->bindValue(':product_name', $data->product_name, PDO::PARAM_INT);
        $update_stmt->bindValue(':category', $data->category, PDO::PARAM_INT);
        $update_stmt->bindValue(':price', $data->price, PDO::PARAM_INT);
        $update_stmt->bindValue(':available_stock', $data->available_stock, PDO::PARAM_INT);
        $update_stmt->execute();

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
/*
if (!isset($data->name) || !isset($data->category) || !isset($data->price) || !isset($data->available_stock)) {
    
}
*/
/*
try {

    $fetch_post = "SELECT * FROM products WHERE product_id=:product_id";
    error_log("\nQUERY A SER CORRIDA select-> ".print_r($fetch_post), 3, "./error.log");
    $update_stmt = $conn->prepare($fetch_post);
    $update_stmt->bindValue(':product_id', $data->product_id, PDO::PARAM_INT);
    $update_stmt->execute();

    if ($update_stmt->rowCount() > 0) {

        $delete_post = "DELETE FROM products WHERE product_id=:product_id";
        error_log("\nQUERY A SER CORRIDA -> ".print_r($delete_post), 3, "./error.log");
        $delete_post_stmt = $conn->prepare($delete_post);
        $delete_post_stmt->bindValue(':product_id', $data->product_id,PDO::PARAM_INT);

        if ($delete_post_stmt->execute()) {

            echo json_encode([
                'success' => 1,
                'message' => 'Product deleted successfully'
            ]);
            exit;
        }

        echo json_encode([
            'success' => 0,
            'message' => 'Product not deleted.'
        ]);
        exit;

    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Invalid ID. No posts found by the ID.'
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

*/