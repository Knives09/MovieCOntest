<section class="hero-section">
    <div class="hero-content">
        <div class="hero-badge">🏆 Challenge Interattiva</div>
        <h1 class="hero-title">
            <span class="student-only">MySQL <span class="hero-vs">Challenge</span></span>
            <span class="teacher-only" style="display:none">
                <span class="text-mysql">MySQL</span> 
                <span class="hero-vs">vs</span> 
                <span class="text-neo4j">Neo4j</span>
            </span>
        </h1>
        <p class="hero-subtitle">
            <span class="student-only">Misura le tue abilità SQL e ottimizza le query su relazioni cinematografiche complesse</span>
            <span class="teacher-only" style="display:none">Scopri la potenza dei database a grafo confrontando query reali su dati cinematografici</span>
        </p>
        <div class="hero-stats" id="hero-stats">
            <div class="stat-card" id="stat-movies">
                <span class="stat-value" data-counter="0">—</span>
                <span class="stat-label">Film</span>
            </div>
            <div class="stat-card" id="stat-actors">
                <span class="stat-value" data-counter="0">—</span>
                <span class="stat-label">Attori</span>
            </div>
            <div class="stat-card" id="stat-relations">
                <span class="stat-value" data-counter="0">—</span>
                <span class="stat-label">Relazioni</span>
            </div>
            <div class="stat-card" id="stat-reviews">
                <span class="stat-value" data-counter="0">—</span>
                <span class="stat-label">Review</span>
            </div>
        </div>
    </div>
    <div class="hero-decoration">
        <div class="orbit orbit-1"></div>
        <div class="orbit orbit-2"></div>
        <div class="orbit orbit-3"></div>
    </div>
</section>

<!-- Concept Section (Teacher Only) -->
<section class="concept-section teacher-only" style="display:none">
    <div class="section-container">
        <h2 class="section-title">Perché un Database a Grafo?</h2>
        <p class="section-subtitle">
            I dati del mondo reale sono <strong>connessi</strong>. Attori recitano in film, utenti scrivono review, 
            generi raggruppano storie. Un DB relazionale gestisce queste connessioni con <em>tabelle di join</em> — 
            un DB a grafo le gestisce con <em>relazioni native</em>.
        </p>

        <div class="comparison-grid">
            <div class="comparison-card mysql-card">
                <div class="card-header">
                    <span class="db-icon">🐬</span>
                    <h3>MySQL <span class="card-tag">Relazionale</span></h3>
                </div>
                <div class="card-body">
                    <div class="schema-visual">
                        <div class="table-box">movies</div>
                        <div class="join-arrow">←JOIN→</div>
                        <div class="table-box">movie_cast</div>
                        <div class="join-arrow">←JOIN→</div>
                        <div class="table-box">actors</div>
                    </div>
                    <ul class="feature-list">
                        <li class="feature-con">Ogni relazione richiede un JOIN</li>
                        <li class="feature-con">Traversal ricorsivi esponenziali</li>
                        <li class="feature-con">Query complesse e illeggibili</li>
                        <li class="feature-pro">Ottimo per aggregazioni semplici</li>
                    </ul>
                </div>
            </div>

            <div class="comparison-card neo4j-card">
                <div class="card-header">
                    <span class="db-icon">🔵</span>
                    <h3>Neo4j <span class="card-tag">Grafo</span></h3>
                </div>
                <div class="card-body">
                    <div class="schema-visual graph-visual">
                        <div class="node node-actor">Actor</div>
                        <div class="edge">—ACTED_IN→</div>
                        <div class="node node-movie">Movie</div>
                        <div class="edge">←REVIEWED—</div>
                        <div class="node node-user">User</div>
                    </div>
                    <ul class="feature-list">
                        <li class="feature-pro">Relazioni native (index-free adjacency)</li>
                        <li class="feature-pro">Traversal O(1) per hop</li>
                        <li class="feature-pro">Cypher: query come linguaggio naturale</li>
                        <li class="feature-con">Meno efficiente per aggregazioni massive</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Challenges Section -->
<section class="challenges-section">
    <div class="section-container">
        <h2 class="section-title">Le 3 Sfide</h2>
        <p class="section-subtitle">
            <span class="student-only">Ogni squadra affronta le stesse sfide relazionali. Riesci a ottimizzare le query?</span>
            <span class="teacher-only" style="display:none">Ogni squadra affronta le stesse sfide. La piattaforma confronta i risultati in tempo reale con Neo4j.</span>
        </p>

        <div class="challenges-grid">
            <a href="/challenge/1" class="challenge-card" id="challenge-card-1">
                <div class="challenge-icon">🎯</div>
                <div class="challenge-number">#1</div>
                <h3>Six Degrees of Kevin Bacon</h3>
                <p>
                    <span class="student-only">Trova tutti gli attori collegati all'attore di partenza entro N gradi di separazione. Ottimizza le connessioni ricorsive.</span>
                    <span class="teacher-only" style="display:none">Trova tutti gli attori collegati entro N gradi di separazione. MySQL crolla con i JOIN ricorsivi.</span>
                </p>
                <div class="challenge-difficulty">
                    <span class="difficulty-dot active"></span>
                    <span class="difficulty-dot active"></span>
                    <span class="difficulty-dot"></span>
                    <span>Difficoltà Media</span>
                </div>
                <span class="challenge-cta">Prova la sfida →</span>
            </a>

            <a href="/challenge/2" class="challenge-card" id="challenge-card-2">
                <div class="challenge-icon">🛤️</div>
                <div class="challenge-number">#2</div>
                <h3>Shortest Path</h3>
                <p>
                    <span class="student-only">Trova il percorso più breve tra due attori attraverso i film che hanno in comune.</span>
                    <span class="teacher-only" style="display:none">Trova il percorso più breve tra due attori attraverso film in comune. Neo4j ha shortestPath() nativo.</span>
                </p>
                <div class="challenge-difficulty">
                    <span class="difficulty-dot active"></span>
                    <span class="difficulty-dot active"></span>
                    <span class="difficulty-dot active"></span>
                    <span>Difficoltà Alta</span>
                </div>
                <span class="challenge-cta">Prova la sfida →</span>
            </a>

            <a href="/challenge/3" class="challenge-card" id="challenge-card-3">
                <div class="challenge-icon">⭐</div>
                <div class="challenge-number">#3</div>
                <h3>Raccomandazioni Film</h3>
                <p>
                    <span class="student-only">Suggerisci film ad un utente con una query di collaborative filtering basata sulle preferenze della sua squadra.</span>
                    <span class="teacher-only" style="display:none">Suggerisci film con collaborative filtering. In MySQL servono 3 self-join + subquery.</span>
                </p>
                <div class="challenge-difficulty">
                    <span class="difficulty-dot active"></span>
                    <span class="difficulty-dot active"></span>
                    <span class="difficulty-dot active"></span>
                    <span>Difficoltà Alta</span>
                </div>
                <span class="challenge-cta">Prova la sfida →</span>
            </a>
        </div>
    </div>
</section>

<!-- Teams Section -->
<section class="teams-section">
    <div class="section-container">
        <h2 class="section-title">Le Squadre</h2>
        <div class="teams-grid">
            <div class="team-card team-alpha" id="team-alpha">
                <div class="team-icon">⚡</div>
                <h3>Team Flash</h3>
                <div class="team-color-bar" style="background: #f97316"></div>
            </div>
            <div class="team-card team-beta" id="team-beta">
                <div class="team-icon">🦇</div>
                <h3>Team Batman</h3>
                <div class="team-color-bar" style="background: #00b4d8"></div>
            </div>
            <div class="team-card team-gamma" id="team-gamma">
                <div class="team-icon">🟢</div>
                <h3>Team Green Lantern</h3>
                <div class="team-color-bar" style="background: #10b981"></div>
            </div>
        </div>
    </div>
</section>
