<div class="header-container">
    <h1 class="page-title">🕸️ Schema Grafo — Neo4j</h1>
    <p class="page-subtitle">Visualizzazione dei nodi, relazioni e proprietà del database a grafo. Confronta questa struttura con lo schema relazionale MySQL.</p>
</div>

<div class="section-container" style="margin-top: var(--space-xl);">

    <!-- ============================================================ -->
    <!-- GRAPH VISUAL DIAGRAM (CSS-based)                             -->
    <!-- ============================================================ -->
    <div style="background: var(--bg-glass); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); padding: var(--space-xl); margin-bottom: var(--space-xl); position: relative; overflow: hidden;">
        <h3 style="margin-bottom: var(--space-lg); font-weight: 800; color: var(--neo4j-primary); text-align: center; font-size: 1.1rem;">
            Modello a Grafo — Nodi e Relazioni
        </h3>

        <!-- Graph Diagram using SVG + positioned nodes -->
        <div style="position: relative; width: 100%; max-width: 800px; margin: 0 auto; height: 420px;">

            <!-- SVG Arrows layer -->
            <svg viewBox="0 0 800 420" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="10" refY="3.5" orient="auto">
                        <polygon points="0 0, 10 3.5, 0 7" fill="#a78bfa" />
                    </marker>
                    <marker id="arrowhead-orange" markerWidth="10" markerHeight="7" refX="10" refY="3.5" orient="auto">
                        <polygon points="0 0, 10 3.5, 0 7" fill="#f97316" />
                    </marker>
                    <marker id="arrowhead-green" markerWidth="10" markerHeight="7" refX="10" refY="3.5" orient="auto">
                        <polygon points="0 0, 10 3.5, 0 7" fill="#10b981" />
                    </marker>
                    <marker id="arrowhead-yellow" markerWidth="10" markerHeight="7" refX="10" refY="3.5" orient="auto">
                        <polygon points="0 0, 10 3.5, 0 7" fill="#fbbf24" />
                    </marker>
                </defs>

                <!-- Actor --ACTED_IN--> Movie -->
                <line x1="220" y1="120" x2="530" y2="120" stroke="#a78bfa" stroke-width="2.5" stroke-dasharray="8,4" marker-end="url(#arrowhead)" />
                <rect x="310" y="97" width="130" height="26" rx="13" fill="rgba(139,92,246,0.2)" stroke="#a78bfa" stroke-width="1"/>
                <text x="375" y="115" text-anchor="middle" fill="#c4b5fd" font-size="11" font-weight="700" font-family="monospace">:ACTED_IN</text>

                <!-- User --REVIEWED--> Movie -->
                <line x1="220" y1="310" x2="530" y2="175" stroke="#f97316" stroke-width="2.5" stroke-dasharray="8,4" marker-end="url(#arrowhead-orange)" />
                <rect x="310" y="227" width="130" height="26" rx="13" fill="rgba(249,115,22,0.2)" stroke="#f97316" stroke-width="1"/>
                <text x="375" y="245" text-anchor="middle" fill="#fdba74" font-size="11" font-weight="700" font-family="monospace">:REVIEWED</text>

                <!-- Movie --HAS_GENRE--> Genre -->
                <line x1="640" y1="165" x2="640" y2="280" stroke="#10b981" stroke-width="2.5" stroke-dasharray="8,4" marker-end="url(#arrowhead-green)" />
                <rect x="655" y="210" width="130" height="26" rx="13" fill="rgba(16,185,129,0.2)" stroke="#10b981" stroke-width="1"/>
                <text x="720" y="228" text-anchor="middle" fill="#6ee7b7" font-size="11" font-weight="700" font-family="monospace">:HAS_GENRE</text>
            </svg>

            <!-- Node: Actor -->
            <div style="position: absolute; top: 60px; left: 40px; z-index: 2; text-align: center;">
                <div style="width: 120px; height: 120px; border-radius: 50%; background: radial-gradient(circle at 35% 35%, rgba(139,92,246,0.4), rgba(139,92,246,0.1)); border: 3px solid #a78bfa; display: flex; align-items: center; justify-content: center; flex-direction: column; box-shadow: 0 0 30px rgba(139,92,246,0.25);">
                    <span style="font-size: 1.8rem;">🎭</span>
                    <span style="color: #c4b5fd; font-weight: 800; font-size: 0.9rem; margin-top: 2px;">Actor</span>
                </div>
            </div>

            <!-- Node: Movie -->
            <div style="position: absolute; top: 60px; left: 540px; z-index: 2; text-align: center;">
                <div style="width: 120px; height: 120px; border-radius: 50%; background: radial-gradient(circle at 35% 35%, rgba(59,130,246,0.4), rgba(59,130,246,0.1)); border: 3px solid #60a5fa; display: flex; align-items: center; justify-content: center; flex-direction: column; box-shadow: 0 0 30px rgba(59,130,246,0.25);">
                    <span style="font-size: 1.8rem;">🎬</span>
                    <span style="color: #93c5fd; font-weight: 800; font-size: 0.9rem; margin-top: 2px;">Movie</span>
                </div>
            </div>

            <!-- Node: User -->
            <div style="position: absolute; top: 250px; left: 40px; z-index: 2; text-align: center;">
                <div style="width: 120px; height: 120px; border-radius: 50%; background: radial-gradient(circle at 35% 35%, rgba(249,115,22,0.4), rgba(249,115,22,0.1)); border: 3px solid #f97316; display: flex; align-items: center; justify-content: center; flex-direction: column; box-shadow: 0 0 30px rgba(249,115,22,0.25);">
                    <span style="font-size: 1.8rem;">👤</span>
                    <span style="color: #fdba74; font-weight: 800; font-size: 0.9rem; margin-top: 2px;">User</span>
                </div>
            </div>

            <!-- Node: Genre -->
            <div style="position: absolute; top: 290px; left: 540px; z-index: 2; text-align: center;">
                <div style="width: 120px; height: 120px; border-radius: 50%; background: radial-gradient(circle at 35% 35%, rgba(16,185,129,0.4), rgba(16,185,129,0.1)); border: 3px solid #10b981; display: flex; align-items: center; justify-content: center; flex-direction: column; box-shadow: 0 0 30px rgba(16,185,129,0.25);">
                    <span style="font-size: 1.8rem;">📁</span>
                    <span style="color: #6ee7b7; font-weight: 800; font-size: 0.9rem; margin-top: 2px;">Genre</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- NODE DETAILS                                                 -->
    <!-- ============================================================ -->
    <h3 style="margin-bottom: var(--space-md); font-weight: 800; color: var(--neo4j-primary); display: flex; align-items: center; gap: 8px; font-size: 1.05rem;">
        <span>⚪</span> Nodi (Labels)
    </h3>
    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: var(--space-lg); line-height: 1.5;">
        A differenza di MySQL, in Neo4j i dati non sono organizzati in tabelle ma in <strong>nodi</strong>. Ogni nodo ha un'etichetta (label) e un insieme di proprietà (key-value). Non servono JOIN: i nodi sono collegati direttamente tramite <strong>relazioni</strong>.
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: var(--space-lg); margin-bottom: var(--space-xl);">

        <!-- Node: Actor -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(139,92,246,0.3); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: #a78bfa; display: flex; align-items: center; gap: 8px;">
                        <span>🎭</span> :Actor
                    </h3>
                    <span style="font-size: 0.7rem; background: rgba(139,92,246,0.15); color: #c4b5fd; padding: 3px 8px; border-radius: var(--radius-sm); font-weight: 700;">5.001 nodi</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Rappresenta un attore. Connesso ai film tramite la relazione <code style="color: #a78bfa;">:ACTED_IN</code>.
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>id</strong> <span style="color: var(--text-muted)">Integer</span></span>
                        <span style="color: #a78bfa; font-weight: 700; font-size: 0.7rem;">🔑 UNIQUE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>tmdb_id</strong> <span style="color: var(--text-muted)">Integer</span></span>
                        <span style="color: #a78bfa; font-weight: 700; font-size: 0.7rem;">🔑 UNIQUE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>name</strong> <span style="color: var(--text-muted)">String</span></span>
                        <span style="color: var(--accent-success); font-size: 0.7rem;">⚡ INDEX</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>profile_path</strong> <span style="color: var(--text-muted)">String</span></span>
                        <span>-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Node: Movie -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(59,130,246,0.3); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: #60a5fa; display: flex; align-items: center; gap: 8px;">
                        <span>🎬</span> :Movie
                    </h3>
                    <span style="font-size: 0.7rem; background: rgba(59,130,246,0.15); color: #93c5fd; padding: 3px 8px; border-radius: var(--radius-sm); font-weight: 700;">484 nodi</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Rappresenta un film. Nodo centrale del grafo: collegato ad attori, utenti e generi.
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>id</strong> <span style="color: var(--text-muted)">Integer</span></span>
                        <span style="color: #60a5fa; font-weight: 700; font-size: 0.7rem;">🔑 UNIQUE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>tmdb_id</strong> <span style="color: var(--text-muted)">Integer</span></span>
                        <span style="color: #60a5fa; font-weight: 700; font-size: 0.7rem;">🔑 UNIQUE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>title</strong> <span style="color: var(--text-muted)">String</span></span>
                        <span style="color: var(--accent-success); font-size: 0.7rem;">⚡ INDEX</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>release_date</strong> <span style="color: var(--text-muted)">String</span></span>
                        <span>-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>overview</strong> <span style="color: var(--text-muted)">String</span></span>
                        <span>-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>vote_average</strong> <span style="color: var(--text-muted)">Float</span></span>
                        <span style="color: var(--accent-success); font-size: 0.7rem;">⚡ INDEX</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>poster_path</strong> <span style="color: var(--text-muted)">String</span></span>
                        <span>-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Node: User -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(249,115,22,0.3); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: #f97316; display: flex; align-items: center; gap: 8px;">
                        <span>👤</span> :User
                    </h3>
                    <span style="font-size: 0.7rem; background: rgba(249,115,22,0.15); color: #fdba74; padding: 3px 8px; border-radius: var(--radius-sm); font-weight: 700;">400 nodi</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Rappresenta un utente. Collegato ai film tramite la relazione <code style="color: #f97316;">:REVIEWED</code>.
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>id</strong> <span style="color: var(--text-muted)">Integer</span></span>
                        <span style="color: #f97316; font-weight: 700; font-size: 0.7rem;">🔑 UNIQUE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>username</strong> <span style="color: var(--text-muted)">String</span></span>
                        <span style="color: #f97316; font-weight: 700; font-size: 0.7rem;">🔑 UNIQUE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>team_name</strong> <span style="color: var(--text-muted)">String</span></span>
                        <span>-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Node: Genre -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(16,185,129,0.3); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: #10b981; display: flex; align-items: center; gap: 8px;">
                        <span>📁</span> :Genre
                    </h3>
                    <span style="font-size: 0.7rem; background: rgba(16,185,129,0.15); color: #6ee7b7; padding: 3px 8px; border-radius: var(--radius-sm); font-weight: 700;">19 nodi</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Rappresenta un genere cinematografico. Connesso ai film tramite <code style="color: #10b981;">:HAS_GENRE</code>.
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>id</strong> <span style="color: var(--text-muted)">Integer</span></span>
                        <span style="color: #10b981; font-weight: 700; font-size: 0.7rem;">🔑 UNIQUE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>name</strong> <span style="color: var(--text-muted)">String</span></span>
                        <span>-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- RELATIONSHIP DETAILS                                         -->
    <!-- ============================================================ -->
    <h3 style="margin-bottom: var(--space-md); font-weight: 800; color: var(--neo4j-primary); display: flex; align-items: center; gap: 8px; font-size: 1.05rem;">
        <span>➡️</span> Relazioni (Relationships)
    </h3>
    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: var(--space-lg); line-height: 1.5;">
        Le relazioni in Neo4j sostituiscono le <strong>tabelle pivot</strong> di MySQL (<code>movie_cast</code>, <code>reviews</code>, <code>movie_genres</code>).
        Ogni relazione ha un tipo, una direzione, e può portare proprietà aggiuntive. Le connessioni sono puntatori fisici in memoria: ecco perché i <strong>traversal</strong> sono istantanei!
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--space-lg); margin-bottom: var(--space-xl);">

        <!-- Relationship: ACTED_IN -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(139,92,246,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                <h3 style="font-weight: 800; color: #a78bfa; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                    <span>🎭</span> :ACTED_IN
                </h3>
                <span style="font-size: 0.7rem; background: rgba(139,92,246,0.15); color: #c4b5fd; padding: 3px 8px; border-radius: var(--radius-sm); font-weight: 700;">6.235 rel.</span>
            </div>

            <!-- Direction -->
            <div style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-md); padding: var(--space-sm); background: rgba(139,92,246,0.06); border-radius: var(--radius-sm); font-size: 0.85rem;">
                <span style="background: rgba(139,92,246,0.2); padding: 3px 10px; border-radius: 20px; font-weight: 700; color: #c4b5fd; font-size: 0.75rem;">Actor</span>
                <span style="color: #a78bfa;">——→</span>
                <span style="background: rgba(59,130,246,0.2); padding: 3px 10px; border-radius: 20px; font-weight: 700; color: #93c5fd; font-size: 0.75rem;">Movie</span>
            </div>

            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-sm); line-height: 1.4;">
                Indica che un attore ha recitato in un film. Equivale a un record nella tabella <code style="color: var(--mysql-primary);">movie_cast</code> di MySQL.
            </p>
            <div style="font-family: var(--font-mono); font-size: 0.78rem; margin-top: var(--space-sm);">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                    <span><strong>character_name</strong> <span style="color: var(--text-muted)">String</span></span>
                    <span style="color: var(--text-muted); font-size: 0.7rem;">Proprietà</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span><strong>cast_order</strong> <span style="color: var(--text-muted)">Integer</span></span>
                    <span style="color: var(--text-muted); font-size: 0.7rem;">Proprietà</span>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); padding: var(--space-xs) var(--space-sm); margin-top: var(--space-md); font-size: 0.75rem; border-top: 1px solid var(--border-glass);">
                <strong style="color: #a78bfa;">Cypher:</strong> <code style="font-size: 0.72rem; color: #fbbf24;">(a:Actor)-[:ACTED_IN]->(m:Movie)</code>
            </div>
        </div>

        <!-- Relationship: REVIEWED -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(249,115,22,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                <h3 style="font-weight: 800; color: #f97316; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                    <span>⭐</span> :REVIEWED
                </h3>
                <span style="font-size: 0.7rem; background: rgba(249,115,22,0.15); color: #fdba74; padding: 3px 8px; border-radius: var(--radius-sm); font-weight: 700;">40.103 rel.</span>
            </div>

            <!-- Direction -->
            <div style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-md); padding: var(--space-sm); background: rgba(249,115,22,0.06); border-radius: var(--radius-sm); font-size: 0.85rem;">
                <span style="background: rgba(249,115,22,0.2); padding: 3px 10px; border-radius: 20px; font-weight: 700; color: #fdba74; font-size: 0.75rem;">User</span>
                <span style="color: #f97316;">——→</span>
                <span style="background: rgba(59,130,246,0.2); padding: 3px 10px; border-radius: 20px; font-weight: 700; color: #93c5fd; font-size: 0.75rem;">Movie</span>
            </div>

            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-sm); line-height: 1.4;">
                Indica che un utente ha recensito un film con un certo voto. Equivale a un record nella tabella <code style="color: var(--mysql-primary);">reviews</code> di MySQL. La relazione porta il <strong>rating</strong> come proprietà diretta.
            </p>
            <div style="font-family: var(--font-mono); font-size: 0.78rem; margin-top: var(--space-sm);">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                    <span><strong>rating</strong> <span style="color: var(--text-muted)">Float</span></span>
                    <span style="color: #f97316; font-size: 0.7rem; font-weight: 600;">⭐ Proprietà chiave</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span><strong>comment</strong> <span style="color: var(--text-muted)">String</span></span>
                    <span style="color: var(--text-muted); font-size: 0.7rem;">Proprietà</span>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); padding: var(--space-xs) var(--space-sm); margin-top: var(--space-md); font-size: 0.75rem; border-top: 1px solid var(--border-glass);">
                <strong style="color: #f97316;">Cypher:</strong> <code style="font-size: 0.72rem; color: #fbbf24;">(u:User)-[r:REVIEWED]->(m:Movie)</code>
            </div>
        </div>

        <!-- Relationship: HAS_GENRE -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(16,185,129,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                <h3 style="font-weight: 800; color: #10b981; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                    <span>📎</span> :HAS_GENRE
                </h3>
                <span style="font-size: 0.7rem; background: rgba(16,185,129,0.15); color: #6ee7b7; padding: 3px 8px; border-radius: var(--radius-sm); font-weight: 700;">~1.200 rel.</span>
            </div>

            <!-- Direction -->
            <div style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-md); padding: var(--space-sm); background: rgba(16,185,129,0.06); border-radius: var(--radius-sm); font-size: 0.85rem;">
                <span style="background: rgba(59,130,246,0.2); padding: 3px 10px; border-radius: 20px; font-weight: 700; color: #93c5fd; font-size: 0.75rem;">Movie</span>
                <span style="color: #10b981;">——→</span>
                <span style="background: rgba(16,185,129,0.2); padding: 3px 10px; border-radius: 20px; font-weight: 700; color: #6ee7b7; font-size: 0.75rem;">Genre</span>
            </div>

            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-sm); line-height: 1.4;">
                Indica il genere di un film. Equivale a un record nella tabella pivot <code style="color: var(--mysql-primary);">movie_genres</code> di MySQL. Non porta proprietà aggiuntive.
            </p>
            <div style="font-family: var(--font-mono); font-size: 0.78rem; margin-top: var(--space-sm);">
                <div style="display: flex; justify-content: space-between; padding: 4px 0; color: var(--text-muted);">
                    <span><em>Nessuna proprietà</em></span>
                    <span style="font-size: 0.7rem;">—</span>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); padding: var(--space-xs) var(--space-sm); margin-top: var(--space-md); font-size: 0.75rem; border-top: 1px solid var(--border-glass);">
                <strong style="color: #10b981;">Cypher:</strong> <code style="font-size: 0.72rem; color: #fbbf24;">(m:Movie)-[:HAS_GENRE]->(g:Genre)</code>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MYSQL vs NEO4J COMPARISON                                    -->
    <!-- ============================================================ -->
    <div style="background: var(--bg-glass); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); padding: var(--space-lg); margin-top: var(--space-lg);">
        <h3 style="margin-bottom: var(--space-md); font-weight: 800; color: var(--neo4j-primary); display: flex; align-items: center; gap: 8px;">
            <span>⚡</span> Perché il Grafo è Più Veloce?
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-lg); font-size: 0.85rem; line-height: 1.6; color: var(--text-secondary);">
            <div>
                <strong style="color: var(--mysql-primary); display: block; margin-bottom: var(--space-xs);">🐬 MySQL (Relazionale)</strong>
                Per trovare gli attori collegati a un attore dato, MySQL deve:
                <ul style="margin-top: 6px; padding-left: var(--space-md);">
                    <li>Fare un <strong>JOIN</strong> su <code>movie_cast</code> per trovare i film</li>
                    <li>Fare un secondo <strong>JOIN</strong> per trovare gli altri attori</li>
                    <li>Ripetere con <strong>CTE ricorsive</strong> ad ogni profondità</li>
                    <li>Costo esponenziale: O(N<sup>d</sup>) ad ogni hop</li>
                </ul>
            </div>
            <div>
                <strong style="color: var(--neo4j-primary); display: block; margin-bottom: var(--space-xs);">🕸️ Neo4j (Grafo)</strong>
                Neo4j non ha bisogno di JOIN. Il traversal è istantaneo:
                <ul style="margin-top: 6px; padding-left: var(--space-md);">
                    <li>Ogni nodo ha <strong>puntatori diretti</strong> ai suoi vicini</li>
                    <li>Il costo per hop è <strong>O(1)</strong> (index-free adjacency)</li>
                    <li><code>shortestPath()</code> è un algoritmo nativo ottimizzato</li>
                    <li>Le query Cypher si leggono come linguaggio naturale</li>
                </ul>
            </div>
        </div>

        <!-- Example pattern matching -->
        <div style="margin-top: var(--space-lg); padding: var(--space-md); background: rgba(0,0,0,0.25); border-radius: var(--radius-md); font-family: var(--font-mono); font-size: 0.82rem; overflow-x: auto;">
            <div style="color: var(--text-muted); margin-bottom: var(--space-xs); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px;">Esempio — Raccomandazioni in Cypher (11 righe vs 22 righe SQL)</div>
            <div><span style="color: #60a5fa; font-weight: 700;">MATCH</span> <span style="color: #fbbf24;">(u:User {id: $userId})</span><span style="color: #a78bfa;">-[r1:REVIEWED]-></span><span style="color: #fbbf24;">(m:Movie)</span></div>
            <div><span style="color: #60a5fa; font-weight: 700;">WHERE</span> <span style="color: #e2e8f0;">r1.rating >= $minRating</span></div>
            <div><span style="color: #60a5fa; font-weight: 700;">WITH</span> <span style="color: #e2e8f0;">u, collect(m) AS myMovies</span></div>
            <div><span style="color: #60a5fa; font-weight: 700;">MATCH</span> <span style="color: #fbbf24;">(m:Movie)</span><span style="color: #f97316;"><-[r2:REVIEWED]-</span><span style="color: #fbbf24;">(other:User)</span></div>
            <div><span style="color: #60a5fa; font-weight: 700;">WHERE</span> <span style="color: #e2e8f0;">m IN myMovies AND r2.rating >= $minRating</span></div>
            <div><span style="color: #60a5fa; font-weight: 700;">WITH</span> <span style="color: #e2e8f0;">u, myMovies, other</span></div>
            <div><span style="color: #60a5fa; font-weight: 700;">MATCH</span> <span style="color: #fbbf24;">(other)</span><span style="color: #10b981;">-[r3:REVIEWED]-></span><span style="color: #fbbf24;">(rec:Movie)</span></div>
            <div><span style="color: #60a5fa; font-weight: 700;">WHERE</span> <span style="color: #e2e8f0;">r3.rating >= $minRating AND NOT rec IN myMovies</span></div>
            <div><span style="color: #60a5fa; font-weight: 700;">RETURN</span> <span style="color: #e2e8f0;">rec.title, COUNT(DISTINCT other) AS score</span></div>
            <div><span style="color: #60a5fa; font-weight: 700;">ORDER BY</span> <span style="color: #e2e8f0;">score DESC</span> <span style="color: #60a5fa; font-weight: 700;">LIMIT</span> <span style="color: #e2e8f0;">10</span></div>
        </div>
    </div>

</div>
