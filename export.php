<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle export requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['export_csv'])) {
        // Export to CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="student_results.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV header
        fputcsv($output, array('Student ID', 'Name', 'Math', 'English', 'Science', 'Total', 'Average', 'Grade'));
        
        // Get all students
        $query = "SELECT * FROM students ORDER BY name";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, array(
                    $row['student_id'],
                    $row['name'],
                    $row['math'],
                    $row['english'],
                    $row['science'],
                    $row['total'],
                    number_format($row['average'], 1),
                    $row['grade']
                ));
            }
        }
        
        fclose($output);
        exit;
    } elseif (isset($_POST['export_pdf'])) {
        // For PDF export, we'll create a simple HTML file that can be printed as PDF
        // In a real application, you would use a library like FPDF or TCPDF
        
        // Get all students
        $query = "SELECT * FROM students ORDER BY name";
        $result = $conn->query($query);
        $students = array();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
        }
        
        // Create printable HTML
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Student Results</title>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .print-header { text-align: center; margin-bottom: 20px; }
                @media print {
                    .no-print { display: none; }
                    body { margin: 0; padding: 20px; }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>Student Results Report</h1>
                <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
            </div>
            
            <div class="no-print" style="text-align: center; margin-bottom: 20px;">
                <button onclick="window.print()">Print/Save as PDF</button>
                <button onclick="window.location=\'export.php\'">Back to Export Page</button>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Math</th>
                        <th>English</th>
                        <th>Science</th>
                        <th>Total</th>
                        <th>Average</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($students as $student) {
            echo '<tr>
                <td>' . htmlspecialchars($student['student_id']) . '</td>
                <td>' . htmlspecialchars($student['name']) . '</td>
                <td>' . $student['math'] . '</td>
                <td>' . $student['english'] . '</td>
                <td>' . $student['science'] . '</td>
                <td>' . $student['total'] . '</td>
                <td>' . number_format($student['average'], 1) . '</td>
                <td>' . $student['grade'] . '</td>
            </tr>';
        }
        
        echo '</tbody>
            </table>
        </body>
        </html>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data - Student Result Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student Result Management System</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)
                <a href="logout.php" class="btn btn-sm">Logout</a>
            </div>
        </div>
        
        <div class="dashboard">
            <div class="sidebar">
                <ul class="menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage_students.php">Manage Students</a></li>
                    <li><a href="reports.php">Performance Reports</a></li>
                    <li><a href="export.php" class="active">Export Data</a></li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li><a href="manage_users.php">Manage Users</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="main-content">
                <h2>Export Student Results</h2>
                
                <div class="export-options">
                    <div class="export-card">
                        <h3>Export to CSV</h3>
                        <p>Download student results as a CSV file that can be opened in Excel or other spreadsheet applications.</p>
                        <form method="POST" action="">
                            <button type="submit" name="export_csv" class="btn">Download CSV</button>
                        </form>
                    </div>
                    
                    <div class="export-card">
                        <h3>Export to PDF</h3>
                        <p>Generate a printable PDF report of all student results.</p>
                        <form method="POST" action="">
                            <button type="submit" name="export_pdf" class="btn">Generate PDF</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>