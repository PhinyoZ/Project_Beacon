const line = require("@line/bot-sdk");
const { config } = require("../config");

const client = new line.Client(config);

async function lineHandler(event, pool) {
  if (event.type === "beacon") {
    const { userId } = event.source;
    const { hwid, dm, type } = event.beacon;
    const datetimeregis = new Date();

    // Map dm values
    let dmText;
    if (dm === "a0a3b33145fe") {
      dmText = "ทางขึ้นบันได (เช็คชื่อเข้าหอพัก)";
    } else if (dm === "a0a3b32fadf2") {
      dmText = "ประตู (เช็คชื่อออกหอพัก)";
    } else {
      dmText = "Check in.";
    }

    // Get user profile to append the name
    let profileName = "";
    try {
      const profile = await client.getProfile(userId);
      profileName = profile.displayName;
    } catch (err) {
      console.error("Failed to get profile:", err);
    }

    const finalText = `${dmText} ${profileName}`;

    try {
      const connection = await pool.getConnection();
      await connection.execute(
        "INSERT INTO beacon_events (user_id, hwid, type, dm, datetimeregis) VALUES (?, ?, ?, ?, ?)",
        [userId, hwid, type, dm, datetimeregis]
      );
      connection.release();
    } catch (err) {
      console.error("Database error:", err);
    }

    return client.replyMessage(event.replyToken, {
      type: "text",
      text: finalText,
    });
  }
  return Promise.resolve(null);
}

module.exports = { lineHandler };
