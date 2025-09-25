<?php
/**
 * Download endpoint for webapp files
 */

// Security check - simple token to prevent unauthorized downloads
$valid_tokens = ['download123', 'webapp-dl', 'ad-manager-dl'];
$token = $_GET['token'] ?? '';

if (!in_array($token, $valid_tokens)) {
    http_response_code(403);
    die('Acesso negado. Token inválido.');
}

$file = 'webapp-ad-manager.tar.gz';
$filepath = __DIR__ . '/' . $file;

if (!file_exists($filepath)) {
    http_response_code(404);
    die('Arquivo não encontrado.');
}

// Set headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/gzip');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

// Clear output buffer
ob_clean();
flush();

// Read and output file
readfile($filepath);
exit;
?>