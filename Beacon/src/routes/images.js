const express = require("express");
const line = require("@line/bot-sdk");
const { config } = require("../config");

module.exports = (pool) => {
  const router = express.Router();

  router.get("/", async (req, res) => {
    try {
      const connection = await pool.getConnection();
      const [rows] = await connection.execute(`
        SELECT DISTINCT user_id FROM beacon_events
      `);
      connection.release();

      const client = new line.Client(config);
      const profilePromises = rows.map(async (row) => {
        const profile = await client.getProfile(row.user_id);
        return { user_id: row.user_id, pictureUrl: profile.pictureUrl };
      });

      const profiles = await Promise.all(profilePromises);
      res.json(profiles);
    } catch (err) {
      console.error("Database error:", err);
      res.status(500).send("Database error");
    }
  });

  return router;
};
