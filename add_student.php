<?php
// --- Database Connection ---
$host = 'localhost';
$db = 'attendance_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

$message = "";
$msg_type = "";

// --- Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $reg_no = trim($_POST['reg_no']);
    $year = intval($_POST['year']);
    
    // Default Password
    $password = "1234"; 

    // 1. Check if Register Number already exists
    $check = $pdo->prepare("SELECT id FROM students WHERE reg_no = ?");
    $check->execute([$reg_no]);
    
    if ($check->rowCount() > 0) {
        $message = "Error: Register Number '$reg_no' already exists!";
        $msg_type = "error";
    } else {
        // 2. Insert New Student
        $sql = "INSERT INTO students (name, reg_no, year_of_study, password) VALUES (:name, :reg, :year, :pass)";
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([
                'name' => $name,
                'reg' => $reg_no,
                'year' => $year,
                'pass' => $password
            ]);
            $message = "Student added successfully!";
            $msg_type = "success";
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Student | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
          colors: {
            brand: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' }
          }
        }
      }
    }
  </script>
  <style>
    body { background-color: #f8fafc; background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px; }
    .glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
  </style>
</head>
<body class="text-slate-600 antialiased min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-lg">
    
    <div class="flex items-center justify-between mb-6">
        <a href="students.php" class="flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors bg-white px-4 py-2 rounded-full shadow-sm border border-slate-200">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="glass-effect rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        
        <div class="bg-slate-900 p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-user-plus text-8xl transform translate-x-4 -translate-y-4"></i>
            </div>
            <h2 class="text-2xl font-bold relative z-10">Add New Student</h2>
            <p class="text-slate-400 text-sm mt-1 relative z-10">Create a new student profile manually.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mx-6 mt-6 p-4 rounded-xl flex items-center gap-3 border shadow-sm <?php echo $msg_type == 'success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'; ?>">
                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center <?php echo $msg_type == 'success' ? 'bg-green-100' : 'bg-red-100'; ?>">
                    <i class="fas <?php echo $msg_type == 'success' ? 'fa-check' : 'fa-exclamation'; ?>"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm"><?php echo $msg_type == 'success' ? 'Success' : 'Error'; ?></h3>
                    <p class="text-xs opacity-90"><?php echo $message; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="p-6 space-y-6">
            
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Register Number <span class="text-red-500">*</span></label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-brand-600 transition-colors">
                        <i class="fas fa-id-card"></i>
                    </span>
                    <input type="text" name="reg_no" required placeholder="e.g. BU261701" 
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent focus:bg-white transition-all placeholder-slate-300">
                </div>
                <p class="text-[10px] text-slate-400 mt-1 ml-1">Must be unique (e.g. BU + Year + 17 + Seq)</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Full Name <span class="text-red-500">*</span></label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-brand-600 transition-colors">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="name" required placeholder="e.g. John Doe" 
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent focus:bg-white transition-all placeholder-slate-300">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Year of Study <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    
                    <label class="cursor-pointer relative">
                        <input type="radio" name="year" value="1" required class="peer sr-only">
                        <div class="p-3 text-center rounded-xl border-2 border-slate-100 bg-white text-slate-400 hover:border-brand-200 hover:bg-brand-50 peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 transition-all duration-200">
                            <div class="text-xs uppercase font-bold mb-1">Year</div>
                            <div class="text-2xl font-black">1</div>
                        </div>
                        <div class="absolute top-2 right-2 w-2 h-2 bg-brand-500 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                    </label>

                    <label class="cursor-pointer relative">
                        <input type="radio" name="year" value="2" class="peer sr-only">
                        <div class="p-3 text-center rounded-xl border-2 border-slate-100 bg-white text-slate-400 hover:border-brand-200 hover:bg-brand-50 peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 transition-all duration-200">
                            <div class="text-xs uppercase font-bold mb-1">Year</div>
                            <div class="text-2xl font-black">2</div>
                        </div>
                        <div class="absolute top-2 right-2 w-2 h-2 bg-brand-500 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                    </label>

                    <label class="cursor-pointer relative">
                        <input type="radio" name="year" value="3" class="peer sr-only">
                        <div class="p-3 text-center rounded-xl border-2 border-slate-100 bg-white text-slate-400 hover:border-brand-200 hover:bg-brand-50 peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 transition-all duration-200">
                            <div class="text-xs uppercase font-bold mb-1">Year</div>
                            <div class="text-2xl font-black">3</div>
                        </div>
                        <div class="absolute top-2 right-2 w-2 h-2 bg-brand-500 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                    </label>

                </div>
            </div>

            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex gap-3">
                <i class="fas fa-info-circle text-indigo-500 mt-0.5"></i>
                <div class="text-xs text-indigo-800 leading-relaxed">
                    <span class="font-bold">Note:</span> A default password of <strong>1234</strong> will be assigned. The student can change this upon their first login.
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-slate-900 text-white font-bold rounded-xl shadow-lg hover:bg-slate-800 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Save Student Record
            </button>

        </form>
    </div>
    
    <p class="text-center text-slate-400 text-xs mt-6">&copy; <?php echo date('Y'); ?> Data Science Department</p>

  </div>

</body>
</html>