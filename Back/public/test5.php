<?php
$ch = curl_init('http://localhost/api/users');
$data = json_encode([
    'nickname' => 'test_user_from_php',
    'email' => 'test333@test.com',
    'password' => '12345678',
    'terminos_aceptados' => true
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
curl_close($ch);

echo "Response from API: \n" . $response . "\n";

$ch2 = curl_init('http://localhost/api/login');
$data2 = json_encode([
    'email' => 'test333@test.com',
    'password' => '12345678'
]);

curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, $data2);

$response2 = curl_exec($ch2);
curl_close($ch2);

echo "Login Response: \n" . $response2 . "\n";
