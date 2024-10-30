const express = require("express");

module.exports = (pool) => {
  const router = express.Router();

  // ดึงข้อมูลจากตาราง dm, beacon_name, และ beacon_place
  router.get("/", async (req, res) => {
    let connection;
    try {
      connection = await pool.getConnection(); // เปิดการเชื่อมต่อฐานข้อมูล
      console.log("Connected to database.");

      // ดึงข้อมูลจากตาราง dm, beacon_name, beacon_place
      const [rows] = await connection.execute(`
        SELECT dm, beacon_name, beacon_place 
        FROM beacon
        ORDER BY dm ASC
      `);

      console.log("Beacon data fetched:", rows);
      res.json(rows); // ส่งข้อมูลออกเป็น JSON
    } catch (err) {
      console.error("Database error:", err);
      res.status(500).send("Database error");
    } finally {
      if (connection) {
        connection.release(); // ปิดการเชื่อมต่อ
        console.log("Database connection released.");
      }
    }
  });

  // API สำหรับอัปเดตข้อมูล dmMeaning ลงในฐานข้อมูล
  router.post("/update-dm-meaning", async (req, res) => {
    let connection;
    try {
      const dmMeaning = req.body; // รับข้อมูล dmMeaning จาก client
      connection = await pool.getConnection();
      console.log("Connected to database for updating dmMeaning.");

      // ทำการอัปเดตข้อมูล dmMeaning ในฐานข้อมูล
      for (const [dm, place_description] of Object.entries(dmMeaning)) {
        await connection.execute(
          `UPDATE beacon_place SET place_description = ? WHERE dm = ?`,
          [place_description, dm]
        );
      }

      console.log("dmMeaning updated in the database.");
      res.status(200).send("dmMeaning updated successfully");
    } catch (err) {
      console.error("Database error during dmMeaning update:", err);
      res.status(500).send("Database error during dmMeaning update");
    } finally {
      if (connection) {
        connection.release();
        console.log("Database connection released after updating dmMeaning.");
      }
    }
  });

  return router;
};
