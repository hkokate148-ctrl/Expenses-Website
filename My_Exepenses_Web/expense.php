<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "expenses_app";

$conn = new mysqli($servername,$username,$password,$dbname);
if($conn->connect_error){
    die(json_encode(["success"=>false,"message"=>"DB connection failed: ".$conn->connect_error]));
}

// Delete expense
if(isset($_GET['action']) && $_GET['action']=="delete" && isset($_GET['id'])){
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id=?");
    $stmt->bind_param("i",$id);
    if($stmt->execute()){
        echo json_encode(["success"=>true,"message"=>"Expense deleted successfully"]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Delete failed"]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Fetch all expenses
if(isset($_GET['action']) && $_GET['action']=="fetch"){
    $res = $conn->query("SELECT id, userId, amount, category, note, date, time FROM expenses ORDER BY id DESC");
    $data=[];
    while($row=$res->fetch_assoc()){ $data[]=$row; }
    echo json_encode($data);
    exit;
}

// Insert expense
$input = json_decode(file_get_contents("php://input"), true);
if(!$input) $input = $_POST;

$userId = 1; // Temporary

$amount = isset($input['amount']) ? floatval($input['amount']) : 0;
$category = isset($input['category']) ? trim($input['category']) : '';
$note = isset($input['note']) ? trim($input['note']) : '';

if($amount <= 0 || empty($category)){
    echo json_encode(["success"=>false,"message"=>"Enter valid amount & category"]);
    exit;
}

$date = date("Y-m-d");
$time = date("H:i:s");

$stmt = $conn->prepare("INSERT INTO expenses (userId, amount, category, note, date, time, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("idssss", $userId, $amount, $category, $note, $date, $time);

if($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Expense added successfully!"]);
}else{
    echo json_encode(["success"=>false,"message"=>"Execute failed: ".$stmt->error]);
}

$stmt->close();
$conn->close();
?>
