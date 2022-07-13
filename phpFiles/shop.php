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

require './Database.php';
// Creating a object to connect to the database
$database = new Database();
$conn = $database->dbConnection();

//Obtaining the input from the frontend
$data = json_decode(file_get_contents("php://input"));

//print_r($data);
//Verification for the imperative parameters to be sent by the client
if ( !isset($data->products2Buy) || !isset($data->moneyGiven) ) {
    echo json_encode([
        'success' => 0,
        'message' => 'You must provide a product or a list of products and money to pay for the products.',
    ]);
    exit;
} 

//Creating an array with all products (main products and extras)
$productList = explode("&,", strval($data->products2Buy));

// Used throughout all this script as a stopping criteria for the FOR cycles
$numberOfProducts = count($productList);


//This line is not needed anymore
//$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);

// Checking if the products exist in inventory
for ($i=0 ; $i<$numberOfProducts ; $i++) {
    $sql2CheckInventory[$i] = "SELECT category, product_name, available_stock FROM products WHERE product_name = '$productList[$i]' ";
    $checkInventoryStmt = $conn->prepare($sql2CheckInventory[$i]);
    $checkInventoryStmt->execute();
    $data2[$i] = $checkInventoryStmt->fetch(PDO::FETCH_ASSOC); 
    if ($data2[$i] == "") {
        echo json_encode([
            'message' => 'The item '.$productList[$i].' is not on our inventory',
        ]);
    }
}

print_r($i);
unset($i);
// Used another For cycle to be able to choose between canceling the order all together ou unseting the unexisting product from the order.
// Any of the below solutions can be nested in the above For cycle.
for ($i=0 ; $i<$numberOfProducts ; $i++) {
    //print_r($data2[$i]['available_stock']);
    /* if ( ($data2[$i]['available_stock'] - 1 < 0 ) && ($data2[$i]['category'] == "Extras") ) {
        //echo "cancelling order because of ".$data2[$i]['product_name'];
        echo json_encode([
            'success' => 0,
            'message' => 'Cancelling the order because there is not enough -> \''.$data2[$i]['product_name'].'\' to fullfill the order',
        ]);
    }*/
    
    //or deleting the product from the order --- just another way of doing it 
    if  (($data2[$i]['available_stock'])  == 0  ) {
        unset($productList[$i]);
    }

}

//re-indexing the values deleted from the second if clause
$productList = array_values($productList);
// end of re-indexing

print_r($productList);

unset ($i);
unset ($stmt);
unset ($data2);


//This for cycle is only for calculating the final price of the order.
//Accessing the price value and add it to the $finalPrice var.
for ($i=0; $i<$numberOfProducts; $i++){
    $sql[$i]= "SELECT * FROM products WHERE product_name = '$productList[$i]' AND available_stock > 0; \n" ;
    $stmt = $conn->prepare($sql[$i]);
    $stmt->execute();
    $data2[$i] = $stmt->fetch(PDO::FETCH_ASSOC);

    $finalPrice = $finalPrice + floatval($data2[$i]['price']) ;
}
print_r($productList);

unset ($i);

//In case the user does not provide enough money.
if ( $finalPrice > $data->moneyGiven ) {
    $moneyClientOwes = $finalPrice - $data->moneyGiven;
    echo json_encode([
        'success' => 0,
        'message' => 'You do not have enough money to pay! You still owe us '.$moneyClientOwes,
    ]);
} else {
    //Calculate the change from the original money handed by the client.
    $change = $data->moneyGiven - $finalPrice;
    print_r($productList);
    //Updating the available stock after buying
    for ($i=0 ; $i<$numberOfProducts ; $i++){
        $updateInventory[$i]= "UPDATE products SET available_stock = available_stock - 1 WHERE product_name = '$productList[$i]'; \n" ;
        $updateStmt2 = $conn->prepare($updateInventory[$i]);
        $updateStmt2->execute();   
    } 
    
    echo json_encode([
        'success' => 1,
        'message1' => 'Your order: ',
        'message2' => 'The total is '.$finalPrice.'\u20ac! You gave me '.$data->moneyGiven.'\u20ac, so the change will be '.$change.'\u20ac'
    ]);
}
































/*
elseif (empty(trim($data->name)) || empty(trim($data->category)) || empty(trim($data->price)) || empty(trim($data->available_stock))) :

    echo json_encode([
        'success' => 0,
        'message' => 'Oops! empty field detected. Please fill all the fields.',
    ]);
    exit;

endif;
*/
/*
try {

    $name = htmlspecialchars(trim($data->name));


    error_log("\nARGUMENTOS", 3, "./error.log");
    error_log("\n$name->".print_r($name), 3, "./error.log");

//INSERT INTO products (name, category, price, available_stock) VALUES ('Latte', 'Espresso Drinks', '2.22', 50);
    $query = "INSERT INTO products (product_name, category, price, available_stock) VALUES(:name,:category,:price,:available_stock)";

    $stmt = $conn->prepare($query);

    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    $stmt->bindValue(':price', $price, PDO::PARAM_STR);
    $stmt->bindValue(':available_stock', $available_stock, PDO::PARAM_STR);

    if ($stmt->execute()) {

        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Data Inserted Successfully.'
        ]);
        exit;
    }
    
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
*/