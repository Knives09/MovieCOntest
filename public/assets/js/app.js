/**
 * MovieChallenge — Main Application JS
 * Handles dashboard stats, counter animations, and search functionality.
 */

// ============================================================
// Utility Functions
// ============================================================

/**
 * Make an API request.
 */
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: { 'Content-Type': 'application/json' },
            ...options,
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    } catch (error) {
        console.error(`API Error: ${url}`, error);
        throw error;
    }
}

/**
 * Animate a counter from 0 to target value.
 */
function animateCounter(element, target, duration = 1500) {
    const start = 0;
    const startTime = performance.now();

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Ease out cubic
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(start + (target - start) * eased);

        element.textContent = current.toLocaleString('it-IT');

        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }

    requestAnimationFrame(update);
}

/**
 * Debounce a function.
 */
function debounce(fn, delay = 300) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

/**
 * Format milliseconds for display.
 */
function formatMs(ms) {
    if (ms >= 1000) {
        return (ms / 1000).toFixed(2) + 's';
    }
    return ms.toFixed(1) + 'ms';
}

// ============================================================
// Dashboard Stats
// ============================================================

async function loadDashboardStats() {
    const heroStats = document.getElementById('hero-stats');
    if (!heroStats) return;

    try {
        const data = await apiRequest('/api/stats');
        const stats = data.neo4j?.result || data.mysql?.result || {};

        const mappings = {
            'stat-movies': stats.movie_count || 0,
            'stat-actors': stats.actor_count || 0,
            'stat-relations': stats.cast_count || 0,
            'stat-reviews': stats.review_count || 0,
        };

        for (const [id, value] of Object.entries(mappings)) {
            const card = document.getElementById(id);
            if (card) {
                const valueEl = card.querySelector('.stat-value');
                if (valueEl) {
                    animateCounter(valueEl, value);
                }
            }
        }
    } catch (error) {
        console.warn('Could not load stats:', error.message);
        // Set fallback values
        document.querySelectorAll('.stat-value').forEach(el => {
            el.textContent = '—';
        });
    }
}

// ============================================================
// Actor Search (reusable)
// ============================================================

function setupActorSearch(inputId, resultsId, selectedId, selectedNameId, clearBtnId, hiddenInputId) {
    const input = document.getElementById(inputId);
    const results = document.getElementById(resultsId);
    const selected = document.getElementById(selectedId);
    const selectedName = document.getElementById(selectedNameId);
    const clearBtn = document.getElementById(clearBtnId);
    const hiddenInput = document.getElementById(hiddenInputId);

    if (!input || !results) return;

    const doSearch = debounce(async function () {
        const query = input.value.trim();
        if (query.length < 2) {
            results.classList.remove('show');
            results.innerHTML = '';
            return;
        }

        try {
            const actors = await apiRequest(`/api/actors/search?q=${encodeURIComponent(query)}`);
            
            if (actors.length === 0) {
                results.innerHTML = '<div class="search-result-item">Nessun attore trovato</div>';
            } else {
                results.innerHTML = actors.map(actor =>
                    `<div class="search-result-item" data-id="${actor.id}" data-name="${actor.name}">
                        ${actor.name}
                        <span style="color: var(--text-tertiary); font-size: 0.8em;">
                            (${actor.movie_count || 0} film)
                        </span>
                    </div>`
                ).join('');
            }

            results.classList.add('show');

            // Click handlers
            results.querySelectorAll('.search-result-item[data-id]').forEach(item => {
                item.addEventListener('click', () => {
                    const actorId = item.dataset.id;
                    const actorName = item.dataset.name;

                    hiddenInput.value = actorId;
                    if (selected) {
                        selectedName.textContent = actorName;
                        selected.style.display = 'flex';
                    }
                    input.style.display = 'none';
                    results.classList.remove('show');
                    updateRunButton();
                });
            });
        } catch (error) {
            console.error('Search error:', error);
        }
    }, 300);

    input.addEventListener('input', doSearch);

    // Close results on outside click
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.classList.remove('show');
        }
    });

    // Clear selection
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            hiddenInput.value = '';
            if (selected) selected.style.display = 'none';
            input.value = '';
            input.style.display = 'block';
            input.focus();
            updateRunButton();
        });
    }
}

// ============================================================
// Update Run Button State
// ============================================================

function updateRunButton() {
    const runBtn = document.getElementById('run-challenge');
    const scalabilityBtn = document.getElementById('run-scalability');

    if (!runBtn) return;

    const challengePage = document.querySelector('.challenge-page');
    if (!challengePage) return;

    const challengeId = parseInt(challengePage.dataset.challengeId);
    const teamSelected = document.querySelector('.team-btn.active');
    const queryInput = document.getElementById('sql-query-input');
    const queryText = queryInput ? queryInput.value.trim() : '';
    
    let ready = !!teamSelected && queryText.length > 5;

    if (challengeId === 1) {
        ready = ready && !!document.getElementById('selected-actor-id')?.value;
    } else if (challengeId === 2) {
        ready = ready &&
            !!document.getElementById('selected-actor1-id')?.value &&
            !!document.getElementById('selected-actor2-id')?.value;
    }

    runBtn.disabled = !ready;
    if (scalabilityBtn) {
        scalabilityBtn.disabled = !teamSelected || !document.getElementById('selected-actor-id')?.value;
    }
}

// ============================================================
// Initialize
// ============================================================

// ============================================================
// Teacher Mode Manager
// ============================================================

window.isTeacherMode = function() {
    return localStorage.getItem('teacher_mode') === 'true';
};

window.setTeacherMode = function(enabled) {
    localStorage.setItem('teacher_mode', enabled ? 'true' : 'false');
    applyTeacherModeStyles();
};

window.applyTeacherModeStyles = function() {
    const isTeacher = window.isTeacherMode();
    
    // Toggle teacher navigation badge
    const badge = document.getElementById('badge-teacher');
    if (badge) {
        badge.style.display = isTeacher ? 'inline-block' : 'none';
    }

    // Toggle nav badges
    const navVs = document.getElementById('nav-badge-vs');
    const navNeo = document.getElementById('nav-badge-neo4j');
    if (navVs) navVs.style.display = isTeacher ? 'inline-block' : 'none';
    if (navNeo) navNeo.style.display = isTeacher ? 'inline-block' : 'none';

    // Show/Hide generic teacher-only and student-only elements
    document.querySelectorAll('.teacher-only').forEach(el => {
        if (el.tagName === 'TH' || el.tagName === 'TD' || el.classList.contains('neo4j-leaderboard-col')) {
            el.style.display = isTeacher ? 'table-cell' : 'none';
        } else if (el.classList.contains('weakness-card') || el.classList.contains('query-panel')) {
            el.style.display = isTeacher ? 'flex' : 'none';
        } else {
            el.style.display = isTeacher ? 'block' : 'none';
        }
    });

    document.querySelectorAll('.student-only').forEach(el => {
        if (el.tagName === 'SPAN' || el.tagName === 'CODE') {
            el.style.display = isTeacher ? 'none' : 'inline';
        } else {
            el.style.display = isTeacher ? 'none' : 'block';
        }
    });

    // Challenge page layout grids adjust
    const queryComparison = document.getElementById('query-comparison');
    if (queryComparison) {
        queryComparison.style.gridTemplateColumns = isTeacher ? '1fr 1fr' : '1fr';
    }

    const weaknessGrid = document.getElementById('weakness-grid');
    if (weaknessGrid) {
        weaknessGrid.style.gridTemplateColumns = isTeacher ? '1fr 1fr' : '1fr';
    }

    // Leaderboard page: Show or hide Reset button
    const resetBtn = document.getElementById('reset-leaderboard-btn');
    if (resetBtn) {
        resetBtn.style.display = isTeacher ? 'inline-block' : 'none';
    }

    // Re-render leaderboard table content if on leaderboard page
    const tbody = document.getElementById('leaderboard-tbody');
    if (tbody && tbody.dataset.hasData === 'true') {
        loadLeaderboard();
    }
};

window.handleTeacherModeUnlock = function() {
    const pwd = prompt('Inserisci la password docente per sbloccare Neo4j:');
    if (pwd === 'grafo') {
        window.setTeacherMode(true);
        alert('👨‍🏫 Modalità Docente ATTIVATA! Neo4j e i benchmark a grafo sono sbloccati.');
    } else if (pwd === 'lock') {
        window.setTeacherMode(false);
        alert('🔒 Modalità Docente DISATTIVATA! Neo4j e i benchmark a grafo sono nascosti.');
    } else if (pwd !== null) {
        alert('❌ Password errata.');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Setup URL query param ?teacher=1 auto-unlock
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('teacher')) {
        const val = urlParams.get('teacher');
        if (val === '1' || val === 'true') {
            localStorage.setItem('teacher_mode', 'true');
        } else if (val === '0' || val === 'false') {
            localStorage.setItem('teacher_mode', 'false');
        }
    }

    window.applyTeacherModeStyles();

    // Setup Reset Leaderboard click
    const resetBtn = document.getElementById('reset-leaderboard-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', async () => {
            if (!confirm('⚠️ ATTENZIONE: Sei sicuro di voler azzerare tutti i punteggi della classifica? Questa azione non può essere annullata.')) {
                return;
            }
            try {
                const response = await apiRequest('/api/leaderboard/reset', { method: 'POST' });
                if (response.success) {
                    alert('✅ Classifica azzerata con successo!');
                    window.location.reload();
                }
            } catch (error) {
                alert('Errore durante il reset: ' + error.message);
            }
        });
    }

    // Secret Logo Click (5 clicks)
    const logo = document.getElementById('nav-logo');
    if (logo) {
        let clicks = 0;
        logo.addEventListener('click', (e) => {
            if (window.isTeacherMode()) return;
            
            e.preventDefault();
            clicks++;
            if (clicks >= 5) {
                clicks = 0;
                window.handleTeacherModeUnlock();
            }
        });
    }

    // Secret Hotkey Ctrl + Shift + T
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 't') {
            e.preventDefault();
            window.handleTeacherModeUnlock();
        }
    });

    // Load dashboard stats
    loadDashboardStats();

    // Setup team selector
    document.querySelectorAll('.team-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.team-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            updateRunButton();
        });
    });

    // Setup depth selector
    document.querySelectorAll('.depth-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.depth-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Setup rating selector
    document.querySelectorAll('.rating-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Setup query input listener
    const queryInput = document.getElementById('sql-query-input');
    if (queryInput) {
        queryInput.addEventListener('input', updateRunButton);
    }

    // Count query lines for Cypher
    const cypherCode = document.getElementById('cypher-code');
    if (cypherCode) {
        const cypherLines = cypherCode.textContent.trim().split('\n').length;
        const cypherLinesEl = document.getElementById('cypher-lines');
        if (cypherLinesEl) cypherLinesEl.textContent = `${cypherLines} righe`;
    }

    // Load users for challenge 3
    const userSelect = document.getElementById('user-select');
    if (userSelect) {
        apiRequest('/api/users').then(users => {
            userSelect.innerHTML = users.map(u =>
                `<option value="${u.id}">${u.username} (${u.team_name})</option>`
            ).join('');
        }).catch(() => {
            userSelect.innerHTML = '<option value="1">user_1 (Team Alpha)</option>';
        });
    }

    // Load leaderboard
    loadLeaderboard();
});

// ============================================================
// Leaderboard
// ============================================================

async function loadLeaderboard() {
    const tbody = document.getElementById('leaderboard-tbody');
    if (!tbody) return;

    try {
        const data = await apiRequest('/api/leaderboard');
        const leaderboard = data.leaderboard || [];

        if (leaderboard.length === 0) return;

        tbody.dataset.hasData = 'true';
        const isTeacher = window.isTeacherMode();

        tbody.innerHTML = leaderboard.map((team, i) =>
            `<tr>
                <td><strong>${i + 1}</strong></td>
                <td><strong>${team.team_name}</strong></td>
                <td>${team.challenges_completed}</td>
                <td><strong>${team.total_score}</strong></td>
                <td>${team.avg_mysql_ms} ms</td>
                <td class="neo4j-leaderboard-col" style="${isTeacher ? '' : 'display:none'}">${team.avg_neo4j_ms} ms</td>
                <td>${team.last_activity || '—'}</td>
            </tr>`
        ).join('');

        // Update podium
        for (let i = 0; i < Math.min(3, leaderboard.length); i++) {
            const place = i + 1;
            const nameEl = document.getElementById(`podium-name-${place}`);
            const scoreEl = document.getElementById(`podium-score-${place}`);
            if (nameEl) nameEl.textContent = leaderboard[i].team_name;
            if (scoreEl) scoreEl.textContent = `${leaderboard[i].total_score} pts`;
        }
    } catch (error) {
        console.warn('Could not load leaderboard:', error.message);
    }
}
