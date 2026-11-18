<?php
declare(strict_types=1);

// Mount behind Source 1 authentication, authorization and CSRF middleware.
require_once __DIR__ . '/../src/CampaignPlanner.php';

use Vox\SmartEngine\CampaignPlanner;

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required']);
    exit;
}
$raw = file_get_contents('php://input');
try {
    $input = json_decode($raw ?: '{}', true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($input)) throw new InvalidArgumentException('JSON object required');
    if (($input['goal'] ?? 'publish_content') !== 'publish_content') {
        throw new InvalidArgumentException('Only publish_content campaigns are supported');
    }
    $result = (new CampaignPlanner())->preview($input);
    echo json_encode(['ok' => true, 'preview' => $result], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
