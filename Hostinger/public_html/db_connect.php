<?php
$servername = "82.197.83.2";
$username = "u897086378_LINEBEACONDB";
$password = "@Devilkung123";
$dbname = "u897086378_LINEBEACONDB";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
?>
