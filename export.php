<?php
include 'connection.php';
try {
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $selected_date = $_GET['date'] ?? date('Y-m-d');

    // Prepare SQL statement
    $stmt = $conn->prepare("
        SELECT 
            passcode, 
            name, 
            time_in, 
            time_out, 
            TIMESTAMPDIFF(HOUR, time_in, time_out) as hours_worked
        FROM 
            attendance_log
        WHERE 
            DATE(time_in) = ?
        ORDER BY 
            time_in ASC
    ");

    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare CSV
    $filename = "attendance_export_" . $selected_date . ".csv";
    
    // Set headers for file download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Write CSV headers
    fputcsv($output, [
        'Passcode', 
        'Employee Name', 
        'Time In', 
        'Time Out', 
        'Hours Worked',
        'Date'
    ]);

    // Write data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['passcode'],
            $row['name'],
            $row['time_in'],
            $row['time_out'] ?? 'Not Clocked Out',
            $row['hours_worked'] ? number_format($row['hours_worked'], 2) : 'N/A',
            $selected_date
        ]);
    }

    // Close output stream
    fclose($output);

    // Close database connection
    $conn->close();
    exit();

} catch (Exception $e) {
    // Error handling
    header('Content-Type: text/html');
    echo "Error exporting attendance: " . $e->getMessage();
    
    if (isset($conn)) {
        $conn->close();
    }
    exit();
}
