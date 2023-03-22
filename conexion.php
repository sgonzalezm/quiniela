<?php
//Variables para datos de conexion con la BD
$host = 'localhost';
$dbname = 'quiniela';
$username = 'root';
$password = '';

//Bucle try catch
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Set default fetch mode to associative array
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


?>