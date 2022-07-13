<?php

//Setting up the HTTP Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

//Only allow GET Method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') :
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Invalid Request Method. HTTP method should be GET',
    ]);
    exit;
endif;

require 'Database.php';
// Creating a object to connect to the database
$database = new Database();
$conn = $database->dbConnection();

//Obtaining the input from the frontend
$data = json_decode(file_get_contents("php://input"));

// This allows the user to either get information about a product or all products
if (isset($data->product_id)) {
    $getQuery = "SELECT * FROM products WHERE product_id=".$data->product_id;
} else {
    $getQuery = "SELECT * FROM products ORDER BY product_id ASC";
}


try {
    $stmt = $conn->prepare($getQuery);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // https://stackoverflow.com/questions/55196852/what-is-the-difference-between-fetch-and-fetchall-in-pdo-query
        // Depending on the quey selected as $getQuery, the fetch is different
        $dbResponse = null;
        if (isset($data->product_id)) {
            $dbResponse = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $dbResponse = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode([
            'success' => 1,
            'data' => $dbResponse,
        ]);
    }
    else {
        echo json_encode([
            'success' => 0,
            'message' => 'The product_id you provided is not valid!',
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => $e->getMessage()
    ]);
    exit;
}