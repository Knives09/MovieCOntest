<div class="header-container">
    <h1 class="page-title">🗂️ Schema Database MySQL</h1>
    <p class="page-subtitle">Riferimento delle tabelle, colonne e relazioni per aiutarti a scrivere query SQL corrette.</p>
</div>

<!-- Schema Container -->
<div class="section-container" style="margin-top: var(--space-xl);">
    <!-- DB Legend & Intro -->
    <div style="background: var(--bg-glass); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); padding: var(--space-lg); margin-bottom: var(--space-xl); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--space-md);">
        <div>
            <h3 style="margin-bottom: var(--space-xs); font-weight: 700; color: var(--text-primary);">Legenda delle Chiavi</h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); max-width: 600px;">
                Usa questo schema come mappa ER. Le chiavi primarie (PK) identificano univocamente la riga, mentre le chiavi esterne (FK) definiscono i collegamenti logici per i tuoi JOIN.
            </p>
        </div>
        <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
            <span style="background: rgba(249, 115, 22, 0.15); border: 1px solid var(--mysql-primary); color: var(--text-primary); padding: 4px 10px; border-radius: var(--radius-sm); font-size: 0.75rem; font-weight: 700; font-family: var(--font-mono);">🗝️ PK (Primary Key)</span>
            <span style="background: rgba(139, 92, 246, 0.15); border: 1px solid var(--neo4j-primary); color: var(--text-primary); padding: 4px 10px; border-radius: var(--radius-sm); font-size: 0.75rem; font-weight: 700; font-family: var(--font-mono);">🔗 FK (Foreign Key)</span>
            <span style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--accent-success); color: var(--text-primary); padding: 4px 10px; border-radius: var(--radius-sm); font-size: 0.75rem; font-weight: 700; font-family: var(--font-mono);">⚡ INDEX (Indicizzato)</span>
        </div>
    </div>

    <!-- Tables Grid Layout -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--space-lg);">

        <!-- Table: movies -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(249, 115, 22, 0.15); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: var(--mysql-primary); display: flex; align-items: center; gap: 8px;">
                        <span>🎬</span> movies
                    </h3>
                    <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Tabella Base</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Memorizza i dati essenziali dei film importati da TMDb.
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--mysql-primary); font-weight: 700;">🗝️ PK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>tmdb_id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--accent-success); font-weight: 600;">⚡ UNIQUE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>title</strong> <span style="color: var(--text-muted)">VARCHAR(500)</span></span>
                        <span style="color: var(--accent-success);">⚡ INDEX</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>release_date</strong> <span style="color: var(--text-muted)">DATE</span></span>
                        <span>-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>overview</strong> <span style="color: var(--text-muted)">TEXT</span></span>
                        <span>-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>vote_average</strong> <span style="color: var(--text-muted)">DECIMAL(3,1)</span></span>
                        <span style="color: var(--accent-success);">⚡ INDEX</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>poster_path</strong> <span style="color: var(--text-muted)">VARCHAR(255)</span></span>
                        <span>-</span>
                    </div>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); padding: var(--space-xs) var(--space-sm); margin-top: var(--space-md); font-size: 0.75rem; border-top: 1px solid var(--border-glass);">
                <strong style="color: var(--neo4j-primary);">Relazioni:</strong> Collega a <code style="color: var(--mysql-primary)">movie_cast</code> e <code style="color: var(--mysql-primary)">movie_genres</code> via <code style="font-family: var(--font-mono)">movie_id</code>.
            </div>
        </div>

        <!-- Table: actors -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(249, 115, 22, 0.15); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: var(--mysql-primary); display: flex; align-items: center; gap: 8px;">
                        <span>🎭</span> actors
                    </h3>
                    <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Tabella Base</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Memorizza i dati anagrafici e i dettagli degli attori.
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--mysql-primary); font-weight: 700;">🗝️ PK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>tmdb_id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--accent-success); font-weight: 600;">⚡ UNIQUE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>name</strong> <span style="color: var(--text-muted)">VARCHAR(255)</span></span>
                        <span style="color: var(--accent-success);">⚡ INDEX</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>profile_path</strong> <span style="color: var(--text-muted)">VARCHAR(255)</span></span>
                        <span>-</span>
                    </div>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); padding: var(--space-xs) var(--space-sm); margin-top: var(--space-md); font-size: 0.75rem; border-top: 1px solid var(--border-glass);">
                <strong style="color: var(--neo4j-primary);">Relazioni:</strong> Collega a <code style="color: var(--mysql-primary)">movie_cast</code> via <code style="font-family: var(--font-mono)">actor_id</code>.
            </div>
        </div>

        <!-- Table: movie_cast -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(249, 115, 22, 0.15); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: var(--mysql-primary); display: flex; align-items: center; gap: 8px;">
                        <span>🔗</span> movie_cast
                    </h3>
                    <span style="font-size: 0.7rem; color: #fbbf24; text-transform: uppercase;">Tabella Pivot / N:M</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Tabella di giunzione tra Film e Attori per rappresentare i membri del cast.
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--mysql-primary); font-weight: 700;">🗝️ PK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>movie_id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--neo4j-primary); font-weight: 700;">🔗 FK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>actor_id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--neo4j-primary); font-weight: 700;">🔗 FK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>character_name</strong> <span style="color: var(--text-muted)">VARCHAR(500)</span></span>
                        <span>-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>cast_order</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span>-</span>
                    </div>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); padding: var(--space-xs) var(--space-sm); margin-top: var(--space-md); font-size: 0.75rem; border-top: 1px solid var(--border-glass);">
                <strong style="color: var(--neo4j-primary);">Esempio Join:</strong> <code style="font-size:0.7rem; color: #fbbf24;">FROM movie_cast mc JOIN actors a ON mc.actor_id = a.id</code>.
            </div>
        </div>

        <!-- Table: genres -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(249, 115, 22, 0.15); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: var(--mysql-primary); display: flex; align-items: center; gap: 8px;">
                        <span>📁</span> genres
                    </h3>
                    <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Tabella Codifica</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Memorizza i generi cinematografici (es. Drama, Comedy, Thriller).
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--mysql-primary); font-weight: 700;">🗝️ PK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>name</strong> <span style="color: var(--text-muted)">VARCHAR(255)</span></span>
                        <span>-</span>
                    </div>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); padding: var(--space-xs) var(--space-sm); margin-top: var(--space-md); font-size: 0.75rem; border-top: 1px solid var(--border-glass);">
                <strong style="color: var(--neo4j-primary);">Relazioni:</strong> Collegato a <code style="color: var(--mysql-primary)">movie_genres</code> via <code style="font-family: var(--font-mono)">genre_id</code>.
            </div>
        </div>

        <!-- Table: movie_genres -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(249, 115, 22, 0.15); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: var(--mysql-primary); display: flex; align-items: center; gap: 8px;">
                        <span>📎</span> movie_genres
                    </h3>
                    <span style="font-size: 0.7rem; color: #fbbf24; text-transform: uppercase;">Tabella Pivot / N:M</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Tabella di giunzione tra Film e Generi (un film può avere più generi).
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>movie_id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--neo4j-primary); font-weight: 700;">🔗 FK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>genre_id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--neo4j-primary); font-weight: 700;">🔗 FK</span>
                    </div>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); padding: var(--space-xs) var(--space-sm); margin-top: var(--space-md); font-size: 0.75rem; border-top: 1px solid var(--border-glass);">
                <strong style="color: var(--neo4j-primary);">Chiave Composta:</strong> `(movie_id, genre_id)` funge da chiave primaria implicita.
            </div>
        </div>

        <!-- Table: users -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(249, 115, 22, 0.15); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: var(--mysql-primary); display: flex; align-items: center; gap: 8px;">
                        <span>👥</span> users
                    </h3>
                    <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Tabella Base</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Memorizza gli utenti fittizi creati per le squadre di studenti.
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--mysql-primary); font-weight: 700;">🗝️ PK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>username</strong> <span style="color: var(--text-muted)">VARCHAR(255)</span></span>
                        <span style="color: var(--accent-success); font-weight: 600;">⚡ UNIQUE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>team_name</strong> <span style="color: var(--text-muted)">VARCHAR(255)</span></span>
                        <span>-</span>
                    </div>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); padding: var(--space-xs) var(--space-sm); margin-top: var(--space-md); font-size: 0.75rem; border-top: 1px solid var(--border-glass);">
                <strong style="color: var(--neo4j-primary);">Relazioni:</strong> Collegato a <code style="color: var(--mysql-primary)">reviews</code> via <code style="font-family: var(--font-mono)">user_id</code>.
            </div>
        </div>

        <!-- Table: reviews -->
        <div class="result-panel" style="text-align: left; padding: var(--space-lg); border-color: rgba(249, 115, 22, 0.15); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: var(--space-sm); margin-bottom: var(--space-md);">
                    <h3 style="font-weight: 800; color: var(--mysql-primary); display: flex; align-items: center; gap: 8px;">
                        <span>⭐</span> reviews
                    </h3>
                    <span style="font-size: 0.7rem; color: #fbbf24; text-transform: uppercase;">Tabella Relazionale / N:M</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4;">
                    Memorizza le recensioni dei film scritte dagli utenti (usata per il collaborative filtering nella Sfida 3).
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-xs); font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--mysql-primary); font-weight: 700;">🗝️ PK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>user_id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--neo4j-primary); font-weight: 700;">🔗 FK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>movie_id</strong> <span style="color: var(--text-muted)">INT</span></span>
                        <span style="color: var(--neo4j-primary); font-weight: 700;">🔗 FK</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>rating</strong> <span style="color: var(--text-muted)">DECIMAL(3,1)</span></span>
                        <span>-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding: 4px 0;">
                        <span><strong>comment</strong> <span style="color: var(--text-muted)">TEXT</span></span>
                        <span>-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span><strong>created_at</strong> <span style="color: var(--text-muted)">TIMESTAMP</span></span>
                        <span>-</span>
                    </div>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); padding: var(--space-xs) var(--space-sm); margin-top: var(--space-md); font-size: 0.75rem; border-top: 1px solid var(--border-glass);">
                <strong style="color: var(--neo4j-primary);">Filtraggio Collaborative:</strong> Molto densa (contiene oltre 18.000 righe di feedback).
            </div>
        </div>

    </div>

    <!-- DB Tips & Queries section -->
    <div style="background: var(--bg-glass); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); padding: var(--space-lg); margin-top: var(--space-xl);">
        <h3 style="margin-bottom: var(--space-md); font-weight: 800; color: var(--mysql-primary); display: flex; align-items: center; gap: 8px;">
            <span>💡</span> Consigli Utili per la Challenge
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-lg); font-size: 0.85rem; line-height: 1.6; color: var(--text-secondary);">
            <div>
                <strong style="color: var(--text-primary); display: block; margin-bottom: var(--space-xs);">1. Uso dei Parametri Nominativi (:name)</strong>
                Le query devono essere parametrizzate per motivi di sicurezza e dinamicità. Usa esattamente i segnaposti nominativi indicati negli header di ciascuna sfida (es. <code>:actor_id</code>, <code>:user_id</code>). Non inserire valori hard-coded nelle query.
            </div>
            <div>
                <strong style="color: var(--text-primary); display: block; margin-bottom: var(--space-xs);">2. Join ricorsivi (WITH RECURSIVE)</strong>
                Per le sfide basate su grafi e attraversamenti (Sfide 1 e 2), MySQL richiede l'utilizzo delle Common Table Expressions (CTE) ricorsive. Assicurati di definire il caso base (ancora) e il caso ricorsivo uniti da <code>UNION</code> o <code>UNION ALL</code>.
            </div>
            <div>
                <strong style="color: var(--text-primary); display: block; margin-bottom: var(--space-xs);">3. Ottimizzazione delle scansioni</strong>
                Per la Sfida 3 (Raccomandazioni), incrociare le tabelle reviews richiede confronti logici pesanti. Assicurati che i tuoi filtri <code>rating >= :min_rating</code> e le esclusioni dei film già visti avvengano con JOIN mirati o sottoquery con <code>NOT IN</code>.
            </div>
        </div>
    </div>
</div>
