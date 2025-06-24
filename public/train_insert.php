<?php

// Supabase PostgreSQL credentials
$host = "db.yajtyhtfnbybeghfflxp.supabase.co";      // ganti dengan hostname Supabase Anda
$user = "postgres";                // default username Supabase
$password = "ramakevin12";   // ganti dengan password Supabase Anda
$dbname = "postgres";              // default dbname di Supabase
$port = 5432;

// Buat koneksi
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Ambil parameter dari GET
$checkpoint = $_GET['checkpoint'] ?? '';
$status = $_GET['status'] ?? '';

// Query dengan prepared statement
$result = pg_query_params($conn, "INSERT INTO train_logs (checkpoint, status) VALUES ($1, $2)", [$checkpoint, $status]);

if ($result) {
    echo "Data recorded successfully";
} else {
    echo "Error: " . pg_last_error($conn);
}

pg_close($conn);
?>
