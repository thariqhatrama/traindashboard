<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "train_monitoring";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get parameters
$checkpoint = $_GET['checkpoint'];
$status = $_GET['status'];

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO train_logs (checkpoint, status) VALUES (?, ?)");
$stmt->bind_param("ss", $checkpoint, $status);

// Execute and check
if ($stmt->execute()) {
    echo "Data recorded successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>