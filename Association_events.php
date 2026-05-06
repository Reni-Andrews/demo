<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }



// 2. Fetch Events
// 2. Fetch Events
$sql = "SELECT * FROM events ORDER BY event_date DESC";
$result_events = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Association Events</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'], },
          colors: { brand: { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7', 900: '#0c4a6e', } },
          animation: { 'fade-in': 'fadeIn 0.8s ease-out', },
          keyframes: { fadeIn: { 'from': { opacity: '0', transform: 'translateY(20px)' }, 'to': { opacity: '1', transform: 'translateY(0)' }, } }
        }
      }
    }
  </script>

  <style>
    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #64748b; }
    .bg-grid-pattern { background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 24px 24px; }
  </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans bg-grid-pattern selection:bg-indigo-100 selection:text-indigo-900 overflow-x-hidden">

  <nav class="fixed top-4 left-4 right-4 md:left-10 md:right-10 z-40 bg-white/80 backdrop-blur-md border border-white/20 shadow-lg rounded-2xl px-6 py-3 flex items-center justify-between transition-all duration-300">
    <div class="flex items-center gap-4">
      <button onclick="openSidebar()" class="p-2 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
        <i class="fas fa-bars text-xl"></i>
      </button>
      <div class="hidden md:flex items-center gap-2">
         <span class="font-bold text-slate-800 tracking-tight">Data<span class="text-indigo-600">Science</span></span>
      </div>
    </div>

    <ul class="hidden md:flex space-x-1 lg:space-x-2 text-sm font-medium text-slate-600" id="navbar-links">
      <li><a href="faculty.html" class="px-4 py-2 rounded-full hover:bg-slate-100 hover:text-indigo-600 transition-all">Faculty</a></li>
      <li><a href="student.html" class="px-4 py-2 rounded-full hover:bg-slate-100 hover:text-indigo-600 transition-all">Student</a></li>
      <li><a href="alumni.php" class="px-4 py-2 rounded-full hover:bg-slate-100 hover:text-indigo-600 transition-all">Alumni</a></li>
      <li><a href="news.html" class="px-4 py-2 rounded-full hover:bg-slate-100 hover:text-indigo-600 transition-all">Notice</a></li>
      <li><a href="contact.html" class="px-4 py-2 rounded-full hover:bg-slate-100 hover:text-indigo-600 transition-all">Contact</a></li>
    </ul>

    <div class="md:hidden">
      <span class="font-bold text-indigo-600">DS</span>
    </div>
  </nav>

  <aside id="sidebar" class="fixed top-0 left-0 h-full w-80 bg-white text-black z-50 transform -translate-x-full transition-transform duration-300 ease-out shadow-2xl flex flex-col">
    <div class="p-6 flex items-center justify-between border-b border-slate-800">
      <h2 class="text-2xl font-extrabold bg-gradient-to-r from-indigo-400 to-cyan-400 bg-clip-text text-transparent">DATA SCIENCE</h2>
      <button onclick="closeSidebar()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white transition-all">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="p-4 space-y-2 overflow-y-auto flex-1">
      <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 ml-2">Main Menu</div>
      <a href="index.html" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 text-slate-800 hover:text-white transition-all group">
        <i class="fas fa-users text-purple-400 group-hover:scale-110 transition-transform"></i> Home
      </a>
      <a href="overview.html" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 text-slate-800 hover:text-white transition-all group">
        <i class="fas fa-search text-indigo-400 group-hover:scale-110 transition-transform"></i> Overview
      </a>
      <a href="Department_activities.html" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 text-slate-800 hover:text-white transition-all group">
        <i class="fas fa-chart-line text-cyan-400 group-hover:scale-110 transition-transform"></i> Activities
      </a>
      <a href="Association_events.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-800 text-white transition-all group">
        <i class="fas fa-users text-purple-400 group-hover:scale-110 transition-transform"></i> Events
      </a>
      <a href="hod_message.html" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 text-slate-800 hover:text-white transition-all group">
        <i class="fas fa-quote-right text-yellow-400 group-hover:scale-110 transition-transform"></i> HOD Message
      </a>
      <a href="achievements.html" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 text-slate-800 hover:text-white transition-all group">
        <i class="fas fa-trophy text-amber-400 group-hover:scale-110 transition-transform"></i> Achievements
      </a>
      <a href="department_files.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 text-slate-800 hover:text-white transition-all group">
        <i class="fas fa-folder-open text-emerald-400 group-hover:scale-110 transition-transform"></i> Files
      </a>
    </div>
  </aside>

  <main id="main-content" class="pt-28 pb-20 px-4 md:px-10 max-w-7xl mx-auto space-y-12 animate-fade-in">
    
    <div class="text-center py-6">
      <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 tracking-tight">
        Association <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Events</span>
      </h1>
      <p class="mt-4 text-slate-500">Connecting students, faculty, and industry leaders.</p>
    </div>

    <!-- PRESIDENT CONTROLS -->
    <!-- PRESIDENT CONTROLS (Moved to Dashboard) -->
    <!-- Form removed as per new workflow -->

    <!-- EVENTS LIST -->
    <div class="grid grid-cols-1 gap-6">
      
      <?php if ($result_events->num_rows > 0): ?>
          <?php while($event = $result_events->fetch_assoc()): ?>
            <?php
                // Fetch Gallery Images
                $event_id = $event['id'];
                $gallery_sql = "SELECT image_path FROM event_gallery WHERE event_id = $event_id";
                $gallery_res = $conn->query($gallery_sql);
                $gallery_images = [];
                if($gallery_res->num_rows > 0){
                    while($row = $gallery_res->fetch_assoc()){
                        $gallery_images[] = $row['image_path'];
                    }
                }
                
                // Combine Cover Image + Gallery
                $all_images = [];
                if(!empty($event['image_path'])) $all_images[] = $event['image_path'];
                $all_images = array_merge($all_images, $gallery_images);
                $unique_id = 'carousel-' . $event['id'];
            ?>
            
            <section class="bg-white rounded-3xl p-0 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-xl transition-all duration-300">
                <div class="flex flex-col md:flex-row">
                    <!-- Image / Carousel -->
                    <div class="w-full md:w-1/3 h-64 md:h-auto relative overflow-hidden bg-gray-200 flex items-center justify-center text-gray-400 group-carousel">
                        
                        <?php if(count($all_images) > 0): ?>
                            <!-- Slides -->
                            <div id="<?php echo $unique_id; ?>" class="w-full h-full relative cursor-pointer" onclick="openLightbox(<?php echo htmlspecialchars(json_encode($all_images)); ?>)">
                                <?php foreach($all_images as $index => $img): ?>
                                    <img src="<?php echo htmlspecialchars($img); ?>" 
                                         class="absolute top-0 left-0 w-full h-full object-cover transition-opacity duration-500 <?php echo $index === 0 ? 'opacity-100 relative' : 'opacity-0'; ?>"
                                         data-index="<?php echo $index; ?>">
                                <?php endforeach; ?>
                                
                                <!-- Controls (only if multiple) -->
                                <?php if(count($all_images) > 1): ?>
                                    <button onclick="event.stopPropagation(); prevSlide('<?php echo $unique_id; ?>')" class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/80 z-20 opacity-0 group-carousel-hover:opacity-100 transition-opacity">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button onclick="event.stopPropagation(); nextSlide('<?php echo $unique_id; ?>')" class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/80 z-20 opacity-0 group-carousel-hover:opacity-100 transition-opacity">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                    
                                    <!-- Indicators -->
                                    <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1 z-20">
                                        <?php foreach($all_images as $index => $img): ?>
                                            <div class="w-2 h-2 rounded-full bg-white/50 transition-colors <?php echo $index === 0 ? 'bg-white' : ''; ?>" data-indicator="<?php echo $index; ?>"></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <i class="fas fa-calendar-alt text-6xl"></i>
                        <?php endif; ?>
                        
                         <div class="absolute bottom-4 left-4 text-white md:hidden z-10 pointer-events-none">
                            <span class="block text-sm font-bold bg-indigo-600 px-2 py-1 rounded w-fit mb-1">
                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-8 flex-1 flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-2 text-sm font-bold text-indigo-600 uppercase tracking-widest">
                            <span class="bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                                <i class="far fa-calendar-alt mr-1"></i> <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                            </span>
                            <span class="text-slate-400">•</span>
                            <span class="text-slate-500"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location'] ?? 'Venue TBA'); ?></span>
                        </div>
                        
                        <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-4 group-hover:text-indigo-700 transition-colors">
                            <?php echo $event['title']; ?>
                        </h2>
                        
                        <p class="text-slate-600 leading-relaxed mb-6">
                           <?php echo nl2br($event['description']); ?>
                        </p>

                        <!-- Time Badge -->
                        <div class="flex items-center gap-2 text-slate-400 font-medium text-sm">
                             <i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($event['event_date'])); ?>
                        </div>
                    </div>
                </div>
            </section>
          <?php endwhile; ?>
      <?php else: ?>
          <section class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 min-h-[400px] relative overflow-hidden group hover:shadow-md transition-all">
             <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-bl-full -mr-10 -mt-10 transition-transform group-hover:scale-110"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                  <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                    <i class="fas fa-handshake text-lg"></i>
                  </div>
                  <h2 class="text-2xl font-bold text-slate-800">Upcoming Events</h2>
                </div>

                <div class="text-slate-600 leading-relaxed">
                   <div class="flex flex-col items-center justify-center py-10 text-center">
                     <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-4">
                       <i class="fas fa-calendar-check text-2xl"></i>
                     </div>
                     <p class="text-slate-500 font-medium">Events will be announced soon.</p>
                     <p class="text-sm text-slate-400">Stay tuned for association meetups and technical seminars.</p>
                   </div>
                </div>
            </div>
          </section>
      <?php endif; ?>
    </div>

  </main>

  <!-- LIGHTBOX MODAL -->
  <div id="lightbox" class="fixed inset-0 z-[100] bg-black/95 hidden flex flex-col justify-center items-center opacity-0 transition-opacity duration-300">
      <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white hover:text-gray-300 text-4xl leading-none">&times;</button>
      
      <div class="relative w-full max-w-5xl h-[80vh] flex items-center justify-center px-4">
          <img id="lightbox-img" src="" class="max-w-full max-h-full object-contain shadow-2xl rounded-sm">
          
          <button onclick="lbPrev()" class="absolute left-4 md:left-8 text-white p-4 hover:bg-white/10 rounded-full transition-colors text-3xl"><i class="fas fa-chevron-left"></i></button>
          <button onclick="lbNext()" class="absolute right-4 md:right-8 text-white p-4 hover:bg-white/10 rounded-full transition-colors text-3xl"><i class="fas fa-chevron-right"></i></button>
      </div>
      
      <div class="text-white/50 mt-4 text-sm font-mono">
          <span id="lb-counter">1</span> / <span id="lb-total">1</span>
      </div>
  </div>

  <style>
      .group-carousel:hover .group-carousel-hover\:opacity-100 { opacity: 1; }
  </style>

  <script>
    function openSidebar() { document.getElementById('sidebar').classList.remove('-translate-x-full'); }
    function closeSidebar() { document.getElementById('sidebar').classList.add('-translate-x-full'); }

    // --- CAROUSEL LOGIC ---
    function nextSlide(containerId) {
        const container = document.getElementById(containerId);
        const images = container.querySelectorAll('img');
        const indicators = container.querySelectorAll('[data-indicator]');
        let activeIndex = 0;
        
        images.forEach((img, i) => {
            if (img.classList.contains('opacity-100')) activeIndex = i;
            img.classList.remove('opacity-100', 'relative');
            img.classList.add('opacity-0', 'absolute');
            
            if(indicators.length > 0) indicators[i].classList.remove('bg-white');
            if(indicators.length > 0) indicators[i].classList.add('bg-white/50');
        });
        
        const nextIndex = (activeIndex + 1) % images.length;
        images[nextIndex].classList.remove('opacity-0', 'absolute');
        images[nextIndex].classList.add('opacity-100', 'relative');
        
        if(indicators.length > 0) {
            indicators[nextIndex].classList.remove('bg-white/50');
            indicators[nextIndex].classList.add('bg-white');
        }
    }

    function prevSlide(containerId) {
        const container = document.getElementById(containerId);
        const images = container.querySelectorAll('img');
        const indicators = container.querySelectorAll('[data-indicator]');
        let activeIndex = 0;
        
        images.forEach((img, i) => {
            if (img.classList.contains('opacity-100')) activeIndex = i;
            img.classList.remove('opacity-100', 'relative');
            img.classList.add('opacity-0', 'absolute');
            
            if(indicators.length > 0) indicators[i].classList.remove('bg-white');
            if(indicators.length > 0) indicators[i].classList.add('bg-white/50');
        });
        
        const prevIndex = (activeIndex - 1 + images.length) % images.length;
        images[prevIndex].classList.remove('opacity-0', 'absolute');
        images[prevIndex].classList.add('opacity-100', 'relative');
        
        if(indicators.length > 0) {
            indicators[prevIndex].classList.remove('bg-white/50');
            indicators[prevIndex].classList.add('bg-white');
        }
    }

    // Auto-play carousels
    setInterval(() => {
        const carousels = document.querySelectorAll('[id^="carousel-"]');
        carousels.forEach(c => {
             // Only auto-play if multiple images and not hovering
             if(c.querySelectorAll('img').length > 1 && !c.matches(':hover')) {
                 nextSlide(c.id);
             }
        });
    }, 4000); // Change slide every 4 seconds


    // --- LIGHTBOX LOGIC ---
    let currentLbImages = [];
    let currentLbIndex = 0;
    const lightbox = document.getElementById('lightbox');
    const lbImg = document.getElementById('lightbox-img');
    const lbCounter = document.getElementById('lb-counter');
    const lbTotal = document.getElementById('lb-total');

    function openLightbox(images) {
        if (!images || images.length === 0) return;
        currentLbImages = images;
        currentLbIndex = 0;
        updateLightbox();
        lightbox.classList.remove('hidden');
        setTimeout(() => lightbox.classList.remove('opacity-0'), 10);
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.add('opacity-0');
        setTimeout(() => {
            lightbox.classList.add('hidden');
            currentLbImages = [];
        }, 300);
        document.body.style.overflow = '';
    }

    function updateLightbox() {
        lbImg.src = currentLbImages[currentLbIndex];
        lbCounter.innerText = currentLbIndex + 1;
        lbTotal.innerText = currentLbImages.length;
    }

    function lbNext() {
        currentLbIndex = (currentLbIndex + 1) % currentLbImages.length;
        updateLightbox();
    }

    function lbPrev() {
        currentLbIndex = (currentLbIndex - 1 + currentLbImages.length) % currentLbImages.length;
        updateLightbox();
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (lightbox.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') lbNext();
        if (e.key === 'ArrowLeft') lbPrev();
    });
  </script>

</body>
</html>
