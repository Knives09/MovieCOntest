/**
 * MovieChallenge — Charts Module
 * Creates and updates Chart.js visualizations for benchmarks.
 */

// Chart.js defaults for dark theme
if (typeof Chart !== 'undefined') {
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.06)';
    Chart.defaults.font.family = "'Inter', sans-serif";
}

// Store chart instances for cleanup
const chartInstances = {};

/**
 * Create a comparison bar chart (MySQL vs Neo4j times).
 */
function createComparisonChart(canvasId, mysqlMs, neo4jMs, label = 'Tempo di Esecuzione') {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    // Destroy existing chart
    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }

    chartInstances[canvasId] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [label],
            datasets: [
                {
                    label: 'MySQL',
                    data: [mysqlMs],
                    backgroundColor: 'rgba(249, 115, 22, 0.6)',
                    borderColor: '#f97316',
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.6,
                },
                {
                    label: 'Neo4j',
                    data: [neo4jMs],
                    backgroundColor: 'rgba(0, 180, 216, 0.6)',
                    borderColor: '#00b4d8',
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2.5,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 13, weight: '600' },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${ctx.raw.toFixed(2)} ms`,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Tempo (ms)',
                        font: { size: 12, weight: '600' },
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.04)',
                    },
                },
                x: {
                    grid: { display: false },
                },
            },
        },
    });
}

/**
 * Create a scalability line chart (time vs depth).
 * This is the KEY chart that shows MySQL's exponential degradation.
 */
function createScalabilityChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }

    const depths = data.map(d => `${d.depth} hop`);
    const mysqlTimes = data.map(d => d.mysql_timeout ? null : d.mysql_ms);
    const neo4jTimes = data.map(d => d.neo4j_ms);

    chartInstances[canvasId] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: depths,
            datasets: [
                {
                    label: 'MySQL',
                    data: mysqlTimes,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#f97316',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                },
                {
                    label: 'Neo4j',
                    data: neo4jTimes,
                    borderColor: '#00b4d8',
                    backgroundColor: 'rgba(0, 180, 216, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#00b4d8',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 14, weight: '600' },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            if (ctx.raw === null) {
                                return `${ctx.dataset.label}: TIMEOUT ⏰`;
                            }
                            return `${ctx.dataset.label}: ${ctx.raw.toFixed(2)} ms`;
                        },
                    },
                    backgroundColor: 'rgba(20, 20, 40, 0.95)',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    titleFont: { size: 14, weight: '700' },
                    bodyFont: { size: 13 },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    type: 'logarithmic',
                    title: {
                        display: true,
                        text: 'Tempo (ms) — Scala Logaritmica',
                        font: { size: 12, weight: '600' },
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.04)',
                    },
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000) return (value/1000).toFixed(0) + 's';
                            return value + 'ms';
                        },
                    },
                },
                x: {
                    title: {
                        display: true,
                        text: 'Profondità di Traversal',
                        font: { size: 12, weight: '600' },
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.04)',
                    },
                },
            },
        },
    });
}

/**
 * Create a leaderboard bar chart.
 */
function createLeaderboardChart(canvasId, teams) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }

    const teamColors = {
        'Team Flash': '#f97316',
        'Team Batman': '#00b4d8',
        'Team Green Lantern': '#10b981',
    };

    chartInstances[canvasId] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: teams.map(t => t.team_name),
            datasets: [{
                label: 'Punteggio',
                data: teams.map(t => t.total_score),
                backgroundColor: teams.map(t => teamColors[t.team_name] || '#8b5cf6'),
                borderRadius: 8,
                barPercentage: 0.6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Punteggio Totale',
                        font: { size: 12, weight: '600' },
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.04)',
                    },
                },
                x: {
                    grid: { display: false },
                },
            },
        },
    });
}
