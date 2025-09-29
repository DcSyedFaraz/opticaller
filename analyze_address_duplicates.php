<?php
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

if ($argc < 2) {
    fwrite(STDERR, "Usage: php analyze_address_duplicates.php <log-file-name>\n");
    exit(1);
}

$logFileName = basename($argv[1]);
$storageDir = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
$logFile = $storageDir . DIRECTORY_SEPARATOR . $logFileName;

if (!is_file($logFile) || !is_readable($logFile)) {
    fwrite(STDERR, "Log file not found or unreadable: {$logFile}\n");
    exit(1);
}

$content = file_get_contents($logFile);
$lines = preg_split('/\r\n|\r|\n/', trim($content));

$addressCounts = [];
$addressDetails = [];

foreach ($lines as $line) {
    if (trim($line) === '') {
        continue;
    }

    $jsonStart = strpos($line, '{');
    if ($jsonStart === false) {
        continue;
    }

    $jsonPart = substr($line, $jsonStart);
    $data = json_decode($jsonPart, true);

    if ($data && isset($data['address']['id'])) {
        $addressId = $data['address']['id'];
        $companyName = $data['address']['company_name'] ?? 'Unknown';
        $timestamp = substr($line, 1, 19);
        $userId = $data['user_id'] ?? 'Unknown';

        if (!isset($addressCounts[$addressId])) {
            $addressCounts[$addressId] = 0;
            $addressDetails[$addressId] = [
                'company_name' => $companyName,
                'loads' => [],
            ];
        }

        $addressCounts[$addressId]++;
        $addressDetails[$addressId]['loads'][] = [
            'timestamp' => $timestamp,
            'user_id' => $userId,
        ];
    }
}

$duplicates = array_filter($addressCounts, function ($count) {
    return $count > 1;
});

$totalLoads = array_sum($addressCounts);
$uniqueAddresses = count($addressCounts);
$duplicateLoads = array_sum($duplicates);
$duplicatePercentage = $totalLoads > 0 ? round(($duplicateLoads / $totalLoads) * 100, 2) : 0.0;

echo "Analyzing log file: {$logFileName}\n";
echo "=================================\n";
echo "Overall addresses in file: {$uniqueAddresses}\n";
echo "Addresses loaded multiple times: " . count($duplicates) . "\n";
echo "Total address loads: {$totalLoads}\n\n";

foreach ($duplicates as $addressId => $count) {
    echo "Address ID: {$addressId}\n";
    echo "Company: " . $addressDetails[$addressId]['company_name'] . "\n";
    echo "Load count: {$count}\n";
    echo "Load times:\n";
    foreach ($addressDetails[$addressId]['loads'] as $load) {
        echo "  - {$load['timestamp']} (User: {$load['user_id']})\n";
    }
    echo "\n";
}

echo "Summary:\n";
echo "========\n";
echo "Total address loads: {$totalLoads}\n";
echo "Unique addresses: {$uniqueAddresses}\n";
echo "Duplicate loads: {$duplicateLoads}\n";
echo "Percentage of duplicate loads: {$duplicatePercentage}%\n";
?>
