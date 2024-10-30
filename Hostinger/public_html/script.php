<?php
$servername = "82.197.83.2";
$username = "u897086378_LINEBEACONDB";
$password = "@Devilkung123";
$dbname = "u897086378_LINEBEACONDB";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึง user_id จากตาราง beacon_events และ group by
$sql = "SELECT user_id FROM beacon_events GROUP BY user_id";
$result = $conn->query($sql);

$channelAccessToken = '42q4Xrfh9WolaQu8L4UCK4kOU/slDFusUtjz0iKZO+tUtKuxpEaZhEOGBDRKUzXOgXjVz84DkHuhp1+It/z1Zp8EagGSbit/UWr3yRMRNcgsTceSUheeglSRYQcmw8nIcAnxYiXYB8icxRHhxx3IEQdB04t89/1O/w1cDnyilFU=';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userId = $row["user_id"];
        
        // ใช้ cURL เพื่อดึงข้อมูลโปรไฟล์จาก Line API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/bot/profile/" . $userId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $channelAccessToken
        ));
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch) . "\n";
            curl_close($ch);
            continue;
        }
        
        curl_close($ch);
        
        $profileData = json_decode($response, true);
        
        if ($profileData === null) {
            echo "Failed to decode JSON response for userId $userId: $response\n";
        } elseif (isset($profileData['message']) && $profileData['message'] === 'Not found') {
            // Log userId ที่ไม่พบในไฟล์หรือฐานข้อมูล
            echo "UserId not found in Line API: $userId\n";
        } elseif (isset($profileData['userId'], $profileData['displayName'], $profileData['pictureUrl'])) {
            $userId = $conn->real_escape_string($profileData['userId']);
            $displayName = $conn->real_escape_string($profileData['displayName']);
            $pictureUrl = $conn->real_escape_string($profileData['pictureUrl']);

            // ตรวจสอบข้อมูลในฐานข้อมูลด้วย ON DUPLICATE KEY UPDATE
            $insertSql = "INSERT INTO beacon_info_student (userId, displayName, pictureUrl) 
                          VALUES ('$userId', '$displayName', '$pictureUrl')
                          ON DUPLICATE KEY UPDATE displayName='$displayName', pictureUrl='$pictureUrl'";

            if ($conn->query($insertSql) === TRUE) {
                echo "Record updated for user: $userId\n";
            } else {
                echo "Error: " . $conn->error . "\n";
            }
        } else {
            echo "No valid data found for user: $userId\n";
        }
    }
} else {
    echo "No records found.\n";
}

$conn->close();
?>
