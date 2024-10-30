const express = require("express");
const line = require("@line/bot-sdk");
const { config, dbConfig } = require("./src/config");
const { lineHandler } = require("./src/handlers/lineHandler");
const indexRoutes = require("./src/routes/index");
const eventsRoutes = require("./src/routes/events");
const imagesRoutes = require("./src/routes/images");
const calculateRoutes = require("./src/routes/calculate");
const studentsRouter = require("./src/routes/students");
const mysql = require("mysql2/promise");
const beaconRoutes = require("./src/routes/beacon");

const app = express();
app.use(express.static("public"));

const pool = mysql.createPool(dbConfig);

// Routes
app.use("/", indexRoutes);
app.use("/events", eventsRoutes(pool));
app.use("/api/images", imagesRoutes(pool));
app.use("/api/images_user", imagesRoutes(pool));
app.use("/calculate", calculateRoutes(pool));
app.use("/students", studentsRouter(pool));
app.use("/beacons", beaconRoutes(pool)); // เปลี่ยนชื่อเส้นทางเป็น "/beacons"

// LINE webhook
app.post("/webhook", line.middleware(config), (req, res) => {
  Promise.all(req.body.events.map((event) => lineHandler(event, pool)))
    .then((result) => res.json(result))
    .catch((err) => {
      console.error(err);
      res.status(500).end();
    });
});

// Start Server
const port = process.env.PORT || 3000;
app.listen(port, () => {
  console.log(`Listening on ${port}`);
});
