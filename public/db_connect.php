<?php
// Ganti dengan data dari Supabase
$host     = "db.yajtyhtfnbybeghfflxp.supabase.co";
$port     = "5432";
$dbname   = "postgres"; // default dari Supabase
$user     = "postgres";
$password = "ramakevin12";

// Buat koneksi PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Cek koneksi
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
?>
