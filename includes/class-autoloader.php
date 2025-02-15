<?php
/**
 * Enhanced Autoloader for WP Equipment Plugin
 *
 * @package     WP_Equipment
 * @subpackage  Includes
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/includes/class-autoloader.php
 */

class WPEquipmentAutoloader {
    private $prefix;
    private $baseDir;
    private $mappings = [];
    private $loadedClasses = [];
    private $debugMode;
    
    public function __construct($prefix, $baseDir) {
        $this->prefix = $prefix;
        $this->baseDir = rtrim($baseDir, '/\\') . '/';
        $this->debugMode = defined('WP_DEBUG') && WP_DEBUG;
        
        // Add default mapping
        $this->addMapping('', 'src/');
    }
    
    public function addMapping($namespace, $directory) {
        $namespace = trim($namespace, '\\');
        $this->mappings[$namespace] = rtrim($directory, '/\\') . '/';
    }
    
    public function register() {
        spl_autoload_register([$this, 'loadClass']);
    }
    
    public function unregister() {
        spl_autoload_unregister([$this, 'loadClass']);
    }
    
    public function loadClass($class) {
        try {
            if (isset($this->loadedClasses[$class])) {
                return true;
            }
            
            if (!$this->isValidClassName($class)) {
                $this->log("Invalid class name format: $class");
                return false;
            }
            
            if (strpos($class, $this->prefix) !== 0) {
                return false;
            }
            
            $relativeClass = substr($class, strlen($this->prefix));
            
            $mappedPath = $this->findMappedPath($relativeClass);
            if (!$mappedPath) {
                $this->log("No mapping found for class: $class");
                return false;
            }
            
            $file = $this->baseDir . $mappedPath;
            
            if (!$this->validateFile($file)) {
                $this->log("File not found or not readable: $file");
                return false;
            }
            
            require_once $file;
            
            if (!$this->verifyClassLoaded($class)) {
                $this->log("Class $class not found in file $file");
                return false;
            }
            
            $this->loadedClasses[$class] = true;
            $this->log("Successfully loaded class: $class");
            
            return true;
            
        } catch (\Exception $e) {
            $this->log("Error loading class $class: " . $e->getMessage());
            return false;
        }
    }
    
    private function isValidClassName($class) {
        return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\\\\]*$/', $class);
    }
    
    private function findMappedPath($relativeClass) {
        foreach ($this->mappings as $namespace => $directory) {
            if (empty($namespace) || strpos($relativeClass, $namespace) === 0) {
                $classPath = empty($namespace) ? $relativeClass : substr($relativeClass, strlen($namespace));
                return $directory . str_replace('\\', '/', $classPath) . '.php';
            }
        }
        return false;
    }
    
    private function validateFile($file) {
        if (!file_exists($file)) {
            $this->log("File does not exist: $file");
            return false;
        }
        
        if (!is_readable($file)) {
            $this->log("File not readable: $file");
            return false;
        }
        
        return true;
    }
    
    private function verifyClassLoaded($class) {
        return class_exists($class, false) || 
               interface_exists($class, false) || 
               trait_exists($class, false);
    }
    
    private function log($message) {
        if ($this->debugMode) {
            // error_log("[WPEquipmentAutoloader] $message");
        }
    }
    
    public function getLoadedClasses() {
        return array_keys($this->loadedClasses);
    }
    
    public function clearCache() {
        $this->loadedClasses = [];
    }
}
