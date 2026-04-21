<?php
class AdminController {
    public function dashboard() {
        // إحصائيات للـ dashboard
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
        $totalStudents = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'professor'");
        $totalProfessors = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) FROM semesters");
        $totalSemesters = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) FROM courses");
        $totalCourses = $stmt->fetchColumn();
        
        include BASE_PATH . '/views/admin/dashboard.php';
    }
    
    public function semesters() {
        $semesters = Semester::getAll();
        include BASE_PATH . '/views/admin/semesters.php';
    }
    
    public function saveSemester() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $label = sanitize($_POST['label']);
            $year = sanitize($_POST['academic_year']);
            $id = $_POST['id'] ?? null;
            
            if ($id) {
                Semester::update($id, $label, $year);
                flash('success', 'Semester updated successfully');
            } else {
                Semester::create($label, $year);
                flash('success', 'Semester created successfully');
            }
        }
        header('Location: index.php?page=admin.semesters');
        exit;
    }
    
    public function toggleSemester() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id']);
            Semester::setAllInactive();
            Semester::setActive($id);
            flash('success', 'Active semester updated');
        }
        header('Location: index.php?page=admin.semesters');
        exit;
    }
    
    public function deleteSemester() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id']);
            
            // التحقق من وجود مواد مرتبطة
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) FROM courses WHERE semester_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                flash('danger', 'Cannot delete: semester has linked courses');
            } else {
                Semester::delete($id);
                flash('success', 'Semester deleted');
            }
        }
        header('Location: index.php?page=admin.semesters');
        exit;
    }
    
    public function courses() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT c.*, s.label as semester_label, s.academic_year 
            FROM courses c
            JOIN semesters s ON c.semester_id = s.id
            ORDER BY s.academic_year DESC, s.label, c.name
        ");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $semesters = Semester::getAll();
        include BASE_PATH . '/views/admin/courses.php';
    }
    
    public function saveCourse() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name']);
            $credits = intval($_POST['credits']);
            $semesterId = intval($_POST['semester_id']);
            $id = $_POST['id'] ?? null;
            
            if ($credits <= 0) {
                flash('danger', 'Credits must be a positive integer');
            } elseif ($id) {
                Course::update($id, $name, $credits, $semesterId);
                flash('success', 'Course updated');
            } else {
                Course::create($name, $credits, $semesterId);
                flash('success', 'Course created');
            }
        }
        header('Location: index.php?page=admin.courses');
        exit;
    }
    
    public function deleteCourse() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id']);
            if (Grade::countByCourse($id) > 0) {
                flash('danger', 'Cannot delete: grades exist for this course');
            } else {
                Course::delete($id);
                flash('success', 'Course deleted');
            }
        }
        header('Location: index.php?page=admin.courses');
        exit;
    }
    
    public function professors() {
        $professors = User::getAllByRole('professor');
        include BASE_PATH . '/views/admin/professors.php';
    }
    
    public function saveProfessor() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name']);
            $email = sanitize($_POST['email']);
            $password = $_POST['password'] ?? '';
            $id = $_POST['id'] ?? null;
            
            if (User::emailExists($email, $id)) {
                flash('danger', 'Email already in use');
            } elseif ($id) {
                User::update($id, $name, $email);
                if (!empty($password)) {
                    User::updatePassword($id, password_hash($password, PASSWORD_BCRYPT));
                }
                flash('success', 'Professor updated');
            } else {
                if (empty($password)) {
                    flash('danger', 'Password required for new professor');
                } else {
                    User::create($name, $email, password_hash($password, PASSWORD_BCRYPT), 'professor');
                    flash('success', 'Professor created');
                }
            }
        }
        header('Location: index.php?page=admin.professors');
        exit;
    }
    
    public function students() {
        $students = User::getAllByRole('student');
        include BASE_PATH . '/views/admin/students.php';
    }
    
    public function assignments() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT a.*, 
                   p.name as professor_name,
                   c.name as course_name,
                   s.label as semester_label,
                   s.academic_year
            FROM assignments a
            JOIN users p ON a.professor_id = p.id
            JOIN courses c ON a.course_id = c.id
            JOIN semesters s ON a.semester_id = s.id
            ORDER BY s.academic_year DESC, s.label
        ");
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $professors = User::getAllByRole('professor');
        $courses = Course::getAll();
        $semesters = Semester::getAll();
        
        include BASE_PATH . '/views/admin/assignments.php';
    }
    
    public function saveAssignment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profId = intval($_POST['professor_id']);
            $courseId = intval($_POST['course_id']);
            $semId = intval($_POST['semester_id']);
            
            if (Assignment::courseAlreadyAssigned($courseId, $semId)) {
                flash('danger', 'This course already has a professor for this semester');
            } else {
                Assignment::create($profId, $courseId, $semId);
                flash('success', 'Assignment created');
            }
        }
        header('Location: index.php?page=admin.assignments');
        exit;
    }
}
