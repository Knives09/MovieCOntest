# 🎬 MovieChallenge — MySQL vs Neo4j

## Descrizione Completa del Progetto

### Panoramica

**MovieChallenge** è una piattaforma didattica interattiva progettata per dimostrare, attraverso 3 sfide pratiche, i limiti dei database relazionali (MySQL) e i punti di forza dei database a grafo (Neo4j) nella gestione di dati fortemente interconnessi.

Il progetto utilizza dati reali provenienti dall'API **TMDb** (The Movie Database) per costruire un dataset di film, attori, cast, utenti e recensioni. I partecipanti — divisi in team — devono scrivere query SQL per risolvere ciascuna challenge; i risultati vengono automaticamente confrontati (in termini di correttezza e performance) con le soluzioni di riferimento eseguite su Neo4j.

### Obiettivi Didattici

1. **Comprendere i limiti del modello relazionale** per operazioni di traversal su grafi (percorsi, connessioni N-hop, pattern matching)
2. **Apprezzare l'index-free adjacency** dei database a grafo e come questa caratteristica renda le query di attraversamento O(1) per hop anziché O(N)
3. **Confrontare concretamente le performance** tramite benchmark in tempo reale con cronometri a precisione al nanosecondo
4. **Imparare a scrivere CTE ricorsive** (`WITH RECURSIVE`) in MySQL 8.0+ come alternativa alle operazioni di traversal native dei grafi

---

## Stack Tecnologico

| Componente         | Tecnologia                                      |
|--------------------|--------------------------------------------------|
| **Backend**        | PHP 8.3 (built-in web server)                    |
| **Database SQL**   | MySQL 8.0 (InnoDB)                               |
| **Database Grafo** | Neo4j 5 Community Edition (con plugin APOC)      |
| **Client Neo4j**   | `laudis/neo4j-php-client` v3.x (protocollo Bolt) |
| **Env Management** | `vlucas/phpdotenv` v5.6                          |
| **Container**      | Docker + Docker Compose                          |
| **Dati**           | TMDb API (The Movie Database)                    |

---

## Architettura del Progetto

```
MovieChallenge/
├── public/
│   └── index.php              # Front controller + routing
├── src/
│   ├── Router.php             # Router con supporto pattern matching
│   ├── Database/
│   │   ├── MySQLConnection.php    # Singleton PDO
│   │   └── Neo4jConnection.php    # Singleton Neo4j Client (Bolt)
│   ├── Repository/
│   │   ├── MySQL/
│   │   │   ├── ChallengeRepository.php  # Query SQL per le 3 challenge
│   │   │   ├── ActorRepository.php      # Ricerca attori
│   │   │   └── MovieRepository.php      # Ricerca film
│   │   └── Neo4j/
│   │       ├── ChallengeRepository.php  # Query Cypher per le 3 challenge
│   │       ├── ActorRepository.php      # Ricerca attori
│   │       └── MovieRepository.php      # Ricerca film
│   ├── Service/
│   │   ├── ChallengeService.php   # Orchestratore: esegue e valida le challenge
│   │   ├── BenchmarkService.php   # Misurazione tempi (hrtime ns) e confronto
│   │   └── TeamService.php        # Gestione team e classifica
│   └── Seeder/
│       ├── MySQLSeeder.php        # Popola MySQL con dati TMDb
│       ├── Neo4jSeeder.php        # Popola Neo4j con dati TMDb
│       └── TMDbImporter.php       # Importa dati dall'API TMDb
├── scripts/
│   ├── setup.php              # Creazione schema MySQL + constraint Neo4j
│   ├── seed.php               # Seeding dei dati
│   ├── import_data.php        # Import da API TMDb
│   └── export_db_to_json.php  # Export dei dati in JSON
├── data/
│   └── dump.json              # Dataset pre-importato (~10MB)
├── templates/                 # Template PHP per le pagine HTML
├── docker-compose.yml         # Orchestrazione container
├── Dockerfile                 # Immagine PHP 8.3
└── composer.json              # Dipendenze PHP
```

---

## Modello Dati

### Schema MySQL (Relazionale)

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   movies     │     │  movie_cast  │     │   actors     │
├──────────────┤     ├──────────────┤     ├──────────────┤
│ id (PK)      │◄────│ movie_id(FK) │     │ id (PK)      │
│ tmdb_id (UQ) │     │ actor_id(FK) │────►│ tmdb_id (UQ) │
│ title        │     │ character    │     │ name         │
│ release_date │     │ cast_order   │     │ profile_path │
│ overview     │     └──────────────┘     └──────────────┘
│ vote_average │
│ poster_path  │
└──────────────┘
       │
       │ movie_id
       ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ movie_genres │     │   reviews    │     │    users     │
├──────────────┤     ├──────────────┤     ├──────────────┤
│ movie_id(FK) │     │ id (PK)      │     │ id (PK)      │
│ genre_id(FK) │     │ user_id(FK)  │────►│ username(UQ) │
└──────────────┘     │ movie_id(FK) │     │ team_name    │
       │             │ rating       │     └──────────────┘
       ▼             │ comment      │
┌──────────────┐     │ created_at   │
│   genres     │     └──────────────┘
├──────────────┤
│ id (PK)      │
│ name (UQ)    │
└──────────────┘
```

**Tabella di supporto:**

```
┌────────────────────┐
│ challenge_results  │
├────────────────────┤
│ id (PK)            │
│ team_name          │
│ challenge_id       │
│ mysql_time_ms      │
│ neo4j_time_ms      │
│ result_count       │
│ score              │
│ completed_at       │
└────────────────────┘
```

### Schema Neo4j (Grafo)

```
(:Actor {id, tmdb_id, name, profile_path})
    -[:ACTED_IN {character, cast_order}]->
(:Movie {id, tmdb_id, title, release_date, overview, vote_average, poster_path})
    -[:HAS_GENRE]->
(:Genre {id, name})

(:User {id, username, team_name})
    -[:REVIEWED {rating, comment, created_at}]->
(:Movie)
```

**Nodi:** `Actor`, `Movie`, `Genre`, `User`
**Relazioni:** `ACTED_IN`, `HAS_GENRE`, `REVIEWED`

---

## Funzionamento dell'Applicazione

1. **L'utente sceglie una challenge** dalla dashboard
2. **Seleziona i parametri** (es. attore di partenza, profondità)
3. **Scrive la query SQL** partendo dal template fornito (con segnaposti `[COMPLETA QUI]`)
4. **Invia la query**: il sistema la esegue su MySQL e contemporaneamente esegue la query Cypher di riferimento su Neo4j
5. **Confronto automatico**: tempi di esecuzione, numero risultati e correttezza vengono comparati
6. **Validazione**: i risultati SQL vengono confrontati con quelli Neo4j per verificare la correttezza
7. **Classifica**: se la soluzione è corretta, il punteggio viene registrato nella leaderboard

---

## Le 3 Challenge

---

## Challenge 1: Six Degrees of Kevin Bacon 🎯

### Descrizione

Trova tutti gli attori collegati entro **N gradi di separazione** da un attore dato. Due attori sono "connessi" se hanno recitato nello stesso film. Con profondità 1 si trovano i co-protagonisti diretti; con profondità 2 si trovano anche i co-protagonisti dei co-protagonisti, e così via.

**Difficoltà:** Media

### Perché MySQL soffre

Ogni "hop" (grado di separazione) richiede un **JOIN aggiuntivo** sulla tabella `movie_cast`. Il costo cresce **esponenzialmente** con la profondità perché:
- Non esiste *index-free adjacency*: MySQL deve consultare indici B-tree ad ogni passo
- Il set intermedio di risultati esplode combinatorialmente
- Le CTE ricorsive generano piani di esecuzione sempre più costosi

### Perché Neo4j eccelle

- **Index-free adjacency**: ogni nodo ha puntatori diretti ai nodi adiacenti → O(1) per hop
- I percorsi a lunghezza variabile (`*1..N`) sono una feature nativa del linguaggio Cypher
- Nessuna esplosione combinatoria: Neo4j attraversa direttamente il grafo

### Template SQL (fornito ai partecipanti)

```sql
WITH RECURSIVE actor_connections AS (
    -- Caso base: seleziona gli attori direttamente connessi all'attore di partenza (:actor_id)
    SELECT mc2.actor_id, 1 as depth
    FROM movie_cast mc1
    JOIN movie_cast mc2 ON mc1.movie_id = mc2.movie_id AND mc1.actor_id != mc2.actor_id
    WHERE mc1.actor_id = :actor_id
    
    UNION
    
    -- Caso ricorsivo: connetti gli attori trovati al passo precedente con i loro co-protagonisti
    SELECT mc2.actor_id, ac.depth + 1
    FROM actor_connections ac
    -- [COMPLETA QUI: inserisci i JOIN necessari tra ac, movie_cast mc1, movie_cast mc2]
    -- [Suggerimento: unisci ac con movie_cast mc1 (actor_id), poi mc1 con mc2 (movie_id)]
    
    WHERE ac.depth < :max_depth
)
SELECT a.name, MIN(ac.depth) as degrees
FROM actor_connections ac
JOIN actors a ON a.id = ac.actor_id
GROUP BY a.name
ORDER BY degrees;
```

### ✅ Soluzione SQL Completa

```sql
WITH RECURSIVE actor_connections AS (
    -- Caso base: tutti gli attori che hanno recitato in almeno un film con l'attore :actor_id
    SELECT mc2.actor_id, 1 as depth
    FROM movie_cast mc1
    JOIN movie_cast mc2 ON mc1.movie_id = mc2.movie_id AND mc1.actor_id != mc2.actor_id
    WHERE mc1.actor_id = :actor_id

    UNION

    -- Caso ricorsivo: per ogni attore trovato al livello precedente,
    -- trova i SUOI co-protagonisti (attori con cui condivide un film)
    SELECT mc2.actor_id, ac.depth + 1
    FROM actor_connections ac
    JOIN movie_cast mc1 ON mc1.actor_id = ac.actor_id
    JOIN movie_cast mc2 ON mc1.movie_id = mc2.movie_id AND mc1.actor_id != mc2.actor_id
    WHERE ac.depth < :max_depth
)
SELECT a.name, MIN(ac.depth) as degrees
FROM actor_connections ac
JOIN actors a ON a.id = ac.actor_id
GROUP BY a.name
ORDER BY degrees;
```

### Spiegazione della soluzione

1. **Caso base (`depth = 1`):** partendo dall'attore `:actor_id`, trova tutti gli attori che compaiono nello stesso film tramite la tabella `movie_cast`. Il self-join `mc1 ↔ mc2` sullo stesso `movie_id` identifica i co-protagonisti.

2. **Caso ricorsivo (`depth + 1`):** prende gli attori trovati al livello precedente (`ac.actor_id`) e ripete lo stesso pattern: cerca quali film hanno fatto (`mc1.actor_id = ac.actor_id`) e chi altro era in quei film (`mc2` sullo stesso `movie_id`). Si ferma quando `depth` raggiunge `:max_depth`.

3. **Aggregazione finale:** `MIN(ac.depth)` assicura che per ogni attore si prenda il grado di separazione più basso (il percorso più breve). `GROUP BY a.name` elimina i duplicati.

### Query Cypher equivalente (Neo4j)

```cypher
MATCH (start:Actor {id: $actorId})
MATCH path = (start)-[:ACTED_IN*1..N]-(other:Actor)
WHERE other <> start
WITH other, min(length(path)) / 2 AS degrees
RETURN other.name AS name, degrees
ORDER BY degrees
```

> **Nota:** in Neo4j il percorso Actor→Movie→Actor conta come lunghezza 2, per questo si divide per 2.

### Parametri disponibili

| Parametro    | Placeholder   | Tipo | Descrizione                       |
|-------------|---------------|------|-----------------------------------|
| Actor ID     | `:actor_id`   | INT  | ID dell'attore di partenza       |
| Max Depth    | `:max_depth`  | INT  | Profondità massima (1-6)         |

---

## Challenge 2: Shortest Path 🛤️

### Descrizione

Trova il **percorso più breve** tra due attori attraverso film in comune. Il risultato deve mostrare la catena completa: `Attore A → Film X → Attore B → Film Y → Attore C`.

**Difficoltà:** Alta

### Perché MySQL soffre

- SQL non è progettato per il **path traversal**: bisogna implementare manualmente un algoritmo BFS
- Ogni livello BFS richiede una nuova query iterativa
- Bisogna tracciare manualmente il percorso completo (nomi attori + titoli film)
- Il tracking degli attori già visitati richiede concatenazione di stringhe e `POSITION()`

### Perché Neo4j eccelle

- `shortestPath()` è un **algoritmo nativo** ottimizzato con BFS bidirezionale
- Restituisce automaticamente il percorso completo con tutti i nodi intermedi
- Una sola riga di Cypher sostituisce decine di righe di PHP+SQL

### Template SQL (fornito ai partecipanti)

```sql
WITH RECURSIVE bfs_path AS (
    -- Caso base: parti dall'attore 1 (:actor_id_1)
    SELECT 
        mc1.actor_id AS current_actor_id,
        CAST(a.name AS CHAR(1000)) AS path_names,
        1 AS depth,
        CAST(CONCAT(',', mc1.actor_id, ',') AS CHAR(1000)) AS visited
    FROM actors a
    JOIN movie_cast mc1 ON a.id = mc1.actor_id
    WHERE mc1.actor_id = :actor_id_1

    UNION ALL

    -- Caso ricorsivo: naviga verso attori connessi tramite i film
    SELECT 
        mc2.actor_id AS current_actor_id,
        -- [COMPLETA QUI: concatena il percorso corrente con il titolo del film
        -- e il nome del co-protagonista]
        -- [Esempio: CONCAT(bp.path_names, ' -> ', m.title, ' -> ', a2.name)]
        
        bp.depth + 1 AS depth,
        CONCAT(bp.visited, mc2.actor_id, ',') AS visited
    FROM bfs_path bp
    -- [COMPLETA QUI: inserisci i JOIN con movie_cast mc1, movies m,
    -- movie_cast mc2 e actors a2]
    
    WHERE POSITION(CONCAT(',', mc2.actor_id, ',') IN bp.visited) = 0
      AND bp.depth < 4
)
SELECT path_names
FROM bfs_path
WHERE current_actor_id = :actor_id_2
ORDER BY depth ASC
LIMIT 1;
```

### ✅ Soluzione SQL Completa

```sql
WITH RECURSIVE bfs_path AS (
    -- Caso base: inizializza il BFS con l'attore di partenza
    SELECT 
        mc1.actor_id AS current_actor_id,
        CAST(a.name AS CHAR(1000)) AS path_names,
        1 AS depth,
        CAST(CONCAT(',', mc1.actor_id, ',') AS CHAR(1000)) AS visited
    FROM actors a
    JOIN movie_cast mc1 ON a.id = mc1.actor_id
    WHERE mc1.actor_id = :actor_id_1

    UNION ALL

    -- Caso ricorsivo: esplora il grafo livello per livello (BFS)
    SELECT 
        mc2.actor_id AS current_actor_id,
        CONCAT(bp.path_names, ' -> ', m.title, ' -> ', a2.name) AS path_names,
        bp.depth + 1 AS depth,
        CONCAT(bp.visited, mc2.actor_id, ',') AS visited
    FROM bfs_path bp
    JOIN movie_cast mc1 ON mc1.actor_id = bp.current_actor_id
    JOIN movies m ON m.id = mc1.movie_id
    JOIN movie_cast mc2 ON mc2.movie_id = mc1.movie_id AND mc2.actor_id != mc1.actor_id
    JOIN actors a2 ON a2.id = mc2.actor_id
    WHERE POSITION(CONCAT(',', mc2.actor_id, ',') IN bp.visited) = 0
      AND bp.depth < 4
)
SELECT path_names
FROM bfs_path
WHERE current_actor_id = :actor_id_2
ORDER BY depth ASC
LIMIT 1;
```

### Spiegazione della soluzione

1. **Caso base:** si inizializza la "coda BFS" con l'attore di partenza (`:actor_id_1`). Si memorizza il suo nome in `path_names` e il suo ID nella stringa `visited` (formato `,id,`) per evitare cicli.

2. **Caso ricorsivo (i 4 JOIN):**
   - `movie_cast mc1 ON mc1.actor_id = bp.current_actor_id` → trova tutti i film dell'attore corrente
   - `movies m ON m.id = mc1.movie_id` → recupera il titolo del film (per il percorso)
   - `movie_cast mc2 ON mc2.movie_id = mc1.movie_id AND mc2.actor_id != mc1.actor_id` → trova i co-protagonisti in quel film
   - `actors a2 ON a2.id = mc2.actor_id` → recupera il nome del co-protagonista

3. **Concatenazione del percorso:** `CONCAT(bp.path_names, ' -> ', m.title, ' -> ', a2.name)` costruisce la stringa del percorso accumulandola ad ogni passo.

4. **Anti-ciclo:** `POSITION(CONCAT(',', mc2.actor_id, ',') IN bp.visited) = 0` verifica che l'attore non sia già stato visitato.

5. **Condizione di stop:** `bp.depth < 4` limita la ricerca a 4 hop (sufficiente per la maggior parte dei percorsi nel dataset).

6. **Selezione del risultato:** si filtra per `current_actor_id = :actor_id_2` (attore di destinazione), si ordina per `depth ASC` e si prende il primo risultato (`LIMIT 1`) = percorso più breve.

### Query Cypher equivalente (Neo4j)

```cypher
MATCH (a1:Actor {id: $actorId1}), (a2:Actor {id: $actorId2})
MATCH path = shortestPath((a1)-[:ACTED_IN*]-(a2))
UNWIND nodes(path) AS node
RETURN 
    CASE WHEN node:Actor THEN node.name 
         WHEN node:Movie THEN node.title 
    END AS name
```

> **Nota:** Neo4j risolve questo problema con **una singola riga** (`shortestPath()`), sfruttando un BFS bidirezionale nativo. In MySQL servono CTE ricorsive, 4 JOIN, tracking manuale del percorso e gestione anti-ciclo.

### Parametri disponibili

| Parametro    | Placeholder     | Tipo | Descrizione                       |
|-------------|-----------------|------|-----------------------------------|
| Actor ID 1   | `:actor_id_1`   | INT  | ID dell'attore di partenza       |
| Actor ID 2   | `:actor_id_2`   | INT  | ID dell'attore di destinazione   |

---

## Challenge 3: Raccomandazioni Film ⭐

### Descrizione

Implementa un sistema di **collaborative filtering**: suggerisci film a un utente basandoti su ciò che è piaciuto ad altri utenti con gusti simili.

Il pattern logico è:
```
Utente U → ha apprezzato Film M ← anche apprezzato da Utente V → V ha apprezzato anche Film R
                                                                    (mai visto da U → RACCOMANDATO!)
```

**Difficoltà:** Alta

### Perché MySQL soffre

- Servono **3 self-join** sulla tabella `reviews` (r1, r2, r3) + una subquery di esclusione
- La query risultante è complessa, poco leggibile e costosa in termini di performance
- L'aggregazione (`COUNT`, `AVG`, `GROUP BY`) appesantisce ulteriormente l'esecuzione

### Perché Neo4j eccelle

- Il pattern di raccomandazione si mappa **direttamente** su un pattern Cypher
- La query è quasi linguaggio naturale: `MATCH (u)-[:REVIEWED]->(m)<-[:REVIEWED]-(other)-[:REVIEWED]->(rec)`
- Nessun bisogno di self-join o subquery

### Template SQL (fornito ai partecipanti)

```sql
SELECT m.title, 
       COUNT(DISTINCT r3.user_id) as score,
       AVG(r3.rating) as avg_rating
FROM reviews r1
-- R2: recensioni degli altri utenti sullo stesso film (r1.movie_id)
JOIN reviews r2 ON r1.movie_id = r2.movie_id AND r1.user_id != r2.user_id AND r2.rating >= :min_rating
-- R3: recensioni degli altri utenti (r2.user_id) su ALTRI film (diversi da r1.movie_id)
-- [COMPLETA QUI: inserisci i JOIN mancanti per r3 (recensioni) e m (movies)]
-- [Ricorda che r3 deve collegare l'utente r2.user_id a film diversi r3.movie_id != r1.movie_id]

WHERE r1.user_id = :user_id 
  AND r1.rating >= :min_rating
  AND r3.movie_id NOT IN (
      SELECT movie_id FROM reviews WHERE user_id = :user_id
  )
GROUP BY m.title
ORDER BY score DESC 
LIMIT 10;
```

### ✅ Soluzione SQL Completa

```sql
SELECT m.title, 
       COUNT(DISTINCT r3.user_id) as score,
       AVG(r3.rating) as avg_rating
FROM reviews r1
JOIN reviews r2 ON r1.movie_id = r2.movie_id 
     AND r1.user_id != r2.user_id 
     AND r2.rating >= :min_rating
JOIN reviews r3 ON r2.user_id = r3.user_id 
     AND r3.movie_id != r1.movie_id 
     AND r3.rating >= :min_rating
JOIN movies m ON m.id = r3.movie_id
WHERE r1.user_id = :user_id 
  AND r1.rating >= :min_rating
  AND r3.movie_id NOT IN (
      SELECT movie_id FROM reviews WHERE user_id = :user_id
  )
GROUP BY m.title
ORDER BY score DESC 
LIMIT 10;
```

### Spiegazione della soluzione

Il pattern di collaborative filtering viene implementato con una catena di JOIN:

1. **`r1` (recensioni dell'utente target):** seleziona tutti i film che l'utente `:user_id` ha valutato con rating ≥ `:min_rating`.

2. **`r2` (utenti con gusti simili):** trova tutti gli altri utenti (`r1.user_id != r2.user_id`) che hanno apprezzato gli stessi film (`r1.movie_id = r2.movie_id`) con rating ≥ `:min_rating`. Questi sono gli utenti "simili".

3. **`r3` (film raccomandati):** per ciascun utente simile (`r2.user_id = r3.user_id`), trova gli altri film che hanno apprezzato (`r3.movie_id != r1.movie_id` e `r3.rating >= :min_rating`). Questi sono i candidati per la raccomandazione.

4. **`movies m`:** `JOIN movies m ON m.id = r3.movie_id` recupera il titolo del film raccomandato.

5. **Esclusione film già visti:** la subquery `NOT IN (SELECT movie_id FROM reviews WHERE user_id = :user_id)` rimuove i film che l'utente ha già recensito.

6. **Scoring:** `COUNT(DISTINCT r3.user_id)` = quanti utenti simili hanno raccomandato quel film (più sono, più il film è rilevante). `AVG(r3.rating)` = voto medio dato a quel film dagli utenti simili.

### Query Cypher equivalente (Neo4j)

```cypher
MATCH (u:User {id: $userId})-[r1:REVIEWED]->(m:Movie)
WHERE r1.rating >= $minRating
WITH u, collect(m) AS myMovies
MATCH (m:Movie)<-[r2:REVIEWED]-(other:User)
WHERE m IN myMovies AND r2.rating >= $minRating AND other <> u
WITH u, myMovies, other
MATCH (other)-[r3:REVIEWED]->(rec:Movie)
WHERE r3.rating >= $minRating AND NOT rec IN myMovies
RETURN rec.title, COUNT(DISTINCT other) AS score
ORDER BY score DESC LIMIT 10
```

> **Nota:** la query Cypher segue lo stesso pattern logico ma in modo **molto più leggibile** — il flusso `u → m ← other → rec` è visivamente chiaro e quasi auto-documentante.

### Parametri disponibili

| Parametro    | Placeholder    | Tipo   | Descrizione                                  |
|-------------|----------------|--------|----------------------------------------------|
| User ID      | `:user_id`     | INT    | ID dell'utente per cui generare raccomandazioni |
| Min Rating   | `:min_rating`  | FLOAT  | Voto minimo per considerare un film "apprezzato" (default: 3.5) |

---

## Confronto Prestazioni — Riepilogo

| Challenge             | MySQL                                                | Neo4j                                   | Speedup Neo4j |
|----------------------|------------------------------------------------------|-----------------------------------------|---------------|
| **Six Degrees (d=2)** | ~50-200ms (CTE ricorsiva con 2 livelli di JOIN)     | ~5-20ms (traversal nativo)              | ~5-10x        |
| **Six Degrees (d=4)** | ~2-30s ⚠️ (esplosione combinatoria)                 | ~20-80ms (crescita lineare)             | ~100-500x     |
| **Shortest Path**     | ~100ms-10s (BFS manuale con query iterative)        | ~5-30ms (`shortestPath()` nativo)       | ~10-100x      |
| **Raccomandazioni**   | ~50-500ms (3 self-join + subquery + aggregazione)   | ~10-50ms (pattern matching diretto)     | ~5-20x        |

> **⚠️ Nota:** i tempi sono indicativi e dipendono dalla dimensione del dataset e dall'hardware. Il punto chiave è che MySQL degrada **esponenzialmente** con la profondità, mentre Neo4j cresce **linearmente**.

---

## Sistema di Benchmark

Il `BenchmarkService` misura i tempi con precisione al **nanosecondo** (`hrtime(true)`) e confronta:

- **Tempo di esecuzione** (ms) — MySQL vs Neo4j
- **Memoria utilizzata** (KB)
- **Numero di risultati** restituiti
- **Speedup** = tempo MySQL / tempo Neo4j
- **Timeout** (20s per query utente, 30s per test scalabilità)

Prima di ogni benchmark su Neo4j, viene eseguito un **pre-warm** della connessione (`RETURN 1`) per escludere il tempo di handshake Bolt dai risultati.

---

## Validazione dei Risultati

Ogni challenge implementa una logica di validazione specifica:

| Challenge       | Criterio di validazione                                                                                       |
|----------------|---------------------------------------------------------------------------------------------------------------|
| **Six Degrees** | ≥85% degli attori trovati da Neo4j devono essere presenti con lo stesso grado di separazione                  |
| **Shortest Path**| Il percorso deve contenere sia l'attore di partenza che quello di arrivo, con una lunghezza coerente (±1 nodo) |
| **Raccomandazioni**| ≥60% dei primi 10 film raccomandati devono corrispondere a quelli calcolati da Neo4j                       |

---

## Setup e Avvio

```bash
# 1. Clona il progetto
git clone <repo-url> && cd MovieChallenge

# 2. Copia il file di configurazione
cp .env.example .env

# 3. Avvia i container Docker
docker-compose up -d

# 4. Installa le dipendenze PHP
docker exec movie_challenge_app composer install

# 5. Setup del database (schema + constraint)
docker exec movie_challenge_app php scripts/setup.php

# 6. Importa i dati
docker exec movie_challenge_app php scripts/seed.php

# 7. Accedi all'applicazione
# http://localhost:8080
# Credenziali: mashfrog / Movies123!
```

---

| Team          | Colore       | Icona |
|---------------|--------------|-------|
| Team Flash    | 🟠 Arancione  | ⚡ (Lightning Bolt) |
| Team Batman   | 🔵 Azzurro    | 🦇 (Bat / Gotham Knight) |
| Team Green Lantern | 🟢 Verde | 🟢 (Ring / Power Battery) |

SPEEDFORCE $\rightarrow$ Team Flash
DARKKNIGHT $\rightarrow$ Team Batman
WILLPOWER $\rightarrow$ Team Green Lantern
---

## Conclusioni

MovieChallenge dimostra in modo pratico e misurabile che i **database relazionali non sono lo strumento giusto per ogni problema**. Quando i dati sono fortemente interconnessi e le query richiedono operazioni di traversal (percorsi, connessioni N-hop, pattern matching), i database a grafo come Neo4j offrono:

1. **Performance superiori** — da 5x a 500x più veloci, con il gap che cresce con la complessità
2. **Query più leggibili** — Cypher esprime in 3-5 righe ciò che in SQL richiede CTE ricorsive, self-join multipli e tracking manuale
3. **Scalabilità lineare** — il tempo di risposta di Neo4j cresce linearmente con la profondità, mentre MySQL degenera esponenzialmente

La lezione chiave è: **scegli lo strumento giusto per il problema giusto**. MySQL resta eccellente per dati tabulari e query OLTP classiche, ma per problemi di tipo grafo, Neo4j è la scelta naturale.
