-- Database: attendance_db

-- 1. Batches Table
CREATE TABLE IF NOT EXISTS batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_year VARCHAR(20) NOT NULL UNIQUE, -- e.g. "2023-2026"
    label VARCHAR(100) NOT NULL, -- e.g. "Class of 2026"
    image_url VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1000&auto=format&fit=crop',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Insert Initial Batches (Ignore if exist)
INSERT IGNORE INTO batches (batch_year, label, image_url) VALUES 
('2023-2026', 'Class of 2026', 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1000&auto=format&fit=crop'),
('2024-2027', 'Class of 2027', 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=1000&auto=format&fit=crop'),
('2025-2028', 'Class of 2028', 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?q=80&w=1000&auto=format&fit=crop');
