<?php

namespace MovieChallenge\Service;

use MovieChallenge\Database\MySQLConnection;
use PDO;

/**
 * Team Service
 * Manages teams, scores, and the leaderboard.
 */
class TeamService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = MySQLConnection::getInstance();
    }

    /**
     * Get all teams.
     */
    public function getTeams(): array
    {
        $stmt = $this->db->query(
            "SELECT team_name, COUNT(*) as member_count
             FROM users
             WHERE team_name IS NOT NULL AND team_name != ''
             GROUP BY team_name
             ORDER BY team_name"
        );
        return $stmt->fetchAll();
    }

    /**
     * Get team members.
     */
    public function getTeamMembers(string $teamName): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE team_name = :team_name ORDER BY username"
        );
        $stmt->execute(['team_name' => $teamName]);
        return $stmt->fetchAll();
    }

    /**
     * Get leaderboard with scores.
     * Scores are based on challenge completion and performance.
     */
    public function getLeaderboard(): array
    {
        // Check if challenge_results table exists
        try {
            $stmt = $this->db->query(
                "SELECT team_name,
                        COUNT(*) as challenges_completed,
                        SUM(score) as total_score,
                        ROUND(AVG(mysql_time_ms), 1) as avg_mysql_ms,
                        ROUND(AVG(neo4j_time_ms), 1) as avg_neo4j_ms,
                        MAX(completed_at) as last_activity
                 FROM challenge_results
                 GROUP BY team_name
                 ORDER BY total_score DESC"
            );
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Table might not exist yet
            return [];
        }
    }

    /**
     * Record a challenge result.
     */
    public function recordResult(
        string $teamName,
        int $challengeId,
        float $mysqlTimeMs,
        float $neo4jTimeMs,
        int $resultCount
    ): void {
        // Calculate score: bonus for query optimization, penalty for slow MySQL queries
        $speedup = $mysqlTimeMs / max(0.001, $neo4jTimeMs);
        $score = (int) min(100, $speedup * 10 + $resultCount);

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO challenge_results 
                 (team_name, challenge_id, mysql_time_ms, neo4j_time_ms, result_count, score, completed_at)
                 VALUES (:team_name, :challenge_id, :mysql_time_ms, :neo4j_time_ms, :result_count, :score, NOW())
                 ON DUPLICATE KEY UPDATE
                   mysql_time_ms = VALUES(mysql_time_ms),
                   neo4j_time_ms = VALUES(neo4j_time_ms),
                   result_count = VALUES(result_count),
                   score = GREATEST(score, VALUES(score)),
                   completed_at = NOW()"
            );
            $stmt->execute([
                'team_name' => $teamName,
                'challenge_id' => $challengeId,
                'mysql_time_ms' => $mysqlTimeMs,
                'neo4j_time_ms' => $neo4jTimeMs,
                'result_count' => $resultCount,
                'score' => $score,
            ]);
        } catch (\PDOException $e) {
            error_log("Could not record result: " . $e->getMessage());
        }
    }

    /**
     * Get default teams for the challenge.
     */
    public function getDefaultTeams(): array
    {
        return [
            ['name' => 'Team Alpha', 'color' => '#f97316', 'icon' => '🦁'],
            ['name' => 'Team Beta', 'color' => '#00b4d8', 'icon' => '🐲'],
            ['name' => 'Team Gamma', 'color' => '#10b981', 'icon' => '🦅'],
        ];
    }
}
