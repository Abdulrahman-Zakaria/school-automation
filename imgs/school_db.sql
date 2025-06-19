CREATE DATABASE IF NOT EXISTS school_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_db;

-- جدول المستخدمين
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin', 'teacher', 'parent', 'student'),
    subject VARCHAR(100) DEFAULT NULL
);

-- جدول الطلاب
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY,
    user_id INT,
    grade_level VARCHAR(10),
    class_name VARCHAR(50),
    qr_code VARCHAR(255) UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- جدول الحضور (مُعدل لإضافة subject و teacher_id)
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    date DATE,
    status ENUM('present','absent'),
    subject VARCHAR(100) DEFAULT NULL,
    teacher_id INT DEFAULT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- جدول الدرجات
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject VARCHAR(100),
    grade VARCHAR(5),
    date DATE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- جدول الجدول العام
CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50),
    subject VARCHAR(100),
    day_of_week ENUM('السبت','الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'),
    time_start TIME,
    time_end TIME
);

-- جدول جدول المعلمين
CREATE TABLE IF NOT EXISTS teacher_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    class_name VARCHAR(50),
    subject VARCHAR(100),
    day_of_week ENUM('السبت','الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'),
    time_start TIME,
    time_end TIME,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- مدير افتراضي
INSERT INTO users (name, email, password, role) VALUES (
    'Admin User',
    'admin@school.com',
    '$2y$10$zL1G7Z2ehUVOF50TzZix9eUzzcDeaU3WmYgE7LJ/BcVXihJAcPtkG', -- كلمة المرور المشفرة لـ 'admin123'
    'admin'
);
