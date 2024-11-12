<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Attendance Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #f4f6f7;
            --text-color: #2c3e50;
            --card-background: #ffffff;
            --hover-color: #2980b9;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            padding: 40px 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 30px;
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .date-selector {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            gap: 15px;
        }

        .date-selector label {
            font-weight: 600;
            color: var(--text-color);
        }

        #attendance_date {
            padding: 10px;
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        #attendance_date:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .submit-btn {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .image-thumbnail {
            max-width: 80px;
            max-height: 80px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .image-thumbnail:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .modal-content:hover {
            transform: scale(1.02);
        }

        .export-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: var(--secondary-color);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .export-btn:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .date-selector {
                flex-direction: column;
                gap: 10px;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Employee Attendance Log</h1>

        <form method="GET">
            <div class="date-selector">
                <label for="attendance_date">Select Date:</label>
                <input type="date" id="attendance_date" name="attendance_date"
                       value="<?php echo isset($_GET['attendance_date']) ? $_GET['attendance_date'] : date('Y-m-d'); ?>">
                <button type="submit" class="submit-btn">Fetch Attendance</button>
            </div>
        </form>

        <div id="imageModal" class="modal" onclick="this.style.display='none'">
            <img class="modal-content" id="modalImage">
        </div>

        <?php
        // Database Connection
        $host = 'localhost';
        $dbname = 'attendance';
        $username = 'root';
        $password = '';

        try {
            $conn = new mysqli($host, $username, $password, $dbname);
            
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            $selected_date = $_GET['attendance_date'] ?? date('Y-m-d');

            // Prepared statement to prevent SQL injection
            $stmt = $conn->prepare("
                SELECT
                    passcode,
                    name,
                    time_in,
                    time_out,
                    time_in_picture,
                    time_out_picture
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

            echo "<h2>Attendance for " . htmlspecialchars($selected_date) . "</h2>";
            
            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr>
                        <th>Passcode</th>
                        <th>Name</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Time In Picture</th>
                        <th>Time Out Picture</th>
                      </tr>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['passcode']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['time_in']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['time_out'] ?? 'Not Clocked Out') . "</td>";
                   
                    // Time In Picture
                    if ($row['time_in_picture']) {
                        $timeInImageSrc = "data:image/png;base64," . base64_encode($row['time_in_picture']);
                        echo "<td><img src='{$timeInImageSrc}' class='image-thumbnail' onclick='showFullImage(this.src)'></td>";
                    } else {
                        echo "<td>No Picture</td>";
                    }

                    // Time Out Picture
                    if ($row['time_out_picture']) {
                        $timeOutImageSrc = "data:image/png;base64," . base64_encode($row['time_out_picture']);
                        echo "<td><img src='{$timeOutImageSrc}' class='image-thumbnail' onclick='showFullImage(this.src)'></td>";
                    } else {
                        echo "<td>No Picture</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";

                // Export button
                echo "<a href='export.php?date=" . urlencode($selected_date) . "' class='export-btn'>Export to CSV</a>";
            } else {
                echo "<p style='text-align: center; color: #888;'>No attendance records found for the selected date.</p>";
            }

        } catch (Exception $e) {
            echo "<div style='color: red; text-align: center;'>Error: " . $e->getMessage() . "</div>";
        } finally {
            if (isset($conn)) $conn->close();
        }
        ?>
    </div>

    <script>
        function showFullImage(src) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = "flex";
            modalImg.src = src;
        }

        // Optional: Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
