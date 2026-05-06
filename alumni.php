<?php
session_start();
// ALUMNI PORTAL - DYNAMIC MEMORIES & SQUAD CHAT
// ---------------------------------------------

include 'db.php'; // Include early for DB access

// 1. Dynamic Login Check (Modified for Privacy)
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : 0;
$user_name = ($isLoggedIn && isset($_SESSION['name'])) ? $_SESSION['name'] : 'Guest';
$user_reg = ($isLoggedIn && isset($_SESSION['reg_no'])) ? $_SESSION['reg_no'] : '';

// 2. Logic: Enforce Privacy
$selected_batch = '';
$showLanding = false;

if (isset($_GET['batch'])) {
     $selected_batch = $_GET['batch'];
     $batchFilter = $selected_batch;
} elseif ($isLoggedIn) {
     // STUDENT MODE: Auto-detect batch from Session Year if not explicitly requested
     $current_year_val = isset($_SESSION['year']) ? intval($_SESSION['year']) : 1;
     // Assume current academic year is part of the batch ending in current_year + (3 - current_year_val) ??
     // Simplified logic: Just rely on session for now unless we want to change it.
     // Reusing previous logic:
     $target_grad_year = 2026; // Hardcoded reference point in previous code, let's make it relative to NOW
     // Better yet, if we want to be dynamic:
     // If Year 1 => Grad Year = Current Year + 2
     // If Year 2 => Grad Year = Current Year + 1
     // If Year 3 => Grad Year = Current Year
     
     // For safety, let's stick to the previous reliable logic or fall back to a default if user didn't select one.
     // However, simpler approach: if student is strictly bound to their batch, calculate it.
     // 2026 (Grad Year) - 3 = 2023 (Start)
     
     $start_year = 2026 - $current_year_val; 
     $end_year = $start_year + 3;
     $selected_batch = "$start_year-$end_year"; 
     $batchFilter = $selected_batch;
} else {
     // GUEST MODE
     if (empty($selected_batch)) {
         header("Location: guest_batches.php");
         exit();
     }
     $batchFilter = $selected_batch;
}

// 3. Fetch Memories
$activities = [];

// Strict Filtering SQL
$sql = "SELECT m.*, s.name as uploader_name FROM memories m JOIN students s ON m.student_id = s.id WHERE 1=1";

if ($batchFilter !== 'all') {
    $sql .= " AND m.batch_year = '$batchFilter'";
} 
// If 'all', we don't add the AND clause, so it fetches everything.

$sql .= " ORDER BY m.event_date DESC";

$result = $conn->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        $key = date('Y-m', strtotime($row['event_date']));
        if (!isset($activities[$key])) { $activities[$key] = []; }
        $activities[$key][] = [
           'title' => $row['caption'], 
           'type'  => 'Memory',
           'desc'  => 'Shared by ' . $row['uploader_name'] . ' (' . $row['batch_year'] . ')',
           'media' => [$row['media_path']],
           'id'    => $row['id']
        ];
    }
}


// 4. Define Timeline Range (DYNAMIC)
if (!empty($selected_batch) && strpos($selected_batch, '-') !== false) {
    $parts = explode('-', $selected_batch);
    $startYear = intval($parts[0]);
    $endYear = intval($parts[1]);
    
    $startDate = new DateTime("$startYear-06-01");
    $endDate   = new DateTime("$endYear-05-31");
} else {
    // Fallback
    $startDate = new DateTime('2023-06-01');
    $endDate   = new DateTime('2026-04-01');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Alumni & Squad Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    
    /* Premium Gradients */
    .bg-gradient-primary { background: linear-gradient(135deg, #4f46e5 0%, #ec4899 100%); }
    .text-gradient { background: linear-gradient(135deg, #4f46e5 0%, #ec4899 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    
    /* Timeline */
    .timeline-line {
        position: absolute; left: 28px; top: 0; bottom: 0; width: 3px; 
        background: cubic-bezier(135deg, #cbd5e1 50%, #e2e8f0 50%);
        background: linear-gradient(to bottom, #cbd5e1, #e2e8f0, transparent);
        z-index: 0;
    }
    @media (min-width: 768px) {
        .timeline-line { left: 50%; transform: translateX(-50%); }
    }

    /* Glassmorphism */
    .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); }
    .glass-dark { background: rgba(30, 41, 59, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
  </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-indigo-500 selection:text-white pb-32">
  
  <!-- FLOATING ACTION BUTTONS -->
  <?php if($isLoggedIn): ?>
  <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-4">
      <!-- Squad Chat (Badge) -->
      <button onclick="toggleSquadChat()" class="group relative w-14 h-14 bg-indigo-600 rounded-full text-white shadow-xl shadow-indigo-500/40 hover:scale-110 transition-all active:scale-95 flex items-center justify-center">
          <i class="fas fa-comments text-xl group-hover:rotate-12 transition-transform"></i>
          <span class="absolute -top-1 -right-1 bg-rose-500 w-4 h-4 rounded-full border-2 border-slate-50"></span>
          <span class="absolute right-16 bg-slate-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Squad Chat</span>
      </button>

      <!-- Add Memory -->
      <button onclick="openUploadModal()" class="group relative w-16 h-16 bg-gradient-primary rounded-full text-white shadow-2xl shadow-pink-500/40 hover:scale-110 transition-all active:scale-95 flex items-center justify-center">
        <i class="fas fa-camera text-2xl group-hover:rotate-12 transition-transform"></i>
        <span class="absolute right-20 bg-slate-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Add Memory</span>
      </button>
  </div>
  <?php endif; ?>

  <!-- HERO HEADER -->
  <div class="relative bg-white pt-32 pb-20 px-6 overflow-hidden border-b border-slate-200">
      <!-- ... (Hero content) ... -->
      <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
      <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-[100px]"></div>
      <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] bg-pink-500/10 rounded-full blur-[100px]"></div>
      
      <div class="relative z-10 max-w-4xl mx-auto text-center">
        <?php if($isLoggedIn): ?>
            <div class="flex flex-col md:flex-row items-center justify-center gap-4 mb-6 animate-fade-in-up">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    Welcome back, <?php echo htmlspecialchars($user_name); ?>
                </div>
                <a href="index.html" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-rose-50 border border-rose-100 text-rose-600 text-sm font-bold hover:bg-rose-100 transition shadow-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        <?php else: ?>

             <a href="guest_batches.php" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-sm font-bold mb-6 hover:bg-slate-200 transition">
                <i class="fas fa-arrow-left"></i> Back to Hub
            </a>
        <?php endif; ?>
        
        <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6 text-slate-900">
          <?php echo !empty($selected_batch) ? "Batch $selected_batch" : "Our Legacy"; ?>
        </h1>
        <p class="text-slate-500 text-xl md:text-2xl max-w-2xl mx-auto leading-relaxed">
          The Unforgettable Moments of <?php echo !empty($selected_batch) ? $selected_batch : 'the Data Science Department'; ?>.
        </p>

        <!-- Stats Row -->
        <div class="flex justify-center gap-8 mt-10 text-slate-400 font-medium text-sm uppercase tracking-widest">
            <div class="flex flex-col items-center">
                <span class="text-2xl font-bold text-slate-900"><?php echo range($startDate->format('Y'), $endDate->format('Y'))[count(range($startDate->format('Y'), $endDate->format('Y'))) -1] - range($startDate->format('Y'), $endDate->format('Y'))[0]; ?></span>
                <span>Years</span>
            </div>
            <div class="w-px h-10 bg-slate-200"></div>
            <div class="flex flex-col items-center">
                <span class="text-2xl font-bold text-slate-900"><?php echo count($activities); ?></span>
                <span>Memories</span>
            </div>
        </div>
      </div>
  </div>

  <!-- TIMELINE CONTENT -->
  <div class="p-4 md:p-12 max-w-5xl mx-auto relative min-h-screen mt-8">
      <div class="timeline-line"></div>
      
      <?php
        $currentDate = clone $startDate;
        $counter = 0;
        
        while ($currentDate <= $endDate) {
            $key = $currentDate->format('Y-m');
            $yearStr = $currentDate->format('Y');
            $hasData = isset($activities[$key]);
            
            $isEven = $counter % 2 == 0;
            $rowClass = $isEven ? 'md:flex-row' : 'md:flex-row-reverse';
            $textClass = $isEven ? 'md:text-left' : 'md:text-right';
            $cardAlign = $isEven ? 'items-start' : 'items-end';
            $marginClass = $isEven ? 'md:pl-16' : 'md:pr-16';
            
            if ($currentDate->format('n') == 6 || $currentDate->format('n') == 1) { 
                 echo "<div class='relative z-10 text-center my-16'>
                    <span class='inline-block bg-slate-900 text-white px-6 py-2 rounded-full text-lg font-bold shadow-xl border-4 border-slate-50'>$yearStr</span>
                 </div>";
            }
      ?>
        <div class="relative z-10 mb-12 flex flex-col <?php echo $rowClass; ?> w-full group">
             <!-- Marker -->
             <div class="absolute left-[28px] md:left-1/2 transform -translate-x-[15px] md:-translate-x-1/2 
                        w-8 h-8 rounded-full border-4 border-slate-50 shadow-md flex items-center justify-center 
                        text-[0px] font-bold z-20 transition-all duration-300 
                        <?php echo $hasData ? 'bg-gradient-primary scale-125 shadow-indigo-500/50' : 'bg-slate-200'; ?>">
             </div>
             
             <!-- Content -->
             <div class="w-full md:w-1/2 pl-16 md:pl-0 <?php echo $marginClass; ?> <?php echo $hasData ? 'opacity-100' : 'opacity-40 hover:opacity-100 transition-opacity'; ?>">
                <div class="flex flex-col <?php echo $cardAlign; ?> gap-4">
                    <span class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2 block <?php echo $isEven ? 'md:text-left' : 'md:text-right'; ?>">
                        <?php echo $currentDate->format('F'); ?>
                    </span>

                    <?php if ($hasData): ?>
                        <div class="w-full space-y-8">
                            <?php foreach ($activities[$key] as $idx => $evt): ?>
                                <div class="bg-white rounded-3xl shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] border border-slate-100 overflow-hidden hover:shadow-[0_25px_50px_-12px_rgba(79,70,229,0.25)] transition-all duration-300 transform hover:-translate-y-1">
                                    <div class="relative h-64 bg-slate-100 group/slider">
                                        <div class="absolute top-4 left-4 z-10">
                                            <span class="px-3 py-1 bg-white/90 backdrop-blur text-indigo-700 text-xs font-bold rounded-lg shadow-sm">
                                                <?php echo $evt['type']; ?>
                                            </span>
                                        </div>
                                        <div class="flex overflow-x-auto snap-x snap-mandatory h-full w-full no-scrollbar">
                                            <?php foreach ($evt['media'] as $media): 
                                                $ext = pathinfo($media, PATHINFO_EXTENSION);
                                                $isVideo = in_array(strtolower($ext), ['mp4', 'webm', 'ogg']);
                                            ?>
                                                <div class="snap-center min-w-full h-full">
                                                    <?php if($isVideo): ?>
                                                        <video controls class="w-full h-full object-cover"><source src="<?php echo $media; ?>"></video>
                                                    <?php else: ?>
                                                        <img src="<?php echo $media; ?>" class="w-full h-full object-cover">
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <h3 class="font-bold text-slate-800 text-xl mb-2"><?php echo htmlspecialchars($evt['title']); ?></h3>
                                        <p class="text-slate-500 text-sm leading-relaxed"><?php echo htmlspecialchars($evt['desc']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                         <div class="hidden md:block h-px w-12 bg-slate-200 my-4"></div>
                    <?php endif; ?>
                </div>
             </div>
             <div class="hidden md:block w-1/2"></div>
        </div>
      <?php 
          $currentDate->modify('+1 month');
          $counter++;
        } 
      ?>
      
      <div class="relative z-10 text-center mt-20">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-900 text-white shadow-2xl animate-bounce">
              <i class="fas fa-flag-checkered text-xl"></i>
          </div>
          <p class="mt-4 text-slate-400 font-medium">To be continued...</p>
      </div>
  </div>

  <?php if($isLoggedIn): ?>
  <!-- MODAL: UPLOAD MEMORY -->
  <div id="uploadModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-4 transition-opacity duration-300">
      <div class="bg-white rounded-3xl p-8 w-full max-w-lg shadow-2xl transform scale-100 transition-transform">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Share a Memory</h2>
            <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
          </div>
          
          <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
              <input type="hidden" name="action" value="upload">
              <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1">When did this happen?</label>
                  <input type="date" name="event_date" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
              </div>
              <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1">Photo or Video</label>
                  <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center hover:bg-slate-50 transition cursor-pointer relative">
                      <input type="file" name="file" accept="image/*,video/mp4" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                      <div class="pointer-events-none">
                          <i class="fas fa-cloud-upload-alt text-3xl text-indigo-400 mb-2"></i>
                          <p class="text-sm text-slate-500 font-medium">Click to upload</p>
                          <p class="text-xs text-slate-400 mt-1">JPG, PNG, MP4</p>
                      </div>
                  </div>
              </div>
              <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1">Caption</label>
                  <textarea name="caption" rows="2" placeholder="Write a caption..." required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"></textarea>
              </div>
              <button type="submit" class="w-full py-3.5 bg-gradient-primary text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transform hover:-translate-y-0.5 transition-all">
                  Post Memory
              </button>
          </form>
      </div>
  </div>

  <!-- SQUAD CHAT DRAWER -->
  <div id="squadChat" class="fixed top-0 right-0 h-full w-[400px] bg-white shadow-2xl z-[70] transform translate-x-full transition-transform duration-500 cubic-bezier(0.16, 1, 0.3, 1) flex flex-col border-l border-slate-100">
      
      <!-- Chat Header -->
      <div class="h-24 bg-slate-900 flex items-center justify-between px-6 relative overflow-hidden shrink-0">
          <div class="absolute inset-0 bg-gradient-to-r from-indigo-900/50 to-purple-900/50"></div>
          <div class="relative z-10 flex items-center gap-4">
               <div class="w-12 h-12 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-lg shadow-inner">
                   <?php echo substr($user_reg, -2); ?>
               </div>
               <div>
                   <h3 class="font-bold text-white text-lg">Squad <?php echo substr($user_reg, -2); ?></h3>
                   <div class="flex items-center gap-1.5 opacity-80">
                       <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                       <span class="text-xs text-slate-200 font-medium">Live Chat</span>
                   </div>
               </div>
          </div>
          <button onclick="toggleSquadChat()" class="relative z-10 w-8 h-8 flex items-center justify-center rounded-full big-white/10 text-white/50 hover:bg-white/20 hover:text-white transition">
              <i class="fas fa-times"></i>
          </button>
      </div>
      
      <!-- Chat Messages -->
      <div id="chatMessages" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50 scroll-smooth">
          <!-- Intro -->
          <div class="text-center py-8">
              <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-3 text-indigo-500">
                  <i class="fas fa-shield-alt text-2xl"></i>
              </div>
              <p class="text-sm text-slate-400">This is a private space for Number Squad <?php echo substr($user_reg, -2); ?>.</p>
              <p class="text-xs text-slate-300 mt-1">Be kind and respectful.</p>
          </div>
      </div>

      <!-- Chat Input -->
      <div class="p-4 bg-white border-t border-slate-100 shrink-0">
          <form id="chatForm" class="flex gap-2 items-end">
              <div class="flex-1 bg-slate-100 rounded-2xl p-2 focus-within:ring-2 focus-within:ring-indigo-500/20 transition-all">
                  <input type="text" id="chatInput" autocomplete="off" class="w-full bg-transparent border-none text-sm px-3 py-2 focus:ring-0 placeholder-slate-400" placeholder="Type a message...">
              </div>
              <button type="submit" class="w-12 h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full flex items-center justify-center shadow-lg shadow-indigo-500/30 transition-transform active:scale-95">
                  <i class="fas fa-paper-plane"></i>
              </button>
          </form>
      </div>
  </div>
  <?php endif; ?>

  <script>
    <?php if($isLoggedIn): ?>
    // --- MEMORY UPLOAD ---
    function openUploadModal() { document.getElementById('uploadModal').classList.remove('hidden'); }
    function closeUploadModal() { document.getElementById('uploadModal').classList.add('hidden'); }
    
    const uploadForm = document.getElementById('uploadForm');
    if(uploadForm) {
        uploadForm.onsubmit = async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            
            try {
                const res = await fetch('backend/memory_api.php', { method: 'POST', body: fd });
                const data = await res.json();
                if(data.status === 'success') {
                    closeUploadModal();
                    // Optional: show toast instead of alert
                    alert('Posted!');
                    location.reload(); 
                } else {
                    alert(data.error);
                }
            } catch(err) { console.error(err); }
        };
    }

    // --- SQUAD CHAT ---
    const squadPanel = document.getElementById('squadChat');
    let chatInterval;

    function toggleSquadChat() {
        if(!squadPanel) return;
        const isClosed = squadPanel.classList.contains('translate-x-full');
        if (isClosed) {
            squadPanel.classList.remove('translate-x-full');
            loadMessages();
            chatInterval = setInterval(loadMessages, 3000); // Poll every 3s
            // Scroll to bottom delay
            setTimeout(() => {
                const c = document.getElementById('chatMessages');
                if(c) c.scrollTop = c.scrollHeight;
            }, 300);
        } else {
            squadPanel.classList.add('translate-x-full');
            clearInterval(chatInterval);
        }
    }

    async function loadMessages() {
        const container = document.getElementById('chatMessages');
        if(!container) return;

        try {
            // Show loading indicator if it's the first load (static intro present)
            if (container.querySelector('.text-center.py-8')) {
                 // Don't wipe it yet, just log
                 console.log("Starting fetch...");
            }

            const res = await fetch('backend/squad_chat_api.php?action=fetch');
            const text = await res.text();
            
            try {
                const data = JSON.parse(text);
                
                if (data.error) {
                    container.innerHTML = `<div class="p-4 text-red-500 text-sm text-center">API Error: ${data.error}</div>`;
                    return;
                }

                if (data.messages && data.messages.length > 0) {
                    // Check scroll
                    const isAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;

                    container.innerHTML = data.messages.map(msg => `
                        <div class="flex flex-col ${msg.is_me ? 'items-end' : 'items-start'} animate-fade-in-up">
                            <div class="flex items-end gap-2 max-w-[85%]">
                                ${!msg.is_me ? '<div class="w-6 h-6 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-[10px] text-white font-bold shrink-0 shadow-sm">' + msg.sender[0] + '</div>' : ''}
                                
                                <div class="${msg.is_me ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-white border border-slate-200 text-slate-700 rounded-bl-none'} p-3 rounded-2xl shadow-sm text-sm leading-relaxed">
                                    ${!msg.is_me ? `<div class="text-[10px] text-indigo-500 font-bold mb-1 block">${msg.sender} <span class="opacity-50 font-normal">(${msg.batch})</span></div>` : ''}
                                    ${msg.message}
                                </div>
                            </div>
                            <span class="text-[9px] text-slate-300 mt-1 mx-2 font-medium">${msg.time}</span>
                        </div>
                    `).join('') + '<div class="h-4"></div>';
                    
                    if(isAtBottom) container.scrollTop = container.scrollHeight;
                } else {
                    container.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm">No messages yet in Squad ' + (data.squad || '?') + '. Say hi!</div>';
                }

            } catch (e) {
                console.error("JSON Error:", e);
                container.innerHTML = `<div class="p-4 text-red-600 text-xs break-all font-mono bg-red-50 rounded">
                    <strong>JSON Parse Error:</strong><br>${e.message}<br><br>
                    <strong>Response:</strong><br>${text.substring(0, 150)}...
                </div>`;
            }
        } catch(e) { 
            console.error("Network Error", e); 
            container.innerHTML = `<div class="p-4 text-red-500 text-center">Network Error: ${e.message}</div>`;
        }
    }

    if(chatForm) {
        chatForm.onsubmit = async (e) => {
            e.preventDefault();
            const inp = document.getElementById('chatInput');
            const msg = inp.value;
            if(!msg.trim()) return;
            
            // UI Feedback
            const btn = chatForm.querySelector('button');
            const originalIcon = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                // console.log("Sending message:", msg); 
                const res = await fetch('backend/squad_chat_api.php?action=send', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: msg })
                });
                
                const text = await res.text(); // Get raw text first
                // console.log("Server response:", text);

                try {
                    const data = JSON.parse(text);
                    if(data.error) {
                        alert("Server Error: " + data.error);
                    } else if(data.status === 'success') {
                        inp.value = '';
                        loadMessages();
                        setTimeout(() => {
                             const c = document.getElementById('chatMessages');
                             if(c) c.scrollTop = c.scrollHeight;
                        }, 100);
                    } else {
                        alert("Unknown response: " + text);
                    }
                } catch(jsonErr) {
                    // JSON Parse failed, likely PHP error output
                    console.error("JSON Error:", jsonErr);
                    alert("Backend Error (Not JSON): " + text.substring(0, 100));
                }
            } catch(err) { 
                console.error("Fetch Error:", err);
                alert("Network/Fetch Error: " + err.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalIcon;
            }
        };
    }
    <?php endif; ?>
  </script>
</body>
</html>