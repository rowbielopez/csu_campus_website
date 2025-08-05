<?php
/**
 * Campus Frontend Cache Helper
 * Simple caching mechanism for campus configurations and content
 */

class CampusCache {
    private static $cache_dir = '';
    private static $cache_enabled = true;
    private static $cache_duration = 3600; // 1 hour
    
    public static function init($cache_dir = null) {
        self::$cache_dir = $cache_dir ?: __DIR__ . '/../../cache';
        
        // Create cache directory if it doesn't exist
        if (!is_dir(self::$cache_dir)) {
            mkdir(self::$cache_dir, 0755, true);
        }
    }
    
    /**
     * Get cached data
     */
    public static function get($key) {
        if (!self::$cache_enabled) {
            return null;
        }
        
        $cache_file = self::getCacheFile($key);
        
        if (!file_exists($cache_file)) {
            return null;
        }
        
        $cache_data = unserialize(file_get_contents($cache_file));
        
        // Check if cache is expired
        if (time() > $cache_data['expires']) {
            unlink($cache_file);
            return null;
        }
        
        return $cache_data['data'];
    }
    
    /**
     * Set cache data
     */
    public static function set($key, $data, $duration = null) {
        if (!self::$cache_enabled) {
            return false;
        }
        
        $duration = $duration ?: self::$cache_duration;
        $cache_file = self::getCacheFile($key);
        
        $cache_data = [
            'data' => $data,
            'expires' => time() + $duration,
            'created' => time()
        ];
        
        return file_put_contents($cache_file, serialize($cache_data)) !== false;
    }
    
    /**
     * Delete cached data
     */
    public static function delete($key) {
        $cache_file = self::getCacheFile($key);
        
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     */
    public static function clear() {
        $files = glob(self::$cache_dir . '/*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Get cache file path
     */
    private static function getCacheFile($key) {
        return self::$cache_dir . '/' . md5($key) . '.cache';
    }
    
    /**
     * Enable/disable caching
     */
    public static function setEnabled($enabled) {
        self::$cache_enabled = $enabled;
    }
    
    /**
     * Set cache duration
     */
    public static function setDuration($duration) {
        self::$cache_duration = $duration;
    }
}

/**
 * Cached version of campus configuration
 */
function get_cached_campus_config($campus_id = null) {
    CampusCache::init();
    
    $cache_key = 'campus_config_' . ($campus_id ?: (defined('CURRENT_CAMPUS_ID') ? CURRENT_CAMPUS_ID : 1));
    
    // Try to get from cache first
    $campus_config = CampusCache::get($cache_key);
    
    if ($campus_config === null) {
        // Cache miss - get from database
        $campus_config = get_campus_config();
        
        // Cache for 1 hour
        CampusCache::set($cache_key, $campus_config, 3600);
    }
    
    return $campus_config;
}

/**
 * Cached version of campus posts
 */
function get_cached_campus_posts($campus_id, $limit = 10, $offset = 0, $featured_only = false) {
    CampusCache::init();
    
    $cache_key = "campus_posts_{$campus_id}_{$limit}_{$offset}_" . ($featured_only ? 'featured' : 'all');
    
    // Try to get from cache first
    $posts = CampusCache::get($cache_key);
    
    if ($posts === null) {
        // Cache miss - get from database
        $posts = get_campus_posts($limit, $offset, $featured_only);
        
        // Cache for 30 minutes
        CampusCache::set($cache_key, $posts, 1800);
    }
    
    return $posts;
}

/**
 * Clear campus-specific cache
 */
function clear_campus_cache($campus_id = null) {
    CampusCache::init();
    
    if ($campus_id) {
        // Clear specific campus cache
        CampusCache::delete('campus_config_' . $campus_id);
        
        // Clear posts cache for this campus
        $cache_dir = __DIR__ . '/../../cache';
        $files = glob($cache_dir . '/*.cache');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data = unserialize($content);
            
            // Check if this cache file contains data for this campus
            if (isset($data['data']) && is_array($data['data'])) {
                $filename = basename($file);
                if (strpos($filename, "campus_posts_{$campus_id}_") !== false) {
                    unlink($file);
                }
            }
        }
    } else {
        // Clear all cache
        CampusCache::clear();
    }
}

/**
 * Minify HTML output
 */
function minify_html($html) {
    // Remove comments
    $html = preg_replace('/<!--.*?-->/s', '', $html);
    
    // Remove extra whitespace
    $html = preg_replace('/\s+/', ' ', $html);
    
    // Remove whitespace around tags
    $html = preg_replace('/>\s+</', '><', $html);
    
    return trim($html);
}

/**
 * Start output buffering with compression
 */
function start_output_compression() {
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ob_start('ob_gzhandler');
    } else {
        ob_start();
    }
}

/**
 * End output buffering and minify if needed
 */
function end_output_compression($minify = true) {
    $content = ob_get_contents();
    ob_end_clean();
    
    if ($minify) {
        $content = minify_html($content);
    }
    
    echo $content;
}
