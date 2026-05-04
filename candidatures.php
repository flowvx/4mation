<?php
require_once __DIR__ . '/includes/functions.php';
requireAdmin();

$pdo = getPDO();

/**
 * Handle Status Updates & Deletions
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = postStr('action');
    $id = (int)postStr('candidature_id', '0');
    $redirectId = (int)postStr('offre_id_redirect', '0');

    if ($action === 'update_statut' && $id > 0) {
        $status = postStr('statut');
        $pdo->prepare("UPDATE candidatures SET statut = ? WHERE id = ?")
            ->execute([$status, $id]);
    }

    if ($action === 'delete' && $id > 0) {
        $files = $pdo->prepare("SELECT cv_path, lettre_path FROM candidatures WHERE id = ?");
        $files->execute([$id]);
        $row = $files->fetch();

        if ($row) {
            foreach (['cv_path', 'lettre_path'] as $path) {
                if ($row[$path] && file_exists(__DIR__ . '/' . $row[$path])) {
                    unlink(__DIR__ . '/' . $row[$path]);
                }
            }
        }
        $pdo->prepare("DELETE FROM candidatures WHERE id = ?")->execute([$id]);
    }

    header("Location: candidatures.php?offre_id=$redirectId");
    exit;
}

/**
 * Data Fetching
 */
$selectedId = (int)getStr('offre_id', '0');

// Fetch admin jobs
$jobsQuery = $pdo->prepare("
    SELECT o.id, o.titre, e.nom as entreprise_nom, COUNT(c.id) as total
    FROM offres o
    JOIN entreprises e ON o.entreprise_id = e.id
    LEFT JOIN candidatures c ON c.offre_id = o.id
    WHERE o.admin_id = ?
    GROUP BY o.id ORDER BY o.date_publication DESC
");
$jobsQuery->execute([$_SESSION['admin_id']]);
$myJobs = $jobsQuery->fetchAll();

if ($selectedId === 0 && !empty($myJobs)) {
    $selectedId = (int)$myJobs[0]['id'];
}

$candidatures = [];
$currentJob = null;

if ($selectedId > 0) {
    $jobInfo = $pdo->prepare("SELECT o.titre, e.nom FROM offres o JOIN entreprises e ON o.entreprise_id = e.id WHERE o.id = ?");
    $jobInfo->execute([$selectedId]);
    $currentJob = $jobInfo->fetch();

    $candQuery = $pdo->prepare("SELECT * FROM candidatures WHERE offre_id = ? ORDER BY score_ats DESC, date_soumission ASC");
    $candQuery->execute([$selectedId]);
    $candidatures = $candQuery->fetchAll();
}

$statusMap = [
    'nouveau'   => ['label' => 'Nouveau',   'color' => '#999'],
    'evalue'    => ['label' => 'Évalué',    'color' => '#FF9800'],
    'shortlist' => ['label' => 'Shortlist', 'color' => '#43A047'],
    'rejete'    => ['label' => 'Rejeté',    'color' => '#e53935'],
];

function getScoreColor($score) {
    if ($score >= 75) return '#43A047';
    if ($score >= 50) return '#FF9800';
    return '#e53935';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Candidatures - 4Mation</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<header class="admin-header">
    <h2>Dashboard Admin <span class="user-info"><?= h($_SESSION['admin_prenom'] . ' ' . $_SESSION['admin_nom']) ?></span></h2>
    <nav class="nav-menu">
        <li><a href="publier.php">+ Offre</a></li>
        <li><a href="index.php">Site</a></li>
        <li><a href="logout.php" class="text-dim">Déconnexion</a></li>
    </nav>
</header>

<main class="dashboard-main">
    <?php renderFlash(); ?>

    <div class="offer-selector">
        <label>Offre active :</label>
        <form method="GET">
            <select name="offre_id" onchange="this.form.submit()">
                <option value="0">Sélectionner une offre</option>
                <?php foreach ($myJobs as $job): ?>
                    <option value="<?= $job['id'] ?>" <?= $selectedId === (int)$job['id'] ? 'selected' : '' ?>>
                        <?= h($job['titre']) ?> (<?= $job['total'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php if ($selectedId > 0): ?>
            <a href="offre.php?id=<?= $selectedId ?>" class="cta-button-sm">Aperçu public</a>
        <?php endif; ?>
    </div>

    <?php if ($currentJob): ?>
        <div class="offer-header">
            <h3>Candidats : <span class="orange-text"><?= h($currentJob['titre']) ?></span></h3>
            <span class="tag"><?= count($candidatures) ?> inscrit(s)</span>
        </div>
    <?php endif; ?>

    <?php if (empty($candidatures) && $selectedId > 0): ?>
        <div class="empty-state">
            <p>Aucune candidature pour le moment.</p>
        </div>
    <?php elseif (!empty($candidatures)): ?>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Candidat</th>
                        <th>Score ATS</th>
                        <th>IA</th>
                        <th>Fichiers</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidatures as $i => $c): 
                        $rank = $i + 1;
                        $rankClass = $rank <= 3 ? ['gold', 'silver', 'bronze'][$rank-1] : '';
                        $sColor = getScoreColor((float)$c['score_ats']);
                        $statusCfg = $statusMap[$c['statut']] ?? $statusMap['nouveau'];
                        
                        $rowClass = '';
                        if($c['statut'] === 'shortlist') $rowClass = 'row-shortlist';
                        if($c['statut'] === 'rejete') $rowClass = 'row-rejete';
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td><span class="rang-badge <?= $rankClass ?>">#<?= $rank ?></span></td>
                        <td>
                            <strong><?= h(strtoupper($c['nom'])) ?> <?= h($c['prenom']) ?></strong><br>
                            <small class="text-dim"><?= h($c['email']) ?></small>
                        </td>
                        <td>
                            <div class="score-container">
                                <span class="score-badge" style="background:<?= $sColor ?>"><?= number_format($c['score_ats'], 1) ?>%</span>
                                <div class="score-bar"><div class="score-bar-fill" style="width:<?= $c['score_ats'] ?>%; background:<?= $sColor ?>"></div></div>
                            </div>
                        </td>
                        <td>
                            <?php if ($c['analyse_ia']): ?>
                                <button class="analyse-toggle" onclick="toggleAnalyse(<?= $c['id'] ?>)">Détails</button>
                                <div class="analyse-box" id="analyse-<?= $c['id'] ?>"><?= h($c['analyse_ia']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= h($c['cv_path']) ?>" target="_blank" class="file-link">CV</a>
                            <?php if ($c['lettre_option'] === 'upload'): ?>
                                | <a href="<?= h($c['lettre_path']) ?>" target="_blank" class="file-link">LM</a>
                            <?php elseif ($c['lettre_redigee']): ?>
                                | <button class="analyse-toggle" onclick="toggleAnalyse('lm-<?= $c['id'] ?>')">LM</button>
                                <div class="analyse-box" id="analyse-lm-<?= $c['id'] ?>"><?= nl2br(h($c['lettre_redigee'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="statut-form">
                                <input type="hidden" name="action" value="update_statut">
                                <input type="hidden" name="candidature_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="offre_id_redirect" value="<?= $selectedId ?>">
                                <select name="statut" onchange="this.form.submit()" style="color:<?= $statusCfg['color'] ?>">
                                    <?php foreach ($statusMap as $key => $cfg): ?>
                                        <option value="<?= $key ?>" <?= $c['statut'] === $key ? 'selected' : '' ?>><?= $cfg['label'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td><small class="text-dim"><?= date('d/m/y H:i', strtotime($c['date_soumission'])) ?></small></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Supprimer ?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="candidature_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="offre_id_redirect" value="<?= $selectedId ?>">
                                <button type="submit" class="cta-button-sm danger-btn">🗑</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<script>
function toggleAnalyse(id) {
    const box = document.getElementById('analyse-' + id);
    if (box) box.style.display = box.style.display === 'block' ? 'none' : 'block';
}
</script>
</body>
</html>