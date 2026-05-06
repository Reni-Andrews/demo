<?php
// --- Database Logic ---
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

$year = $_GET['year'] ?? 1;
$date = $_GET['date'] ?? date('Y-m-d');

// Fetch logic
// FIXED: Changed 's.register_number' to 's.reg_no'
// FIXED: Changed 's.year' to 's.year_of_study'
$stmt = $pdo->prepare("
  SELECT s.reg_no AS register_number, s.name,
         COALESCE(a.status, 'Not Marked') AS status,
         COALESCE(a.reason, '') AS reason
  FROM students s
  LEFT JOIN attendance_logs a ON s.id = a.student_id AND a.log_date = :date
  WHERE s.year_of_study = :year
  ORDER BY s.reg_no
");

// Note: I also updated the JOIN table to 'attendance_logs' and column 'log_date' 
// to match your student_dashboard.php structure. If your table is actually named 'attendance', change it back.

try {
    $stmt->execute(['year' => $year, 'date' => $date]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query Failed: " . $e->getMessage());
}

$dateFormatted = date('l, F j, Y', strtotime($date));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance History | Admin Dashboard</title>
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
          <i class="fas fa-history"></i>
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
        <a href="absentees.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition">
          <i class="fas fa-user-times w-5 text-center"></i> <span>Absentees</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-50 text-indigo-600 font-medium">
          <i class="fas fa-history w-5 text-center"></i> <span>History</span>
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
            <h2 class="text-xl font-bold text-slate-800">Attendance History</h2>
            <p class="text-xs text-slate-500 font-medium">View and Download Logs</p>
          </div>
        </div>
      </div>
    </nav>

    <div class="p-4 md:p-8 max-w-7xl mx-auto space-y-6">

      <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl">
              <i class="fas fa-file-csv"></i>
            </div>
            <div>
              <h3 class="font-bold text-lg">Export Attendance Data</h3>
              <p class="text-emerald-100 text-sm">Download detailed CSV reports for offline use.</p>
            </div>
          </div>
          
          <form method="GET" action="download_csv.php" class="flex flex-wrap items-end gap-3 w-full md:w-auto">
            <div class="w-full sm:w-auto">
              <label class="block text-xs font-medium text-emerald-100 mb-1">Year</label>
              <select name="year" class="w-full bg-white/10 border border-white/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-white/20">
                <option value="1" class="text-slate-800">1st Year</option>
                <option value="2" class="text-slate-800">2nd Year</option>
                <option value="3" class="text-slate-800">3rd Year</option>
              </select>
            </div>
            
            <div class="w-full sm:w-auto">
              <label class="block text-xs font-medium text-emerald-100 mb-1">From</label>
              <input type="date" name="from" required max="<?= date('Y-m-d') ?>" class="w-full bg-white/10 border border-white/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-white/20 text-white placeholder-emerald-200">
            </div>

            <div class="w-full sm:w-auto">
              <label class="block text-xs font-medium text-emerald-100 mb-1">To</label>
              <input type="date" name="to" required max="<?= date('Y-m-d') ?>" class="w-full bg-white/10 border border-white/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-white/20 text-white placeholder-emerald-200">
            </div>

            <button type="submit" class="bg-white text-emerald-600 hover:bg-emerald-50 font-bold py-2 px-4 rounded-lg text-sm shadow-md transition flex items-center gap-2">
              <i class="fas fa-download"></i> Download
            </button>
          </form>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="font-bold text-slate-700 mb-4 border-b border-slate-100 pb-2">View Daily Logs</h3>
        <form method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
          
          <div class="flex-1 w-full">
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Select Year</label>
            <div class="relative">
              <select name="year" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 py-3 px-4 pr-8 rounded-xl leading-tight focus:outline-none focus:bg-white focus:border-indigo-500">
                <option value="1" <?= $year == 1 ? 'selected' : '' ?>>1st Year Students</option>
                <option value="2" <?= $year == 2 ? 'selected' : '' ?>>2nd Year Students</option>
                <option value="3" <?= $year == 3 ? 'selected' : '' ?>>3rd Year Students</option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-700">
                <i class="fas fa-chevron-down text-xs"></i>
              </div>
            </div>
          </div>

          <div class="flex-1 w-full">
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Select Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" max="<?= date('Y-m-d') ?>" 
              class="w-full bg-slate-50 border border-slate-200 text-slate-700 py-3 px-4 rounded-xl leading-tight focus:outline-none focus:bg-white focus:border-indigo-500">
          </div>

          <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl transition shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
            <i class="fas fa-search"></i> Load Data
          </button>
        </form>
      </div>

      <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <div>
             <h4 class="font-bold text-slate-700">Attendance Records</h4>
             <p class="text-xs text-slate-500 mt-1"><?= $dateFormatted ?></p>
          </div>
          <span class="text-xs font-medium px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg">
            Total: <?= count($students) ?> Students
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white text-xs uppercase text-slate-400 font-bold tracking-wider border-b border-slate-100">
                <th class="p-4 w-16 text-center">#</th>
                <th class="p-4">Register No</th>
                <th class="p-4">Name</th>
                <th class="p-4 text-center">Status</th>
                <th class="p-4">Reason</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm">
              <?php if (count($students) > 0): ?>
                <?php foreach ($students as $index => $s): 
                    $isAbsent = ($s['status'] === 'Absent');
                    $isNotMarked = ($s['status'] === 'Not Marked');
                ?>
                  <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-center text-slate-400 font-mono text-xs"><?= $index + 1 ?></td>
                    <td class="p-4 font-mono font-medium text-slate-600"><?= htmlspecialchars($s['register_number']) ?></td>
                    <td class="p-4 font-medium text-slate-700"><?= htmlspecialchars($s['name']) ?></td>
                    <td class="p-4 text-center">
                      <?php if ($isAbsent): ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-600 border border-red-100">
                          <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Absent
                        </span>
                      <?php elseif ($isNotMarked): ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                          <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Not Marked
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-600 border border-green-100">
                          <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Present
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="p-4 text-slate-500 italic">
                      <?= !empty($s['reason']) ? htmlspecialchars($s['reason']) : '<span class="text-slate-300">-</span>' ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="p-8 text-center text-slate-400">
                    <div class="flex flex-col items-center">
                      <i class="fas fa-folder-open text-3xl mb-2 opacity-50"></i>
                      <p>No records found for this date.</p>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
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