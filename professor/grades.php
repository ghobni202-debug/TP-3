<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Professor Dashboard - Grade Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Professor: <?= htmlspecialchars($_SESSION['name']) ?></span>
            <a href="index.php?page=logout" class="btn btn-outline-light">Logout</a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h2>Grade Entry System</h2>
        
        <div id="feedback"></div>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <label class="form-label">Select Semester</label>
                <select id="semesterSelect" class="form-select">
                    <option value="">-- Select Semester --</option>
                    <?php
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("
                        SELECT DISTINCT s.id, s.label, s.academic_year 
                        FROM semesters s
                        JOIN assignments a ON a.semester_id = s.id
                        WHERE a.professor_id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $semesters = $stmt->fetchAll();
                    foreach ($semesters as $sem): ?>
                        <option value="<?= $sem['id'] ?>">
                            <?= htmlspecialchars($sem['label'] . ' - ' . $sem['academic_year']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label">Select Course</label>
                <select id="courseSelect" class="form-select" disabled>
                    <option value="">-- First select semester --</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button id="saveGradesBtn" class="btn btn-primary w-100">Save All Grades</button>
            </div>
        </div>
        
        <div class="mt-4">
            <table class="table table-bordered" id="gradeTable" style="display: none;">
            </table>
        </div>
    </div>
    
    <script src="assets/js/professor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
