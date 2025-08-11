-- Carousel Management Table Migration
-- This table stores carousel items for each campus

CREATE TABLE IF NOT EXISTS carousel_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campus_id INT NOT NULL,
    title VARCHAR(255) NULL,
    description TEXT NULL,
    image_path VARCHAR(500) NOT NULL,
    image_alt VARCHAR(255) NULL,
    link_url VARCHAR(500) NULL,
    link_target ENUM('_self', '_blank') DEFAULT '_self',
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_campus_id (campus_id),
    INDEX idx_active_order (campus_id, is_active, display_order),
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE CASCADE
);

-- Insert sample carousel items for Andrews Campus
INSERT INTO carousel_items (campus_id, title, description, image_path, image_alt, display_order, is_active) VALUES
(1, 'IZN 2025 Innovation Summit', 'Leading the future of technology and innovation at Andrews Campus.', '../../public/img/izn2025 (1).jpg', 'IZN 2025 Event', 1, TRUE),
(1, 'Level 3 AA Cup Championship', 'Excellence in sports and athletic achievement representing our campus pride.', '../../public/img/level3aacup (1).jpg', 'Level 3 AA Cup Championship', 2, TRUE),
(1, 'MTLE March 2025', 'Major Teacher Learning Enhancement program advancing educational excellence.', '../../public/img/MTLEMARCH2025 (1).jpg', 'MTLE March 2025', 3, TRUE);

-- Insert sample carousel items for Carig Campus
INSERT INTO carousel_items (campus_id, title, description, image_path, image_alt, display_order, is_active) VALUES
(2, 'MTLE March 2025', 'Major Teacher Learning Enhancement program at the main campus - advancing educational excellence.', '../../public/img/MTLEMARCH2025 (1).jpg', 'MTLE March 2025', 1, TRUE),
(2, 'Level 3 AA Cup Championship', 'Athletic excellence and sportsmanship representing the main campus in regional competitions.', '../../public/img/level3aacup (1).jpg', 'Level 3 AA Cup Championship', 2, TRUE),
(2, 'IZN 2025 Innovation Summit', 'Leading research and innovation initiatives at the flagship campus of CSU.', '../../public/img/izn2025 (1).jpg', 'IZN 2025 Event', 3, TRUE);
