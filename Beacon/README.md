# Beacon Service for Project Beacon

โฟลเดอร์นี้ประกอบด้วยโค้ดสำหรับการพัฒนา Beacon Service ในโปรเจ็กต์ Project Beacon ซึ่งใช้ Express.js เพื่อจัดการการสื่อสารและอัปเดตสถานะการเข้า-ออกของผู้พักอาศัยโดยใช้สัญญาณจาก LINE Beacon

## สารบัญ
- [ภาพรวม](#ภาพรวม)
- [ความต้องการของระบบ](#ความต้องการของระบบ)
- [การเริ่มต้นด้วย Basic Express.js Starter ใน CodeSandbox](#การเริ่มต้นด้วย-basic-expressjs-starter-ใน-codesandbox)
- [การตั้งค่าและการใช้งาน](#การตั้งค่าและการใช้งาน)
- [การอัพโหลดไฟล์จาก GitHub ไปยัง CodeSandbox](#การอัพโหลดไฟล์จาก-github-ไปยัง-codesandbox)
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
- อัปโหลดไฟล์จากโฟลเดอร์ `Beacon` ของคุณไปยัง CodeSandbox โดยสามารถลากและวางไฟล์ลงใน CodeSandbox ได้โดยตรง หรือใช้วิธีการลิงก์กับ GitHub (ตามรายละเอียดในส่วนถัดไป)

## การตั้งค่าและการใช้งาน

1. **อัปเดตไฟล์ `.env`** (ถ้ามีการใช้งาน):
   - ตั้งค่าตัวแปรสภาพแวดล้อม เช่น `PORT` ในไฟล์ `.env` สำหรับการรันแอปพลิเคชัน:
     ```env
     PORT=3000
     ```

2. **ตั้งค่าและทดสอบ Endpoint สำหรับ Beacon:**
   - คุณสามารถกำหนดให้ Endpoint เช่น `/beacon` รับข้อมูลจาก LINE Beacon API และทำการประมวลผลข้อมูลที่ได้รับ โดยอาจมีการเก็บข้อมูลลงฐานข้อมูล หรือแสดงผลในคอนโซล

3. **รันโปรเจ็กต์ใน CodeSandbox:**
   - CodeSandbox จะรันแอปพลิเคชันของคุณโดยอัตโนมัติ เมื่อมีการแก้ไขไฟล์ คุณสามารถเปิด URL ที่แสดงอยู่ใน CodeSandbox เพื่อตรวจสอบการทำงานของเซิร์ฟเวอร์

## การอัพโหลดไฟล์จาก GitHub ไปยัง CodeSandbox

ถ้าคุณมีโค้ดอยู่ใน GitHub แล้ว คุณสามารถเชื่อมต่อ CodeSandbox กับ GitHub ได้โดยการทำตามขั้นตอนต่อไปนี้:

### 1. ลิงก์โปรเจ็กต์กับ GitHub Repository
- เปิด [CodeSandbox](https://codesandbox.io/) และไปที่หน้า **Dashboard**
- คลิกปุ่ม **Create Sandbox** แล้วเลือก **Import Project**
- เลือก **GitHub Repository** และกรอก URL ของโปรเจ็กต์บน GitHub ของคุณ เช่น `https://github.com/PhinyoZ/Project_Beacon`
- คลิกปุ่ม **Import and Fork** เพื่อดึงโปรเจ็กต์จาก GitHub มาใช้ใน CodeSandbox

### 2. การอัปเดตและ Push โค้ดกลับไปยัง GitHub
- เมื่อทำการเปลี่ยนแปลงโค้ดใน CodeSandbox แล้ว คุณสามารถ push การแก้ไขกลับไปยัง GitHub ได้โดยตรง:
  - ไปที่เมนู **Git** ใน CodeSandbox
  - คลิกปุ่ม **Commit Changes** และกรอกข้อความ commit
  - จากนั้นคลิกปุ่ม **Push** เพื่อส่งการเปลี่ยนแปลงกลับไปที่ GitHub

## การทดสอบการทำงาน

1. **ทดสอบด้วยสัญญาณ Beacon:**
   - เมื่อเซิร์ฟเวอร์ Express.js ของคุณรันอยู่ ให้ส่งสัญญาณจาก LINE Beacon ไปยังเซิร์ฟเวอร์ และตรวจสอบว่าข้อมูล Beacon ถูกส่งมาที่ Endpoint `/beacon` อย่างถูกต้อง

2. **ตรวจสอบสถานะ:**
   - ตรวจสอบว่ามีการบันทึกข้อมูลการเข้า-ออกของ Beacon ในระบบของคุณตามที่ออกแบบไว้ เช่น การแสดงผลในคอนโซล หรือการจัดเก็บในฐานข้อมูล
   - คุณสามารถใช้ `console.log` เพื่อดูข้อมูลที่ได้รับจาก Beacon

---

โปรดตรวจสอบให้แน่ใจว่าได้ตั้งค่าและทดสอบระบบทั้งหมดตามขั้นตอนข้างต้น เพื่อให้ Beacon Service ทำงานได้อย่างสมบูรณ์บน CodeSandbox
