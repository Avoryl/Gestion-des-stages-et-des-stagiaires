<?php
require_once 'config.php';

$file = isset($_GET['file']) ? basename((string) $_GET['file']) : '';

if ($file === '' || strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'pdf') {
    http_response_code(400);
    exit('Fichier invalide.');
}

$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $file;

if (!is_file($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    exit('Fichier introuvable.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . rawurlencode($file) . '"');
header('Content-Length: ' . filesize($filePath));
header('Content-Transfer-Encoding: binary');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, must-revalidate');
header('Pragma: public');

readfile($filePath);
exit;