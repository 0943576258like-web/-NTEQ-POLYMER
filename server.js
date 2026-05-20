const express = require('express');
const fs = require('fs');
const path = require('path');
const app = express();
const PORT = process.env.PORT || 3000;

// รองรับการรับส่งข้อมูลแบบ JSON และ Form
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// เรียกใช้งานไฟล์สถิติต่างๆ ในโฟลเดอร์หลัก (เช่น หน้าแรก, รูปภาพ, CSS)
app.use(express.static(path.join(__dirname)));

// สั่งให้เซิร์ฟเวอร์เปิดหน้าแอดมินเมื่อเข้าลิงก์ /admin
app.use('/admin', express.static(path.join(__dirname, 'admin')));

// API สำหรับดึงข้อมูลไปแสดงผล
app.get('/data.json', (req, res) => {
    const filePath = path.join(__dirname, 'data.json');
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.json({ title: "", announcement: "" });
    }
});

// API สำหรับรับข้อมูลจากหลังบ้านมาบันทึก
app.post('/save-data', (req, res) => {
    const newData = req.body;
    const filePath = path.join(__dirname, 'data.json');
    
    fs.writeFile(filePath, JSON.stringify(newData, null, 2), (err) => {
        if (err) {
            console.error(err);
            return res.status(500).send('Error saving data');
        }
        res.send('Success');
    });
});

app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
