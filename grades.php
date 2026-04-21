<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

foreach (glob(__DIR__ . '/../models/*.php') as $file) {
    require_once $file;
}

header('Content-Type: application/json');

// التحقق من الدور
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$professorId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'courses') {
    $semesterId = intval($_GET['semester_id']);
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT c.id, c.name 
        FROM courses c
        JOIN assignments a ON a.course_id = c.id
        WHERE a.professor_id = ? AND a.semester_id = ?
    ");
    $stmt->execute([$professorId, $semesterId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($courses);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'students') {
    $semesterId = intval($_GET['semester_id']);
    $courseId = intval($_GET['course_id']);
    
    // التحقق من الصلاحية
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT id FROM assignments 
        WHERE professor_id = ? AND course_id = ? AND semester_id = ?
    ");
    $stmt->execute([$professorId, $courseId, $semesterId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    
    // جلب الطلاب المسجلين
    $stmt = $db->prepare("
        SELECT u.id, u.name 
        FROM users u
        JOIN enrollments e ON e.student_id = u.id
        WHERE e.semester_id = ?
        ORDER BY u.name
    ");
    $stmt->execute([$semesterId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // جلب الدرجات الموجودة
    foreach ($students as &$student) {
        $stmt = $db->prepare("
            SELECT grade FROM grades 
            WHERE student_id = ? AND course_id = ? AND semester_id = ?
        ");
        $stmt->execute([$student['id'], $courseId, $semesterId]);
        $grade = $stmt->fetch(PDO::FETCH_ASSOC);
        $student['grade'] = $grade ? floatval($grade['grade']) : null;
    }
    
    echo json_encode($students);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    $semesterId = intval($_POST['semester_id']);
    $courseId = intval($_POST['course_id']);
    $grades = $_POST['grades'] ?? [];
    
    // التحقق من الصلاحية
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT id FROM assignments 
        WHERE professor_id = ? AND course_id = ? AND semester_id = ?
    ");
    $stmt->execute([$professorId, $courseId, $semesterId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    
    $saved = 0;
    $validGrades = ['0.0', '1.0', '2.0', '3.0', '4.0'];
    
    foreach ($grades as $entry) {
        $studentId = intval($entry['student_id']);
        $grade = floatval($entry['grade']);
        
        if (!in_array($entry['grade'], $validGrades)) {
            continue;
        }
        
        Grade::upsert($studentId, $courseId, $semesterId, $professorId, $grade);
        GpaRecord::recompute($studentId, $semesterId);
        $saved++;
    }
    
    echo json_encode(['success' => true, 'saved' => $saved]);
    
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}
