<?php

require_once __DIR__ . '/../models/ResultsModel.php';

if (!function_exists('getExerciseRoundConfig')) {
    function getExerciseRoundConfig(string $exerciseId, array $rounds): array
    {
        $resultsModel = new ResultsModel();
        $userEmail = isset($_SESSION) ? ($_SESSION['user']['email'] ?? '') : '';
        $numericExerciseId = (int) $exerciseId;
        $stmt = $resultsModel->getDb()->prepare(
            'SELECT COUNT(*) FROM results WHERE email = ? AND (exercise_id = ? OR exercise_id = ?) AND elapsed > 0'
        );
        $stmt->execute([$userEmail, $exerciseId, $numericExerciseId]);
        $passCount = (int) $stmt->fetchColumn();

        $round = min($passCount + 1, 3);
        $config = $rounds[$round] ?? $rounds[3];
        $config['round'] = $round;
        $config['pass_count'] = min($passCount, 3);

        return $config;
    }
}
