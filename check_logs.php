<?php
/**
 * Check Laravel Logs for Dead Pigs Sale Test
 */

require __DIR__ . '/vendor/autoload.php';

$logDir = __DIR__ . '/storage/logs';
$files = scandir($logDir, SCANDIR_SORT_DESCENDING);

// หา log file ล่าสุด
$latestLog = null;
foreach ($files as $file) {
    if (strpos($file, 'laravel') !== false && $file !== '.' && $file !== '..') {
        $latestLog = $file;
        break;
    }
}

if (!$latestLog) {
    echo "❌ ไม่พบ log file\n";
    exit(1);
}

$logPath = $logDir . '/' . $latestLog;
echo "📄 Reading log: $latestLog\n";
echo "───────────────────────────────────────\n";

$content = file_get_contents($logPath);
$lines = explode("\n", $content);

// หา debug log ล่าสุด
$deadPigsLogs = [];
foreach ($lines as $line) {
    if (strpos($line, 'Dead Pigs Test') !== false) {
        $deadPigsLogs[] = $line;
    }
}

if (empty($deadPigsLogs)) {
    echo "❌ ไม่พบ 'Dead Pigs Test' ใน log\n";
    echo "📋 ล่าสุด 20 บรรทัด:\n";
    $latest = array_slice($lines, -20);
    foreach ($latest as $line) {
        if (trim($line)) echo "  " . $line . "\n";
    }
} else {
    echo "✅ พบ Dead Pigs Test logs:\n\n";
    foreach (array_slice($deadPigsLogs, -5) as $log) {  // แสดง 5 ล่าสุด
        echo $log . "\n";
    }
}

echo "\n───────────────────────────────────────\n";
echo "✅ Done\n";
?>
