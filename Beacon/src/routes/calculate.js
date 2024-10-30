const express = require("express");

module.exports = (pool) => {
  const router = express.Router();

  router.get("/:userId", async (req, res) => {
    const { userId } = req.params;
    try {
      const connection = await pool.getConnection();
      const [rows] = await connection.execute(
        `
        SELECT user_id, dm, datetimeregis
        FROM beacon_events
        WHERE user_id = ?
        AND (dm = 'a0a3b32fadf2' OR dm = 'a0a3b33145fe')
        ORDER BY datetimeregis ASC
      `,
        [userId]
      );
      connection.release();

      if (rows.length < 2) {
        return res.status(200).json({ status: "ไม่สามารถคำนวณสถานะได้" });
      }

      let status = "ไม่สามารถคำนวณสถานะได้";
      let lastEntry = null;

      rows.forEach((event) => {
        if (event.dm === "a0a3b32fadf2") {
          // ประตู
          lastEntry = event;
        } else if (event.dm === "a0a3b33145fe" && lastEntry) {
          // ทางขึ้นบรรได
          if (
            new Date(lastEntry.datetimeregis) < new Date(event.datetimeregis)
          ) {
            status = "ไม่อยู่ในหอพัก";
          } else {
            status = "อยู่ในหอพัก";
          }
          lastEntry = null; // รีเซ็ตหลังจากจับคู่แล้ว
        }
      });

      if (lastEntry) {
        status = "อยู่ในหอพัก";
      }

      return res.json({ status });
    } catch (err) {
      console.error("Database error:", err);
      res.status(500).send("Database error");
    }
  });

  return router;
};
