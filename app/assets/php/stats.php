<?php
header('Content-Type: application/json');

// CPU-Auslastung abrufen
$cpuLoad = shell_exec("top -bn1 | grep 'Cpu(s)' | awk '{print $2 + $4}'");

// RAM-Nutzung abrufen
$ramUsage = shell_exec("free -m | awk 'NR==2{print $3}'");
$ramTotal = shell_exec("free -m | awk 'NR==2{print $2}'");

echo json_encode([
    "cpu" => floatval($cpuLoad),
    "ramUsed" => intval($ramUsage),
    "ramTotal" => intval($ramTotal)
]);
