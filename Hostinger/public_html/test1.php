<?php
function getUserStatus($userId, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT user_id, dm, datetimeregis
            FROM beacon_events
            WHERE user_id = ?
            AND (dm = 'a0a3b32fadf2' OR dm = 'a0a3b32f6ed2')
            ORDER BY datetimeregis ASC
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) < 2) {
            return json_encode(['status' => 'ไม่สามารถคำนวณสถานะได้']);
        }

        $status = "ไม่สามารถคำนวณสถานะได้";
        $lastEntry = null;

        foreach ($rows as $event) {
            if ($event['dm'] === "a0a3b32fadf2") {
                // เข้า
                $lastEntry = $event;
            } elseif ($event['dm'] === "a0a3b32f6ed2" && $lastEntry) {
                // ออก
                if (strtotime($lastEntry['datetimeregis']) < strtotime($event['datetimeregis'])) {
                    $status = "ไม่อยู่ในหอพัก";
                } else {
                    $status = "อยู่ในหอพัก";
                }
                $lastEntry = null; // รีเซ็ตหลังจากจับคู่แล้ว
            }
        }

        if ($lastEntry) {
            $status = "อยู่ในหอพัก";
        }

        return json_encode(['status' => $status]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        return "Database error";
    }
}

// การใช้งานฟังก์ชัน
header('Content-Type: application/json');

$dsn = 'mysql:host=your_host;dbname=your_dbname;charset=utf8';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['userId'])) {
        $userId = $_GET['userId'];
        echo getUserStatus($userId, $pdo);
    } else {
        echo json_encode(['status' => 'Invalid request']);
    }
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    http_response_code(500);
    echo "Connection failed";
}
