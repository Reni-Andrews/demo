<?php
session_start();
include 'db.php';

// Auth Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: student_login.html");
    exit();
}

$msg = "";
$msg_type = "";

// --- ACTIONS ---

// 1. Add Batch
if (isset($_POST['add_batch'])) {
    $start_year = intval($_POST['start_year']);
    $end_year = intval($_POST['end_year']);
    $label = "Class of " . $end_year;
    $batch_year = "$start_year-$end_year";
    
    // Check if exists
    $check = $conn->query("SELECT id FROM batches WHERE batch_year = '$batch_year'");
    if ($check->num_rows > 0) {
        $msg = "Batch $batch_year already exists!"; $msg_type = "error";
    } else {
        // Simple default image logic or random from a set
        $default_imgs = [
            'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1523580494863-6f3031224c94?q=80&w=1000&auto=format&fit=crop'
        ];
        $img = $default_imgs[rand(0, 2)];
        
        $stmt = $conn->prepare("INSERT INTO batches (batch_year, label, image_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $batch_year, $label, $img);
        
        if ($stmt->execute()) {
            $msg = "Batch $batch_year added successfully!"; $msg_type = "success";
        } else {
            $msg = "Error adding batch: " . $conn->error; $msg_type = "error";
        }
    }
}

// 2. Delete Memory
if (isset($_GET['delete_memory'])) {
    $mem_id = intval($_GET['delete_memory']);
    $conn->query("DELETE FROM memories WHERE id=$mem_id");
    $msg = "Memory deleted."; $msg_type = "success";
}

// 3. Download Reports
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    if ($type == 'memories') {
        fputcsv($output, ['ID', 'Student ID', 'Uploader Name', 'Batch', 'Formatted Date', 'Caption', 'Media Type', 'Media Path']);
        $res = $conn->query("SELECT m.*, s.name FROM memories m JOIN students s ON m.student_id = s.id ORDER BY m.event_date DESC");
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, [$row['id'], $row['student_id'], $row['name'], $row['batch_year'], $row['event_date'], $row['caption'], $row['media_type'], $row['media_path']]);
        }
    } elseif ($type == 'chats') {
        fputcsv($output, ['ID', 'Squad', 'Sender ID', 'Sender Name', 'Message', 'Sent At']);
        $res = $conn->query("SELECT c.*, s.name FROM squad_chats c JOIN students s ON c.sender_id = s.id ORDER BY c.sent_at DESC");
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, [$row['id'], $row['squad_number'], $row['sender_id'], $row['name'], $row['message'], $row['sent_at']]);
        }
    }
    
    fclose($output);
    exit();
}

// --- DATA FETCHING ---
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_year DESC");
$recent_memories = $conn->query("SELECT m.*, s.name FROM memories m JOIN students s ON m.student_id = s.id ORDER BY m.created_at DESC LIMIT 20");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Portal - Alumni</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    
    <nav class="bg-slate-900 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fas fa-user-shield text-indigo-400"></i> Admin Portal
            </h1>
            <div class="flex items-center gap-4">
                <span class="text-xs bg-indigo-600 px-2 py-1 rounded">Logged in as Admin</span>
                <a href="alumni.php" class="text-sm hover:text-indigo-300">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="max-w-7xl mx-auto p-6">
        
        <?php if($msg): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- LEFT: Batch Management -->
            <div class="space-y-8">
                
                <!-- Add Batch Card -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-green-500"></i> Add New Batch
                    </h2>
                    <form method="post" class="flex gap-4 items-end">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Start Year</label>
                            <input type="number" name="start_year" placeholder="e.g. 2025" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">End Year</label>
                            <input type="number" name="end_year" placeholder="e.g. 2028" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="submit" name="add_batch" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-indigo-700 transition">
                            Add
                        </button>
                    </form>
                    <p class="text-xs text-gray-400 mt-2">Automatically sets "Class of [End Year]" label.</p>
                </div>
                
                <!-- Active Batches -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                     <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-layer-group text-blue-500"></i> Active Batches
                    </h2>
                    <div class="space-y-3">
                        <?php while($b = $batches->fetch_assoc()): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                <span class="font-bold text-gray-700"><?php echo $b['batch_year']; ?></span>
                                <span class="text-sm text-gray-500"><?php echo $b['label']; ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <!-- Reports -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                     <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-file-export text-orange-500"></i> Administration Reports
                    </h2>
                    
                    <div class="space-y-6">
                        <!-- Memories Report -->
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase mb-2">Memories Report</p>
                            <div class="flex gap-2">
                                <a href="export_report.php?type=memories&format=doc" class="flex-1 text-center bg-blue-50 text-blue-700 font-bold py-2 rounded-lg text-xs hover:bg-blue-100 transition">
                                    <i class="fas fa-file-word mr-1"></i> DOC
                                </a>
                                <a href="export_report.php?type=memories&format=print" target="_blank" class="flex-1 text-center bg-indigo-50 text-indigo-700 font-bold py-2 rounded-lg text-xs hover:bg-indigo-100 transition">
                                    <i class="fas fa-file-pdf mr-1"></i> Print / PDF
                                </a>
                                <a href="?export=memories" class="text-center bg-gray-50 text-gray-600 font-bold py-2 px-3 rounded-lg text-xs hover:bg-gray-100 transition">
                                    <i class="fas fa-file-csv"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Chat Logs -->
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase mb-2">Squad Chat Logs</p>
                            <div class="flex gap-2">
                                <a href="export_report.php?type=chats&format=doc" class="flex-1 text-center bg-blue-50 text-blue-700 font-bold py-2 rounded-lg text-xs hover:bg-blue-100 transition">
                                    <i class="fas fa-file-word mr-1"></i> DOC
                                </a>
                                <a href="export_report.php?type=chats&format=print" target="_blank" class="flex-1 text-center bg-indigo-50 text-indigo-700 font-bold py-2 rounded-lg text-xs hover:bg-indigo-100 transition">
                                    <i class="fas fa-file-pdf mr-1"></i> Print / PDF
                                </a>
                                <a href="?export=chats" class="text-center bg-gray-50 text-gray-600 font-bold py-2 px-3 rounded-lg text-xs hover:bg-gray-100 transition">
                                    <i class="fas fa-file-csv"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>
            
            <!-- RIGHT: Moderation Feed -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 h-fit">
                <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="fas fa-gavel text-red-500"></i> Recent Uploads
                </h2>
                
                <div class="space-y-6 max-h-[800px] overflow-y-auto pr-2">
                    <?php if($recent_memories->num_rows > 0): ?>
                        <?php while($mem = $recent_memories->fetch_assoc()): ?>
                            <div class="relative group border border-gray-100 rounded-xl overflow-hidden hover:shadow-md transition">
                                <div class="h-40 bg-gray-100 relative">
                                    <?php 
                                        $ext = pathinfo($mem['media_path'], PATHINFO_EXTENSION);
                                        if(in_array(strtolower($ext), ['mp4','webm'])): 
                                    ?>
                                        <video src="<?php echo $mem['media_path']; ?>" class="w-full h-full object-cover" muted></video>
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/20 text-white"><i class="fas fa-play"></i></div>
                                    <?php else: ?>
                                        <img src="<?php echo $mem['media_path']; ?>" class="w-full h-full object-cover">
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold text-indigo-600"><?php echo $mem['batch_year']; ?></span>
                                        <span class="text-xs text-gray-400"><?php echo date('M d, Y', strtotime($mem['event_date'])); ?></span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-800 line-clamp-2 mb-2"><?php echo htmlspecialchars($mem['caption']); ?></p>
                                    <p class="text-xs text-gray-400">By: <?php echo htmlspecialchars($mem['name']); ?></p>
                                    
                                    <a href="?delete_memory=<?php echo $mem['id']; ?>" onclick="return confirm('Are you sure you want to delete this memory? This cannot be undone.')" class="mt-4 block w-full text-center bg-red-50 text-red-600 hover:bg-red-100 py-2 rounded-lg text-xs font-bold transition">
                                        Delete Post
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-center text-gray-400 py-8">No recent memories found.</p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
    </div>

</body>
</html>
