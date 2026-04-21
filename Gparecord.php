<?php
class GpaRecord {
    public static function recompute($studentId, $semesterId) {
        $grades = Grade::getAllWithCredits($studentId, $semesterId);
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($grades as $grade) {
            $totalPoints += $grade['grade'] * $grade['credits'];
            $totalCredits += $grade['credits'];
        }
        
        $gpa = null;
        if ($totalCredits > 0) {
            $gpa = round($totalPoints / $totalCredits, 2);
        }
        
        if ($gpa !== null) {
            return self::upsert($studentId, $semesterId, $gpa);
        }
        return false;
    }
    
    public static function upsert($studentId, $semesterId, $gpa) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO gpa_records (student_id, semester_id, gpa) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE gpa = ?
        ");
        return $stmt->execute([$studentId, $semesterId, $gpa, $gpa]);
    }
    
    public static function get($studentId, $semesterId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT gpa FROM gpa_records WHERE student_id = ? AND semester_id = ?");
        $stmt->execute([$studentId, $semesterId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
