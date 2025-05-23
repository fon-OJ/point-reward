# 🏆 Point Reward System

ระบบสะสมแต้มแลกของรางวัล สำหรับการฝึกใช้งาน Vue 3 + PHP API แบบ mock (ไม่มีฐานข้อมูล)

##  Users
email: jone@example.com <br>
password: 1234<br>

email: jane@example.com<br>
password: 1234<br>

## Top Up Code
  1. BONUS50 = 50
  2. WELCOME100 = 100
  3. FREE200 =  200
  4. FREE300 = 300
  5. FREE400 = 400
  6. FREE500 = 500

## 🔧 Tech Stack
- Frontend: Vue 3 + Vite + Bootstrap 5 
- Backend: PHP 8.x (Pure PHP APIs)
- Auth: JWT (Mock user data)
- Containerization: Docker (ไม่ใช้ฐานข้อมูล)

---

## 📦 Features

- 🔐 Login ด้วย email/password (mock user)
- 💰 แสดงแต้มของผู้ใช้
- 🎁 แสดงของรางวัลทั้งหมด (แยกตามประเภท)
- 🛍️ แลกของรางวัล (ตรวจสอบแต้มก่อนแลก)
- 📱 รองรับ Responsive ทั้ง Desktop และ Mobile

---

## 🚀 การใช้งานผ่าน Docker

### 1. สร้าง Image และ Container

```bash
docker-compose up --build
```

###  2. เปิดใช้งานบน browser
Frontend: http://localhost:3000
Backend: http://localhost:8000

### 3. ตัวอย่าง API Endpoint

| Method | URL                         | Description             |
|--------|-----------------------------|-------------------------|
| POST   | `/login`                    | เข้าสู่ระบบ             |
| GET    | `/me`                       | ข้อมูลผู้ใช้งานปัจจุบัน |
| GET    | `/rewards`                 | รายการของรางวัลทั้งหมด   |
| POST   | `/rewards/redeem`          | แลกของรางวัล             |
| GET    | `/rewards/categories`      | ประเภทของรางวัลทั้งหมด   |
| GET    | `/rewards/categories/:cat` | ของรางวัลตามประเภท       |
| POST   | `/topup`                   | เติมแต้มด้วยรหัส         |
| GET    | `/topup/history`           | ดูประวัติการเติมแต้ม     |