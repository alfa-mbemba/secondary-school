-- PostgreSQL Schema for School Management System

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255)
);

-- Insert admin
INSERT INTO admin (username, password) 
VALUES ('admin', MD5('admin123'))
ON CONFLICT (username) DO NOTHING;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id SERIAL PRIMARY KEY,
    admission_no VARCHAR(20) UNIQUE,
    full_name VARCHAR(100),
    class VARCHAR(20),
    parent_phone VARCHAR(15),
    address TEXT,
    fee_paid DECIMAL(10,2) DEFAULT 0,
    total_fees DECIMAL(10,2) DEFAULT 5000,
    registered_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teachers table
CREATE TABLE IF NOT EXISTS teachers (
    id SERIAL PRIMARY KEY,
    teacher_id VARCHAR(20) UNIQUE,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(15),
    subject VARCHAR(50),
    class_assigned VARCHAR(20),
    password VARCHAR(255),
    last_password_change TIMESTAMP,
    registered_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample teacher
INSERT INTO teachers (teacher_id, full_name, email, phone, subject, class_assigned, password) 
VALUES ('TCH001', 'John Doe', 'john@school.com', '0712345678', 'Mathematics', 'Form 1', MD5('teacher123'))
ON CONFLICT (teacher_id) DO NOTHING;

-- Parents table
CREATE TABLE IF NOT EXISTS parents (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(15),
    student_admission_no VARCHAR(20),
    password VARCHAR(255),
    registered_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Results table
CREATE TABLE IF NOT EXISTS results (
    id SERIAL PRIMARY KEY,
    student_id INTEGER REFERENCES students(id) ON DELETE CASCADE,
    exam_name VARCHAR(50),
    subject VARCHAR(50),
    marks INTEGER,
    total_marks INTEGER DEFAULT 100,
    grade CHAR(2)
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id SERIAL PRIMARY KEY,
    student_id INTEGER REFERENCES students(id) ON DELETE CASCADE,
    teacher_id INTEGER REFERENCES teachers(id) ON DELETE CASCADE,
    date DATE,
    status VARCHAR(10) CHECK (status IN ('Present', 'Absent', 'Late')),
    remark TEXT
);

-- Exam timetable table
CREATE TABLE IF NOT EXISTS exam_timetable (
    id SERIAL PRIMARY KEY,
    exam_name VARCHAR(50),
    class VARCHAR(20),
    subject VARCHAR(50),
    exam_date DATE,
    start_time TIME,
    end_time TIME,
    venue VARCHAR(100),
    created_by INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fee payments table
CREATE TABLE IF NOT EXISTS fee_payments (
    id SERIAL PRIMARY KEY,
    student_id INTEGER REFERENCES students(id) ON DELETE CASCADE,
    amount DECIMAL(10,2),
    payment_date DATE,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    receipt_no VARCHAR(50),
    status VARCHAR(20) DEFAULT 'Completed'
);

-- Insert sample student
INSERT INTO students (admission_no, full_name, class, parent_phone) 
VALUES ('STU001', 'John Student', 'Form 1', '0712345678')
ON CONFLICT (admission_no) DO NOTHING;

-- Create indexes for better performance
CREATE INDEX idx_students_admission ON students(admission_no);
CREATE INDEX idx_results_student ON results(student_id);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_attendance_student ON attendance(student_id);

-- Display message
SELECT '✅ Database schema created successfully!' as status;