<?php

namespace App\Services\Results;

class GradeCalculator
{
    /**
     * @return array{total_score: float, grade: string, remark: string}
     */
    public function fromScores(float $caScore, float $examScore): array
    {
        $totalScore = round($caScore + $examScore, 2);

        return [
            'total_score' => $totalScore,
            'grade' => $this->gradeFor($totalScore),
            'remark' => $this->remarkFor($totalScore),
        ];
    }

    protected function gradeFor(float $totalScore): string
    {
        return match (true) {
            $totalScore >= 70 => 'A',
            $totalScore >= 60 => 'B',
            $totalScore >= 50 => 'C',
            $totalScore >= 45 => 'D',
            $totalScore >= 40 => 'E',
            default => 'F',
        };
    }

    protected function remarkFor(float $totalScore): string
    {
        return match (true) {
            $totalScore >= 70 => 'Excellent',
            $totalScore >= 60 => 'Very Good',
            $totalScore >= 50 => 'Good',
            $totalScore >= 45 => 'Fair',
            $totalScore >= 40 => 'Pass',
            default => 'Needs Improvement',
        };
    }
}
