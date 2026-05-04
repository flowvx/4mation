<?php
 

require_once __DIR__ . '/includes/functions.php';

$pdo = getPDO();
 
$offreId = (int)getStr('id', '0');

if ($offreId <= 0) {
    header('Location: offres.php');
    exit;
}
 
$stmt = $pdo->prepare('
    SELECT
        o.*,
        e.nom        AS entreprise_nom,
        e.logo_url   AS entreprise_logo,
        e.site_web   AS entreprise_site,
        e.description AS entreprise_desc,
        m.nom_metier AS metier,
        a.prenom     AS admin_prenom,
        a.nom        AS admin_nom
    FROM offres o
    INNER JOIN entreprises e ON o.entreprise_id = e.id
    LEFT  JOIN metiers     m ON o.metier_id     = m.id
    LEFT  JOIN admins      a ON o.admin_id      = a.id
    WHERE o.id = :id AND o.is_active = 1
');
$stmt->execute([':id' => $offreId]);
$offre = $stmt->fetch();

 
if (!$offre) {
    http_response_code(404);
    echo '<h2 style="font-family:sans-serif;text-align:center;margin-top:80px;color:#fff">
            Offre introuvable ou expirée.
          </h2>
          <p style="text-align:center"><a href="offres.php" style="color:#FF4D00">← Retour aux offres</a></p>';
    exit;
}
 
startSecureSession();
$nbCandidatures = 0;
if (!empty($_SESSION['admin_id'])) {
    $cStmt = $pdo->prepare('SELECT COUNT(*) FROM candidatures WHERE offre_id = :id');
    $cStmt->execute([':id' => $offreId]);
    $nbCandidatures = (int)$cStmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($offre['titre']) ?> - 4Mation</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/offre.css">
</head>
<body>

<header>
    <div class="header-container">
        <a href="index.php"><img src="assets/logo.png" alt="4Mation" class="logo"></a>
        <nav class="nav-menu">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="offres.php">Toutes les offres</a></li>
        </nav>
        <a href="formulaire.php?offre_id=<?= $offreId ?>" class="cta-button">Postuler directement</a>
    </div>
</header>

<main class="offer-detail-container">

 
    <section class="offer-header-block">
        <div class="company-brand">
            <?php if (!empty($offre['entreprise_logo'])): ?>
                <img src="<?= h($offre['entreprise_logo']) ?>"
                     alt="Logo <?= h($offre['entreprise_nom']) ?>"
                     class="offer-logo"
                      >
            <?php endif; ?>

            <div class="title-meta">
                <span class="tag"><?= h($offre['type_contrat']) ?></span>
                <?php if ($offre['metier']): ?>
                    <span class="tag" style="background:rgba(100,100,255,.15);color:#aab4ff;border-color:rgba(100,100,255,.3)">
                        <?= h($offre['metier']) ?>
                    </span>
                <?php endif; ?>
                <h1><?= h($offre['titre']) ?></h1>
                <p class="company-name">
                    <?php if (!empty($offre['entreprise_site'])): ?>
                        <a href="<?= h($offre['entreprise_site']) ?>" target="_blank" rel="noopener"
                           style="color:inherit;text-decoration:underline;">
                            <?= h($offre['entreprise_nom']) ?>
                        </a>
                    <?php else: ?>
                        <?= h($offre['entreprise_nom']) ?>
                    <?php endif; ?>
                    • <?= h($offre['lieu']) ?>
                </p>
            </div>
        </div>

        <div class="post-date">
            Publiée le <?= h(date('d/m/Y', strtotime($offre['date_publication']))) ?>
            <?php if ($nbCandidatures > 0): ?>
                <br><span style="color:var(--orange);font-weight:700"><?= $nbCandidatures ?> candidature(s)</span>
            <?php endif; ?>
        </div>
    </section>

    <div class="offer-content-layout">

 
        <article class="description-column">

            <?php if (!empty($offre['entreprise_desc'])): ?>
            <div class="content-section">
                <h2>À propos de l'entreprise</h2>
                <p><?= nl2br(h($offre['entreprise_desc'])) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($offre['missions'])): ?>
            <div class="content-section">
                <h2>Vos missions</h2>
                <?= nl2br(h($offre['missions'])) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($offre['profil_recherche'])): ?>
            <div class="content-section">
                <h2>Votre profil</h2>
                <?= nl2br(h($offre['profil_recherche'])) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($offre['competences_cles'])): ?>
            <div class="content-section">
                <h2>Compétences clés</h2>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
                    <?php foreach (explode(',', $offre['competences_cles']) as $competence): ?>
                        <?php $c = trim($competence); if ($c): ?>
                        <span style="background:var(--surface-3);border:1px solid #333;padding:4px 12px;border-radius:999px;font-size:12px;color:var(--text-muted)">
                            <?= h($c) ?>
                        </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="cta-footer">
                <a href="formulaire.php?offre_id=<?= $offreId ?>" class="hero-cta">
                    Candidater à cette offre
                </a>
            </div>
        </article>
 
        <aside class="info-sidebar">
            <div class="info-card">
                <h3>Infos Clés</h3>

                <div class="info-item">
                    <span class="label">Type de contrat</span>
                    <span class="value"><?= h($offre['type_contrat']) ?></span>
                </div>

                <?php if (!empty($offre['temps_travail'])): ?>
                <div class="info-item">
                    <span class="label">Temps de travail</span>
                    <span class="value"><?= h($offre['temps_travail']) ?></span>
                </div>
                <?php endif; ?>

                <div class="info-item">
                    <span class="label">Télétravail</span>
                    <span class="value">
                        <?php if ($offre['teletravail_possible']): ?>
                            ✅ Oui
                            <?php if ($offre['teletravail_frequence']): ?>
                                (<?= h($offre['teletravail_frequence']) ?>)
                            <?php endif; ?>
                        <?php else: ?>
                            ❌ Non
                        <?php endif; ?>
                    </span>
                </div>

                <?php if (!empty($offre['langues'])): ?>
                <div class="info-item">
                    <span class="label">Langues</span>
                    <span class="value"><?= h($offre['langues']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($offre['experience'])): ?>
                <div class="info-item">
                    <span class="label">Expérience</span>
                    <span class="value"><?= h($offre['experience']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($offre['niveau_etude'])): ?>
                <div class="info-item">
                    <span class="label">Niveau requis</span>
                    <span class="value"><?= h($offre['niveau_etude']) ?></span>
                </div>
                <?php endif; ?>

                <div class="info-item">
                    <span class="label">Localisation</span>
                    <span class="value"><?= h($offre['lieu']) ?></span>
                </div>
            </div>

            <div class="share-box">
                <p>Cette offre intéresse un ami ?</p>
                <button class="share-btn" onclick="
                    navigator.clipboard.writeText(window.location.href)
                        .then(() => this.textContent = '✅ Lien copié !')
                        .catch(() => this.textContent = 'Copie impossible');
                    setTimeout(() => this.textContent = 'Partager l\'offre', 2000);
                ">Partager l'offre</button>
            </div>
        </aside>

    </div>
</main>

<footer>
    <div class="footer-links">
        <a href="MentionLegal.html">Mentions Légales</a>
        <a href="#">Contact</a>
        <a href="#">© 2026 4Mation Centre de Formation</a>
    </div>
</footer>

</body>
</html>
