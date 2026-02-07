<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (
    !$data ||
    empty($data['from']) ||
    empty($data['to']) ||
    empty($data['day']) ||
    empty($data['start']) ||
    empty($data['end'])
) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid payload',
        'received' => $data
    ]);
    exit;
}

/**
 * Dia base SEMPRE no fuso da empresa (from)
 */
$companyTz = new DateTimeZone($data['from']);
$localTz   = new DateTimeZone($data['to']);

/**
 * Cria a data base no fuso da empresa
 * Ex: "next Monday"
 */
$baseDate = new DateTime("next " . $data['day'], $companyTz);

/**
 * Horários informados (empresa)
 */
[$sh, $sm] = explode(':', $data['start']);
[$eh, $em] = explode(':', $data['end']);

$companyStart = clone $baseDate;
$companyStart->setTime((int)$sh, (int)$sm);

$companyEnd = clone $baseDate;
$companyEnd->setTime((int)$eh, (int)$em);

/**
 * Se o expediente passa da meia-noite no fuso da empresa
 */
if ($companyEnd <= $companyStart) {
    $companyEnd->modify('+1 day');
}

/**
 * Conversão para o fuso local
 */
$localStart = clone $companyStart;
$localEnd   = clone $companyEnd;

$localStart->setTimezone($localTz);
$localEnd->setTimezone($localTz);

/**
 * Helper para dia da semana
 */
function weekday(DateTime $date) {
    return $date->format('l'); // Monday, Tuesday...
}

$companyDate = $companyStart->format('Y-m-d');
$localDate   = $localStart->format('Y-m-d');

if ($localDate < $companyDate) {
    $case = 'company_ahead';
} elseif ($localDate > $companyDate) {
    $case = 'company_behind';
} else {
    $case = 'same_day';
}


echo json_encode([
    'company' => [
        'day' => weekday($companyStart),
        'date' => $companyStart->format('Y-m-d'),
        'start' => $companyStart->format('H:i'),
        'end' => $companyEnd->format('H:i'),
        'timezone' => $data['from']
    ],
    'local' => [
        'day' => weekday($localStart),
        'date' => $localStart->format('Y-m-d'),
        'start' => $localStart->format('H:i'),
        'end' => $localEnd->format('H:i'),
        'timezone' => $data['to']
    ],
    'case' => $case
]);