<?php
include 'db_connection.php';

if (isset($_GET['st_ID'])) {
    $st_ID = $_GET['st_ID'];
    
    // Get subjects for this student
    $subjects_sql = "SELECT s.subject_id, s.subject_title, s.subject_code
                    FROM subject s
                    JOIN student_subject ss ON s.subject_id = ss.subject_id
                    WHERE ss.st_ID = '$st_ID'";
    $subjects_result = $conn->query($subjects_sql);
    
    if ($subjects_result->num_rows > 0) {
        // Create hidden field for st_ID
        echo "<input type='hidden' name='st_ID' value='$st_ID'>";
        
        echo "<h4>Enter Marks:</h4>";
        
        // Subject dropdown
        echo "<div style='margin-bottom: 15px;'>";
        echo "<label><strong>Select Subject:</strong></label>";
        echo "<select id='subject_dropdown' name='subject_id' class='input-field' style='width:100%; margin-top:10px; margin-bottom:15px;'>";
        echo "<option value=''>-- Select Subject --</option>";
        
        while ($subject = $subjects_result->fetch_assoc()) {
            // Get existing mark if any
            $marks_sql = "SELECT mark FROM marks WHERE st_ID = '$st_ID' AND subject_id = '{$subject['subject_id']}'";
            $marks_result = $conn->query($marks_sql);
            
            $mark_value = '';
            if ($marks_result && $marks_result->num_rows > 0) {
                $mark_row = $marks_result->fetch_assoc();
                $mark_value = $mark_row['mark'];
            }
            
            echo "<option value='{$subject['subject_id']}' data-mark='$mark_value'>{$subject['subject_title']} ({$subject['subject_code']})</option>";
        }
        
        echo "</select>";
        echo "</div>";
        
        // Always show mark input field
        echo "<div id='mark_field_container'>";
        echo "<label><strong>Mark:</strong></label>";
        echo "<input type='number' name='mark' id='mark_input' min='0' max='100' class='input-field' style='width:100%; margin-top:10px;' required>";
        echo "<button type='submit' class='btn' style='margin-top:15px; background-color:#4CAF50; color:white; padding:10px 20px; border:none; cursor:pointer; border-radius:5px;'>Submit Mark</button>";
        echo "</div>";
        
        // JavaScript to update mark value when subject changes
        echo "<script>
        document.getElementById('subject_dropdown').addEventListener('change', function() {
            var dropdown = document.getElementById('subject_dropdown');
            var markInput = document.getElementById('mark_input');
            
            if (dropdown.value) {
                // Get the pre-existing mark if any
                var selectedOption = dropdown.options[dropdown.selectedIndex];
                var existingMark = selectedOption.getAttribute('data-mark');
                
                // Set the mark input value
                markInput.value = existingMark;
            } else {
                markInput.value = '';
            }
        });
        </script>";
        
    } else {
        echo "<p>No subjects found for this student.</p>";
    }
} else {
    echo "<p>Please select a student first.</p>";
}
?>