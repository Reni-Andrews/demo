<?php

$database_host = "localhost";
$username = "root";
$password = "";
$database_name = "sample";

// Create connection
$conn = new mysqli($database_host, $username, $password, $database_name);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}