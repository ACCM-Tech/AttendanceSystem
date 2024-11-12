<?php
include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
            padding: 20px;
        }

        .employee-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .employee-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 300px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .employee-card:hover {
            transform: scale(1.05);
        }

        .employee-card img {
            border-radius: 50%;
            width: 200px;
            height: 200px;
            object-fit: cover;
            margin-bottom: 15px;
            border: 4px solid #4CAF50;
        }

        .employee-details {
            margin-top: 10px;
        }

        .employee-details p {
            margin: 5px 0;
            color: #333;
        }

        .employee-id {
            color: #666;
            font-size: 14px;
        }

        .no-employees {
            text-align: center;
            color: #888;
            margin-top: 50px;
            font-size: 18px;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="employee-container">
        <?php
        $sql = "SELECT * FROM employees";
        $result = $conn->query($sql);

        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Check if picture exists and is not null
                    $pictureHtml = $row["picture"] 
                        ? "<img src='data:image/jpeg;base64,". base64_encode($row["picture"]). "' alt='Employee Picture'>" 
                        : "<img src='placeholder.jpg' alt='No Picture'>";
                    
                    echo "
                    <div class='employee-card'>
                        {$pictureHtml}
                        <div class='employee-details'>
                            <p class='employee-id'>ID: {$row['id']}</p>
                            <p><strong>Name:</strong> {$row['name']}</p>
                        </div>
                    </div>";
                }
            } else {
                echo "<div class='no-employees'>No employees found.</div>";
            }
        } else {
            echo "<div class='error-message'>Error: " . $conn->error . "</div>";
        }

        // Close the connection
        $conn->close();
        ?>
    </div>
</body>
</html>
