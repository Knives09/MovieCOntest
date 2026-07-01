<section class="leaderboard-page">
    <div class="section-container">
        <h1 class="page-title">🏆 Classifica Squadre</h1>
        <p class="page-subtitle">Punteggi basati su completamento sfide e performance delle query.</p>

        <!-- Podium -->
        <div class="podium" id="podium">
            <div class="podium-place place-2" id="podium-2">
                <div class="podium-avatar">🥈</div>
                <div class="podium-name" id="podium-name-2">—</div>
                <div class="podium-score" id="podium-score-2">0 pts</div>
                <div class="podium-bar bar-2"></div>
            </div>
            <div class="podium-place place-1" id="podium-1">
                <div class="podium-avatar">🥇</div>
                <div class="podium-name" id="podium-name-1">—</div>
                <div class="podium-score" id="podium-score-1">0 pts</div>
                <div class="podium-bar bar-1"></div>
            </div>
            <div class="podium-place place-3" id="podium-3">
                <div class="podium-avatar">🥉</div>
                <div class="podium-name" id="podium-name-3">—</div>
                <div class="podium-score" id="podium-score-3">0 pts</div>
                <div class="podium-bar bar-3"></div>
            </div>
        </div>

        <!-- Detailed Results -->
        <div class="result-data" id="leaderboard-data">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
                <h3 style="margin-bottom: 0;">📋 Dettaglio Risultati</h3>
                <button class="run-btn run-btn-secondary" id="reset-leaderboard-btn" style="display: none; margin-right: 0; padding: var(--space-sm) var(--space-lg); font-size: 0.85rem;">
                    🔄 Reset Classifica
                </button>
            </div>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Squadra</th>
                            <th>Sfide completate</th>
                            <th>Punteggio</th>
                            <th>Media MySQL (ms)</th>
                            <th class="neo4j-leaderboard-col" style="display:none">Media Neo4j (ms)</th>
                            <th>Ultima attività</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboard-tbody">
                        <tr>
                            <td colspan="7" class="empty-state">
                                <p>🏁 Nessun risultato ancora.</p>
                                <p>Vai alle <a href="/challenge/1">sfide</a> per iniziare!</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Teams Chart -->
        <div class="chart-container" id="leaderboard-chart-container" style="display:none">
            <h3 class="chart-title">📊 Punteggi per Squadra</h3>
            <canvas id="leaderboardChart"></canvas>
        </div>
    </div>
</section>
