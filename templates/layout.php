<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MovieChallenge — Confronto interattivo tra MySQL e Neo4j su dati cinematografici reali. Scopri la potenza dei database a grafo.">
    <title><?= htmlspecialchars($pageTitle ?? 'MovieChallenge') ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- App Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav" id="main-nav">
        <div class="nav-container">
            <a href="/" class="nav-logo" id="nav-logo">
                <span class="logo-icon">🎬</span>
                <span class="logo-text">Movie<span class="logo-highlight">Challenge</span></span>
            </a>
            
            <div class="nav-links">
                <a href="/" class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/' ? 'active' : '') ?>" id="nav-dashboard">
                    <span class="nav-icon">📊</span>
                    Dashboard
                </a>
                <a href="/challenge/1" class="nav-link <?= (str_starts_with($_SERVER['REQUEST_URI'], '/challenge') ? 'active' : '') ?>" id="nav-challenges">
                    <span class="nav-icon">🎯</span>
                    Sfide
                </a>

                <a href="/schema" class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/schema' ? 'active' : '') ?>" id="nav-schema">
                    <span class="nav-icon">🗂️</span>
                    Schema DB
                </a>

                <a href="/graph-schema" class="nav-link teacher-only <?= ($_SERVER['REQUEST_URI'] === '/graph-schema' ? 'active' : '') ?>" id="nav-graph-schema" style="display:none">
                    <span class="nav-icon">🕸️</span>
                    Schema Grafo
                </a>

                <a href="/leaderboard" class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/leaderboard' ? 'active' : '') ?>" id="nav-leaderboard">
                    <span class="nav-icon">🏆</span>
                    Classifica
                </a>
            </div>

            <div class="nav-badge">
                <span class="badge-teacher" id="badge-teacher" style="display:none">👨‍🏫 Docente</span>
                <span class="badge-mysql">MySQL</span>
                <span class="badge-vs" id="nav-badge-vs">vs</span>
                <span class="badge-neo4j" id="nav-badge-neo4j">Neo4j</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="main-content">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="main-footer" id="main-footer">
        <div class="footer-container">
            <p>🎬 MovieChallenge — Powered by <span class="text-mysql">MySQL 8</span><span class="teacher-only" style="display:none"> & <span class="text-neo4j">Neo4j 5</span></span></p>
            <p class="footer-sub"><span class="student-only">Ottimizzazione Query Relazionali</span><span class="teacher-only" style="display:none">Database a Grafo vs Relazionale</span> • Challenge Edition</p>
        </div>
    </footer>

    <!-- App Scripts -->
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/charts.js"></script>
    <script src="/assets/js/challenge.js"></script>
</body>
</html>
