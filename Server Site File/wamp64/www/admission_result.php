<?php include 'nav.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Result</title>
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f4f8;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"] {
            padding: 10px;
            margin-top: 5px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        button {
            padding: 10px 20px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn {
            background-color: #0275d8;
            color: white;
        }
        .reset-btn {
            background-color: #d9534f;
            color: white;
        }
        .search-again {
            text-align: center;
            margin-top: 30px;
        }
        .search-again button {
            background-color: #5cb85c;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 id="titleHeading" style="color:#003366; text-align:center;">Admission Result Search</h2>

    <form id="resultForm">
        <label for="applicantId">Applicant ID</label>
        <input type="text" id="applicantId" name="applicantId" required>

        <label for="academicyear">Academic Year</label>
        <input type="number" id="academicyear" name="academicyear" required>

        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob" required>

        <div class="buttons">
            <button type="reset" class="reset-btn">Reset</button>
            <button type="submit" class="submit-btn">Submit</button>
        </div>
    </form>

    <div id="result" style="margin-top: 30px;"></div>

    <div class="search-again" id="searchAgainBtn" style="display:none;">
        <button onclick="resetForm()">Search Again</button>
    </div>
</div>

<script>
    const form = document.getElementById('resultForm');
    const resultBox = document.getElementById('result');
    const formContainer = document.getElementById('resultForm');
    const searchAgainBtn = document.getElementById('searchAgainBtn');
    const titleHeading = document.getElementById('titleHeading');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('display_admission_result.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            resultBox.innerHTML = data;
            formContainer.style.display = 'none';
            searchAgainBtn.style.display = 'block';
            titleHeading.textContent = 'Admission Result';
        })
        .catch(err => {
            resultBox.innerHTML = '<div style="color:red;">Error loading result.</div>';
        });
    });

    function resetForm() {
        form.reset();
        resultBox.innerHTML = '';
        formContainer.style.display = 'block';
        searchAgainBtn.style.display = 'none';
        titleHeading.textContent = 'Admission Result Search';
    }
</script>

<?php include 'footer.php'; ?>
</body>
</html>
