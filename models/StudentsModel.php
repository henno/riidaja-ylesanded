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
     * Sync students from results and sessions tables to students table
     * This ensures all students who have submitted results OR started sessions are in the students table
     */
    private function syncStudentsFromResults() {
        // Sync from results table
        $this->db->query('
            INSERT OR IGNORE INTO students (email, name)
            SELECT DISTINCT email, name
            FROM results
            WHERE email IS NOT NULL AND name IS NOT NULL
        ');

        // Also sync from exercise_sessions (includes students with only abandoned sessions)
        $this->db->query('
            INSERT OR IGNORE INTO students (email, name)
            SELECT DISTINCT email, name
            FROM exercise_sessions
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

    /**
     * Get active students within a specific time period
     *
     * @param int $days Number of days to look back (default 30)
     * @return array Array of active students with their activity info
     */
    public function getActiveStudents($days = 30) {
        $this->syncStudentsFromResults();

        $stmt = $this->db->prepare('
            SELECT
                s.email,
                s.name,
                s.grade,
                COUNT(r.id) as result_count,
                MAX(r.timestamp) as last_activity,
                MIN(r.timestamp) as first_activity
            FROM students s
            INNER JOIN results r ON s.email = r.email
            WHERE r.timestamp >= datetime("now", "-" || ? || " days")
            GROUP BY s.email, s.name, s.grade
            ORDER BY last_activity DESC
        ');
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active students grouped by day
     *
     * @param int $days Number of days to look back
     * @return array Array with dates as keys and student arrays as values
     */
    public function getActiveStudentsByDay($days = 30) {
        $stmt = $this->db->prepare('
            SELECT
                date(r.timestamp) as activity_date,
                r.email,
                r.name,
                COUNT(r.id) as result_count,
                SUM(ABS(r.elapsed)) as total_seconds
            FROM results r
            WHERE r.timestamp >= datetime("now", "-" || ? || " days")
            GROUP BY date(r.timestamp), r.email, r.name
            ORDER BY activity_date DESC, r.name ASC
        ');
        $stmt->execute([$days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by date
        $grouped = [];
        foreach ($results as $row) {
            $date = $row['activity_date'];
            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            $grouped[$date][] = $row;
        }
        return $grouped;
    }

    /**
     * Get active students grouped by week
     *
     * @param int $weeks Number of weeks to look back
     * @return array Array with week info as keys and student arrays as values
     */
    public function getActiveStudentsByWeek($weeks = 4) {
        $days = $weeks * 7;
        $stmt = $this->db->prepare('
            SELECT
                strftime("%Y-%W", r.timestamp) as week_num,
                MIN(date(r.timestamp)) as week_start,
                r.email,
                r.name,
                COUNT(r.id) as result_count
            FROM results r
            WHERE r.timestamp >= datetime("now", "-" || ? || " days")
            GROUP BY week_num, r.email, r.name
            ORDER BY week_num DESC, r.name ASC
        ');
        $stmt->execute([$days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by week
        $grouped = [];
        foreach ($results as $row) {
            $week = $row['week_num'];
            if (!isset($grouped[$week])) {
                $grouped[$week] = [
                    'week_start' => $row['week_start'],
                    'students' => []
                ];
            }
            $grouped[$week]['students'][] = $row;
        }
        return $grouped;
    }

    /**
     * Get active students for a specific week by offset
     *
     * @param int $weekOffset 0 = current week, 1 = last week, 2 = week before last, etc.
     * @return array Array with date as key and students array as value
     */
    public function getActiveStudentsByWeekOffset($weekOffset = 0) {
        // Calculate week start (Monday) and end (Sunday) based on offset
        $today = new DateTime();
        $dayOfWeek = (int)$today->format('N'); // 1 = Monday, 7 = Sunday

        // Go to Monday of current week
        $monday = clone $today;
        $monday->modify('-' . ($dayOfWeek - 1) . ' days');

        // Apply week offset
        if ($weekOffset > 0) {
            $monday->modify('-' . ($weekOffset * 7) . ' days');
        }

        $sunday = clone $monday;
        $sunday->modify('+6 days');

        $weekStart = $monday->format('Y-m-d');
        $weekEnd = $sunday->format('Y-m-d');

        $stmt = $this->db->prepare('
            SELECT
                date(r.timestamp) as activity_date,
                r.email,
                r.name,
                COUNT(r.id) as result_count,
                SUM(ABS(r.elapsed)) as total_seconds
            FROM results r
            WHERE date(r.timestamp) >= ? AND date(r.timestamp) <= ?
            GROUP BY date(r.timestamp), r.email, r.name
            ORDER BY activity_date DESC, r.name ASC
        ');
        $stmt->execute([$weekStart, $weekEnd]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by date
        $grouped = [];
        foreach ($results as $row) {
            $date = $row['activity_date'];
            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            $grouped[$date][] = $row;
        }
        return $grouped;
    }

    /**
     * Get week date range info for display
     *
     * @param int $weekOffset 0 = current week, 1 = last week, etc.
     * @return array Array with 'start', 'end', and 'label' keys
     */
    public function getWeekInfo($weekOffset = 0) {
        $today = new DateTime();
        $dayOfWeek = (int)$today->format('N');

        $monday = clone $today;
        $monday->modify('-' . ($dayOfWeek - 1) . ' days');

        if ($weekOffset > 0) {
            $monday->modify('-' . ($weekOffset * 7) . ' days');
        }

        $sunday = clone $monday;
        $sunday->modify('+6 days');

        return [
            'start' => $monday->format('d.m.Y'),
            'end' => $sunday->format('d.m.Y'),
            'label' => $monday->format('d.m') . ' - ' . $sunday->format('d.m.Y')
        ];
    }

    /**
     * Get active students by date range
     * Includes both completed results AND session time (including abandoned sessions)
     * This ensures all time spent is tracked, even if student refreshes the page
     *
     * @param string $dateFrom Start date (Y-m-d format)
     * @param string $dateTo End date (Y-m-d format)
     * @return array Array with date as key and students array as value
     */
    public function getActiveStudentsByDateRange($dateFrom, $dateTo) {
        // Combined query: get results AND sessions, then merge them
        // Sessions track ALL time including abandoned attempts (prevents F5 cheating)
        $stmt = $this->db->prepare('
            SELECT
                activity_date,
                email,
                name,
                SUM(result_count) as result_count,
                SUM(session_count) as session_count,
                SUM(total_seconds) as total_seconds
            FROM (
                -- Completed results (for counting completed exercises)
                SELECT
                    date(r.timestamp) as activity_date,
                    r.email,
                    r.name,
                    COUNT(r.id) as result_count,
                    0 as session_count,
                    0 as total_seconds
                FROM results r
                WHERE date(r.timestamp) >= ? AND date(r.timestamp) <= ?
                GROUP BY date(r.timestamp), r.email, r.name

                UNION ALL

                -- All sessions (for tracking total time including abandoned)
                SELECT
                    date(s.started_at) as activity_date,
                    s.email,
                    s.name,
                    0 as result_count,
                    COUNT(s.id) as session_count,
                    COALESCE(SUM(s.duration_seconds), 0) as total_seconds
                FROM exercise_sessions s
                WHERE date(s.started_at) >= ? AND date(s.started_at) <= ?
                  AND s.duration_seconds IS NOT NULL
                GROUP BY date(s.started_at), s.email, s.name
            ) combined
            GROUP BY activity_date, email, name
            ORDER BY activity_date DESC, name ASC
        ');
        $stmt->execute([$dateFrom, $dateTo, $dateFrom, $dateTo]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by date
        $grouped = [];
        foreach ($results as $row) {
            $date = $row['activity_date'];
            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            $grouped[$date][] = $row;
        }
        return $grouped;
    }

    /**
     * Calculate preset date ranges
     *
     * @param string $preset Preset name (today, week, month, prevmonth, prevmonth_to_today, prevyear)
     * @return array Array with 'from' and 'to' dates in Y-m-d format
     */
    public function getPresetDateRange($preset) {
        $today = new DateTime();
        $year = (int)$today->format('Y');
        $month = (int)$today->format('m');
        $dayOfWeek = (int)$today->format('N'); // 1 = Monday, 7 = Sunday

        switch ($preset) {
            case 'today':
                $from = $to = $today->format('Y-m-d');
                break;

            case 'week':
                // Monday of current week
                $monday = clone $today;
                $monday->modify('-' . ($dayOfWeek - 1) . ' days');
                $from = $monday->format('Y-m-d');
                $to = $today->format('Y-m-d');
                break;

            case 'month':
                $from = $today->format('Y-m-01');
                $to = $today->format('Y-m-d');
                break;

            case 'prevmonth':
                $prevMonth = new DateTime($today->format('Y-m-01'));
                $prevMonth->modify('-1 month');
                $from = $prevMonth->format('Y-m-01');
                $to = $prevMonth->format('Y-m-t'); // Last day of month
                break;

            case 'prevmonth_to_today':
                $prevMonth = new DateTime($today->format('Y-m-01'));
                $prevMonth->modify('-1 month');
                $from = $prevMonth->format('Y-m-01');
                $to = $today->format('Y-m-d');
                break;

            case 'prevyear':
                $from = ($year - 1) . '-01-01';
                $to = ($year - 1) . '-12-31';
                break;

            default:
                // Default to current week
                $monday = clone $today;
                $monday->modify('-' . ($dayOfWeek - 1) . ' days');
                $from = $monday->format('Y-m-d');
                $to = $today->format('Y-m-d');
        }

        return [
            'from' => $from,
            'to' => $to
        ];
    }
}
