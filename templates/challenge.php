<section class="challenge-page" data-challenge-id="<?= $challenge['id'] ?>">
    <!-- Challenge Header -->
    <div class="challenge-header">
        <div class="challenge-header-content">
            <div class="challenge-meta">
                <a href="/" class="back-link" id="back-to-dashboard">← Dashboard</a>
                <span class="challenge-badge">Sfida #<?= $challenge['id'] ?></span>
            </div>
            <h1>
                <span class="challenge-icon-lg"><?= $challenge['icon'] ?></span>
                <?= htmlspecialchars($challenge['title']) ?>
            </h1>
            <p class="challenge-desc"><?= htmlspecialchars($challenge['description']) ?></p>
            
            <div class="weakness-grid" id="weakness-grid">
                <div class="weakness-card mysql-weakness">
                    <div class="weakness-icon">🐬 MySQL</div>
                    <div class="weakness-label">Punto debole</div>
                    <p><?= htmlspecialchars($challenge['mysql_weakness']) ?></p>
                </div>
                <div class="weakness-card neo4j-strength teacher-only" style="display:none">
                    <div class="weakness-icon">🔵 Neo4j</div>
                    <div class="weakness-label">Punto di forza</div>
                    <p><?= htmlspecialchars($challenge['neo4j_strength']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Query Input & Reference -->
    <div class="section-container">
        <h2 class="section-title">
            <span class="student-only">Scrivi la tua Query MySQL</span>
            <span class="teacher-only" style="display:none">Confronto MySQL vs Neo4j</span>
        </h2>
        <p class="section-subtitle">
            <span class="student-only">Scrivi la query SQL per risolvere la sfida utilizzando i segnaposti corretti. Clicca su Esegui per testarla.</span>
            <span class="teacher-only" style="display:none">Scrivi la tua query SQL e usa i segnaposti corretti. Clicca su Esegui per validare e misurare le performance rispetto a Neo4j.</span>
        </p>
        <div class="query-comparison" id="query-comparison">
            <div class="query-panel mysql-panel" id="mysql-query-panel">
                <div class="query-panel-header" style="display: flex; flex-direction: column; align-items: stretch; gap: var(--space-xs); padding: var(--space-sm) var(--space-lg);">
                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span class="db-tag tag-mysql">🐬 MySQL / SQL Editor</span>
                        <button id="reset-template-btn" style="background: rgba(255,255,255,0.08); border: 1px solid var(--border-glass); border-radius: var(--radius-sm); color: var(--text-primary); cursor: pointer; font-size: 0.75rem; padding: 3px 10px; transition: all var(--transition-fast);">
                            🔄 Ripristina Template
                        </button>
                    </div>
                    <div style="border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: var(--space-xs); display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Parametri query:</span>
                        <span class="query-lines" id="placeholder-info" style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--neo4j-primary); font-weight: 600;">
                            <?php if ($challenge['id'] === 1): ?>
                                :actor_id, :max_depth (fissato a 6)
                            <?php elseif ($challenge['id'] === 2): ?>
                                :actor_id_1, :actor_id_2
                            <?php elseif ($challenge['id'] === 3): ?>
                                :user_id, :min_rating
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                <textarea id="sql-query-input" class="query-textarea" data-template="<?= htmlspecialchars($sqlTemplate) ?>" placeholder="Scrivi la tua query SQL qui..."><?= htmlspecialchars($sqlTemplate) ?></textarea>
            </div>
            
            <div class="query-panel neo4j-panel teacher-only" id="neo4j-query-panel" style="display:none">
                <div class="query-panel-header" style="display: flex; flex-direction: column; align-items: stretch; gap: var(--space-xs); padding: var(--space-sm) var(--space-lg);">
                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span class="db-tag tag-neo4j">🔵 Riferimento Neo4j / Cypher</span>
                        <span class="query-lines" id="cypher-lines" style="font-size: 0.75rem; color: var(--text-muted);">— righe</span>
                    </div>
                    <div style="border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: var(--space-xs); display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Traversal Grafo:</span>
                        <span style="font-size: 0.75rem; color: var(--accent-success); font-weight: 600;">Nativo ed Ottimizzato</span>
                    </div>
                </div>
                <pre class="query-code" id="neo4j-query-code"><code id="cypher-code"><?= htmlspecialchars($cypherQuery) ?></code></pre>
            </div>
        </div>
    </div>

    <!-- Challenge Controls -->
    <div class="section-container">
        <h2 class="section-title">Esegui la Sfida</h2>
        
        <div class="controls-card" id="challenge-controls">
            <!-- Team Code Input -->
            <div class="control-group">
                <label class="control-label" for="team-code-input">Codice Squadra</label>
                <input type="text" id="team-code-input" class="search-input" 
                       placeholder="Inserisci il codice della tua squadra..." 
                       autocomplete="off" maxlength="30"
                       style="text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">
                <p class="control-hint">Ogni squadra deve usare il proprio codice univoco assegnato dal docente.</p>
            </div>

            <?php if ($challenge['id'] === 1): ?>
            <!-- Challenge 1: Six Degrees -->
            <div class="control-group">
                <label class="control-label" for="actor-search">Attore di partenza</label>
                <div class="search-input-wrapper">
                    <input type="text" id="actor-search" class="search-input" 
                           placeholder="Cerca un attore (es. Tom Hanks)..." autocomplete="off">
                    <div class="search-results" id="actor-results"></div>
                </div>
                <input type="hidden" id="selected-actor-id" value="">
                <div class="selected-actor" id="selected-actor" style="display:none">
                    <span id="selected-actor-name"></span>
                    <button class="clear-btn" id="clear-actor">✕</button>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Gradi di separazione (profondità)</label>
                <div style="font-size: 1.15rem; font-weight: 700; color: var(--neo4j-primary); padding: var(--space-xs) 0;">
                    6 Gradi (Kevin Bacon Limit)
                </div>
                <p class="control-hint" style="color: var(--accent-danger)">⚠️ Nota: La scansione fino al 6° grado è bloccata per testare la scalabilità relazionale con query ricorsive.</p>
            </div>
            <?php elseif ($challenge['id'] === 2): ?>
            <!-- Challenge 2: Shortest Path -->
            <div class="control-group">
                <label class="control-label" for="actor1-search">Primo attore</label>
                <div class="search-input-wrapper">
                    <input type="text" id="actor1-search" class="search-input" 
                           placeholder="Cerca il primo attore..." autocomplete="off">
                    <div class="search-results" id="actor1-results"></div>
                </div>
                <input type="hidden" id="selected-actor1-id" value="">
                <div class="selected-actor" id="selected-actor1" style="display:none">
                    <span id="selected-actor1-name"></span>
                    <button class="clear-btn" id="clear-actor1">✕</button>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="actor2-search">Secondo attore</label>
                <div class="search-input-wrapper">
                    <input type="text" id="actor2-search" class="search-input" 
                           placeholder="Cerca il secondo attore..." autocomplete="off">
                    <div class="search-results" id="actor2-results"></div>
                </div>
                <input type="hidden" id="selected-actor2-id" value="">
                <div class="selected-actor" id="selected-actor2" style="display:none">
                    <span id="selected-actor2-name"></span>
                    <button class="clear-btn" id="clear-actor2">✕</button>
                </div>
            </div>
            <?php elseif ($challenge['id'] === 3): ?>
            <!-- Challenge 3: Recommendations -->
            <div class="control-group">
                <label class="control-label" for="user-select">Utente</label>
                <select id="user-select" class="select-input">
                    <option value="">Caricamento utenti...</option>
                </select>
            </div>
            <div class="control-group">
                <label class="control-label" for="min-rating">Rating minimo</label>
                <div class="rating-selector">
                    <?php foreach ([2.5, 3.0, 3.5, 4.0, 4.5] as $r): ?>
                    <button class="rating-btn <?= $r === 3.5 ? 'active' : '' ?>" 
                            data-rating="<?= $r ?>" id="rating-<?= str_replace('.', '', (string)$r) ?>">⭐ <?= $r ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Run Button -->
            <div class="control-group">
                <button class="run-btn" id="run-challenge" disabled>
                    <span class="run-icon">▶</span>
                    <span class="run-text">Esegui Challenge</span>
                    <span class="run-loading" style="display:none">⏳ In esecuzione...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="section-container" id="results-section" style="display:none">
        <h2 class="section-title">Risultati</h2>

        <!-- Validation Output Card -->
        <div class="validation-card" id="validation-card" style="display:none">
            <div class="validation-status" id="validation-status"></div>
            <div class="validation-message" id="validation-message"></div>
        </div>
        
        <!-- Performance Comparison -->
        <div class="results-comparison" id="results-comparison">
            <div class="result-panel result-mysql" id="result-mysql">
                <div class="result-header">
                    <span class="db-tag tag-mysql">🐬 MySQL</span>
                </div>
                <div class="result-time">
                    <span class="time-value" id="mysql-time">—</span>
                    <span class="time-unit">ms</span>
                </div>
                <div class="result-count" id="mysql-count">— risultati</div>
                <div class="result-memory" id="mysql-memory">— KB memoria</div>
            </div>

            <div class="result-vs">
                <div class="speedup-badge" id="speedup-badge">
                    <span class="speedup-value" id="speedup-value">—</span>
                    <span class="speedup-label">speedup</span>
                </div>
                <div class="winner-label" id="winner-label"></div>
            </div>

            <div class="result-panel result-neo4j" id="result-neo4j">
                <div class="result-header">
                    <span class="db-tag tag-neo4j">🔵 Neo4j</span>
                </div>
                <div class="result-time">
                    <span class="time-value" id="neo4j-time">—</span>
                    <span class="time-unit">ms</span>
                </div>
                <div class="result-count" id="neo4j-count">— risultati</div>
                <div class="result-memory" id="neo4j-memory">— KB memoria</div>
            </div>
        </div>

        <!-- Results Chart -->
        <div class="chart-container" id="result-chart-container">
            <canvas id="resultChart"></canvas>
        </div>



        <!-- Result Data Table -->
        <div class="result-data" id="result-data">
            <h3>Dettaglio Risultati <span class="data-source" id="data-source">(Neo4j)</span></h3>
            <div class="data-table-wrapper">
                <table class="data-table" id="data-table">
                    <thead id="data-thead"></thead>
                    <tbody id="data-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Challenge Navigation -->
    <div class="section-container">
        <div class="challenge-nav">
            <?php if ($challenge['id'] > 1): ?>
            <a href="/challenge/<?= $challenge['id'] - 1 ?>" class="challenge-nav-btn prev" id="prev-challenge">
                ← Sfida precedente
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>
            
            <?php if ($challenge['id'] < 3): ?>
            <a href="/challenge/<?= $challenge['id'] + 1 ?>" class="challenge-nav-btn next" id="next-challenge">
                Sfida successiva →
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>
