const express = require("express");
const line = require("@line/bot-sdk");
const { config } = require("../config");

module.exports = (pool) => {
  const router = express.Router();

  router.get("/", async (req, res) => {
    let connection;
    try {
      connection = await pool.getConnection(); // เชื่อมต่อฐานข้อมูล
      console.log("Database connection established.");

      // ใช้ LEFT JOIN เพื่อดึงข้อมูลจาก beacon_events และ student_info โดยให้แสดงทุกคนที่เคยเข้า ไม่ว่าจะมี room_number หรือไม่
      const [rows] = await connection.execute(`
        SELECT beacon_events.user_id, MAX(beacon_events.datetimeregis) AS latest, beacon_events.dm, student_info.room_number
        FROM beacon_events
        LEFT JOIN student_info ON beacon_events.user_id = student_info.userId
        GROUP BY beacon_events.user_id, beacon_events.dm
        ORDER BY latest DESC
        LIMIT 500
      `);

      console.log("Data fetched from database:", rows);

      const client = new line.Client(config);

      // ดึงข้อมูลโปรไฟล์จาก LINE API พร้อมกับข้อมูลที่ดึงจากฐานข้อมูล
      const profilePromises = rows.map(async (row) => {
        try {
          const profile = await client.getProfile(row.user_id); // ดึงข้อมูลโปรไฟล์จาก LINE API
          console.log(
            "Fetched profile from LINE API for user_id:",
            row.user_id,
            profile
          );
          return {
            user_id: row.user_id,
            displayName: profile.displayName,
            pictureUrl: profile.pictureUrl,
            latest: row.latest,
            dm: row.dm,
            room_number: row.room_number || "ยังไม่ได้ลงทะเบียน", // ถ้าไม่มี room_number ให้แสดงว่า "ยังไม่ได้ลงทะเบียนห้อง"
          };
        } catch (lineError) {
          console.error("Error fetching profile from LINE API:", lineError);
          // ในกรณีที่เกิดข้อผิดพลาดกับ LINE API, คืนค่าข้อมูลที่ไม่สมบูรณ์ (หรือกำหนดค่าเริ่มต้น)
          return {
            user_id: row.user_id,
            displayName: "Unknown",
            pictureUrl: "default.jpg", // ใช้รูปภาพเริ่มต้นหากไม่สามารถดึงรูปได้
            latest: row.latest,
            dm: row.dm,
            room_number: row.room_number || "ยังไม่ได้ลงทะเบียน", // ส่งคืน room_number จากฐานข้อมูลหรือแสดงว่า "ยังไม่ได้ลงทะเบียนห้อง"
          };
        }
      });

      const profiles = await Promise.all(profilePromises); // รอให้ทุกคำสั่งเสร็จสิ้น
      console.log("Profiles prepared:", profiles);

      res.json(profiles);
    } catch (err) {
      console.error("Database error:", err);
      res.status(500).send("Database error");
    } finally {
      if (connection) {
        connection.release(); // ปิดการเชื่อมต่อหรือคืนการเชื่อมต่อกลับไปยัง pool
        console.log("Database connection released.");
      }
    }
  });

  return router;
};
