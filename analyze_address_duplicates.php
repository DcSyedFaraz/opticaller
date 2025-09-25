<?php
// Read the address log file and parse JSON entries
$logFile = 'c:\Users\Desktop\AppData\Local\Temp\tmp-13628-sRlQN60STIuj\address-2025-09-25.log';
$content = file_get_contents($logFile);
$lines = explode("\n", trim($content));

$addressCounts = [];
$addressDetails = [];

foreach ($lines as $line) {
    if (empty(trim($line))) continue;

    // Extract JSON part after the timestamp
    $jsonStart = strpos($line, '{');
    if ($jsonStart === false) continue;

    $jsonPart = substr($line, $jsonStart);
    $data = json_decode($jsonPart, true);

    if ($data && isset($data['address']['id'])) {
        $addressId = $data['address']['id'];
        $companyName = $data['address']['company_name'] ?? 'Unknown';
        $timestamp = substr($line, 1, 19); // Extract timestamp
        $userId = $data['user_id'] ?? 'Unknown';

        if (!isset($addressCounts[$addressId])) {
            $addressCounts[$addressId] = 0;
            $addressDetails[$addressId] = [
                'company_name' => $companyName,
                'loads' => []
            ];
        }

        $addressCounts[$addressId]++;
        $addressDetails[$addressId]['loads'][] = [
            'timestamp' => $timestamp,
            'user_id' => $userId
        ];
    }
}

// Find addresses that loaded more than once
$duplicates = array_filter($addressCounts, function($count) {
    return $count > 1;
});

echo "Addresses that loaded more than once:\n";
echo "=====================================\n";
echo "Total addresses processed: " . count($addressCounts) . "\n";
echo "Addresses loaded multiple times: " . count($duplicates) . "\n\n";

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

// Summary statistics
$totalLoads = array_sum($addressCounts);
$uniqueAddresses = count($addressCounts);
$duplicateLoads = array_sum($duplicates);

echo "Summary:\n";
echo "========\n";
echo "Total address loads: {$totalLoads}\n";
echo "Unique addresses: {$uniqueAddresses}\n";
echo "Duplicate loads: {$duplicateLoads}\n";
echo "Percentage of duplicate loads: " . round(($duplicateLoads / $totalLoads) * 100, 2) . "%\n";
?>
