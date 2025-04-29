ALTER TABLE app_user AUTO_INCREMENT = 742600;

INSERT INTO app_user (user_name, user_password, email_address, mobile_number) VALUES
('Alice Johnson', 'password123', 'alice742600@example.com', '9876543201'),
('Bob Smith', 'securepass', 'bob742601@example.com', '9876543202'),
('Charlie Brown', 'mypassword', 'charlie742602@example.com', '9876543203'),
('David Miller', 'strongpass', 'david742603@example.com', '9876543204'),
('Emma Wilson', 'pass4321', 'emma742604@example.com', '9876543205');

ALTER TABLE class AUTO_INCREMENT = 1;

INSERT INTO class (class_name, total_student, room_number) VALUES
('Class One', (SELECT COUNT(*) FROM student WHERE class_id = 1), 'A101'),
('Class Two', (SELECT COUNT(*) FROM student WHERE class_id = 2), 'A102'),
('Class Three', (SELECT COUNT(*) FROM student WHERE class_id = 3), 'A103'),
('Class Four', (SELECT COUNT(*) FROM student WHERE class_id = 4), 'A104'),
('Class Five', (SELECT COUNT(*) FROM student WHERE class_id = 5), 'A105'),
('Class Six', (SELECT COUNT(*) FROM student WHERE class_id = 6), 'A106'),
('Class Seven', (SELECT COUNT(*) FROM student WHERE class_id = 7), 'A107'),
('Class Eight', (SELECT COUNT(*) FROM student WHERE class_id = 8), 'A108'),
('Class Nine', (SELECT COUNT(*) FROM student WHERE class_id = 9), 'A109'),
('Class Ten', (SELECT COUNT(*) FROM student WHERE class_id = 10), 'A110');


ALTER TABLE subject AUTO_INCREMENT = 1000;

INSERT INTO subject (subject_title, subject_code, class_id) VALUES
('Bangla - Class 1', 'BNG101', 1),
('Bangla - Class 2', 'BNG102', 2),
('Bangla - Class 3', 'BNG103', 3),
('Bangla - Class 4', 'BNG104', 4),
('Bangla - Class 5', 'BNG105', 5),
('Bangla - Class 6', 'BNG106', 6),
('Bangla - Class 7', 'BNG107', 7),
('Bangla - Class 8', 'BNG108', 8),
('Bangla - Class 9', 'BNG109', 9),
('Bangla - Class 10', 'BNG110', 10);

ALTER TABLE student AUTO_INCREMENT = 1;



INSERT INTO student (student_id, student_name, date_of_birth, father_name, mother_name, guardian_mobile_number, admission_date, user_id, class_id, academic_year, student_group) VALUES
('STU202500', 'Alice Johnson', '2015-02-10', 'John Johnson', 'Mary Johnson', '01710000001', '2024-01-10', 742600, 1, 2024, 'A'),
('STU202501', 'Bob Smith', '2014-05-15', 'Robert Smith', 'Emily Smith', '01710000002', '2024-01-12', 742601, 2, 2024, 'B'),
('STU202502', 'Charlie Brown', '2013-08-20', 'Michael Brown', 'Sarah Brown', '01710000003', '2024-01-14', 742602, 3, 2024, 'A'),
('STU202503', 'David Miller', '2012-11-30', 'Daniel Miller', 'Sophia Miller', '01710000004', '2024-01-16', 742603, 4, 2024, 'B'),
('STU202504', 'Emma Wilson', '2011-07-22', 'James Wilson', 'Olivia Wilson', '01710000005', '2024-01-18', 742604, 5, 2024, 'A');


ALTER TABLE result AUTO_INCREMENT = 5000;
INSERT INTO result (total_marks, GPA, grade, st_ID) VALUES
(450, 4.00, 'A+', 1),
(420, 3.80, 'A-', 2),
(390, 3.50, 'B+', 3),
(470, 4.20, 'A+', 4),
(430, 3.90, 'A-', 5);

INSERT INTO teacher (teacher_id, teacher_name, date_of_birth, joining_date, designation, user_id) VALUES
('T001', 'Dr. James Brown', '1980-05-20', '2010-08-15', 'Professor', 742600),
('T002', 'Mr. Robert Johnson', '1982-10-10', '2012-07-18', 'Lecturer', 742601),
('T003', 'Mrs. Emily Carter', '1978-12-01', '2008-06-20', 'Senior Teacher', 742602),
('T004', 'Ms. Olivia Wilson', '1985-03-30', '2015-09-05', 'Assistant Professor', 742603),
('T005', 'Dr. Daniel White', '1981-07-22', '2011-04-10', 'Professor', 742604);


INSERT INTO attendance (attendance_date, attendance_status, st_ID) VALUES
('2024-02-01', 'Present', 1),
('2024-02-02', 'Absent', 2),
('2024-02-03', 'Late', 3),
('2024-02-04', 'Present', 4),
('2024-02-05', 'Absent', 5);

INSERT INTO marks (mark, grade, st_ID, subject_id) VALUES
(85, 'A', 1, 1000),
(78, 'B+', 2, 1001),
(92, 'A+', 3, 1002),
(67, 'C', 4, 1003),
(89, 'A-', 5, 1004);

INSERT INTO notice (notice_title, notice_description, notice_date) VALUES
('Exam Schedule Released', 'The final exam schedule for this semester has been published. Please check the notice board for details.', '2024-03-01'),
('Holiday Notice', 'The institution will remain closed on March 17 due to National Day celebrations.', '2024-03-10'),
('Library Update', 'New books have been added to the library collection. Visit the library to explore them.', '2024-02-25'),
('Annual Sports Day', 'The annual sports day will be held on April 10. Students are encouraged to participate.', '2024-03-20'),
('Parent-Teacher Meeting', 'A parent-teacher meeting will be held on March 30 to discuss students’ performance.', '2024-03-15');

INSERT INTO institution_event (title, category, event_date, event_description) VALUES
('Science Fair 2024', 'Academic', '2024-04-15', 'An exhibition where students present their innovative science projects.'),
('Annual Sports Meet', 'Sports', '2024-05-10', 'A day filled with various sports competitions and activities.'),
('Cultural Festival', 'Cultural', '2024-06-20', 'A celebration of music, dance, drama, and art performances by students.'),
('Tech Workshop', 'Technology', '2024-07-05', 'A hands-on workshop on coding, robotics, and emerging technologies.'),
('Debate Competition', 'Academics', '2024-08-12', 'An inter-school debate competition with exciting topics.');

INSERT INTO achievement (award_name, achievement_date, st_ID, t_ID, event_id) VALUES
('Best Science Project', '2024-04-15', 1, NULL, 1), -- Student won in Science Fair
('Sports Champion', '2024-05-10', 2, NULL, 2), -- Student won in Annual Sports Meet
('Best Cultural Performer', '2024-06-20', 3, NULL, 3), -- Student won in Cultural Festival
('Tech Innovator Award', '2024-07-05', NULL, 1, 4), -- Teacher received award at Tech Workshop
('Debate Winner', '2024-08-12', 4, NULL, 5); -- Student won Debate Competition

INSERT INTO admission_form (applicant_name, applicant_id, date_of_birth, father_name, mother_name, guardian_mobile_number, mobile_number, previous_school_name, class_id, academic_year) VALUES
('Rahim Ahmed', 'ADM2024001', '2015-04-10', 'Kamal Ahmed', 'Rina Ahmed', '01720000001', '01730000001', 'Sunrise School', 1, 2024),
('Karim Hossain', 'ADM2024002', '2014-07-15', 'Jamal Hossain', 'Fatema Hossain', '01720000002', '01730000002', 'Greenwood Academy', 2, 2024),
('Mina Akter', 'ADM2024003', '2013-08-22', 'Shafiqul Islam', 'Ayesha Begum', '01720000003', '01730000003', 'Lakeview School', 3, 2024),
('Rifat Chowdhury', 'ADM2024004', '2012-11-05', 'Selim Chowdhury', 'Nasima Chowdhury', '01720000004', '01730000004', 'Springfield School', 4, 2024),
('Sadia Rahman', 'ADM2024005', '2011-06-18', 'Hafiz Rahman', 'Jannat Ara', '01720000005', '01730000005', 'Bluebell Academy', 5, 2024);

INSERT INTO teacher_event (t_ID, event_id) VALUES
(1, 1),  -- Teacher 1 assigned to Science Fair
(2, 2),  -- Teacher 2 assigned to Sports Meet
(3, 3),  -- Teacher 3 assigned to Cultural Festival
(4, 4),  -- Teacher 4 assigned to Tech Workshop
(5, 5);  -- Teacher 5 assigned to Debate Competition

INSERT INTO student_event (st_ID, event_id) VALUES
(1, 1),  -- Student 1 participating in Science Fair
(2, 2),  -- Student 2 participating in Sports Meet
(3, 3),  -- Student 3 participating in Cultural Festival
(4, 4),  -- Student 4 participating in Tech Workshop
(5, 5);  -- Student 5 participating in Debate Competition

INSERT INTO teacher_class (t_ID, class_id) VALUES
(1, 1),  -- Teacher 1 assigned to Class 1
(2, 2),  -- Teacher 2 assigned to Class 2
(3, 3),  -- Teacher 3 assigned to Class 3
(4, 4),  -- Teacher 4 assigned to Class 4
(5, 5);  -- Teacher 5 assigned to Class 5

INSERT INTO teacher_subject (t_ID, subject_id, academic_year) VALUES
(1, 1000, 2024),  -- Teacher 1 teaching Subject 1000 in 2024
(2, 1001, 2024),  -- Teacher 2 teaching Subject 1001 in 2024
(3, 1002, 2024),  -- Teacher 3 teaching Subject 1002 in 2024
(4, 1003, 2024),  -- Teacher 4 teaching Subject 1003 in 2024
(5, 1004, 2024);  -- Teacher 5 teaching Subject 1004 in 2024

INSERT INTO student_subject (st_ID, subject_id) VALUES
(1, 1000),  -- Student 1 enrolled in Subject 1000
(2, 1001),  -- Student 2 enrolled in Subject 1001
(3, 1002),  -- Student 3 enrolled in Subject 1002
(4, 1003),  -- Student 4 enrolled in Subject 1003
(5, 1004);  -- Student 5 enrolled in Subject 1004





