
<link rel="stylesheet" href="footer.css">

<style>
  .container {
    max-width: 500px;
    margin: 80px auto;
    background-color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
  }

  .header {
    text-align: center;
    margin-bottom: 25px;
  }

  .header h2 {
    margin: 0;
    color: #004080;
  }

  form {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 12px 20px;
    align-items: center;
  }

  label {
    font-weight: bold;
    color: #333;
    text-align: right;
    padding-right: 10px;
  }

  input,
  select {
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    width: 100%;
  }

  .buttons {
    grid-column: span 2;
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
  }

  .buttons .reset-btn,
  .buttons .submit-btn {
    flex: 1;
    padding: 10px;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
  }

  .buttons .reset-btn {
    background-color: #6c757d;
    color: #fff;
    margin-right: 10px;
  }

  .buttons .submit-btn {
    background-color: #28a745;
    color: #fff;
  }

  @media (max-width: 600px) {
    form {
      grid-template-columns: 1fr;
    }

    label {
      text-align: left;
    }

    .buttons {
      flex-direction: column;
    }

    .buttons .reset-btn {
      margin-right: 0;
      margin-bottom: 10px;
    }
  }

  .error-message {
    text-align: center;
    color: red;
    margin-bottom: 15px;
    font-weight: bold;
  }
</style>

<?php
include 'nav.php';
$error = "";

// Set default empty values to prevent undefined index warnings
$username = $_POST['username'] ?? '';
$class_name = $_POST['class'] ?? '';
$roll = $_POST['roll'] ?? '';
$password = $_POST['password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  include 'db_connection.php';

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Step 1: Check login
  $stmt = $conn->prepare("SELECT st_ID FROM student_login WHERE user_name = ? AND user_password = ?");
  $stmt->bind_param("ss", $username, $password);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows === 1) {
    $stmt->bind_result($st_ID);
    $stmt->fetch();
    $stmt->close();

    // Step 2: Get student info
    $student_query = $conn->prepare("SELECT student_id, class_id FROM student WHERE st_ID = ?");
    $student_query->bind_param("i", $st_ID);
    $student_query->execute();
    $student_result = $student_query->get_result();

    if ($student = $student_result->fetch_assoc()) {
      $student_id = $student['student_id'];
      $class_id = $student['class_id'];

      // Step 3: Get class info
      $class_query = $conn->prepare("SELECT class_name, group_ FROM class WHERE class_id = ?");
      $class_query->bind_param("i", $class_id);
      $class_query->execute();
      $class_result = $class_query->get_result();

      if ($class_row = $class_result->fetch_assoc()) {
        $db_class_name = $class_row['class_name'];
        $db_group = $class_row['group_'];

        // Step 4: Match class and roll
        if ($class_name == $db_class_name && $roll == $student_id) {
          // Redirect to result page with POST
          echo "
          <form id='redirectForm' action='result.php' method='post'>
            <input type='hidden' name='username' value='$username'>
            <input type='hidden' name='class' value='$class_name'>
            <input type='hidden' name='roll' value='$roll'>
            <input type='hidden' name='password' value='$password'>
          </form>
          <script>document.getElementById('redirectForm').submit();</script>
          ";
          exit;
        } else {
          $error = "Result Not Found. Enter correct information.";
        }
      } else {
        $error = "Class information not found.";
      }
    } else {
      $error = "Student not found.";
    }
  } else {
    $error = "Invalid username or password.";
  }

  $conn->close();
}
?>


<div class="container">
  <div class="header">
    <h2>Internal Result Login</h2>
  </div>

  <?php if ($error): ?>
    <div class="error-message"><?php echo $error; ?></div>
  <?php endif; ?>

  <form action="internal_result.php" method="post">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" placeholder="Enter your username" required>

    <label for="class">Class</label>
    <select id="class" name="class" required>
      <option value="" disabled selected>Select class</option>
      <?php
        $conn = new mysqli("localhost", "root", "", "school_management_system");
        $class_query = "SELECT DISTINCT class_name, group_ FROM class ORDER BY class_name";
        $result = $conn->query($class_query);
        while ($row = $result->fetch_assoc()) {
          $display = $row['class_name'] . " - " . $row['group_'];
          echo "<option value='{$row['class_name']}'>{$display}</option>";
        }
        $conn->close();
      ?>
    </select>

    <label for="roll">Roll</label>
    <input type="text" id="roll" name="roll" placeholder="Enter your roll number" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" placeholder="Enter your password" required>

    <div class="buttons">
      <button type="reset" class="reset-btn">Reset</button>
      <button type="submit" class="submit-btn">Submit</button>
    </div>
  </form>
</div>

<?php include 'footer.php'; ?>
