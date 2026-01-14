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
        // Get active tab (default to 'active' for active students)
        $activeTab = $_GET['tab'] ?? 'active';

        // Get period filter
        $period = $_GET['period'] ?? '30';
        $groupBy = $_GET['group'] ?? 'day';

        // Get week offset for navigation (0 = current week) - kept for backwards compatibility
        $weekOffset = isset($_GET['week']) ? max(0, (int)$_GET['week']) : 0;

        // Get preset or custom date range
        $periodPreset = $_GET['preset'] ?? 'week';

        // Get date range - from URL params or calculate from preset
        if (isset($_GET['from']) && isset($_GET['to'])) {
            // Custom dates from URL
            $dateFrom = $_GET['from'];
            $dateTo = $_GET['to'];
            // If custom dates don't match any preset, clear the preset
            $presetDates = $this->studentsModel->getPresetDateRange($periodPreset);
            if ($dateFrom !== $presetDates['from'] || $dateTo !== $presetDates['to']) {
                $periodPreset = '';
            }
        } else {
            // Calculate from preset
            $presetDates = $this->studentsModel->getPresetDateRange($periodPreset);
            $dateFrom = $presetDates['from'];
            $dateTo = $presetDates['to'];
        }

        // Get all students with their current grades (for the classes tab)
        $students = $this->studentsModel->getAllStudents();

        // Get student count by grade for statistics
        $gradeStats = $this->studentsModel->getStudentCountByGrade();

        // Get active students data
        $activeStudents = [];
        $studentsByDay = [];
        $studentsByWeek = [];
        $weekInfo = null;

        if ($activeTab === 'active') {
            // Use date range based navigation
            $studentsByDay = $this->studentsModel->getActiveStudentsByDateRange($dateFrom, $dateTo);

            // Format week info for display (still useful for label)
            $weekInfo = [
                'start' => (new DateTime($dateFrom))->format('d.m.Y'),
                'end' => (new DateTime($dateTo))->format('d.m.Y'),
                'label' => (new DateTime($dateFrom))->format('d.m') . ' - ' . (new DateTime($dateTo))->format('d.m.Y')
            ];

            // Count unique students in this period
            $uniqueEmails = [];
            foreach ($studentsByDay as $dayStudents) {
                foreach ($dayStudents as $student) {
                    $uniqueEmails[$student['email']] = true;
                }
            }
            $activeStudents = array_keys($uniqueEmails);
        }

        // Pass isAdmin to view
        $isAdmin = $this->isAdmin;

        // Include the view
        include __DIR__ . '/../views/students.php';
    }
}
