<?php
// Redirect to login page
header("Location: login.php");
exit;

// Initialize students array if not exists
if (!isset($_SESSION['students'])) {
    $_SESSION['students'] = array();
}

// Function to calculate grade based on average
function calculateGrade($average)
{
    if ($average >= 90) return 'A';
    elseif ($average >= 80) return 'B';
    elseif ($average >= 70) return 'C';
    elseif ($average >= 60) return 'D';
    else return 'F';
}

// Function to save results to text file
function saveToFile($students)
{
    $file = fopen("student_results.txt", "w");
    fwrite($file, "Student Results Report\n");
    fwrite($file, str_repeat("=", 80) . "\n");
    fwrite($file, sprintf(
        "%-20s %-10s %-8s %-8s %-8s %-8s %-8s %-8s\n",
        "Name",
        "ID",
        "Math",
        "English",
        "Science",
        "Total",
        "Average",
        "Grade"
    ));
    fwrite($file, str_repeat("-", 80) . "\n");

    foreach ($students as $student) {
        fwrite($file, sprintf(
            "%-20s %-10s %-8s %-8s %-8s %-8s %-8.1f %-8s\n",
            $student['name'],
            $student['id'],
            $student['math'],
            $student['english'],
            $student['science'],
            $student['total'],
            $student['average'],
            $student['grade']
        ));
    }
    fclose($file);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $name = trim($_POST['student_name']);
    $id = trim($_POST['student_id']);
    $math = (int)$_POST['math_marks'];
    $english = (int)$_POST['english_marks'];
    $science = (int)$_POST['science_marks'];

    // Validation
    $errors = array();

    if (empty($name)) $errors[] = "Student name is required";
    if (empty($id)) $errors[] = "Student ID is required";
    if ($math < 0 || $math > 100) $errors[] = "Math marks must be between 0-100";
    if ($english < 0 || $english > 100) $errors[] = "English marks must be between 0-100";
    if ($science < 0 || $science > 100) $errors[] = "Science marks must be between 0-100";

    // Check for duplicate ID
    foreach ($_SESSION['students'] as $student) {
        if ($student['id'] == $id) {
            $errors[] = "Student ID already exists";
            break;
        }
    }

    if (empty($errors)) {
        // Calculate total, average, and grade
        $total = $math + $english + $science;
        $average = $total / 3;
        $grade = calculateGrade($average);

        // Add student to session array
        $_SESSION['students'][] = array(
            'name' => $name,
            'id' => $id,
            'math' => $math,
            'english' => $english,
            'science' => $science,
            'total' => $total,
            'average' => $average,
            'grade' => $grade
        );

        // Save to file
        saveToFile($_SESSION['students']);

        $success_message = "Student result added successfully!";
    }
}

// Clear all results
if (isset($_GET['clear'])) {
    $_SESSION['students'] = array();
    if (file_exists("student_results.txt")) {
        unlink("student_results.txt");
    }
    $success_message = "All results cleared!";
}

// Calculate statistics
$total_students = count($_SESSION['students']);
$class_average = 0;
$grade_counts = array('A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0);

if ($total_students > 0) {
    $total_avg = 0;
    foreach ($_SESSION['students'] as $student) {
        $total_avg += $student['average'];
        $grade_counts[$student['grade']]++;
    }
    $class_average = $total_avg / $total_students;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Management System - PHP Only</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .table th {
            background-color: #28a745;
            color: white;
            border: none;
        }

        .grade-A {
            color: #28a745;
            font-weight: bold;
        }

        .grade-B {
            color: #17a2b8;
            font-weight: bold;
        }

        .grade-C {
            color: #ffc107;
            font-weight: bold;
        }

        .grade-D {
            color: #fd7e14;
            font-weight: bold;
        }

        .grade-F {
            color: #dc3545;
            font-weight: bold;
        }

        .stats-card {
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .form-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header text-center">
        <div class="container">
            <h1><i class="fas fa-graduation-cap"></i> Student Result Management System</h1>
            <p class="lead">Welcome to the Student Result Management System</p>
        </div>
    </div>

    <div class="container">
        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Total Students</h6>
                                <h1 class="display-4"><?php echo $total_students; ?></h1>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Class Average</h6>
                                <h1 class="display-4"><?php echo number_format($class_average, 1); ?>%</h1>
                            </div>
                            <i class="fas fa-chart-line fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">A Grades</h6>
                                <h1 class="display-4"><?php echo $grade_counts['A']; ?></h1>
                            </div>
                            <i class="fas fa-award fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Failed (F)</h6>
                                <h1 class="display-4"><?php echo $grade_counts['F']; ?></h1>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Input Form -->
            <div class="col-lg-4 mb-4">
                <div class="form-section">
                    <h4 class="text-success mb-4">
                        <i class="fas fa-user-plus"></i> Add New Student
                    </h4>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="student_name" class="form-label">
                                <i class="fas fa-user"></i> Student Name *
                            </label>
                            <input type="text" class="form-control" id="student_name" name="student_name"
                                value="<?php echo isset($_POST['student_name']) ? htmlspecialchars($_POST['student_name']) : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="student_id" class="form-label">
                                <i class="fas fa-id-card"></i> Student ID *
                            </label>
                            <input type="text" class="form-control" id="student_id" name="student_id"
                                value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>" required>
                        </div>

                        <hr>
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-clipboard-list"></i> Subject Marks (0-100)
                        </h6>

                        <div class="mb-3">
                            <label for="math_marks" class="form-label">Mathematics *</label>
                            <input type="number" class="form-control" id="math_marks" name="math_marks"
                                min="0" max="100" value="<?php echo isset($_POST['math_marks']) ? $_POST['math_marks'] : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="english_marks" class="form-label">English *</label>
                            <input type="number" class="form-control" id="english_marks" name="english_marks"
                                min="0" max="100" value="<?php echo isset($_POST['english_marks']) ? $_POST['english_marks'] : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="science_marks" class="form-label">Science *</label>
                            <input type="number" class="form-control" id="science_marks" name="science_marks"
                                min="0" max="100" value="<?php echo isset($_POST['science_marks']) ? $_POST['science_marks'] : ''; ?>" required>
                        </div>

                        <button type="submit" name="add_student" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-plus"></i> Add Student Result
                        </button>
                    </form>
                </div>

                <!-- Grade Distribution -->
                <?php if ($total_students > 0): ?>
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Grade Distribution</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($grade_counts as $grade => $count): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="grade-<?php echo $grade; ?>">Grade <?php echo $grade; ?>:</span>
                                    <span class="badge bg-light text-dark"><?php echo $count; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Results Display -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-table"></i> Student Results</h5>
                        <div>
                            <?php if ($total_students > 0): ?>
                                <a href="?clear=1" class="btn btn-outline-light btn-sm"
                                    onclick="return confirm('Are you sure you want to clear all results?')">
                                    <i class="fas fa-trash"></i> Clear All
                                </a>
                                <a href="export.php" class="btn btn-light btn-sm ms-1">
                                    <i class="fas fa-download"></i> Export CSV
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($total_students == 0): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No Results Found</h5>
                                <p class="text-muted">Add student results using the form on the left.</p>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Results are automatically saved to both session storage and text file.
                                    </small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th><i class="fas fa-user"></i> Name</th>
                                            <th>Math</th>
                                            <th>English</th>
                                            <th>Science</th>
                                            <th>Total</th>
                                            <th>Average</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['students'] as $index => $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                                <td><strong><?php echo htmlspecialchars($student['name']); ?></strong></td>
                                                
                                                <td><?php echo $student['math']; ?></td>
                                                <td><?php echo $student['english']; ?></td>
                                                <td><?php echo $student['science']; ?></td>
                                                <td><strong><?php echo $student['total']; ?></strong>/300</td>
                                                <td><?php echo number_format($student['average'], 1); ?>%</td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                                            echo $student['grade'] == 'A' ? 'success' : ($student['grade'] == 'B' ? 'info' : ($student['grade'] == 'C' ? 'warning' : ($student['grade'] == 'D' ? 'secondary' : 'danger')));
                                                                            ?> grade-<?php echo $student['grade']; ?>">
                                                        <?php echo $student['grade']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Grading Scale -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-info-circle text-primary"></i> Grading Scale Information
                        </h6>
                        <div class="row text-center">
                            <div class="col">
                                <span class="grade-A">A:</span> 90-100%
                            </div>
                            <div class="col">
                                <span class="grade-B">B:</span> 80-89%
                            </div>
                            <div class="col">
                                <span class="grade-C">C:</span> 70-79%
                            </div>
                            <div class="col">
                                <span class="grade-D">D:</span> 60-69%
                            </div>
                            <div class="col">
                                <span class="grade-F">F:</span> Below 60%
                            </div>
                        </div>
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-save"></i> All data is automatically saved to 'student_results.txt' file.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-5 text-muted bg-white">
        <div class="container">
            <p class="mb-0">
                <i class="fas fa-code"></i> Student Result Management System - Pure PHP Implementation
            </p>
            <small>All right reserved. Copyright &copy; 2023.</small>
        </div>
    </footer>

    <!-- Bootstrap JS (only for dismissible alerts, no custom JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>