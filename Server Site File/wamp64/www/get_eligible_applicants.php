<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo "Unauthorized access";
    exit();
}

include 'db_connection.php';

// Get parameters
$class_name = $_GET['class_name'] ?? '';
$group = $_GET['group_'] ?? '';

if (empty($class_name) || empty($group)) {
    echo "<p>Please select class and group</p>";
    exit();
}

// Get class ID
$class_query = "SELECT class_id, total_seat FROM class WHERE class_name = '$class_name' AND group_ = '$group'";
$class_result = $conn->query($class_query);

if ($class_result->num_rows == 0) {
    echo "<p>Class information not found</p>";
    exit();
}

$class_data = $class_result->fetch_assoc();
$class_id = $class_data['class_id'];
$total_seats = $class_data['total_seat'];

// Count occupied seats (students already in this class)
$occupied_seats_query = "SELECT COUNT(*) as occupied_seats FROM student WHERE class_id = '$class_id'";
$occupied_seats_result = $conn->query($occupied_seats_query);
$occupied_seats_data = $occupied_seats_result->fetch_assoc();
$occupied_seats = $occupied_seats_data['occupied_seats'];

// Calculate available seats
$available_seats = $total_seats - $occupied_seats;

if ($available_seats <= 0) {
    echo "<p>No seats available in this class</p>";
    exit();
}

// Get applicants with admission results who are not yet in student table
$applicants_query = "
    SELECT af.admission_id, af.applicant_name, af.applicant_id, ar.marks,
    (SELECT COUNT(*) + 1 FROM admission_result ar2 
     JOIN admission_form af2 ON ar2.admission_id = af2.admission_id 
     WHERE af2.class_id = af.class_id AND ar2.marks > ar.marks) as `rank`
    FROM admission_result ar
    JOIN admission_form af ON ar.admission_id = af.admission_id
    WHERE af.class_id = '$class_id'
    AND NOT EXISTS (
        SELECT 1 FROM student s WHERE s.admission_id = af.admission_id
    )
    ORDER BY ar.marks DESC, af.applicant_name ASC
";

$applicants_result = $conn->query($applicants_query);

if (!$applicants_result) {
    echo "<p>Error executing query: " . $conn->error . "</p>";
    exit();
}

if ($applicants_result->num_rows == 0) {
    echo "<p>No eligible applicants found</p>";
    exit();
}

// Display information
echo "<h4>Class: $class_name - Group: $group</h4>";
echo "<p>Total Seats: $total_seats | Occupied Seats: $occupied_seats | Available Seats: $available_seats</p>";

echo "<table class='applicant-table'>
        <thead>
            <tr>
                <th>Applicant Name</th>
                <th>Applicant ID</th>
                <th>Marks</th>
                <th>Rank</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>";

$serial = 0;
while ($row = $applicants_result->fetch_assoc()) {
    $serial++;
    // Check if applicant is eligible (marks > 0 and within available seats)
    $eligible = ($row['marks'] > 0 && $serial <= $available_seats) ? "Yes" : "No";
    
    echo "<tr" . ($eligible == "Yes" ? " class='eligible'" : "") . ">
            <td>{$row['applicant_name']}</td>
            <td>{$row['applicant_id']}</td>
            <td>{$row['marks']}</td>
            <td>{$row['rank']}</td>
            <td>";
            
    if ($eligible == "Yes") {
        // Change to use the JavaScript modal function
        echo "<a href='process_admission.php?admission_id={$row['admission_id']}' class='admit-btn'>Admit</a>";

        echo " <a href='reject_applicant.php?admission_id={$row['admission_id']}&class_name=$class_name&group_=$group' class='reject-btn'>Reject</a>";
    } else {
        echo "Not Eligible";
    }
    
    echo "</td></tr>";
}

echo "</tbody></table>";

// Add some CSS for highlighting eligible applicants
echo "<style>
    tr.eligible {
        background-color: #e8f5e9 !important;
    }
    tr.eligible:nth-child(even) {
        background-color: #c8e6c9 !important; 
    }
    .admit-btn, .reject-btn {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 3px;
        color: white;
        text-decoration: none;
        margin: 0 5px;
        cursor: pointer;
        border: none;
    }
    .admit-btn {
        background-color: #4CAF50;
    }
    .admit-btn:hover {
        background-color: #388E3C;
    }
    .reject-btn {
        background-color: #f44336;
    }
    .reject-btn:hover {
        background-color: #d32f2f;
    }
</style>";

$conn->close();
?>