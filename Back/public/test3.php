<?php
// Call the login API endpoint directly.

$ch = curl_init('http://localhost/api/login');
$data = json_encode([
    'email' => 'admin@codeo.com',
    'password' => '12341234'
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
curl_close($ch);

echo "Response from API: \n" . $response . "\n";
