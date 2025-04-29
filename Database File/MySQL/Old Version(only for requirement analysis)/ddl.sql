-- User Table (Independent)
CREATE TABLE app_user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(50) NOT NULL,
    user_password VARCHAR(255) NOT NULL,
    email_address VARCHAR(100) NOT NULL UNIQUE,
    mobile_number VARCHAR(15) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Class Table (Independent)
CREATE TABLE class (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL,
    total_student INT NOT NULL DEFAULT 0,
    room_number VARCHAR(10) NOT NULL
) ENGINE=InnoDB;

-- Subject Table (Depends on Class)
CREATE TABLE subject (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_title VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    class_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES class(class_id)
) ENGINE=InnoDB;

-- Student Table (Depends on User and Class)
CREATE TABLE student (
    st_ID INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    student_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    father_name VARCHAR(100),
    mother_name VARCHAR(100),
    guardian_mobile_number VARCHAR(15),
    admission_date DATE NOT NULL,
    user_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year YEAR NOT NULL,  
    student_group VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES app_user(user_id),
    FOREIGN KEY (class_id) REFERENCES class(class_id)
) ENGINE=InnoDB;

-- Result Table (Depends on Student)
CREATE TABLE result (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    total_marks INT NOT NULL,
    GPA DECIMAL(3,2) NOT NULL,
    grade VARCHAR(2) NOT NULL,
    st_ID INT NOT NULL UNIQUE,
    FOREIGN KEY (st_ID) REFERENCES student(st_ID)
) ENGINE=InnoDB;

-- Teacher Table (Depends on User)
CREATE TABLE teacher (
    t_ID INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id VARCHAR(50) NOT NULL UNIQUE,
    teacher_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    joining_date DATE NOT NULL,
    designation VARCHAR(100),
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES app_user(user_id)
) ENGINE=InnoDB;

-- Attendance Table (Depends on Student)
CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    attendance_date DATE NOT NULL,  
    attendance_status ENUM('Present', 'Absent', 'Late') NOT NULL,  -- Renamed from 'status' to 'attendance_status'
    st_ID INT NOT NULL,
    FOREIGN KEY (st_ID) REFERENCES student(st_ID)  
) ENGINE=InnoDB;

-- Marks Table (Depends on Student and Subject)
CREATE TABLE marks (
    marks_id INT AUTO_INCREMENT PRIMARY KEY,
    mark INT NOT NULL,
    grade CHAR(2) NOT NULL,
    st_ID INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (st_ID) REFERENCES student(st_ID)  ,
    FOREIGN KEY (subject_id) REFERENCES subject(subject_id)  
) ENGINE=InnoDB;

-- Notice Table (Independent)
CREATE TABLE notice (
    notice_id INT AUTO_INCREMENT PRIMARY KEY,
    notice_title VARCHAR(255) NOT NULL,
    notice_description TEXT NOT NULL,  
    notice_date DATE NOT NULL  
) ENGINE=InnoDB;

-- Institution Event Table (Independent)
CREATE TABLE institution_event (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    event_date DATE NOT NULL,  
    event_description TEXT 
) ENGINE=InnoDB;

-- Achievement Table (Depends on Student, Teacher, and Institution Event)
CREATE TABLE achievement (
    achievement_id INT AUTO_INCREMENT PRIMARY KEY,
    award_name VARCHAR(255) NOT NULL,
    achievement_date DATE NOT NULL,   
    st_ID INT,
    t_ID INT,
    event_id INT,
    FOREIGN KEY (st_ID) REFERENCES student(st_ID)  ,
    FOREIGN KEY (t_ID) REFERENCES teacher(t_ID)  ,
    FOREIGN KEY (event_id) REFERENCES institution_event(event_id)  
) ENGINE=InnoDB;

-- Admission Form Table (Depends on Class)
CREATE TABLE admission_form (
    admission_id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_name VARCHAR(100) NOT NULL,
    applicant_id VARCHAR(50) NOT NULL UNIQUE,
    date_of_birth DATE NOT NULL,
    father_name VARCHAR(100),
    mother_name VARCHAR(100),
    guardian_mobile_number VARCHAR(15),
    mobile_number VARCHAR(15),
    previous_school_name VARCHAR(255),
    class_id INT NOT NULL,
    academic_year YEAR NOT NULL,
    FOREIGN KEY (class_id) REFERENCES class(class_id)  
) ENGINE=InnoDB;

-- Teacher-Event Association Table
CREATE TABLE teacher_event (
    t_ID INT NOT NULL,
    event_id INT NOT NULL,
    PRIMARY KEY (t_ID, event_id),
    FOREIGN KEY (t_ID) REFERENCES teacher(t_ID)  ,
    FOREIGN KEY (event_id) REFERENCES institution_event(event_id)  
) ENGINE=InnoDB;

-- Student-Event Association Table
CREATE TABLE student_event (
    st_ID INT NOT NULL,
    event_id INT NOT NULL,
    PRIMARY KEY (st_ID, event_id),
    FOREIGN KEY (st_ID) REFERENCES student(st_ID)  ,
    FOREIGN KEY (event_id) REFERENCES institution_event(event_id)  
) ENGINE=InnoDB;

-- Teacher-Class Association Table
CREATE TABLE teacher_class (
    t_ID INT NOT NULL,
    class_id INT NOT NULL,
    PRIMARY KEY (t_ID, class_id),
    FOREIGN KEY (t_ID) REFERENCES teacher(t_ID)  ,
    FOREIGN KEY (class_id) REFERENCES class(class_id)  
) ENGINE=InnoDB;

-- Teacher-Subject Association Table
CREATE TABLE teacher_subject (
    t_ID INT NOT NULL,
    subject_id INT NOT NULL,
    academic_year YEAR NOT NULL,
    PRIMARY KEY (t_ID, subject_id, academic_year),
    FOREIGN KEY (t_ID) REFERENCES teacher(t_ID)  ,
    FOREIGN KEY (subject_id) REFERENCES subject(subject_id)  
) ENGINE=InnoDB;

-- Student-Subject Association Table
CREATE TABLE student_subject (
    st_ID INT NOT NULL,
    subject_id INT NOT NULL,
    PRIMARY KEY (st_ID, subject_id),
    FOREIGN KEY (st_ID) REFERENCES student(st_ID)  ,
    FOREIGN KEY (subject_id) REFERENCES subject(subject_id)  
) ENGINE=InnoDB;
