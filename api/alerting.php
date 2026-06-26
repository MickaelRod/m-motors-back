<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';

initialiser_cors_json();
demarrer_session_admin();
exiger_admin();

verifier_methode('GET');

$niveau  = isset($_GET['niveau']) ? strtoupper(trim($_GET['niveau'])) : '';
$niveaux_valides = ['INFO', 'WARNING', 'ERROR'];
$lignes_max = 200;

$fichier_log = __DIR__ . '/../logs/app.log';

if (!file_exists($fichier_log)) {
    echo json_encode(["entrees" => []]);
    exit();
}

$lignes = file($fichier_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$lignes = array_reverse($lignes);

$entrees = [];
foreach ($lignes as $ligne) {
    // Format : [2026-06-26 15:32:11] [ERROR] contexte — message
    if (!preg_match('/^\[(.+?)\] \[(.+?)\] (.+?) — (.+)$/', $ligne, $correspondances)) {
        continue;
    }

    $niv = $correspondances[2];

    if (!empty($niveau) && in_array($niveau, $niveaux_valides, true) && $niv !== $niveau) {
        continue;
    }

    $entrees[] = [
        'date'     => $correspondances[1],
        'niveau'   => $niv,
        'contexte' => $correspondances[3],
        'message'  => $correspondances[4],
    ];

    if (count($entrees) >= $lignes_max) {
        break;
    }
}

echo json_encode(["entrees" => $entrees]);
