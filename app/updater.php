<?php
// URL de la API
$apiUrl = "https://www.thesportsdb.com/api/v1/json/3/eventsseason.php?id=4328&s=2014-2015";

// Consumir la API con cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpStatus != 200) {
    die("Error al consumir la API. Código HTTP: $httpStatus");
}

// Imprimir la respuesta JSON en pantalla
header('Content-Type: application/json');
echo $response;
?>

