<?php
require_once __DIR__ . '/Database.php';

class StudentsModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Get all unique students who have submitted results
     *
     * @return array Array of students with email, name, and current grade
     */
    public function getAllStudents() {
        // First, ensure all students from results are in the students table
        $this->syncStudentsFromResults();

        $stmt = $this->db->query('
            SELECT email, name, grade
            FROM students
            ORDER BY name
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sync students from results table to students table
     * This ensures all students who have submitted results are in the students table
     */
    private function syncStudentsFromResults() {
        $this->db->query('
            INSERT OR IGNORE INTO students (email, name)
            SELECT DISTINCT email, name
            FROM results
            WHERE email IS NOT NULL AND name IS NOT NULL
        ');
    }

    /**
     * Get a student's current grade
     *
     * @param string $email Student's email
     * @return string|null Current grade or null if not set
     */
    public function getStudentGrade($email) {
        $stmt = $this->db->prepare('
            SELECT grade
            FROM students
            WHERE email = ?
        ');
        $stmt->execute([$email]);
        return $stmt->fetchColumn();
    }

    /**
     * Update a student's grade
     *
     * @param string $email Student's email
     * @param string|null $grade New grade (null to remove grade)
     * @return bool Success status
     */
    public function updateStudentGrade($email, $grade) {
        try {
            // Ensure the student exists in the students table
            $this->syncStudentsFromResults();

            // Convert empty string to null
            if ($grade === '') {
                $grade = null;
            }

            // Update the grade in the students table
            $stmt = $this->db->prepare('UPDATE students SET grade = ? WHERE email = ?');
            $result = $stmt->execute([$grade, $email]);

            return $result;
        } catch (Exception $e) {
            error_log("Error updating student grade: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get student count by grade
     *
     * @return array Array with grade as key and count as value
     */
    public function getStudentCountByGrade() {
        // Ensure students table is up to date
        $this->syncStudentsFromResults();

        $stmt = $this->db->query('
            SELECT
                COALESCE(grade, "Määramata") as grade_label,
                COUNT(*) as count
            FROM students
            GROUP BY grade
            ORDER BY grade
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
