<?php
// Enable strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure proper JSON headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection parameters
$host = 'localhost';
$dbname = 'attendance';
$username = 'root';
$password = '';

// Upload directory
$uploadDir = 'uploads/';

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Capture raw POST data
$rawInput = file_get_contents('php://input');
$postData = json_decode($rawInput, true);

// Fallback to $_POST if json_decode fails
if (json_last_error() !== JSON_ERROR_NONE) {
    $postData = $_POST;
}

// Extract parameters
$passcode = $postData['passcode'] ?? '';
$action = $postData['action'] ?? '';
$capturedPicture = $postData['capturedPicture'] ?? '';
$employeeName = $postData['employeeName'] ?? '';

// Function to send JSON response
function sendResponse($data) {
    echo json_encode($data);
    exit;
}

// Function to save image
function saveImage($imageData, $prefix) {
    global $uploadDir, $passcode;
    
    // Remove data URL prefix
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    
    // Decode image
    $imageBlob = base64_decode($imageData);
    
    // Generate unique filename
    $filename = $uploadDir . $prefix . '_' . $passcode . '_' . date('YmdHis') . '.png';
    
    // Save image
    if (file_put_contents($filename, $imageBlob)) {
        return $imageBlob;
    }
    
    return null;
}

// Validate input
if (empty($passcode) || !is_numeric($passcode) || strlen($passcode) !== 4) {
    sendResponse([
        'valid' => false,
        'error' => 'Invalid passcode format'
    ]);
}

// Create database connection
try {
    $conn = new mysqli($host, $username, $password, $dbname);
   
    if ($conn->connect_error) {
        sendResponse([
            'valid' => false,
            'error' => 'Database connection failed: ' . $conn->connect_error
        ]);
    }

    // Verify passcode exists in employees table
    $stmt = $conn->prepare("SELECT name FROM employees WHERE passcode = ?");
    $stmt->bind_param("s", $passcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendResponse([
            'valid' => false,
            'error' => 'Employee not found'
        ]);
    }

    $employee = $result->fetch_assoc();

    // Decode and save captured picture
    $capturedImageBlob = saveImage($capturedPicture, $action);

    // Process action
    switch($action) {
        case 'timeIn':
            // Check if already timed in today
            $checkStmt = $conn->prepare("
                SELECT id FROM attendance_log 
                WHERE passcode = ? AND DATE(time_in) = CURDATE() AND time_out IS NULL
            ");
            $checkStmt->bind_param("s", $passcode);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                sendResponse([
                    'valid' => false,
                    'error' => 'You are already clocked in today'
                ]);
            }

            // Insert time in record
            $insertStmt = $conn->prepare("
                INSERT INTO attendance_log 
                (passcode, name, time_in, time_in_picture) 
                VALUES (?, ?, NOW(), ?)
            ");
            $insertStmt->bind_param("sss", 
                $passcode, 
                $employee['name'], 
                $capturedImageBlob
            );
            $insertStmt->execute();

            sendResponse([
                'valid' => true,
                'message' => 'Time In Successful',
                'employee' => ['name' => $employee['name']]
            ]);
            break;

        case 'timeOut':
            // Find most recent time in without time out
            $findStmt = $conn->prepare("
                SELECT id FROM attendance_log
                WHERE passcode = ? AND time_out IS NULL
                ORDER BY time_in DESC LIMIT 1
            ");
            $findStmt->bind_param("s", $passcode);
            $findStmt->execute();
            $findResult = $findStmt->get_result();

            if ($findResult->num_rows > 0) {
                $updateStmt = $conn->prepare("
                    UPDATE attendance_log
                    SET time_out = NOW(), 
                        time_out_picture = ?
                    WHERE passcode = ? AND time_out IS NULL
                ");
                $updateStmt->bind_param("bs", 
                    $capturedImageBlob, 
                    $passcode
                );
                $updateStmt->execute();

                sendResponse([
                    'valid' => true,
                    'message' => 'Time Out Successful',
                    'employee' => ['name' => $employee['name']]
                ]);
            } else {
                sendResponse([
                    'valid' => false,
                    'error' => 'No active time in found'
                ]);
            }
            break;

        case 'registerEmployee':
            // Optional: Add employee registration logic
            sendResponse([
                'valid' => true,
                'message' => 'Employee Registration Initiated',
                'employee' => ['name' => $employee['name']]
            ]);
            break;

        default:
            sendResponse([
                'valid' => false,
                'error' => 'Invalid action'
            ]);
    }

} catch (Exception $e) {
    sendResponse([
        'valid' => false,
        'error' => 'Unexpected error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
