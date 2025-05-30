<?php

class StudentsController {
    private $studentsModel;
    private $isAdmin;

    public function __construct($studentsModel, $isAdmin) {
        $this->studentsModel = $studentsModel;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Show the students management page
     */
    public function show() {
        // Only allow admin access
        if (!$this->isAdmin) {
            header('HTTP/1.1 403 Forbidden');
            echo '<h2>Juurdepääs keelatud</h2><p>Teil pole õigust seda lehte vaadata.</p>';
            return;
        }

        // Get all students with their current grades
        $students = $this->studentsModel->getAllStudents();
        
        // Get student count by grade for statistics
        $gradeStats = $this->studentsModel->getStudentCountByGrade();

        // Include the view
        include __DIR__ . '/../views/students.php';
    }
}
