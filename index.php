<?php
/**
 * Main Entry Point
 * 
 * Handles routing for the application
 */

// Define base path
define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);

// Auto-configure from example files if needed
setupFromExampleFiles();

// Include required files
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/utilities.php';
require_once 'includes/error_handler.php';

/**
 * Setup configuration files from example files if they don't exist
 */
function setupFromExampleFiles() {
    // Check and setup .htaccess file
    $htaccessFile = BASE_PATH . '.htaccess';
    $htaccessExampleFile = BASE_PATH . '.htaccess.example';
    
    if (!file_exists($htaccessFile) && file_exists($htaccessExampleFile)) {
        // Get current URL path
        $urlPath = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $urlParts = parse_url($_SERVER['REQUEST_URI']);
            if (isset($urlParts['path'])) {
                $path = $urlParts['path'];
                // Remove filename part if present
                $path = preg_replace('#/[^/]*$#', '', $path);
                if ($path !== '/') {
                    $urlPath = $path;
                }
            }
        }
        
        // Read example file
        $htaccessContent = file_get_contents($htaccessExampleFile);
        
        // Update RewriteBase and ErrorDocument paths
        if (!empty($urlPath)) {
            // Update RewriteBase
            $htaccessContent = preg_replace(
                '#RewriteBase\s+/[^\s]*/#', 
                "RewriteBase $urlPath/", 
                $htaccessContent
            );
            
            // Update ErrorDocument paths
            $htaccessContent = preg_replace(
                '#ErrorDocument\s+(\d+)\s+/[^\s]*/#', 
                "ErrorDocument $1 $urlPath/", 
                $htaccessContent
            );
        } else {
            // Root installation
            $htaccessContent = preg_replace('#RewriteBase\s+/[^\s]*/#', 'RewriteBase /', $htaccessContent);
            $htaccessContent = preg_replace('#ErrorDocument\s+(\d+)\s+/[^\s]*/#', 'ErrorDocument $1 /', $htaccessContent);
        }
        
        // Write updated content to .htaccess
        file_put_contents($htaccessFile, $htaccessContent);
    }
    
    // Check and setup .env file
    $envFile = BASE_PATH . '.env';
    $envExampleFile = BASE_PATH . '.env.example';
    
    if (!file_exists($envFile) && file_exists($envExampleFile)) {
        // Determine base URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        
        // Get path from REQUEST_URI
        $urlPath = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $urlParts = parse_url($_SERVER['REQUEST_URI']);
            if (isset($urlParts['path'])) {
                $path = $urlParts['path'];
                // Remove filename part if present
                $path = preg_replace('#/[^/]*$#', '', $path);
                if ($path !== '/') {
                    $urlPath = $path;
                }
            }
        }
        
        $baseUrl = $protocol . $host . $urlPath;
        if (substr($baseUrl, -1) !== '/') {
            $baseUrl .= '/';
        }
        
        // Read example file
        $envContent = file_get_contents($envExampleFile);
        
        // Update BASE_URL
        $envContent = preg_replace(
            '#BASE_URL\s*=\s*[^\s\n]*#', 
            "BASE_URL=$baseUrl", 
            $envContent
        );
        
        // If no BASE_URL found, add it
        if (strpos($envContent, 'BASE_URL=') === false) {
            $envContent .= "\nBASE_URL=$baseUrl\n";
        }
        
        // Write updated content to .env
        file_put_contents($envFile, $envContent);
    }
}

// Check if it's a short URL via GET parameter (from .htaccess rewrite)
if (isset($_GET['short_url']) && !empty($_GET['short_url'])) {
    $shortCode = $_GET['short_url'];
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT uuid FROM documents WHERE short_url = :short_url");
    $stmt->bindParam(':short_url', $shortCode);
    $stmt->execute();
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($document) {
        // Redirect to view page using UUID
        header("Location: " . BASE_URL . "view.php?uuid=" . $document['uuid']);
        exit;
    } else {
        // Short URL not found
        http_response_code(404);
        include '404.php';
        exit;
    }
}

// Get request URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path for subdirectory installations
$basePath = parse_url(BASE_URL, PHP_URL_PATH);
if ($basePath && $basePath !== '/') {
    // If we're in a subdirectory, remove the base path from the request URI
    if (strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
}

$requestUri = trim($requestUri, '/');

// Handle short URLs
if (preg_match('#^s/([a-zA-Z0-9]+)$#', $requestUri, $matches)) {
    $shortUrl = $matches[1];
    
    // Get document ID from short URL
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT id, uuid FROM documents WHERE short_url = :short_url");
    $stmt->bindParam(':short_url', $shortUrl);
    $stmt->execute();
    $document = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($document) {
        // Redirect to view page using UUID if available, otherwise fallback to ID
        if (!empty($document['uuid'])) {
            header("Location: " . BASE_URL . "view.php?uuid=" . $document['uuid']);
        } else {
            header("Location: " . BASE_URL . "view.php?id=" . $document['id']);
        }
        exit;
    } else {
        // Short URL not found
        http_response_code(404);
        include '404.php';
        exit;
    }
}

// Handle QR code requests
if (preg_match('#^qr/([0-9]+)$#', $requestUri, $matches)) {
    $_GET['id'] = $matches[1];
    include 'qr.php';
    exit;
}

// Handle admin routes
if (strpos($requestUri, 'admin') === 0) {
    $adminPath = substr($requestUri, 5);
    
    if (empty($adminPath) || $adminPath === '/') {
        include 'admin/index.php';
        exit;
    }
    
    $adminFile = 'admin' . $adminPath . '.php';
    if (file_exists($adminFile)) {
        include $adminFile;
        exit;
    }
    
    // Admin file not found, show 404
    include '404.php';
    exit;
}

// Default route
switch ($requestUri) {
    case '':
    case 'index.php':
        include 'home.php';
        break;
    case 'view':
    case 'view.php':
        include 'view.php';
        break;
    case 'download':
    case 'download.php':
        include 'download.php';
        break;
    case 'mobile_view':
    case 'mobile_view.php':
        include 'mobile_view.php';
        break;
    default:
        // 404 Not Found
        http_response_code(404);
        include '404.php';
        break;
}
