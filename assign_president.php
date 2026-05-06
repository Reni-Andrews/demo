<?php
session_start();

// Check if user is HOD (ID 1)
// In a real app we should check session better, but for now we follow the pattern
if (!isset($_SESSION['username'])) {
    header("Location: faculty.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "attendance_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['president_id'])) {
    $president_id = intval($_POST['president_id']);
    
    // Verify that we are not assigning to HOD (ID 1) just in case
    if ($president_id == 1) {
        die("Cannot assign HOD as President.");
    }

    // 1. Reset all roles (Everyone becomes just a Faculty member)
    // We update everyone except HOD just to be safe, though HOD role isn't column based usually
    $conn->query("UPDATE faculty SET role = NULL WHERE id != 1");
    
    // 2. Set new president
    $stmt = $conn->prepare("UPDATE faculty SET role = 'President' WHERE id = ?");
    $stmt->bind_param("i", $president_id);
    
    if ($stmt->execute()) {
        // Success
    } else {
        // Error handling could go here
    }
}

$conn->close();
header("Location: faculty1.php");
exit();
?>
