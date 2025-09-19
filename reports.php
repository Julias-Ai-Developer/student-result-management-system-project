<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get performance statistics
$stats = array();

// Total students
$query = "SELECT COUNT(*) as total FROM students";
$result = $conn->query($query);
$stats['total_students'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

// Average marks by subject
$query = "SELECT 
    AVG(math) as avg_math, 
    AVG(english) as avg_english, 
    AVG(science) as avg_science,
    AVG(average) as overall_avg
    FROM students";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['avg_math'] = round($row['avg_math'], 1);
    $stats['avg_english'] = round($row['avg_english'], 1);
    $stats['avg_science'] = round($row['avg_science'], 1);
    $stats['overall_avg'] = round($row['overall_avg'], 1);
}

// Highest and lowest scores
$query = "SELECT 
    MAX(math) as max_math, MIN(math) as min_math,
    MAX(english) as max_english, MIN(english) as min_english,
    MAX(science) as max_science, MIN(science) as min_science,
    MAX(average) as max_avg, MIN(average) as min_avg
    FROM students";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['max_math'] = $row['max_math'];
    $stats['min_math'] = $row['min_math'];
    $stats['max_english'] = $row['max_english'];
    $stats['min_english'] = $row['min_english'];
    $stats['max_science'] = $row['max_science'];
    $stats['min_science'] = $row['min_science'];
    $stats['max_avg'] = round($row['max_avg'], 1);
    $stats['min_avg'] = round($row['min_avg'], 1);
}

// Grade distribution
$query = "SELECT grade, COUNT(*) as count FROM students GROUP BY grade ORDER BY grade";
$result = $conn->query($query);
$grade_distribution = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $grade_distribution[$row['grade']] = $row['count'];
    }
}

// Top performers (top 5)
$query = "SELECT * FROM students ORDER BY average DESC LIMIT 5";
$result = $conn->query($query);
$top_performers = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $top_performers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Reports - Student Result Management System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* --- Grade Distribution Circular Chart --- */
        .grade-distribution {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .grade-circle-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100px;
        }

        .circle-chart {
            width: 80px;
            height: 80px;
            transform: rotate(-90deg);
        }

        .circle-bg {
            fill: none;
            stroke: #eee;
            stroke-width: 3.8;
        }

        .circle {
            fill: none;
            stroke-width: 3.8;
            stroke-linecap: round;
            transition: stroke-dasharray 1s ease-out;
        }

        .percentage {
            font-size: 0.5em;
            text-anchor: middle;
            fill: #333;
            transform: rotate(90deg);
        }

        .grade-label {
            margin-top: 5px;
            font-weight: bold;
            text-align: center;
        }
    </style>
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
                    <li><a href="reports.php" class="active">Performance Reports</a></li>
                    <li><a href="export.php">Export Data</a></li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li><a href="manage_users.php">Manage Users</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="main-content">
                <div class="report-header">
                    <h2>Class Performance Report</h2>
                    <div class="report-actions">
                        <button class="btn" onclick="window.print()">Print Report</button>
                    </div>
                </div>

                <div class="report-section overview-section">
                    <h3>Performance Overview</h3>
                    <div class="stats-grid">
                        <div class="stat-box">
                            <h4>Total Students</h4>
                            <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                        </div>
                        <div class="stat-box">
                            <h4>Overall Average</h4>
                            <div class="stat-value"><?php echo $stats['overall_avg']; ?></div>
                        </div>
                        <div class="stat-box">
                            <h4>Highest Average</h4>
                            <div class="stat-value"><?php echo $stats['max_avg']; ?></div>
                        </div>
                        <div class="stat-box">
                            <h4>Lowest Average</h4>
                            <div class="stat-value"><?php echo $stats['min_avg']; ?></div>
                        </div>
                    </div>
                </div>

                <div class="report-grid">
                    <div class="report-section subject-performance">
                        <h3>Subject Performance</h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Average</th>
                                    <th>Highest</th>
                                    <th>Lowest</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Math</strong></td>
                                    <td><?php echo $stats['avg_math']; ?></td>
                                    <td><?php echo $stats['max_math']; ?></td>
                                    <td><?php echo $stats['min_math']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>English</strong></td>
                                    <td><?php echo $stats['avg_english']; ?></td>
                                    <td><?php echo $stats['max_english']; ?></td>
                                    <td><?php echo $stats['min_english']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Science</strong></td>
                                    <td><?php echo $stats['avg_science']; ?></td>
                                    <td><?php echo $stats['max_science']; ?></td>
                                    <td><?php echo $stats['min_science']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                  <div class="report-section grade-chart">
    <h3>Grade Distribution</h3>
    <div class="grade-distribution-icons" style="display:flex; gap:20px; flex-wrap:wrap;">
        <?php 
        $gradeIcons = [
            'A' => '⭐',   // Star for A
            'B' => '🌟',   // Bright star for B
            'C' => '✅',   // Checkmark for C
            'D' => '⚠️',   // Warning for D
            'F' => '❌'    // Cross for F
        ];
        foreach (['A','B','C','D','F'] as $grade):
            $count = isset($grade_distribution[$grade]) ? $grade_distribution[$grade] : 0;
        ?>
            <div class="grade-icon-container" style="text-align:center;">
                <div class="grade-icon" style="font-size:32px;"><?php echo $gradeIcons[$grade]; ?></div>
                <div class="grade-label" style="margin-top:5px; font-weight:bold;">
                    <?php echo $grade; ?> (<?php echo $count; ?>)
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

                </div>

                <div class="report-section top-performers">
                    <h3>Top Performers</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
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
                            <?php if (count($top_performers) > 0): ?>
                                <?php foreach ($top_performers as $index => $student): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo $student['math']; ?></td>
                                        <td><?php echo $student['english']; ?></td>
                                        <td><?php echo $student['science']; ?></td>
                                        <td><?php echo number_format($student['average'], 1); ?></td>
                                        <td><?php echo $student['grade']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Animate circular grade charts
        document.querySelectorAll('.circle').forEach(circle => {
            const dash = circle.getAttribute('stroke-dasharray');
            circle.setAttribute('stroke-dasharray', '0,100');
            setTimeout(() => {
                circle.setAttribute('stroke-dasharray', dash);
            }, 200);
        });
    </script>
</body>

</html>