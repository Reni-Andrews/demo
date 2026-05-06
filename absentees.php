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

// --- FIX 1: Set Timezone to India ---
date_default_timezone_set('Asia/Kolkata'); 

$today = date('Y-m-d');
$displayDate = date('l, F j, Y'); 

// --- FIX 2: Correct Table Name (attendance_logs) and Column (log_date) ---
$stmt = $pdo->prepare("
  SELECT 
    s.reg_no AS register_number, 
    s.name, 
    s.year_of_study AS year, 
    a.reason
  FROM attendance_logs a  /* CHANGED FROM 'attendance' TO 'attendance_logs' */
  JOIN students s ON a.student_id = s.id
  WHERE a.log_date = :today /* CHANGED FROM 'date' TO 'log_date' */
  AND TRIM(LOWER(a.status)) = 'absent'
  ORDER BY s.year_of_study ASC, s.reg_no ASC
");
$stmt->execute(['today' => $today]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by year
$groupedAbsentees = [];
foreach ($rows as $row) {
    $groupedAbsentees[$row['year']][] = $row;
}

// Calculate total absentees for the badge
$totalAbsentees = count($rows);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Absentees List | Admin Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          colors: {
            slate: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 800: '#1e293b', 900: '#0f172a' },
            primary: '#4f46e5',
          }
        }
      }
    }
  </script>

  <style>
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    .sidebar-transition { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(8px); }
  </style>
</head>
<body class="text-slate-600 antialiased">

  <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-40 hidden backdrop-blur-sm transition-opacity"></div>

  <aside id="sidebar" class="fixed top-0 left-0 z-50 w-72 h-screen bg-white border-r border-slate-200 sidebar-transition -translate-x-full md:translate-x-0">
    <div class="flex flex-col h-full">
      <div class="h-20 flex items-center px-8 border-b border-slate-100">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200 mr-3">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <div>
          <h1 class="text-lg font-bold text-slate-800">Data Science</h1>
          <p class="text-xs text-slate-400 font-medium">Admin Portal</p>
        </div>
      </div>

      <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
        <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Menu</p>
        
        <a href="attendance.html" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition">
          <i class="fas fa-clipboard-check w-5 text-center"></i> <span>Attendance</span>
        </a>
        
        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-red-50 text-red-600 font-medium">
          <i class="fas fa-user-times w-5 text-center"></i> <span>Absentees</span>
          <span class="ml-auto bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo $totalAbsentees; ?></span>
        </a>

        <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition">
          <i class="fas fa-chart-pie w-5 text-center"></i> <span>Analytics</span>
        </a>
        
        <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Database</p>
        <a href="students.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition">
          <i class="fas fa-users w-5 text-center"></i> <span>Students</span>
        </a>
      </div>

      <div class="p-4 border-t border-slate-100">
        <button onclick="window.location.href='index.html'" class="w-full py-2.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-100 transition flex items-center justify-center gap-2">
          <i class="fas fa-sign-out-alt"></i> Logout
        </button>
      </div>
    </div>
  </aside>

  <main class="md:ml-72 min-h-screen transition-all duration-300">
    
    <nav class="sticky top-0 z-30 glass-nav border-b border-slate-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <button onclick="toggleSidebar()" class="md:hidden p-2 text-slate-500 hover:bg-slate-100 rounded-lg">
            <i class="fas fa-bars text-xl"></i>
          </button>
          <div>
            <h2 class="text-xl font-bold text-slate-800">Daily Absentees Report</h2>
            <p class="text-xs text-slate-500 font-medium"><?php echo $displayDate; ?></p>
          </div>
        </div>
        
        <a href="attendance.html" class="hidden sm:flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600 transition">
          <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
      </div>
    </nav>

    <div class="p-4 md:p-8 max-w-5xl mx-auto">
      
      <div class="bg-gradient-to-r from-red-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg mb-8 flex items-center justify-between">
        <div>
          <p class="text-red-100 text-sm font-medium mb-1">Total Absent Today</p>
          <h1 class="text-4xl font-bold"><?php echo $totalAbsentees; ?></h1>
        </div>
        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-2xl">
          <i class="fas fa-user-slash"></i>
        </div>
      </div>

      <div class="space-y-8">
        <?php 
          $yearLabels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year'];
          foreach ($yearLabels as $year => $label): 
            $students = isset($groupedAbsentees[$year]) ? $groupedAbsentees[$year] : [];
            $count = count($students);
        ?>
        
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-bold text-slate-700 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
              <?php echo $label; ?>
            </h3>
            <span class="text-xs font-semibold px-2 py-1 rounded bg-slate-200 text-slate-600">
              <?php echo $count; ?> Absent
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-white text-xs uppercase text-slate-400 font-bold tracking-wider border-b border-slate-100">
                  <th class="p-4 w-16 text-center">#</th>
                  <th class="p-4">Reg No</th>
                  <th class="p-4">Student Name</th>
                  <th class="p-4 w-1/3">Reason</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50 text-sm">
                <?php if ($count > 0): ?>
                  <?php foreach ($students as $index => $row): ?>
                  <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-center text-slate-400 font-mono text-xs"><?php echo $index + 1; ?></td>
                    <td class="p-4 font-mono font-medium text-slate-600"><?php echo htmlspecialchars($row['register_number']); ?></td>
                    <td class="p-4">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center font-bold text-xs">
                          <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                        </div>
                        <span class="font-medium text-slate-800"><?php echo htmlspecialchars($row['name']); ?></span>
                      </div>
                    </td>
                    <td class="p-4 text-slate-500 italic">
                      <?php echo !empty($row['reason']) ? htmlspecialchars($row['reason']) : '<span class="text-slate-300">No reason provided</span>'; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4" class="p-8 text-center">
                      <div class="flex flex-col items-center justify-center text-slate-400">
                        <i class="fas fa-check-circle text-4xl text-green-400 mb-2"></i>
                        <p class="font-medium text-slate-600">Full Attendance</p>
                        <p class="text-xs">No students are absent from <?php echo $label; ?> today.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <?php endforeach; ?>
      </div>

    </div>
  </main>

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebar-overlay');
      
      if (sidebar.classList.contains('-translate-x-full')) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
      } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
      }
    }
  </script>
</body>
</html>