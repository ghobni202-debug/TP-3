<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

foreach (glob(__DIR__ . '/../models/*.php') as $file) {
    require_once $file;
}

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$studentId = $_SESSION['user_id'];
$action = $_GET['action'] ?? null;

if ($action === 'current') {
    $semester = Semester::getActive();
    
    if (!$semester) {
        echo json_encode(['error' => 'No active semester']);
        exit;
    }
    
    // التحقق من تسجيل الطالب
    if (!Enrollment::exists($studentId, $semester['id'])) {
        echo json_encode(['error' => 'Not enrolled in active semester']);
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT c.id, c.name, c.credits 
        FROM courses c
        WHERE c.semester_id = ?
    ");
    $stmt->execute([$semester['id']]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($courses as &$course) {
        $grade = Grade::get($studentId, $course['id'], $semester['id']);
        $course['grade'] = $grade !== null ? floatval($grade) : null;
        $course['grade_points'] = $course['grade'] !== null ? $course['grade'] * $course['credits'] : null;
    }
    
    $gpaRecord = GpaRecord::get($studentId, $semester['id']);
    
    echo json_encode([
        'semester' => $semester,
        'courses' => $courses,
        'gpa' => $gpaRecord ? floatval($gpaRecord['gpa']) : null
    ]);
    
} elseif ($action === 'history') {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT s.id, s.label, s.academic_year, gr.gpa
        FROM enrollments e
        JOIN semesters s ON e.semester_id = s.id
        LEFT JOIN gpa_records gr ON gr.student_id = e.student_id AND gr.semester_id = s.id
        WHERE e.student_id = ?
        ORDER BY s.academic_year DESC, s.label
    ");
    $stmt->execute([$studentId]);
    $semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($semesters as &$sem) {
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.credits, g.grade
            FROM courses c
            LEFT JOIN grades g ON g.course_id = c.id AND g.student_id = ? AND g.semester_id = c.semester_id
            WHERE c.semester_id = ?
        ");
        $stmt->execute([$studentId, $sem['id']]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($courses as &$course) {
            $course['grade'] = $course['grade'] !== null ? floatval($course['grade']) : null;
            $course['grade_points'] = $course['grade'] !== null ? $course['grade'] * $course['credits'] : null;
        }
        
        $sem['courses'] = $courses;
        $sem['gpa'] = $sem['gpa'] !== null ? floatval($sem['gpa']) : null;
    }
    
    echo json_encode($semesters);
    
} elseif ($action === 'export') {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT 
            s.label as semester_label,
            s.academic_year,
            c.name as course_name,
            c.credits,
            g.grade,
            (g.grade * c.credits) as grade_points
        FROM grades g
        JOIN semesters s ON g.semester_id = s.id
        JOIN courses c ON g.course_id = c.id
        WHERE g.student_id = ?
        ORDER BY s.academic_year DESC, s.label, c.name
    ");
    $stmt->execute([$studentId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="gpa_history.csv"');
    
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Semester', 'Academic Year', 'Course', 'Credits', 'Grade', 'Grade Points']);
    
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['semester_label'],
            $row['academic_year'],
            $row['course_name'],
            $row['credits'],
            $row['grade'],
            $row['grade_points']
        ]);
    }
    
    fclose($out);
    exit;
    
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
}
