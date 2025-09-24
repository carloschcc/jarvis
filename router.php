<?php
/**
 * Router for PHP built-in server
 * This file handles static asset serving for development
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files directly
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf)$/', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    
    if (file_exists($filePath)) {
        // Set proper content type
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf'
        ];
        
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        
        header("Content-Type: $mimeType");
        header("Cache-Control: public, max-age=3600");
        readfile($filePath);
        return true; // Static file served
    } else {
        http_response_code(404);
        echo "Static file not found: " . htmlspecialchars($requestUri);
        return true; // Prevent fallback to index.php
    }
}

// Let index.php handle all other requests
return false;