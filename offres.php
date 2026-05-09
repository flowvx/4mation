<?php
 

require_once __DIR__ . '/includes/functions.php';

$pdo = getPDO();

// RÉCUPÉRATION DES FILTRES  
$q            = getStr('q');
$lieu         = getStr('lieu');
$type_contrat = getStr('type_contrat');
$metier_id    = (int)getStr('metier_id', '0');
$teletravail  = getStr('teletravail', '');
$page         = max(1, (int)getStr('page', '1'));
$per_page     = 10;
$offset       = ($page - 1) * $per_page;
 
$conditions = ['o.is_active = 1'];
$params     = [];

if ($q !== '') {
    $conditions[] = '(o.titre LIKE :q1 OR o.missions LIKE :q2 OR e.nom LIKE :q3)';
    $params[':q1'] = '%' . $q . '%';
    $params[':q2'] = '%' . $q . '%';
    $params[':q3'] = '%' . $q . '%';
}

if ($lieu !== '') {
    $conditions[] = 'o.lieu LIKE :lieu';
    $params[':lieu'] = '%' . $lieu . '%';
}

if ($type_contrat !== '') {
    $conditions[] = 'o.type_contrat = :type_contrat';
    $params[':type_contrat'] = $type_contrat;
}

if ($metier_id > 0) {
    $conditions[] = 'o.metier_id = :metier_id';
    $params[':metier_id'] = $metier_id;
}

if ($teletravail === '1') {
    $conditions[] = 'o.teletravail_possible = 1';
}

$whereClause = 'WHERE ' . implode(' AND ', $conditions);

 
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM offres o INNER JOIN entreprises e ON o.entreprise_id = e.id LEFT JOIN metiers m ON o.metier_id = m.id $whereClause");
$countStmt->execute($params);
$totalResults = (int)$countStmt->fetchColumn();
$totalPages   = (int)ceil($totalResults / $per_page);
 
$mainSql = "
    SELECT o.id, o.titre, o.lieu, o.type_contrat, o.date_publication, o.teletravail_possible, o.niveau_etude,
           e.nom AS entreprise_nom, e.logo_url AS entreprise_logo, m.nom_metier AS metier
    FROM offres o
    INNER JOIN entreprises e ON o.entreprise_id = e.id
    LEFT  JOIN metiers     m ON o.metier_id     = m.id
    $whereClause
    ORDER BY o.date_publication DESC
    LIMIT :limit OFFSET :offset
";

$mainStmt = $pdo->prepare($mainSql); 
foreach ($params as $key => $val) {
    $mainStmt->bindValue($key, $val);
}
$mainStmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
$mainStmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
$mainStmt->execute();
$offres = $mainStmt->fetchAll();

$metiers = $pdo->query('SELECT id, nom_metier FROM metiers ORDER BY nom_metier ASC')->fetchAll();

function timeAgo(string $dateStr): string {
    $diff = time() - strtotime($dateStr);
    if ($diff < 3600)   return 'Il y a ' . round($diff / 60) . ' min';
    if ($diff < 86400)  return 'Il y a ' . round($diff / 3600) . ' h';
    if ($diff < 604800) return 'Il y a ' . round($diff / 86400) . ' j';
    return date('d/m/Y', strtotime($dateStr));
}

 
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche d\'offres - 4Mation</title>
    <link rel="stylesheet" href="/css/index.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/offres.css?v=<?= time() ?>">
    <style>
        .pagination { display:flex; gap:8px; justify-content:center; margin-top:32px; }
        .pagination a, .pagination span { padding:8px 14px; border-radius:6px; background:var(--surface-2); border:1px solid #333; color:var(--text-muted); font-size:13px; text-decoration:none; }
        .pagination .active { background:var(--orange); color:#fff; border-color:var(--orange); }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href="index.php"><img src="img/66pp-removebg-preview.png" alt="4Mation" class="logo"></a>
        <a href="login.php" class="cta-button">Espace Admin</a>
    </div>
</header>

<main class="search-layout">
    <aside class="filters-sidebar">
        <form method="GET" action="offres.php" id="filter-form">
            <div class="filter-group">
                <h3>Type de contrat</h3>
                <label class="custom-check">
                    <span>
                        <input type="radio" name="type_contrat" value="" ' . ($type_contrat === '' ? 'checked' : '') . '> Tous
                    </span>
                </label>
                <label class="custom-check">
                    <span>
                        <input type="radio" name="type_contrat" value="Stage" ' . ($type_contrat === 'Stage' ? 'checked' : '') . '> Stage
                    </span>
                </label>
                <label class="custom-check">
                    <span>
                        <input type="radio" name="type_contrat" value="Alternance" ' . ($type_contrat === 'Alternance' ? 'checked' : '') . '> Alternance
                    </span>
                </label>
            </div>

            <div class="filter-group">
                <h3>Télétravail</h3>
                <label class="custom-check">
                    <input type="checkbox" name="teletravail" value="1" ' . ($teletravail === '1' ? 'checked' : '') . '>
                    Télétravail possible
                </label>
            </div>

            <div class="filter-group">
                <h3>Métier</h3>
                <select name="metier_id" class="filter-select" onchange="this.form.submit()">
                    <option value="0">Tous les métiers</option>';
                    foreach ($metiers as $m) {
                        $selected = ($metier_id === (int)$m['id']) ? 'selected' : '';
                        echo '<option value="' . (int)$m['id'] . '" ' . $selected . '>' . h($m['nom_metier']) . '</option>';
                    }
echo '          </select>
            </div>

            <input type="hidden" name="q" value="' . h($q) . '">
            <input type="hidden" name="lieu" value="' . h($lieu) . '">

            <button type="submit" class="hero-cta" style="width:100%;margin-top:8px;font-size:13px;padding:10px;">
                Appliquer les filtres
            </button>
        </form>
    </aside>

    <section class="results-container">
        <form class="search-top-bar" method="GET" action="offres.php">
            <input type="hidden" name="type_contrat" value="' . h($type_contrat) . '">
            <input type="hidden" name="metier_id" value="' . $metier_id . '">
            <input type="hidden" name="teletravail" value="' . h($teletravail) . '">

            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" name="q" placeholder="DevOps, Analyste SOC, Stagiaire IA..." value="' . h($q) . '">
            </div>
            <input type="text" name="lieu" placeholder="Ville..." value="' . h($lieu) . '" style="padding:16px;background:var(--surface-2);border:1px solid #222;border-radius:999px;color:white;font-size:14px;width:160px;">
            <button type="submit" class="hero-cta">Rechercher</button>
        </form>

        <div class="results-info">
            <span><strong>' . $totalResults . '</strong> résultat' . ($totalResults > 1 ? 's' : '') . '</span>';
            if ($q || $lieu || $type_contrat || $metier_id) {
                echo '<a href="offres.php" style="font-size:12px;color:var(--orange-light); margin-left:12px;">✕ Effacer les filtres</a>';
            }
echo '  </div>

        <div class="offers-list">';
            if (empty($offres)) {
                echo '<div style="text-align:center; padding:60px 20px; color:var(--text-muted);">
                        <p style="font-size:40px; margin-bottom:16px;">🔍</p>
                        <p>Aucune offre ne correspond à vos critères.</p>
                        <a href="offres.php" style="color:var(--orange-light); margin-top:12px; display:inline-block;">Voir toutes les offres</a>
                      </div>';
            } else {
                foreach ($offres as $offre) {
                    echo '<div class="job-item">
                            <div class="job-main-info">';
                                if (!empty($offre['entreprise_logo'])) {
                                    echo '<img src="' . h($offre['entreprise_logo']) . '" alt="Logo" class="job-logo" onerror="this.style.display=\'none\'">';
                                } else {
                                    echo '<div class="job-logo" style="display:flex;align-items:center;justify-content:center;font-size:24px;background:var(--surface-2);">🏢</div>';
                                }
                    echo '      <div class="job-text">
                                    <h3>' . h($offre['titre']) . '</h3>
                                    <p class="job-sub">' . h($offre['entreprise_nom']) . ' • ' . h($offre['lieu']) . '</p>
                                    <div class="job-tags">
                                        <span class="tag-outline">' . h($offre['lieu']) . '</span>
                                        <span class="tag-outline">' . h($offre['type_contrat']) . '</span>';
                                        if ($offre['teletravail_possible']) {
                                            echo '<span class="tag-outline" style="color:#5af0b8;border-color:rgba(0,200,150,.3)">Télétravail</span>';
                                        }
                                        if ($offre['metier']) {
                                            echo '<span class="tag-outline">' . h($offre['metier']) . '</span>';
                                        }
                    echo '          </div>
                                </div>
                            </div>
                            <div class="job-actions">
                                <span class="time-ago">' . timeAgo($offre['date_publication']) . '</span>
                                <a href="offre.php?id=' . (int)$offre['id'] . '" class="view-btn">Voir l\'offre</a>
                            </div>
                          </div>';
                }
            }
echo '  </div>';

        if ($totalPages > 1) {
            echo '<div class="pagination">';
            $queryParams = array_filter([
                'q'            => $q,
                'lieu'         => $lieu,
                'type_contrat' => $type_contrat,
                'metier_id'    => $metier_id ?: null,
                'teletravail'  => $teletravail,
            ]);

            for ($i = 1; $i <= $totalPages; $i++) {
                $params_i = http_build_query(array_merge($queryParams, ['page' => $i]));
                $cls = ($i === $page) ? 'active' : '';
                echo '<a href="offres.php?' . $params_i . '" class="' . $cls . '">' . $i . '</a>';
            }
            echo '</div>';
        }

echo '    </section>
</main>
</body>
</html>';
?>