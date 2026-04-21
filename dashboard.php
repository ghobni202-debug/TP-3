<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - My Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Student: <?= htmlspecialchars($_SESSION['name']) ?></span>
            <div>
                <a href="index.php?page=student.history" class="btn btn-outline-info me-2">History</a>
                <a href="index.php?page=logout" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h2>Current Semester Grades</h2>
        <div id="currentGrades">Loading...</div>
        
        <div class="mt-3">
            <a href="api/gpa.php?action=export" class="btn btn-success">Export CSV</a>
        </div>
    </div>
    
    <script src="assets/js/student.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
