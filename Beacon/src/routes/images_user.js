const express = require("express");
const line = require("@line/bot-sdk");
const { config } = require("../config");

module.exports = (pool) => {
  const router = express.Router();

  // Endpoint เพื่อดึงรูปโปรไฟล์
  router.get("/api/images", async (req, res) => {
    try {
      const connection = await pool.getConnection();
      const [rows] = await connection.execute(`
        SELECT DISTINCT user_id FROM beacon_events
      `);
      connection.release();

      const client = new line.Client(config);
      const profilePromises = rows.map(async (row) => {
        try {
          const profile = await client.getProfile(row.user_id);
          return { user_id: row.user_id, pictureUrl: profile.pictureUrl };
        } catch (err) {
          console.error(`Error fetching profile for user_id ${row.user_id}:`, err);
          return { user_id: row.user_id, pictureUrl: "default.jpg" }; // ใช้รูปภาพเริ่มต้นถ้าไม่สามารถดึงรูปได้
        }
      });

      const profiles = await Promise.all(profilePromises);
      res.json(profiles);
    } catch (err) {
      console.error("Database error:", err);
      res.status(500).send("Database error");
    }
  });

  // Endpoint เพื่อดึงข้อมูล events
  router.get("/events", async (req, res) => {
    try {
      const connection = await pool.getConnection();
      const [rows] = await connection.execute(`
        SELECT user_id, MAX(datetimeregis) AS latest, dm, status
        FROM beacon_events
        GROUP BY user_id, dm, status
        ORDER BY latest DESC
        LIMIT 500
      `);
      connection.release();

      res.json(rows);
    } catch (err) {
      console.error("Database error:", err);
      res.status(500).send("Database error");
    }
  });

  return router;
};
