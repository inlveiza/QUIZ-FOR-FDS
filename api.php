<?php
include 'Connection.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($con->connect_error) {
    send_response(500, ['message' => 'Database connection failed: ' . $con->connect_error]);
    exit;
}
$request_method = $_SERVER['REQUEST_METHOD'];
function send_response($status_code, $data) {
    http_response_code($status_code);
    echo json_encode($data);
}
switch ($request_method) {
    case 'GET':
        // Retrieve 
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                send_response(200, $user);
            } else {
                send_response(404, ['message' => 'User not found']);
            }
        } else {
            $query = "SELECT * FROM users";
            $result = $con->query($query);
            $users = $result->fetch_all(MYSQLI_ASSOC);
            send_response(200, $users);
        }
        break;

    case 'POST':
    // Insert
        $data = $_POST;

        if (!isset($data['firstname']) || empty(trim($data['firstname'])) || !isset($data['lastname']) || empty(trim($data['lastname']))) {
            send_response(400, ['message' => 'Invalid input']);
            exit;
        }
        $firstname = trim($data['firstname']);
        $lastname = trim($data['lastname']);
        $is_admin = isset($data['is_admin']) ? 1 : 0;
        $query = "INSERT INTO users (firstname, lastname, is_admin) VALUES (?, ?, ?)";
        $stmt = $con->prepare($query);
        if ($stmt === false) {
            send_response(500, ['message' => 'Query preparation failed: ' . $con->error]);
            exit;
        }
        $stmt->bind_param('ssi', $firstname, $lastname, $is_admin);
        if ($stmt->execute() === false) {
            send_response(500, ['message' => 'Execution failed: ' . $stmt->error]);
            exit;
        }

        if ($stmt->affected_rows > 0) {
            send_response(201, ['message' => 'User created successfully']);
        } else {
            send_response(500, ['message' => 'Failed to create user']);
        }
        $stmt->close(); 
        break;

    case 'PUT':
        // Update 
        if (!isset($_GET['id'])) {
            send_response(400, ['message' => 'User ID not provided']);
            exit;
        }
    
        $id = intval($_GET['id']); 
        if ($id <= 0) {
            send_response(400, ['message' => 'Invalid input']);
            exit;
        }
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    
        if (!$user) {
            send_response(404, ['message' => 'User not found']);
            exit;
        }
        parse_str(file_get_contents('php://input'), $data); 
    
        if (!isset($data['firstname']) || empty(trim($data['firstname'])) || 
            !isset($data['lastname']) || empty(trim($data['lastname']))) {
            send_response(400, ['message' => 'Invalid input']);
            exit;
        }
    
        $firstname = trim($data['firstname']); 
        $lastname = trim($data['lastname']); 
        $is_admin = isset($data['is_admin']) ? 1 : 0; 
    
        $query = "UPDATE users SET firstname = ?, lastname = ?, is_admin = ? WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param('ssii', $firstname, $lastname, $is_admin, $id);
        $stmt->execute();
    
        if ($stmt->affected_rows > 0) {
            send_response(200, ['message' => 'User updated successfully']); 
        } else {
            send_response(404, ['message' => 'User not found']); 
        }
        $stmt->close(); 
        break;
    
    case 'DELETE':
        // Delete 
        if (!isset($_GET['id'])) {
            send_response(400, ['message' => 'User ID not provided']);
            exit;
        }

        $id = intval($_GET['id']);
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            send_response(200, ['message' => 'User deleted successfully']);
        } else {
            send_response(404, ['message' => 'User not found']);
        }
        $stmt->close(); 
        break;

    default:
        send_response(405, ['message' => 'Method not allowed']);
        break;
}
$con->close();
?>
