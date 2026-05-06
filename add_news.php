<?php
session_start();

// --- Access Control ---
// Restrict access to 'editor' or 'admin' roles
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'editor' && $_SESSION['role'] !== 'admin')) {
    header("Location: student_login.html?error=Unauthorized Access");
    exit();
}

// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "news";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Logic ---
$message = "";
$msg_type = "";
$edit_id = null;
$edit_content = "";

// 1. Get Stats for Dashboard
$total_news_result = $conn->query("SELECT COUNT(*) as count FROM news");
$total_news = $total_news_result->fetch_assoc()['count'];

$last_news_result = $conn->query("SELECT created_at FROM news ORDER BY created_at DESC LIMIT 1");
$last_update = ($last_news_result->num_rows > 0) ? date("M d, h:i A", strtotime($last_news_result->fetch_assoc()['created_at'])) : "N/A";

// 2. Handle Delete
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    header("Location: add_news.php?msg=deleted"); // Redirect to clear query params
    exit;
}

// 3. Handle Edit Fetch
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_content = $row['content'];
    }
}

// 4. Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $news_content = trim($_POST['news_content']);
    $news_id = isset($_POST['news_id']) ? intval($_POST['news_id']) : null;

    if (!empty($news_content)) {
        if ($news_id) {
            $stmt = $conn->prepare("UPDATE news SET content = ? WHERE id = ?");
            $stmt->bind_param("si", $news_content, $news_id);
            $stmt->execute();
            header("Location: add_news.php?msg=updated");
        } else {
            $stmt = $conn->prepare("INSERT INTO news (content) VALUES (?)");
            $stmt->bind_param("s", $news_content);
            $stmt->execute();
            header("Location: add_news.php?msg=added");
        }
        exit;
    }
}

// 5. Handle Toast Messages from Redirects
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') { $message = "News item removed successfully."; $msg_type = "error"; }
    if ($_GET['msg'] == 'updated') { $message = "News updated successfully."; $msg_type = "success"; }
    if ($_GET['msg'] == 'added')   { $message = "News published successfully."; $msg_type = "success"; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard | Manage News</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
          colors: {
            brand: { 50: '#f0fdfa', 100: '#ccfbf1', 500: '#14b8a6', 600: '#0d9488', 900: '#134e4a' },
            dark: { 800: '#1e293b', 900: '#0f172a' }
          },
          animation: { 'fade-in': 'fadeIn 0.5s ease-out forwards' },
          keyframes: { fadeIn: { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } } }
        }
      }
    }
  </script>
  <style>
    body { background-color: #f8fafc; }
    /* Soft Gradient Mesh Background */
    .mesh-bg {
      position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1;
      background: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                  radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                  radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
      background-size: cover;
    }
    .glass-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
  </style>
</head>
<body class="text-slate-600 font-sans selection:bg-brand-500 selection:text-white">

  <div class="mesh-bg opacity-10"></div>


  <aside id="sidebar" class="fixed top-0 left-0 h-screen w-72 bg-dark-900 text-white z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 flex flex-col border-r border-slate-800 shadow-2xl">
    <div class="p-8 pb-4">
      <h2 class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-teal-400 to-blue-500 bg-clip-text text-transparent">DATA SCIENCE</h2>
      <p class="text-xs text-slate-400 font-medium mt-1">Faculty Admin Portal</p>
    </div>

    <nav class="flex-1 px-4 space-y-2 mt-4">
      <a href="index.html" class="flex items-center px-4 py-3 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all group">
        <span class="w-6 opacity-70 group-hover:opacity-100">🏠</span> <span class="font-medium">Home</span>
      </a>
      <a href="overview.html" class="flex items-center px-4 py-3 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all group">
        <span class="w-6 opacity-70 group-hover:opacity-100">🔍</span> <span class="font-medium">Overview</span>
      </a>
      <a href="add_news.php" class="flex items-center px-4 py-3 bg-brand-600 text-white shadow-lg shadow-brand-500/20 rounded-xl transition-all">
        <span class="w-6">📢</span> <span class="font-bold">Manage News</span>
      </a>
      <a href="faculty.html" class="flex items-center px-4 py-3 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all group">
        <span class="w-6 opacity-70 group-hover:opacity-100">👥</span> <span class="font-medium">Faculty</span>
      </a>
    </nav>

    <div class="p-6 border-t border-slate-800">
      <button onclick="logout()" class="flex items-center w-full px-4 py-3 bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white rounded-xl transition-all font-semibold text-sm">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
        Sign Out
      </button>
    </div>
  </aside>

  <main class="md:ml-72 min-h-screen flex flex-col relative transition-all duration-300">
    
    <header class="md:hidden flex items-center justify-between p-4 bg-white/80 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200">
      <span class="font-bold text-slate-800">News Manager</span>
      <button onclick="toggleSidebar()" class="p-2 text-slate-600 bg-slate-100 rounded-lg">☰</button>
    </header>

    <div class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="lg:col-span-2">
          <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
          <p class="text-slate-500 mt-1">Welcome back. Here is what's happening today.</p>
        </div>
        <div class="flex gap-4">
          <div class="flex-1 bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center gap-4">
             <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold">#</div>
             <div>
               <p class="text-xs font-bold text-slate-400 uppercase">Total Notices</p>
               <p class="text-xl font-bold text-slate-800"><?php echo $total_news; ?></p>
             </div>
          </div>
          <div class="flex-1 bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center gap-4">
             <div class="w-10 h-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center font-bold">🕒</div>
             <div>
               <p class="text-xs font-bold text-slate-400 uppercase">Last Update</p>
               <p class="text-sm font-bold text-slate-800"><?php echo $last_update; ?></p>
             </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
        
        <div class="xl:col-span-1 sticky top-8 z-10">
          <div class="glass-card rounded-3xl p-6 shadow-xl shadow-slate-200/50">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
              <span class="w-8 h-8 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center">✎</span>
              <?php echo $edit_id ? "Edit Announcement" : "Create Announcement"; ?>
            </h2>

            <?php if ($message): ?>
              <div class="mb-4 p-3 rounded-xl text-sm font-semibold flex items-center gap-2 animate-fade-in
                <?php echo $msg_type === 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100'; ?>">
                <span><?php echo $msg_type === 'success' ? '✅' : '⚠️'; ?></span>
                <?php echo htmlspecialchars($message); ?>
              </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
              <input type="hidden" name="news_id" value="<?php echo $edit_id ?? ''; ?>"/>
              
              <div class="relative group">
                <textarea name="news_content" rows="6" required placeholder="Write something important..."
                  class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all resize-none shadow-inner"><?php echo htmlspecialchars($edit_content); ?></textarea>
              </div>

              <div class="flex gap-2">
                <button type="submit"
                  class="flex-1 bg-slate-900 hover:bg-brand-600 text-white font-bold py-3.5 rounded-xl shadow-lg hover:shadow-brand-500/30 hover:-translate-y-0.5 transition-all duration-200 flex justify-center items-center gap-2">
                  <?php if($edit_id): ?>
                    <span>Update</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  <?php else: ?>
                    <span>Publish Now</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                  <?php endif; ?>
                </button>
                
                <?php if($edit_id): ?>
                  <a href="add_news.php" class="px-4 py-3.5 rounded-xl bg-slate-100 text-slate-600 font-semibold hover:bg-slate-200 transition-colors">Cancel</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>

        <div class="xl:col-span-2 space-y-6">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Recent Notices</h2>
            <div class="hidden sm:flex items-center bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
               <span class="text-slate-400 text-xs mr-2">🔍</span>
               <input type="text" placeholder="Search notices..." class="text-sm outline-none w-32 placeholder-slate-400">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php
              $result = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
              if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
              <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] hover:shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1)] hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between h-full relative overflow-hidden">
                
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-400 to-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                <div>
                  <div class="flex justify-between items-start mb-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-500">
                      <?php echo date("M d", strtotime($row['created_at'])); ?>
                    </span>
                    
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                      <a href="?edit=<?php echo $row['id']; ?>" class="p-1.5 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-600 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                      </a>
                      <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete permanently?');" class="p-1.5 text-red-600 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                      </a>
                    </div>
                  </div>
                  
                  <p class="text-slate-700 font-medium leading-relaxed whitespace-pre-wrap"><?php echo htmlspecialchars($row['content']); ?></p>
                </div>
                
                <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between">
                   <span class="text-xs text-slate-400 font-medium"><?php echo date("h:i A", strtotime($row['created_at'])); ?></span>
                   <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                </div>
              </div>
            <?php 
                endwhile; 
              else:
            ?>
              <div class="col-span-full flex flex-col items-center justify-center py-16 text-center border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50">
                 <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-3xl mb-4">📭</div>
                 <h3 class="text-lg font-bold text-slate-700">No News Published</h3>
                 <p class="text-slate-400 max-w-xs mx-auto mt-2">Create your first announcement using the form on the left.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </main>

  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleSidebar = () => sidebar.classList.toggle('-translate-x-full');

    function logout() {
      if(confirm("Are you sure you want to log out?")) {
        window.location.href = "logout.php";
      }
    }
    
    // Auto-dismiss alerts
    setTimeout(() => {
       const alert = document.querySelector('.animate-fade-in');
       if(alert) {
         alert.classList.add('opacity-0');
         setTimeout(() => alert.remove(), 500);
       }
    }, 4000);
  </script>
</body>
</html>
<?php $conn->close(); ?>