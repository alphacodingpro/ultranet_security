<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/includes/budget-package.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'Use POST.']); exit; }
if (!checkRateLimit('budget_calculator', 30, 300)) { http_response_code(429); echo json_encode(['ok'=>false,'error'=>'Please wait a few minutes before trying again.']); exit; }
try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) throw new InvalidArgumentException('Invalid request.');
    echo json_encode(['ok'=>true,'package'=>buildBudgetPackage($input)]);
} catch (InvalidArgumentException $e) {
    http_response_code(422); echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(503); echo json_encode(['ok'=>false,'error'=>'Catalog temporarily unavailable. Please try again.']);
} catch (RuntimeException $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
