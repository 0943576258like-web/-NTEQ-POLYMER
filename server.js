const express = require('express');
const { spawn } = require('child_process');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3000;

app.use(cors());
app.use(express.json());

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

app.get('/api/process', (req, res) => {
    // รับค่าตัวเลขมาจากช่องกรอกบนหน้าเว็บ (ถ้าไม่มีจะใช้ค่าเริ่มต้นเป็น 42)
    const inputNumber = req.query.num || "42";

    // ส่งค่าตัวเลขพ่วงเข้าไปตอนสั่งรันไฟล์ Python
    const pythonProcess = spawn('python', ['process.py', inputNumber]);
    let pythonData = '';

    pythonProcess.stdout.on('data', (data) => {
        pythonData += data.toString();
    });

    pythonProcess.on('close', (code) => {
        try {
            const parsedData = JSON.parse(pythonData);
            res.json({
                message: "ระบบเชื่อมต่อเว็บและ API สมบูรณ์แบบ",
                python_data: `${parsedData.note} (สูตรคำนวณ x 2 ผลลัพธ์คือ: ${parsedData.computed_value})`
            });
        } catch (e) {
            res.status(500).json({ error: "ไม่สามารถแปลงข้อมูลจาก Python ได้" });
        }
    });
});

app.listen(PORT, () => {
    console.log(`เซิร์ฟเวอร์โฉมใหม่เปิดใช้งานแล้วที่: http://localhost:${PORT}`);
});
