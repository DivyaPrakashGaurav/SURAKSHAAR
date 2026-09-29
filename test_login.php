<?php
$url = 'http://localhost/SIH26041admin/api/v1/auth/login.php';
$data = [
    'worker_id' => 'TEST-001',
    'name' => 'Test Worker',
    'language' => 'English',
    'role' => 'Worker',
    'site' => 'Mining Site'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
echo "Response: $response\n";
curl_close($ch);
