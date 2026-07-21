/**
 * MovieChallenge — Challenge Interaction Module
 * Handles running challenges, displaying results, and benchmark tests.
 */

// ============================================================
// Challenge Page Initialization
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    const challengePage = document.querySelector('.challenge-page');
    if (!challengePage) return;

    const challengeId = parseInt(challengePage.dataset.challengeId);

    // Setup Reset Template click
    const resetTemplateBtn = document.getElementById('reset-template-btn');
    const queryInput = document.getElementById('sql-query-input');
    if (resetTemplateBtn && queryInput) {
        resetTemplateBtn.addEventListener('click', () => {
            if (confirm('Sei sicuro di voler ripristinare il template SQL originale per questa sfida? Eventuali modifiche andranno perse.')) {
                queryInput.value = queryInput.dataset.template || '';
                if (window.updateRunButton) {
                    window.updateRunButton();
                }
            }
        });
    }

    // Setup based on challenge type
    if (challengeId === 1) {
        setupChallenge1();
    } else if (challengeId === 2) {
        setupChallenge2();
    } else if (challengeId === 3) {
        setupChallenge3();
    }

    // Update run button state based on preloaded template
    if (window.updateRunButton) {
        window.updateRunButton();
    }
});

// ============================================================
// Challenge 1: Six Degrees of Kevin Bacon
// ============================================================

function setupChallenge1() {
    // Setup actor search
    setupActorSearch(
        'actor-search',
        'actor-results',
        'selected-actor',
        'selected-actor-name',
        'clear-actor',
        'selected-actor-id'
    );

    const runBtn = document.getElementById('run-challenge');
    if (runBtn) {
        runBtn.addEventListener('click', async () => {
            const actorId = parseInt(document.getElementById('selected-actor-id').value);
            const maxDepth = 6;
            const isTeacher = window.isTeacherMode();
            const TEAM_CODES = {
                'SPEEDFORCE': 'Team Flash',
                'DARKKNIGHT': 'Team Batman',
                'WILLPOWER': 'Team Green Lantern'
            };
            const teamCode = document.getElementById('team-code-input')?.value.trim().toUpperCase() || '';
            const teamName = isTeacher ? 'Team Superman' : (TEAM_CODES[teamCode] || '');
            const queryVal = document.getElementById('sql-query-input')?.value || '';

            if (!actorId) return;

            setRunning(runBtn, true);

            try {
                const result = await apiRequest('/api/challenge/1/run', {
                    method: 'POST',
                    body: JSON.stringify({ 
                        actor_id: actorId, 
                        max_depth: maxDepth, 
                        team_name: teamName,
                        sql_query: queryVal,
                        teacher_mode: window.isTeacherMode()
                    }),
                });

                displayResults(result, 1);
            } catch (error) {
                alert('Errore: ' + error.message);
            } finally {
                setRunning(runBtn, false);
            }
        });
    }


}

// ============================================================
// Challenge 2: Shortest Path
// ============================================================

function setupChallenge2() {
    // Setup two actor searches
    setupActorSearch(
        'actor1-search', 'actor1-results',
        'selected-actor1', 'selected-actor1-name',
        'clear-actor1', 'selected-actor1-id'
    );

    setupActorSearch(
        'actor2-search', 'actor2-results',
        'selected-actor2', 'selected-actor2-name',
        'clear-actor2', 'selected-actor2-id'
    );

    const runBtn = document.getElementById('run-challenge');
    if (runBtn) {
        runBtn.addEventListener('click', async () => {
            const actorId1 = parseInt(document.getElementById('selected-actor1-id').value);
            const actorId2 = parseInt(document.getElementById('selected-actor2-id').value);
            const isTeacher = window.isTeacherMode();
            const TEAM_CODES = {
                'SPEEDFORCE': 'Team Flash',
                'DARKKNIGHT': 'Team Batman',
                'WILLPOWER': 'Team Green Lantern'
            };
            const teamCode = document.getElementById('team-code-input')?.value.trim().toUpperCase() || '';
            const teamName = isTeacher ? 'Team Superman' : (TEAM_CODES[teamCode] || '');
            const queryVal = document.getElementById('sql-query-input')?.value || '';

            if (!actorId1 || !actorId2) return;

            setRunning(runBtn, true);

            try {
                const result = await apiRequest('/api/challenge/2/run', {
                    method: 'POST',
                    body: JSON.stringify({
                        actor_id_1: actorId1,
                        actor_id_2: actorId2,
                        team_name: teamName,
                        sql_query: queryVal,
                        teacher_mode: window.isTeacherMode()
                    }),
                });

                displayResults(result, 2);
            } catch (error) {
                alert('Errore: ' + error.message);
            } finally {
                setRunning(runBtn, false);
            }
        });
    }
}

// ============================================================
// Challenge 3: Recommendations
// ============================================================

function setupChallenge3() {
    // Setup rating button click handlers
    const ratingBtns = document.querySelectorAll('.rating-btn');
    ratingBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            ratingBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Auto-enable run button when user is selected
    const userSelect = document.getElementById('user-select');
    if (userSelect) {
        userSelect.addEventListener('change', updateRunButton);
    }

    const runBtn = document.getElementById('run-challenge');
    if (runBtn) {
        runBtn.addEventListener('click', async () => {
            const userId = parseInt(document.getElementById('user-select')?.value || 1);
            const minRating = parseFloat(document.querySelector('.rating-btn.active')?.dataset.rating || 3.5);
            const isTeacher = window.isTeacherMode();
            const TEAM_CODES = {
                'SPEEDFORCE': 'Team Flash',
                'DARKKNIGHT': 'Team Batman',
                'WILLPOWER': 'Team Green Lantern'
            };
            const teamCode = document.getElementById('team-code-input')?.value.trim().toUpperCase() || '';
            const teamName = isTeacher ? 'Team Superman' : (TEAM_CODES[teamCode] || '');
            const queryVal = document.getElementById('sql-query-input')?.value || '';

            setRunning(runBtn, true);

            try {
                const result = await apiRequest('/api/challenge/3/run', {
                    method: 'POST',
                    body: JSON.stringify({
                        user_id: userId,
                        min_rating: minRating,
                        team_name: teamName,
                        sql_query: queryVal,
                        teacher_mode: window.isTeacherMode()
                    }),
                });

                displayResults(result, 3);
            } catch (error) {
                alert('Errore: ' + error.message);
            } finally {
                setRunning(runBtn, false);
            }
        });
    }
}

// ============================================================
// Display Results
// ============================================================

function displayResults(result, challengeId) {
    const section = document.getElementById('results-section');
    if (!section) return;

    section.style.display = 'block';
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });

    // Update validation card status
    const validationCard = document.getElementById('validation-card');
    const validationStatus = document.getElementById('validation-status');
    const validationMessage = document.getElementById('validation-message');

    let isValid = false;
    let message = '';
    let isSqlError = !!result.mysql.error;

    if (isSqlError) {
        validationCard.className = 'validation-card error';
        validationStatus.textContent = '❌ Errore di Sintassi MySQL';
        validationMessage.textContent = result.mysql.error;
    } else if (result.validation && result.validation.valid === true) {
        validationCard.className = 'validation-card success';
        validationStatus.textContent = '✅ Risultato Convalidato!';
        validationMessage.textContent = result.validation.message;
        isValid = true;
    } else {
        validationCard.className = 'validation-card error';
        validationStatus.textContent = '❌ Risultato Non Valido';
        validationMessage.textContent = result.validation ? result.validation.message : 'I dati restituiti differiscono dal database a grafo di riferimento.';
    }
    validationCard.style.display = 'flex';

    // Update time values
    const mysqlTime = document.getElementById('mysql-time');
    const neo4jTime = document.getElementById('neo4j-time');
    const mysqlCount = document.getElementById('mysql-count');
    const neo4jCount = document.getElementById('neo4j-count');
    const mysqlMemory = document.getElementById('mysql-memory');
    const neo4jMemory = document.getElementById('neo4j-memory');
    const speedupBadge = document.getElementById('speedup-badge');
    const winnerLabel = document.getElementById('winner-label');
    const resultNeo4jPanel = document.getElementById('result-neo4j');
    const resultsComparisonContainer = document.getElementById('results-comparison');
    const resultVsContainer = document.querySelector('.result-vs');

    const isTeacher = window.isTeacherMode();

    // Toggle Neo4j specific visual panels based on Teacher Mode
    if (resultNeo4jPanel) resultNeo4jPanel.style.display = isTeacher ? 'block' : 'none';
    if (resultVsContainer) resultVsContainer.style.display = isTeacher ? 'block' : 'none';

    // If SQL errored, display N/A for MySQL performance metrics
    if (isSqlError) {
        if (mysqlTime) mysqlTime.textContent = 'N/A';
        if (mysqlCount) mysqlCount.textContent = '— risultati';
        if (mysqlMemory) mysqlMemory.textContent = '— KB memoria';
        if (speedupBadge) speedupBadge.style.display = 'none';
        if (winnerLabel) winnerLabel.textContent = '';
    } else {
        if (mysqlTime) mysqlTime.textContent = result.mysql.time_ms.toFixed(1);
        if (mysqlCount) mysqlCount.textContent = `${result.mysql.result_count} risultati`;
        if (mysqlMemory) mysqlMemory.textContent = `${result.mysql.memory_kb} KB memoria`;
        
        if (isTeacher) {
            if (speedupBadge) {
                speedupBadge.style.display = 'flex';
                const valEl = document.getElementById('speedup-value');
                if (valEl) valEl.textContent = `${result.speedup}x`;
            }
            if (winnerLabel) {
                if (result.winner === 'neo4j') {
                    winnerLabel.textContent = '🏆 Neo4j vince!';
                    winnerLabel.style.color = '#00b4d8';
                } else {
                    winnerLabel.textContent = '🏆 MySQL vince!';
                    winnerLabel.style.color = '#f97316';
                }
            }
        }
    }

    if (neo4jTime) neo4jTime.textContent = result.neo4j.time_ms.toFixed(1);
    if (neo4jCount) neo4jCount.textContent = `${result.neo4j.result_count} risultati`;
    if (neo4jMemory) neo4jMemory.textContent = `${result.neo4j.memory_kb} KB memoria`;

    // Only render performance comparison charts if SQL query was successful and Teacher Mode is active
    const chartContainer = document.getElementById('result-chart-container');
    if (isSqlError || !isTeacher) {
        chartContainer.style.display = 'none';
    } else {
        chartContainer.style.display = 'block';
        createComparisonChart('resultChart', result.mysql.time_ms, result.neo4j.time_ms);
    }

    // Populate data table
    populateDataTable(result, challengeId);
}

function populateDataTable(result, challengeId) {
    const thead = document.getElementById('data-thead');
    const tbody = document.getElementById('data-tbody');
    const dataSourceEl = document.getElementById('data-source');
    
    if (dataSourceEl) {
        dataSourceEl.textContent = '(MySQL)';
    }

    if (!thead || !tbody) return;

    // Use MySQL results so students see what their query returned
    const data = result.mysql.result || [];

    if (!Array.isArray(data) || data.length === 0) {
        thead.innerHTML = '';
        tbody.innerHTML = '<tr><td class="empty-state">Nessun risultato trovato</td></tr>';
        return;
    }

    // Build headers from first row keys
    const keys = Object.keys(data[0]);
    thead.innerHTML = '<tr>' + keys.map(k => `<th>${k}</th>`).join('') + '</tr>';

    // Build rows (limit to 50)
    const rows = data.slice(0, 50);
    tbody.innerHTML = rows.map(row =>
        '<tr>' + keys.map(k => {
            let val = row[k];
            if (val === null || val === undefined) val = '—';
            if (typeof val === 'number' && !Number.isInteger(val)) val = val.toFixed(2);
            return `<td>${val}</td>`;
        }).join('') + '</tr>'
    ).join('');
}



// ============================================================
// Benchmark Page
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    // Benchmark actor search
    if (document.getElementById('bench-actor-search')) {
        setupActorSearch(
            'bench-actor-search', 'bench-actor-results',
            'bench-selected-actor', 'bench-selected-actor-name',
            'bench-clear-actor', 'bench-selected-actor-id'
        );

        // Override updateRunButton for benchmark page
        const origUpdate = window.updateRunButton;
        window.updateRunButton = function () {
            if (origUpdate) origUpdate();
            const benchBtn = document.getElementById('run-full-benchmark');
            if (benchBtn) {
                benchBtn.disabled = !document.getElementById('bench-selected-actor-id')?.value;
            }
        };
    }

    // Full benchmark button
    const benchBtn = document.getElementById('run-full-benchmark');
    if (benchBtn) {
        benchBtn.addEventListener('click', async () => {
            const actorId = parseInt(document.getElementById('bench-selected-actor-id').value);
            if (!actorId) return;

            setRunning(benchBtn, true);

            try {
                // Run scalability test
                const scalability = await apiRequest('/api/challenge/1/scalability', {
                    method: 'POST',
                    body: JSON.stringify({ actor_id: actorId }),
                });

                // Show results
                document.getElementById('benchmark-results').style.display = 'block';

                // Create scalability chart
                createScalabilityChart('benchScalabilityChart', scalability);

                // Create comparison chart
                if (scalability.length > 0) {
                    const lastResult = scalability[scalability.length - 1];
                    createComparisonChart(
                        'benchComparisonChart',
                        lastResult.mysql_ms,
                        lastResult.neo4j_ms,
                        `Profondità max (${lastResult.depth} hop)`
                    );
                }

                // Populate summary table
                const tbody = document.getElementById('bench-summary-tbody');
                if (tbody) {
                    tbody.innerHTML = scalability.map(d => `<tr>
                        <td><strong>${d.depth} hop</strong></td>
                        <td style="color: #f97316; font-weight: 600">
                            ${d.mysql_timeout ? '⏰ TIMEOUT' : d.mysql_ms.toFixed(1) + ' ms'}
                        </td>
                        <td style="color: #00b4d8; font-weight: 600">${d.neo4j_ms.toFixed(1)} ms</td>
                        <td style="color: #10b981; font-weight: 800">${d.speedup}x</td>
                        <td>${d.mysql_count}</td>
                        <td>${d.neo4j_count}</td>
                    </tr>`).join('');
                }

                document.getElementById('benchmark-results')
                    .scrollIntoView({ behavior: 'smooth' });

            } catch (error) {
                alert('Errore benchmark: ' + error.message);
            } finally {
                setRunning(benchBtn, false);
            }
        });
    }
});

// ============================================================
// Helpers
// ============================================================

function setRunning(btn, running) {
    if (!btn) return;

    const text = btn.querySelector('.run-text');
    const loading = btn.querySelector('.run-loading');

    if (running) {
        btn.disabled = true;
        if (text) text.style.display = 'none';
        if (loading) loading.style.display = 'inline';
    } else {
        btn.disabled = false;
        if (text) text.style.display = 'inline';
        if (loading) loading.style.display = 'none';
    }
}
