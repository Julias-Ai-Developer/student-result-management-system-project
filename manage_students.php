<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Function to calculate grade based on average
function calculateGrade($average) {
    if ($average >= 90) return 'A';
    elseif ($average >= 80) return 'B';
    elseif ($average >= 70) return 'C';
    elseif ($average >= 60) return 'D';
    else return 'F';
}

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add or Update Student
    if (isset($_POST['save_student'])) {
        $student_id = trim($_POST['student_id']);
        $name = trim($_POST['name']);
        $math = (int)$_POST['math'];
        $english = (int)$_POST['english'];
        $science = (int)$_POST['science'];
        
        // Validation
        $errors = array();
        if (empty($student_id)) $errors[] = "Student ID is required";
        if (empty($name)) $errors[] = "Name is required";
        if ($math < 0 || $math > 100) $errors[] = "Math marks must be between 0-100";
        if ($english < 0 || $english > 100) $errors[] = "English marks must be between 0-100";
        if ($science < 0 || $science > 100) $errors[] = "Science marks must be between 0-100";
        
        if (empty($errors)) {
            // Calculate total, average, and grade
            $total = $math + $english + $science;
            $average = $total / 3;
            $grade = calculateGrade($average);
            
            // Check if this is an update or new record
            if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
                // Update existing record
                $id = (int)$_POST['edit_id'];
                $stmt = $conn->prepare("UPDATE students SET student_id=?, name=?, math=?, english=?, science=?, total=?, average=?, grade=? WHERE id=?");
                $stmt->bind_param("ssiiiidsi", $student_id, $name, $math, $english, $science, $total, $average, $grade, $id);
                
                if ($stmt->execute()) {
                    $success_message = "Student record updated successfully!";
                } else {
                    $error_message = "Error updating record: " . $conn->error;
                }
                $stmt->close();
            } else {
                // Check for duplicate student ID
                $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
                $stmt->bind_param("s", $student_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error_message = "Student ID already exists!";
                } else {
                    // Insert new record
                    $stmt = $conn->prepare("INSERT INTO students (student_id, name, math, english, science, total, average, grade) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssiiiids", $student_id, $name, $math, $english, $science, $total, $average, $grade);
                    
                    if ($stmt->execute()) {
                        $success_message = "Student record added successfully!";
                    } else {
                        $error_message = "Error adding record: " . $conn->error;
                    }
                }
                $stmt->close();
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    }
    
    // Delete Student
    if (isset($_POST['delete_student'])) {
        $id = (int)$_POST['delete_id'];
        
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $success_message = "Student record deleted successfully!";
        } else {
            $error_message = "Error deleting record: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get student to edit if edit_id is provided in URL
$edit_student = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $edit_student = $result->fetch_assoc();
    }
    $stmt->close();
}

// Get all students for display
$students = array();
$query = "SELECT * FROM students ORDER BY name";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Student Result Management System</title>
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
                    <li><a href="manage_students.php" class="active">Manage Students</a></li>
                    <li><a href="reports.php">Performance Reports</a></li>
                    <li><a href="export.php">Export Data</a></li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li><a href="manage_users.php">Manage Users</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="main-content">
                <h2><?php echo $edit_student ? 'Edit Student' : 'Add New Student'; ?></h2>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="form-container">
                    <form method="POST" action="" class="student-form">
                        <?php if ($edit_student): ?>
                            <input type="hidden" name="edit_id" value="<?php echo $edit_student['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="student_id">Student ID:</label>
                                <input type="text" id="student_id" name="student_id" class="form-control" value="<?php echo $edit_student ? htmlspecialchars($edit_student['student_id']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="name">Name:</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo $edit_student ? htmlspecialchars($edit_student['name']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="math">Math:</label>
                                <input type="number" id="math" name="math" class="form-control" min="0" max="100" value="<?php echo $edit_student ? $edit_student['math'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="english">English:</label>
                                <input type="number" id="english" name="english" class="form-control" min="0" max="100" value="<?php echo $edit_student ? $edit_student['english'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="science">Science:</label>
                                <input type="number" id="science" name="science" class="form-control" min="0" max="100" value="<?php echo $edit_student ? $edit_student['science'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="save_student" class="btn btn-success"><?php echo $edit_student ? 'Update Student' : 'Add Student'; ?></button>
                            <?php if ($edit_student): ?>
                                <a href="manage_students.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <h2>Student Records</h2>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Math</th>
                                <th>English</th>
                                <th>Science</th>
                                <th>Total</th>
                                <th>Average</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($students) > 0): ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo $student['math']; ?></td>
                                        <td><?php echo $student['english']; ?></td>
                                        <td><?php echo $student['science']; ?></td>
                                        <td><?php echo $student['total']; ?></td>
                                        <td><?php echo number_format($student['average'], 2); ?></td>
                                        <td><?php echo $student['grade']; ?></td>
                                        <td>
                                            <a href="manage_students.php?edit=<?php echo $student['id']; ?>" class="btn btn-sm">Edit</a>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="delete_id" value="<?php echo $student['id']; ?>">
                                                <button type="submit" name="delete_student" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 20px;">No records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>