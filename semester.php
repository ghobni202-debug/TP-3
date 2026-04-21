<?php
class Semester {
    public static function getAll() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM semesters ORDER BY academic_year DESC, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function create($label, $academic_year) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO semesters (label, academic_year) VALUES (?, ?)");
        return $stmt->execute([$label, $academic_year]);
    }
    
    public static function update($id, $label, $academic_year) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE semesters SET label = ?, academic_year = ? WHERE id = ?");
        return $stmt->execute([$label, $academic_year, $id]);
    }
    
    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM semesters WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function setAllInactive() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE semesters SET is_active = FALSE");
        return $stmt->execute();
    }
    
    public static function setActive($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE semesters SET is_active = TRUE WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function getActive() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM semesters WHERE is_active = TRUE LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
