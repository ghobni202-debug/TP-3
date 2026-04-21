<?php
class Grade {
    public static function upsert($studentId, $courseId, $semesterId, $professorId, $grade) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO grades (student_id, course_id, semester_id, professor_id, grade) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE grade = ?, professor_id = ?
        ");
        return $stmt->execute([$studentId, $courseId, $semesterId, $professorId, $grade, $grade, $professorId]);
    }
    
    public static function get($studentId, $courseId, $semesterId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT grade FROM grades WHERE student_id = ? AND course_id = ? AND semester_id = ?");
        $stmt->execute([$studentId, $courseId, $semesterId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['grade'] : null;
    }
    
    public static function getAllWithCredits($studentId, $semesterId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT g.grade, c.credits 
            FROM grades g
            JOIN courses c ON g.course_id = c.id
            WHERE g.student_id = ? AND g.semester_id = ?
        ");
        $stmt->execute([$studentId, $semesterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function countByCourse($courseId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM grades WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetchColumn();
    }
    
    public static function countByStudentSemester($studentId, $semesterId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM grades WHERE student_id = ? AND semester_id = ?");
        $stmt->execute([$studentId, $semesterId]);
        return $stmt->fetchColumn();
    }
    
    public static function deleteByStudent($studentId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM grades WHERE student_id = ?");
        return $stmt->execute([$studentId]);
    }
}
