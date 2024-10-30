# Beacon Service for Project Beacon

โฟลเดอร์นี้ประกอบด้วยโค้ดสำหรับการพัฒนา Beacon Service ในโปรเจ็กต์ Project Beacon ซึ่งใช้ Express.js เพื่อจัดการการสื่อสารและอัปเดตสถานะการเข้า-ออกของผู้พักอาศัยโดยใช้สัญญาณจาก LINE Beacon

## สารบัญ
- [ภาพรวม](#ภาพรวม)
- [ความต้องการของระบบ](#ความต้องการของระบบ)
- [การเริ่มต้นด้วย Basic Express.js Starter ใน CodeSandbox](#การเริ่มต้นด้วย-basic-expressjs-starter-ใน-codesandbox)
- [การตั้งค่าและการใช้งาน](#การตั้งค่าและการใช้งาน)
- [การทดสอบการทำงาน](#การทดสอบการทำงาน)

## ภาพรวม
Beacon Service นี้พัฒนาขึ้นด้วย **Express.js** ซึ่งใช้เพื่อตรวจสอบและจัดการสัญญาณจาก LINE Beacon ในระบบ Project Beacon โค้ดถูกพัฒนาและทดสอบใน **CodeSandbox** โดยใช้ **Basic Express.js Starter** เพื่อความสะดวกและรวดเร็วในการตั้งค่าระบบ

## ความต้องการของระบบ
- Node.js (ในกรณีที่ต้องการรันในเครื่องของคุณเอง)
- CodeSandbox (สำหรับการพัฒนาและทดสอบ)
- Basic Express.js Starter

## การเริ่มต้นด้วย Basic Express.js Starter ใน CodeSandbox

### 1. เปิด CodeSandbox และสร้างโปรเจ็กต์ใหม่
- ไปที่ [CodeSandbox](https://codesandbox.io/) และล็อกอินเข้าสู่ระบบ
- คลิกที่ปุ่ม **Create Sandbox** แล้วเลือก **Node** จากนั้นเลือก **Express** ในหมวดหมู่ **Starter Templates**
- คุณจะได้โปรเจ็กต์พื้นฐานที่ใช้ Express.js พร้อมใช้งาน

### 2. อัปโหลดโค้ด Beacon Service ของคุณ
- ลบไฟล์ที่ไม่จำเป็น เช่น `routes/index.js` หรือไฟล์ตัวอย่างอื่น ๆ จากโปรเจ็กต์ใน CodeSandbox
- อัปโหลดไฟล์จากโฟลเดอร์ `Beacon` ของคุณไปยัง CodeSandbox โดยสามารถลากและวางไฟล์ลงใน CodeSandbox ได้โดยตรง

### 3. ตั้งค่า Express.js
- แก้ไขโค้ดใน `app.js` เพื่อรองรับการใช้งาน LINE Beacon API โดยตั้งค่า Route ที่จะรับข้อมูลจากสัญญาณ Beacon เช่น:
  ```javascript
  const express = require("express");
  const app = express();
  const port = process.env.PORT || 3000;

  app.use(express.json());

  // Route สำหรับรับข้อมูลจาก LINE Beacon
  app.post("/beacon", (req, res) => {
    const { hwid, dm } = req.body;  // รับข้อมูล HWID และข้อมูลอื่น ๆ จาก Beacon
    console.log(`Received beacon signal from HWID: ${hwid}`);
    // ทำการประมวลผลสัญญาณที่ได้รับที่นี่
    res.sendStatus(200);
  });

  app.listen(port, () => {
    console.log(`Beacon service running on port ${port}`);
  });
