<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

// 1. Check Auth
if (!isset($_SESSION['username'])) {
    die("Unauthorized Access");
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id, role FROM faculty WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user || $user['role'] !== 'President') {
    die("❌ Permission Denied: Only the Association President can manage events.");
}

$faculty_id = $user['id'];

// 2. Handle Upload
if (isset($_POST['upload_event'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $date = $_POST['event_date'];
    
    // File Upload
    $target_dir = "uploads/events/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_path = NULL;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $ext = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
        $filename = "EVENT_" . time() . "." . $ext;
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES['event_image']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO association_events (title, description, event_date, image_path, uploaded_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $title, $desc, $date, $file_path, $faculty_id);
    $stmt->execute();
    
    header("Location: Association_events.php");
    exit();
}

// 3. Handle Delete
if (isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];
    
    // Get file path to unlink
    // Delete from events table
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    
    header("Location: Association_events.php");
    header("Location: Association_events.php");
    exit();
}
?>
