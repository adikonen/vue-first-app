<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTION");

$db_file = new PDO(
    'sqlite:'.__DIR__.'/main.db',
    null,
    null,
    [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);
// Create employees table.
$db_file->exec(
    "CREATE TABLE IF NOT EXISTS fruits(
        id INTEGER PRIMARY KEY, 
        name TEXT,
        price INTEGER
    )"
);

if($_SERVER['REQUEST_METHOD'] === 'GET')
{
    $data = $db_file->query("SELECT * FROM fruits")->fetchAll();
    echo json_encode($data);
}

else if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $input = json_decode(file_get_contents("php://input"));
    
    $insert = "INSERT INTO fruits (name, price) VALUES (:name, :price)";
    $stmt = $db_file->prepare($insert);
    
    // Bind parameters to statement variables.
    $stmt->bindParam(':name', $input->name, PDO::PARAM_STR);
    $stmt->bindParam(':price', $input->price, PDO::PARAM_INT);
     
    // Execute statement.
    $stmt->execute();
    
}
else if($_SERVER["REQUEST_METHOD"] === "PUT")
{
    $inputs = json_decode(file_get_contents("php://input"));
    echo json_encode($inputs);
    $update = "UPDATE fruits SET name = :newName, price = :newPrice WHERE name = :name and price = :price";
    
    $stmt = $db_file->prepare($update);
    foreach($inputs as $input)
    {
        $stmt->bindParam(':name', $input->name, PDO::PARAM_STR);
        $stmt->bindParam(':price', $input->price, PDO::PARAM_INT);
        $stmt->bindParam(':newName', $input->newName, PDO::PARAM_STR);
        $stmt->bindParam(':newPrice',$input->newPrice, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    // // Execute statement.
}
else if($_SERVER["REQUEST_METHOD"] === "DELETE")
{
    $input = json_decode(file_get_contents("php://input"));
    $delete = "DELETE FROM fruits WHERE name = :name and price = :price";

    $stmt = $db_file->prepare($delete);
    $stmt->bindParam(':name', $input->name, PDO::PARAM_STR);
    $stmt->bindParam(':price', $input->price, PDO::PARAM_INT);
     
    // Execute statement.
    $stmt->execute();
}
