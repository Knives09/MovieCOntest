<?php

namespace MovieChallenge\Service;

/**
 * Benchmark Service
 * Measures and compares execution times between MySQL and Neo4j queries.
 * Provides precise timing with microsecond resolution.
 */
class BenchmarkService
{
    /**
     * Execute a callable and measure its execution time.
     *
     * @param callable $fn The function to benchmark
     * @return array ['result' => mixed, 'time_ms' => float, 'memory_kb' => int]
     */
    public function measure(callable $fn): array
    {
        // Force garbage collection before measurement
        gc_collect_cycles();

        $memBefore = memory_get_usage(true);
        $start = hrtime(true); // Nanosecond precision

        $result = $fn();

        $end = hrtime(true);
        $memAfter = memory_get_usage(true);

        $timeMs = ($end - $start) / 1_000_000; // Convert ns to ms
        $memoryKb = ($memAfter - $memBefore) / 1024;

        return [
            'result' => $result,
            'time_ms' => round($timeMs, 3),
            'memory_kb' => max(0, (int) $memoryKb),
        ];
    }

    /**
     * Run the same challenge on both MySQL and Neo4j, comparing results.
     *
     * @param callable $mysqlFn MySQL query function
     * @param callable $neo4jFn Neo4j query function
     * @return array Comparison results with timing
     */
    public function compare(callable $mysqlFn, callable $neo4jFn): array
    {
        $mysql = $this->measure($mysqlFn);
        $neo4j = $this->measure($neo4jFn);

        $speedup = $mysql['time_ms'] > 0
            ? round($mysql['time_ms'] / max(0.001, $neo4j['time_ms']), 1)
            : 0;

        return [
            'mysql' => [
                'result' => $mysql['result'],
                'time_ms' => $mysql['time_ms'],
                'memory_kb' => $mysql['memory_kb'],
                'result_count' => is_array($mysql['result']) ? count($mysql['result']) : 0,
            ],
            'neo4j' => [
                'result' => $neo4j['result'],
                'time_ms' => $neo4j['time_ms'],
                'memory_kb' => $neo4j['memory_kb'],
                'result_count' => is_array($neo4j['result']) ? count($neo4j['result']) : 0,
            ],
            'speedup' => $speedup,
            'winner' => $mysql['time_ms'] > $neo4j['time_ms'] ? 'neo4j' : 'mysql',
        ];
    }

    /**
     * Run a user-provided SQL query vs a reference Neo4j query.
     */
    public function compareUserQuery(string $userSql, array $params, callable $neo4jFn): array
    {
        $mysqlFn = function() use ($userSql, $params) {
            $db = \MovieChallenge\Database\MySQLConnection::getInstance();
            $stmt = $db->prepare($userSql);
            foreach ($params as $key => $val) {
                // Only bind if the placeholder is actually used in the user SQL query
                if (str_contains($userSql, ':' . $key)) {
                    if (is_int($val)) {
                        $stmt->bindValue($key, $val, \PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue($key, $val, \PDO::PARAM_STR);
                    }
                }
            }
            $stmt->execute();
            return $stmt->fetchAll();
        };

        $mysql = $this->measureWithTimeout($mysqlFn, 20000); // 20 second timeout
        $neo4j = $this->measure($neo4jFn);

        $speedup = $mysql['time_ms'] > 0
            ? round($mysql['time_ms'] / max(0.001, $neo4j['time_ms']), 1)
            : 0;

        return [
            'mysql' => [
                'result' => $mysql['result'],
                'time_ms' => $mysql['time_ms'],
                'memory_kb' => $mysql['memory_kb'],
                'result_count' => is_array($mysql['result']) ? count($mysql['result']) : 0,
                'timeout' => $mysql['timeout'] ?? false,
                'error' => $mysql['error'] ?? null,
            ],
            'neo4j' => [
                'result' => $neo4j['result'],
                'time_ms' => $neo4j['time_ms'],
                'memory_kb' => $neo4j['memory_kb'],
                'result_count' => is_array($neo4j['result']) ? count($neo4j['result']) : 0,
            ],
            'speedup' => $speedup,
            'winner' => $mysql['time_ms'] > $neo4j['time_ms'] ? 'neo4j' : 'mysql',
        ];
    }

    /**
     * Run a scalability test: execute the same query with increasing depth/complexity.
     * This is the KEY visualization that shows MySQL degrades exponentially.
     *
     * @param callable $mysqlFactory Function that accepts depth and returns a callable
     * @param callable $neo4jFactory Function that accepts depth and returns a callable
     * @param array $depths Array of depth values to test
     * @return array Results for each depth level
     */
    public function scalabilityTest(
        callable $mysqlFactory,
        callable $neo4jFactory,
        array $depths = [1, 2, 3, 4, 5, 6]
    ): array {
        $results = [];

        foreach ($depths as $depth) {
            $mysqlFn = $mysqlFactory($depth);
            $neo4jFn = $neo4jFactory($depth);

            // Set a timeout for MySQL (it can hang at high depths)
            $mysqlResult = $this->measureWithTimeout($mysqlFn, 30000); // 30 second timeout
            $neo4jResult = $this->measure($neo4jFn);

            $results[] = [
                'depth' => $depth,
                'mysql_ms' => $mysqlResult['time_ms'],
                'mysql_timeout' => $mysqlResult['timeout'] ?? false,
                'mysql_count' => is_array($mysqlResult['result']) ? count($mysqlResult['result']) : 0,
                'neo4j_ms' => $neo4jResult['time_ms'],
                'neo4j_count' => is_array($neo4jResult['result']) ? count($neo4jResult['result']) : 0,
                'speedup' => $mysqlResult['time_ms'] > 0
                    ? round($mysqlResult['time_ms'] / max(0.001, $neo4jResult['time_ms']), 1)
                    : 0,
            ];
        }

        return $results;
    }

    /**
     * Execute with a timeout (prevents MySQL from hanging at high depths).
     */
    private function measureWithTimeout(callable $fn, int $timeoutMs = 30000): array
    {
        gc_collect_cycles();
        $memBefore = memory_get_usage(true);
        $start = hrtime(true);

        try {
            $result = $fn();
            $end = hrtime(true);
            $timeMs = ($end - $start) / 1_000_000;

            if ($timeMs > $timeoutMs) {
                return [
                    'result' => [],
                    'time_ms' => $timeMs,
                    'memory_kb' => 0,
                    'timeout' => true,
                ];
            }

            return [
                'result' => $result,
                'time_ms' => round($timeMs, 3),
                'memory_kb' => max(0, (int) ((memory_get_usage(true) - $memBefore) / 1024)),
                'timeout' => false,
            ];
        } catch (\Throwable $e) {
            $end = hrtime(true);
            return [
                'result' => [],
                'time_ms' => round(($end - $start) / 1_000_000, 3),
                'memory_kb' => 0,
                'timeout' => true,
                'error' => $e->getMessage(),
            ];
        }
    }
}
