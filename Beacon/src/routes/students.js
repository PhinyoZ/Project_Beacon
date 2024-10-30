const express = require("express");

module.exports = (pool) => {
  const router = express.Router();

  // Route สำหรับดึงข้อมูลนักศึกษาทั้งหมด
  router.get("/", async (req, res) => {
    let connection;
    try {
      // เชื่อมต่อกับฐานข้อมูล
      connection = await pool.getConnection();

      // ดึงข้อมูลนักศึกษาทั้งหมดจากตาราง student_info
      const [rows] = await connection.execute(`
        SELECT userId, student_id, first_name, last_name, room_number 
        FROM student_info
      `);

      // ส่งข้อมูลกลับเป็น JSON
      res.json(rows);
    } catch (err) {
      console.error("Database error:", err);
      res.status(500).send("Database error");
    } finally {
      if (connection) {
        connection.release(); // คืนการเชื่อมต่อกลับไปยัง pool
      }
    }
  });

  return router;
};
