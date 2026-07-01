<section class="benchmark-page">
    <div class="section-container">
        <h1 class="page-title">⚡ Benchmark Comparativo</h1>
        <p class="page-subtitle">
            Confronto sistematico delle performance tra MySQL e Neo4j 
            su tutte e 3 le sfide con profondità crescente.
        </p>

        <!-- Quick Benchmark -->
        <div class="benchmark-controls" id="benchmark-controls">
            <div class="control-group">
                <label class="control-label" for="bench-actor-search">Attore per il benchmark</label>
                <div class="search-input-wrapper">
                    <input type="text" id="bench-actor-search" class="search-input" 
                           placeholder="Cerca un attore (consigliato: attore popolare)..." autocomplete="off">
                    <div class="search-results" id="bench-actor-results"></div>
                </div>
                <input type="hidden" id="bench-selected-actor-id" value="">
                <div class="selected-actor" id="bench-selected-actor" style="display:none">
                    <span id="bench-selected-actor-name"></span>
                    <button class="clear-btn" id="bench-clear-actor">✕</button>
                </div>
            </div>
            <button class="run-btn" id="run-full-benchmark" disabled>
                <span class="run-icon">🚀</span>
                <span class="run-text">Lancia Benchmark Completo</span>
                <span class="run-loading" style="display:none">⏳ Benchmark in corso...</span>
            </button>
        </div>

        <!-- Benchmark Results -->
        <div id="benchmark-results" style="display:none">
            <!-- Scalability Chart -->
            <div class="chart-container chart-large">
                <h3 class="chart-title">📈 Scalabilità: Six Degrees (1→5 hop)</h3>
                <p class="chart-subtitle">
                    Questo è il grafico chiave: mostra come il tempo di MySQL 
                    <strong>esplode esponenzialmente</strong> mentre Neo4j resta quasi costante.
                </p>
                <canvas id="benchScalabilityChart"></canvas>
            </div>

            <!-- Bar Chart Comparison -->
            <div class="chart-container">
                <h3 class="chart-title">📊 Confronto Tempo di Esecuzione</h3>
                <canvas id="benchComparisonChart"></canvas>
            </div>

            <!-- Summary Table -->
            <div class="result-data">
                <h3>📋 Riepilogo Dettagliato</h3>
                <div class="data-table-wrapper">
                    <table class="data-table" id="bench-summary-table">
                        <thead>
                            <tr>
                                <th>Profondità</th>
                                <th>MySQL (ms)</th>
                                <th>Neo4j (ms)</th>
                                <th>Speedup</th>
                                <th>Risultati MySQL</th>
                                <th>Risultati Neo4j</th>
                            </tr>
                        </thead>
                        <tbody id="bench-summary-tbody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Key Takeaways -->
            <div class="takeaway-section" id="takeaway-section">
                <h3 class="section-title">🎓 Cosa Abbiamo Imparato</h3>
                <div class="takeaway-grid">
                    <div class="takeaway-card">
                        <div class="takeaway-icon">📐</div>
                        <h4>Complessità</h4>
                        <p>MySQL: O(n^d) dove d è la profondità. Ogni hop moltiplica i JOIN necessari.</p>
                        <p>Neo4j: O(d) grazie all'index-free adjacency. Ogni hop è un semplice puntatore.</p>
                    </div>
                    <div class="takeaway-card">
                        <div class="takeaway-icon">💡</div>
                        <h4>Leggibilità</h4>
                        <p>SQL: Query ricorsive con CTE, self-join multipli, subquery di esclusione.</p>
                        <p>Cypher: Pattern naturali che descrivono la struttura del grafo.</p>
                    </div>
                    <div class="takeaway-card">
                        <div class="takeaway-icon">🎯</div>
                        <h4>Quando Usare Cosa</h4>
                        <p><strong>MySQL:</strong> Dati tabulari, aggregazioni, report, transazioni ACID complesse.</p>
                        <p><strong>Neo4j:</strong> Relazioni complesse, raccomandazioni, social network, fraud detection.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
