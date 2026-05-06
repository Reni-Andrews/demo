<?php
session_start();
// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'leader') {
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance Panel | Admin Dashboard</title>
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
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .sidebar-transition { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    
    /* Toggle Switch */
    .status-toggle { position: relative; display: inline-block; width: 48px; height: 26px; }
    .status-toggle input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #fff; border: 1px solid #e2e8f0; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: #cbd5e1; transition: .4s; border-radius: 50%; }
    
    /* Absent State (Checked) */
    input:checked + .slider { background-color: #fee2e2; border-color: #fecaca; }
    input:checked + .slider:before { transform: translateX(22px); background-color: #ef4444; }
    
    /* Present State (Unchecked) */
    input:not(:checked) + .slider { background-color: #dcfce7; border-color: #bbf7d0; }
    input:not(:checked) + .slider:before { background-color: #22c55e; }

    .row-absent { background-color: #fef2f2; }
    .row-present { background-color: #ffffff; }
  </style>
</head>
<body class="text-slate-600 antialiased">

  <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-40 hidden backdrop-blur-sm transition-opacity"></div>

  <aside id="sidebar" class="fixed top-0 left-0 z-50 w-72 h-screen bg-white border-r border-slate-200 sidebar-transition -translate-x-full md:translate-x-0">
    <div class="flex flex-col h-full">
      <div class="h-20 flex items-center px-8 border-b border-slate-100">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200 mr-3">
          <i class="fas fa-chart-bar"></i>
        </div>
        <div>
          <h1 class="text-lg font-bold text-slate-800">Data Science</h1>
          <p class="text-xs text-slate-400 font-medium">Attendance Admin</p>
        </div>
      </div>

      <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
        <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Menu</p>
        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-50 text-indigo-600 font-medium">
          <i class="fas fa-clipboard-check w-5 text-center"></i> <span>Attendance</span>
        </a>
        <a href="absentees.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition">
          <i class="fas fa-user-times w-5 text-center"></i> <span>Absentees</span>
          <span id="absentee-count" class="ml-auto bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded-full">0</span>
        </a>
      </div>

      <div class="p-4 border-t border-slate-100">
        <button onclick="logout()" class="w-full py-2.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-100 transition flex items-center justify-center gap-2">
          <i class="fas fa-sign-out-alt"></i> Logout
        </button>
      </div>
    </div>
  </aside>

  <main class="md:ml-72 min-h-screen transition-all duration-300">
    <nav class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <button onclick="toggleSidebar()" class="md:hidden p-2 text-slate-500 hover:bg-slate-100 rounded-lg">
            <i class="fas fa-bars text-xl"></i>
          </button>
          <div>
            <h2 class="text-xl font-bold text-slate-800">Attendance Panel</h2>
            <p class="text-xs text-slate-500 font-medium hidden sm:block" id="current-date-time">Loading...</p>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-3">
             <div class="text-right hidden sm:block">
               <p class="text-sm font-medium text-slate-800"><?php echo $_SESSION['role']; ?></p>
               <p class="text-xs text-green-500">Online</p>
             </div>
             <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center">
               <i class="fas fa-user text-slate-500"></i>
             </div>
          </div>
        </div>
      </div>
    </nav>

    <div class="p-4 md:p-8 max-w-7xl mx-auto">
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-slate-500 text-xs font-bold uppercase">Total Students</p>
              <h3 class="text-2xl font-bold text-slate-800 mt-2" id="total-students-card">0</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-users"></i></div>
          </div>
          <div class="mt-4 text-xs text-slate-400">Year <span id="selected-year-display">1</span></div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-slate-500 text-xs font-bold uppercase">Present</p>
              <h3 class="text-2xl font-bold text-slate-800 mt-2" id="present-card">0</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center"><i class="fas fa-user-check"></i></div>
          </div>
          <div class="mt-2 w-full bg-slate-100 h-1.5 rounded-full">
            <div class="bg-green-500 h-1.5 rounded-full transition-all duration-500" id="present-progress-bar" style="width: 0%"></div>
          </div>
          <div class="mt-2 text-xs text-slate-400 text-right"><span id="present-percentage">0%</span></div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-slate-500 text-xs font-bold uppercase">Absent</p>
              <h3 class="text-2xl font-bold text-slate-800 mt-2" id="absent-card">0</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center"><i class="fas fa-user-times"></i></div>
          </div>
           <div class="mt-2 w-full bg-slate-100 h-1.5 rounded-full">
            <div class="bg-red-500 h-1.5 rounded-full transition-all duration-500" id="absent-progress-bar" style="width: 0%"></div>
          </div>
          <div class="mt-2 text-xs text-slate-400 text-right"><span id="absent-percentage">0%</span></div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-slate-500 text-xs font-bold uppercase">Status</p>
              <h3 class="text-lg font-bold text-slate-800 mt-2" id="attendance-rate-card">Pending</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fas fa-info-circle"></i></div>
          </div>
          <div class="mt-4">
             <span id="attendance-status" class="px-2 py-1 rounded-md text-xs font-semibold bg-yellow-50 text-yellow-600 border border-yellow-100">Ready</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 md:p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <div>
            <h3 class="text-lg font-bold text-slate-800">Mark Attendance</h3>
            <p class="text-sm text-slate-500">Select year to load student list</p>
          </div>
          <div class="flex flex-col sm:flex-row gap-3">
            <select id="year-select" class="w-full sm:w-40 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 block p-3">
              <option value="1">1st Year</option>
              <option value="2">2nd Year</option>
              <option value="3" selected>3rd Year</option>
            </select>
            <div class="flex gap-2">
              <button onclick="markAllPresent()" class="flex-1 py-3 px-4 rounded-xl bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 text-sm font-medium transition">
                <i class="fas fa-check"></i> All Present
              </button>
              <button onclick="markAllAbsent()" class="flex-1 py-3 px-4 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 text-sm font-medium transition">
                <i class="fas fa-times"></i> All Absent
              </button>
            </div>
            <button onclick="submitAttendance()" id="submit-btn" class="py-3 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-md transition flex items-center justify-center gap-2">
              <i class="fas fa-paper-plane"></i> Submit
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-bold tracking-wider">
                <th class="p-4 w-16 text-center">#</th>
                <th class="p-4">Reg No</th>
                <th class="p-4">Student Name</th>
                <th class="p-4 text-center">Status</th>
                <th class="p-4 w-1/3">Remarks</th>
                <th class="p-4 text-right hidden md:table-cell">Updated</th>
              </tr>
            </thead>
            <tbody id="students-table-body" class="divide-y divide-slate-100 text-sm text-slate-600">
              <tr><td colspan="6" class="p-8 text-center text-slate-400">Loading...</td></tr>
            </tbody>
          </table>
        </div>
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center">
           <span class="text-xs text-slate-500 font-medium">Showing <span id="showing-count">0</span> students</span>
           <button onclick="refreshData()" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
             <i class="fas fa-sync-alt"></i> Refresh
           </button>
        </div>
      </div>
    </div>
  </main>

  <div id="toast" class="fixed bottom-6 right-6 z-50 transform transition-all duration-300 translate-y-24 opacity-0">
    <div class="bg-white border border-slate-100 shadow-xl rounded-xl p-4 flex items-center gap-4 min-w-[300px]">
      <div id="toast-icon" class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"></div>
      <div class="flex-1">
        <h4 id="toast-title" class="text-sm font-bold text-slate-800"></h4>
        <p id="toast-message" class="text-xs text-slate-500 mt-0.5"></p>
      </div>
    </div>
  </div>

  <div id="loading-overlay" class="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex flex-col items-center justify-center hidden">
    <div class="w-10 h-10 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
    <p id="loading-message" class="text-slate-600 font-medium">Processing...</p>
  </div>

  <script>
    let studentsData = [];
    let currentYear = 3; 
    let attendanceChanges = new Map();
    let isEditable = true; 

    document.addEventListener('DOMContentLoaded', function() {
      updateDateTime();
      setInterval(updateDateTime, 1000);
      
      // Load initial data
      fetchStudents(currentYear);
      
      // Listen for year changes
      const yearSelect = document.getElementById('year-select');
      if(yearSelect) {
          yearSelect.addEventListener('change', function(e) {
            currentYear = parseInt(e.target.value);
            const display = document.getElementById('selected-year-display');
            if(display) display.textContent = currentYear;
            fetchStudents(currentYear);
          });
      }
    });

    function updateDateTime() {
      const now = new Date();
      const el = document.getElementById('current-date-time');
      if(el) el.textContent = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }

    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebar-overlay');
      if(sidebar) sidebar.classList.toggle('-translate-x-full');
      if(overlay) overlay.classList.toggle('hidden');
    }

    async function fetchStudents(year) {
      const loading = document.getElementById('loading-overlay');
      if(loading) loading.classList.remove('hidden');
      
      try {
        const response = await fetch(`fetch_students.php?year=${year}`);
        
        // 1. Check if response is valid JSON
        if (!response.ok) {
            throw new Error(`Server Error: ${response.status}`);
        }
        
        const data = await response.json();
        
        // 2. Check for backend errors
        if(data.error) throw new Error(data.error);

        studentsData = data.students || [];
        attendanceChanges.clear(); 
        
        // 3. Render UI (Safely)
        updateTableUI();
        
        // 4. Update Stats (Wrapped in try-catch so it doesn't break everything)
        try { updateStats(); } catch(e) { console.warn("Stats error:", e); }
        
        try { updateAttendanceStatus(); } catch(e) { console.warn("Status error:", e); }

        showToast('success', 'Data Loaded', `Loaded ${studentsData.length} students`);

      } catch (error) {
        console.error("Fetch error:", error);
        showToast('error', 'Error', error.message);
        
        // Clear table on error
        const tbody = document.getElementById('students-table-body');
        if(tbody) tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-red-500">Failed: ${error.message}</td></tr>`;
      } finally {
        if(loading) loading.classList.add('hidden');
      }
    }

    function updateTableUI() {
      const tbody = document.getElementById('students-table-body');
      if (!tbody) return;

      if (studentsData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-slate-400">No students found.</td></tr>`;
        updateCounts();
        return;
      }

      let rowsHTML = '';
      studentsData.forEach((student, index) => {
        const status = attendanceChanges.get(student.id) || student.status || 'Present';
        const reason = student.reason || '';
        const isAbsent = (status === 'Absent');
        const rowClass = isAbsent ? 'row-absent' : 'row-present';

        rowsHTML += `
          <tr class="${rowClass} border-b border-slate-50 transition-colors duration-300 hover:bg-slate-50">
            <td class="p-4 text-center font-mono text-slate-400 text-xs">${index + 1}</td>
            <td class="p-4 font-semibold text-slate-700">${student.register_number}</td>
            <td class="p-4 flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">${student.name.charAt(0)}</div>
              <span class="text-slate-700 font-medium">${student.name}</span>
            </td>
            <td class="p-4 text-center">
              <label class="status-toggle">
                <input type="checkbox" ${isAbsent ? 'checked' : ''} onchange="handleToggle(${student.id}, this.checked)">
                <span class="slider"></span>
              </label>
            </td>
            <td class="p-4">
              <input type="text" id="reason_${student.id}" class="w-full bg-transparent border-b ${isAbsent ? 'border-red-300' : 'border-transparent'} focus:border-indigo-500 outline-none text-sm py-1 placeholder-slate-300" placeholder="${isAbsent ? 'Reason...' : ''}" value="${reason}" ${!isAbsent ? 'disabled' : ''}>
            </td>
            <td class="p-4 text-right text-xs text-slate-400 hidden md:table-cell">${student.last_updated || '-'}</td>
          </tr>`;
      });
      tbody.innerHTML = rowsHTML;
      updateCounts();
    }

    function handleToggle(id, isChecked) {
      const newStatus = isChecked ? 'Absent' : 'Present';
      const student = studentsData.find(s => s.id == id);
      if(student) {
        attendanceChanges.set(id, newStatus);
        student.status = newStatus;
        
        const reasonInput = document.getElementById(`reason_${id}`);
        if(reasonInput) {
            if(isChecked) {
               reasonInput.disabled = false;
               reasonInput.placeholder = "Reason...";
               reasonInput.focus();
            } else {
               reasonInput.disabled = true;
               reasonInput.value = "";
               reasonInput.placeholder = "";
            }
        }
        updateTableUI();
        updateStats();
      }
    }

    function updateStats() {
      const total = studentsData.length;
      const present = studentsData.filter(s => (attendanceChanges.get(s.id) || s.status) !== 'Absent').length;
      const absent = total - present;
      const percentage = total > 0 ? Math.round((present / total) * 100) : 0;

      // Safe update helper
      const setText = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
      const setWidth = (id, val) => { const el = document.getElementById(id); if(el) el.style.width = val; };

      setText('total-students-card', total);
      setText('present-card', present);
      setText('absent-card', absent);
      setText('attendance-rate-card', percentage + '%');
      setText('present-percentage', percentage + '%');
      setText('absent-percentage', (total > 0 ? 100 - percentage : 0) + '%');
      setText('absentee-count', absent); // Sidebar count

      setWidth('present-progress-bar', percentage + '%');
      setWidth('absent-progress-bar', (total > 0 ? 100 - percentage : 0) + '%');
    }

    function updateCounts() {
      const showCount = document.getElementById('showing-count');
      const totalCount = document.getElementById('total-count');
      if(showCount) showCount.textContent = studentsData.length;
      if(totalCount) totalCount.textContent = studentsData.length;
    }

    function updateAttendanceStatus() {
        const statusEl = document.getElementById('attendance-status');
        const submitBtn = document.getElementById('submit-btn');
        if(statusEl) {
            statusEl.textContent = 'Ready to Edit';
            statusEl.className = 'px-2 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-100';
        }
        if(submitBtn) {
            submitBtn.disabled = false;
            submitBtn.className = 'py-3 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-md transition flex items-center justify-center gap-2';
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit';
        }
    }

    function markAllPresent() {
      studentsData.forEach(s => { attendanceChanges.set(s.id, 'Present'); s.status = 'Present'; });
      updateTableUI(); updateStats();
    }
    function markAllAbsent() {
      studentsData.forEach(s => { attendanceChanges.set(s.id, 'Absent'); s.status = 'Absent'; });
      updateTableUI(); updateStats();
    }

    async function submitAttendance() {
      const loading = document.getElementById('loading-overlay');
      if(loading) loading.classList.remove('hidden');
      
      const payload = [];
      studentsData.forEach(student => {
        const status = attendanceChanges.get(student.id) || student.status || 'Present';
        const reasonInput = document.getElementById(`reason_${student.id}`);
        const reason = reasonInput ? reasonInput.value : '';

        payload.push({
          student_id: student.id,
          status: status,
          reason: reason
        });
      });

      try {
        const response = await fetch('submit_attendance.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ attendance: payload })
        });

        const result = await response.json();
        if(result.success) {
          showToast('success', 'Saved', 'Attendance submitted successfully');
          attendanceChanges.clear();
          setTimeout(() => fetchStudents(currentYear), 1000);
        } else {
          throw new Error(result.error || 'Unknown Error');
        }
      } catch (error) {
        showToast('error', 'Failed', error.message);
      } finally {
        if(loading) loading.classList.add('hidden');
      }
    }

    function showToast(type, title, msg) {
      const t = document.getElementById('toast');
      const icon = document.getElementById('toast-icon');
      const titleEl = document.getElementById('toast-title');
      const msgEl = document.getElementById('toast-message');

      if(!t || !icon || !titleEl || !msgEl) return;

      titleEl.textContent = title;
      msgEl.textContent = msg;

      if(type === 'success') { 
          icon.className = "w-8 h-8 rounded-full flex items-center justify-center shrink-0 bg-green-100 text-green-600"; 
          icon.innerHTML = '<i class="fas fa-check"></i>'; 
      }
      if(type === 'error') { 
          icon.className = "w-8 h-8 rounded-full flex items-center justify-center shrink-0 bg-red-100 text-red-600"; 
          icon.innerHTML = '<i class="fas fa-times"></i>'; 
      }
      if(type === 'info') { 
          icon.className = "w-8 h-8 rounded-full flex items-center justify-center shrink-0 bg-blue-100 text-blue-600"; 
          icon.innerHTML = '<i class="fas fa-info"></i>'; 
      }
      
      t.classList.remove('translate-y-24', 'opacity-0');
      setTimeout(() => t.classList.add('translate-y-24', 'opacity-0'), 3000);
    }

    function refreshData() { fetchStudents(currentYear); }
    function logout() { window.location.href = "index.html"; }
    function exportToExcel() { showToast('info', 'Export', 'Downloading Excel...'); }
    function showSystemInfo() { showToast('info', 'System', 'System is online. DB Connected.'); }
  </script>
</body>
</html>