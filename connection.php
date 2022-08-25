<?php
/**
 * php-test.
 *
 * @author  Mirfayz Nosirov
 * Created: 26.08.2022 / 00:11
 */

// Constants
$servername = "localhost";
$username = "root";
$password = "";

// Database connection
try {
    $conn = new PDO("mysql:host=$servername;dbname=test", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}