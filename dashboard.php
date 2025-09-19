<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user information
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Result Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student Result Management System</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($username); ?> (<?php echo htmlspecialchars($role); ?>)
                <a href="logout.php" class="btn btn-sm">Logout</a>
            </div>
        </div>
        
        <div class="dashboard">
            <div class="sidebar">
                <ul class="menu">
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="manage_students.php">Manage Students</a></li>
                    <li><a href="reports.php">Performance Reports</a></li>
                    <li><a href="export.php">Export Data</a></li>
                    <?php if ($role == 'admin'): ?>
                    <li><a href="manage_users.php">Manage Users</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="main-content">
                <h2>Dashboard</h2>
                
                <?php
                // Get statistics
                $total_students = 0;
                $query = "SELECT COUNT(*) as count FROM students";
                $result = $conn->query($query);
                if ($result && $row = $result->fetch_assoc()) {
                    $total_students = $row['count'];
                }
                
                $grade_stats = array('A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0);
                $query = "SELECT grade, COUNT(*) as count FROM students GROUP BY grade";
                $result = $conn->query($query);
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $grade_stats[$row['grade']] = $row['count'];
                    }
                }
                ?>
                
                <div class="stats-container">
                    <div class="stat-card">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                    
                    <?php foreach ($grade_stats as $grade => $count): ?>
                    <div class="stat-card">
                        <h3><?php echo $count; ?></h3>
                        <p>Grade <?php echo $grade; ?> Students</p>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="grade-distribution">
                    <h3>Grade Distribution</h3>
                    <?php 
                    // Calculate max count for scaling the chart
                    $maxCount = max($grade_stats);
                    ?>
                    
                    <div class="grade-chart">
                        <?php foreach ($grade_stats as $grade => $count): 
                            // Calculate height percentage based on max count
                            $heightPercentage = ($maxCount > 0) ? ($count / $maxCount) * 100 : 0;
                        ?>
                            <div class="grade-column">
                                <div class="grade-column-bar" style="height: <?php echo max(5, $heightPercentage); ?>%">
                                    <span class="grade-column-value"><?php echo $count; ?></span>
                                </div>
                                <div class="grade-column-label"><?php echo $grade; ?></div>
                                <div class="grade-column-count"><?php echo $count; ?> students</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="grade-legend">
                        <div class="grade-legend-item">
                            <div class="grade-legend-color"></div>
                            <span>Student Count</span>
                        </div>
                    </div>
                </div>
                
                <div class="recent-results">
                    <h3>Recent Results</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Math</th>
                                <th>English</th>
                                <th>Science</th>
                                <th>Average</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM students ORDER BY created_at DESC LIMIT 5";
                            $result = $conn->query($query);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . $row['math'] . "</td>";
                                    echo "<td>" . $row['english'] . "</td>";
                                    echo "<td>" . $row['science'] . "</td>";
                                    echo "<td>" . number_format($row['average'], 1) . "</td>";
                                    echo "<td>" . $row['grade'] . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No results found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>