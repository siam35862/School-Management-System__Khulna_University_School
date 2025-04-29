IF NOT EXISTS CREATE DATABASE school_management_system;

CREATE TABLE admin_login (
  adl_id int NOT NULL AUTO_INCREMENT,
  user_name varchar(255) NOT NULL,
  user_password varchar(255) NOT NULL,
  PRIMARY KEY (adl_id),
  UNIQUE KEY (user_name, user_password)
) ENGINE = InnoDB;

CREATE TABLE class (
  class_id int NOT NULL AUTO_INCREMENT,
  class_name varchar(50) NOT NULL,
  room_number varchar(10) NOT NULL,
  group_ enum(
    'Science',
    'Humanities',
    'Commerce',
    'Not Applicable'
  ) NOT NULL,
  total_seat int NOT NULL,
  PRIMARY KEY(class_id)
) ENGINE = InnoDB;

CREATE TABLE admission_form (
  admission_id int NOT NULL AUTO_INCREMENT,
  applicant_name varchar(100) NOT NULL,
  applicant_id varchar(50) NOT NULL,
  date_of_birth date NOT NULL,
  father_name varchar(100) DEFAULT NULL,
  mother_name varchar(100) DEFAULT NULL,
  guardian_mobile_number varchar(15) DEFAULT NULL,
  mobile_number varchar(15) NOT NULL,
  previous_school_name varchar(255) DEFAULT NULL,
  class_id int NOT NULL,
  admission_year int NOT NULL,
  email varchar(100) NOT NULL,
  gender enum('Male', 'Female') NOT NULL,
  village varchar(100) DEFAULT NULL,
  post_code int DEFAULT NULL,
  upazila varchar(100) DEFAULT NULL,
  zila varchar(100) DEFAULT NULL,
  group_ enum(
    'Science',
    'Humanities',
    'Commerce',
    'Not Applicable'
  ) NOT NULL,
  optional_subject varchar(100) DEFAULT NULL,
  PRIMARY KEY (admission_id),
  FOREIGN KEY (class_id) REFERENCES class (class_id) ON DELETE CASCADE,
  UNIQUE KEY applicant_id (applicant_id)
) ENGINE = InnoDB;

CREATE TABLE admission_result (
  ar_id int NOT NULL AUTO_INCREMENT,
  admission_id int NOT NULL,
  marks int NOT NULL,
  PRIMARY KEY (ar_id),
  FOREIGN KEY (admission_id) REFERENCES admission_form(admission_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE student (
  st_ID int NOT NULL AUTO_INCREMENT,
  student_id varchar(50) NOT NULL,
  admission_date date NOT NULL,
  class_id int NOT NULL,
  academic_year year NOT NULL,
  admission_id int NOT NULL,
  optional_subject varchar(100) NOT NULL,
  village varchar(100) DEFAULT NULL,
  post_code int DEFAULT NULL,
  upazila varchar(100) DEFAULT NULL,
  zila varchar(100) DEFAULT NULL,
  PRIMARY KEY(st_ID),
  UNIQUE KEY (student_id, class_id),
  FOREIGN KEY (admission_id) REFERENCES admission_form (admission_id),
  FOREIGN KEY (class_id) REFERENCES class (class_id)
)ENGINE=InnoDB;


CREATE TABLE attendance (
  attendance_id int NOT NULL AUTO_INCREMENT,
  attendance_status enum('Present', 'Absent', 'Late') NOT NULL,
  st_ID int NOT NULL,
  class_id int NOT NULL,
  attendance_date timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (attendance_id),
  FOREIGN KEY (st_ID) REFERENCES student (st_ID) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES class (class_id) ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE footer_info (
  id int NOT NULL AUTO_INCREMENT,
  address varchar(255) DEFAULT NULL,
  phone varchar(50) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  PRIMARY KEY(id)
) ENGINE = InnoDB;

CREATE TABLE gallery (
  id int NOT NULL AUTO_INCREMENT,
  file_name varchar(255) NOT NULL,
  type enum('photo', 'video') NOT NULL,
  title varchar(250) DEFAULT NULL,
  upload_date timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id)

) ENGINE = InnoDB;

CREATE TABLE institution_event (
  event_id int NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  category varchar(100) NOT NULL,
  event_date date NOT NULL,
  event_description text,
  PRIMARY KEY (event_id)
) ENGINE = InnoDB;

CREATE TABLE subject (
  subject_id int NOT NULL AUTO_INCREMENT,
  subject_title varchar(100) NOT NULL,
  subject_code varchar(20) NOT NULL,
  class_id int NOT NULL,
  PRIMARY KEY(subject_id),
  UNIQUE(subject_code, class_id),
  FOREIGN KEY (class_id) REFERENCES class (class_id)
) ENGINE = InnoDB;


CREATE TABLE marks (
  marks_id int NOT NULL AUTO_INCREMENT,
  mark int NOT NULL,
  st_ID int NOT NULL,
  subject_id int NOT NULL,
  PRIMARY KEY(marks_id),
  UNIQUE KEY (st_ID, subject_id),
  FOREIGN KEY (st_ID) REFERENCES student (st_ID) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subject (subject_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE messages (
  id int NOT NULL AUTO_INCREMENT,
  role varchar(100) NOT NULL,
  name varchar(100) NOT NULL,
  title varchar(255) NOT NULL,
  message text NOT NULL,
  image varchar(255) NOT NULL,
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id)
) ENGINE = InnoDB;

CREATE TABLE notice (
  notice_id int NOT NULL AUTO_INCREMENT,
  notice_title varchar(255) NOT NULL,
  notice_description text NOT NULL,
  notice_date timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(notice_id)
) ENGINE = InnoDB;

CREATE TABLE school_info (
  id int NOT NULL AUTO_INCREMENT,
  established_year year NOT NULL,
  short_history text,
  full_history text,
  school_name varchar(250) DEFAULT NULL,
  address varchar(250) DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  PRIMARY KEY(id)
) ENGINE = InnoDB;


CREATE TABLE student_event (
  st_ID int NOT NULL,
  event_id int NOT NULL,
  PRIMARY KEY(st_ID, event_id),
  FOREIGN KEY (st_ID) REFERENCES student (st_ID) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES institution_event (event_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE student_login (
  stl_id int NOT NULL AUTO_INCREMENT,
  user_name varchar(255) NOT NULL,
  user_password varchar(255) NOT NULL,
  st_ID int NOT NULL,
  PRIMARY KEY(stl_id),
  UNIQUE KEY (user_name, user_password),
  FOREIGN KEY (st_ID) REFERENCES student (st_ID) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE student_subject (
  st_ID int NOT NULL,
  subject_id int NOT NULL,
  PRIMARY KEY (st_ID, subject_id),
  FOREIGN KEY (subject_id) REFERENCES subject (subject_id) ON DELETE CASCADE,
  FOREIGN KEY (st_ID) REFERENCES student (st_ID) ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE teacher (
  teacher_ID int NOT NULL AUTO_INCREMENT,
  teacher_name varchar(100) NOT NULL,
  date_of_birth date NOT NULL,
  joining_date date NOT NULL,
  designation varchar(100) NOT NULL,
  service_status varchar(50) DEFAULT 'active',
  teaching_subject varchar(100) DEFAULT NULL,
  phone_number varchar(20) NOT NULL,
  email varchar(100) DEFAULT NULL,
  qualification varchar(100) DEFAULT NULL,
  district varchar(100) DEFAULT NULL,
  PRIMARY KEY(teacher_ID)
) ENGINE = InnoDB;

CREATE TABLE teacher_event (
  t_ID int NOT NULL,
  event_id int NOT NULL,
  PRIMARY KEY(t_id, event_id),
  FOREIGN KEY (t_ID) REFERENCES teacher (teacher_ID) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES institution_event (event_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE teacher_login (
  tl_id int NOT NULL AUTO_INCREMENT,
  user_name varchar(255) NOT NULL,
  user_password varchar(255) NOT NULL,
  teacher_ID int DEFAULT NULL,
  PRIMARY KEY(tl_id),
  UNIQUE(user_name,user_password),
  FOREIGN KEY (teacher_ID) REFERENCES teacher (teacher_ID) ON DELETE CASCADE

) ENGINE = InnoDB;

CREATE TABLE achievement (
  achievement_id int NOT NULL AUTO_INCREMENT,
  award_name varchar(255) NOT NULL,
  achievement_date date NOT NULL,
  achievement_description text,
  st_ID int DEFAULT NULL,
  t_ID int DEFAULT NULL,
  event_id int DEFAULT NULL,
  PRIMARY KEY (achievement_id),
  FOREIGN KEY (st_ID) REFERENCES student (st_ID) ON DELETE CASCADE,
  FOREIGN KEY (t_ID) REFERENCES teacher (teacher_ID) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES institution_event (event_id) ON DELETE
  SET NULL
) ENGINE = InnoDB;
