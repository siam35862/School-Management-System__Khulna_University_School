<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $applicantId = $_POST['applicantId'];
    $academicYear = $_POST['academicyear'];
    $dob = $_POST['dob'];

    $stmt = $conn->prepare("
        SELECT af.*, ar.marks, c.class_name, c.group_ AS class_group, c.total_seat
        FROM admission_form af
        LEFT JOIN admission_result ar ON af.admission_id = ar.admission_id
        JOIN class c ON af.class_id = c.class_id
        WHERE af.applicant_id = ? 
          AND af.admission_year = ? 
          AND af.date_of_birth = ?
    ");
    $stmt->bind_param("sis", $applicantId, $academicYear, $dob);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();

        if (is_null($data['marks']) || $data['marks'] === "") {
            echo "<div class='message'>Result not published yet or applicant not found.</div>";
            exit;
        }

        $classId = $data['class_id'];
        $className=$data['class_name'];
        $marks = $data['marks'];
        $group = $data['group_'];
        $totalSeat = $data['total_seat'];

        $rankSql = "
            SELECT COUNT(*)+1 AS applicant_rank 
            FROM admission_result ar
            JOIN admission_form af ON ar.admission_id = af.admission_id
            WHERE af.class_id = ?
        ";
        if ($classId == 9) {
            $rankSql .= " AND af.group_ = ? ";
        }
        $rankSql .= " AND ar.marks > ?";

        if ($classId == 9) {
            $rankStmt = $conn->prepare($rankSql);
            $rankStmt->bind_param("isd", $classId, $group, $marks);
        } else {
            $rankStmt = $conn->prepare($rankSql);
            $rankStmt->bind_param("id", $classId, $marks);
        }

        $rankStmt->execute();
        $rankResult = $rankStmt->get_result();
        $rankRow = $rankResult->fetch_assoc();
        $rank = $rankRow['applicant_rank'];

        $status = ($rank <= $totalSeat) ? "Selected" : "Not Selected";

        echo "
        <div class='info'>
            <label>Applicant ID: {$data['applicant_id']}</label>
           

            <label>Applicant Name: {$data['applicant_name']}</label>
           

            <label>Father's Name: {$data['father_name']}</label>
   

            <label>Mother's Name: {$data['mother_name']}</label>
            

            <label>Date of Birth: {$data['date_of_birth']}</label>
        

            <label>Applying Class: {$data['class_name']}</label>";
            

        if ($className == 'Nine') {
            echo"<label>Group: {$data['group_']}</label>";
        }

        echo"
            <label>Obtained Mark: {$marks}</label>
          

            <label>Rank: {$rank}</label>
      

            <label>Status: {$status}</label>
           
        </div>";
    } else {
        echo "<div class='message'>Result not published yet or applicant not found.</div>";
    }
}
?>

