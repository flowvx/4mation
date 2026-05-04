<?php
require_once __DIR__ . '/includes/functions.php';

$pdo = getPDO();

$offreId = (int)getStr('offre_id', '0');
$offre   = null;

if ($offreId > 0) {
    $stmt = $pdo->prepare('
        SELECT o.id, o.titre, o.type_contrat, o.lieu, o.niveau_etude, o.experience,
               e.nom AS entreprise_nom
        FROM offres o
        INNER JOIN entreprises e ON o.entreprise_id = e.id
        WHERE o.id = :id AND o.is_active = 1
    ');
    $stmt->execute([':id' => $offreId]);
    $offre = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Candidature - 4Mation</title>
    <!-- Ajout du cache-busting pour forcer la mise à jour sur Railway -->
    <link rel="stylesheet" href="css/formulaire.css?v=<?= time(); ?>">
</head>
<body>

<main>
    <header>
        <h1 class="orange-text">Formulaire de Candidature</h1>
    </header>

    <?php renderFlash(); ?>

    <!-- INFOS DE L'OFFRE -->
    <section class="glass-card" id="offer-information">
        <p>Veuillez remplir le formulaire ci-dessous pour postuler. Assurez-vous de fournir des informations complètes au format PDF.</p>
        <p class="text-muted"><em>Tous les champs obligatoires sont marqués *.</em></p>

        <?php if ($offre): ?>
            <p><strong>Intitulé du poste :</strong> <span class="orange-text"><?= h($offre['titre']) ?></span></p>
            <p><strong>Entreprise :</strong> <span><?= h($offre['entreprise_nom']) ?></span></p>
            <p><strong>Type :</strong> <span><?= h($offre['type_contrat']) ?></span> - <strong>Lieu :</strong> <span><?= h($offre['lieu']) ?></span></p>
        <?php else: ?>
            <p style="color:var(--primary-orange)">
                ⚠️ Aucune offre sélectionnée. 
                <a href="offres.php" style="color:white; text-decoration:underline;">Choisir une offre</a>
            </p>
        <?php endif; ?>
    </section>

    <!-- FORMULAIRE -->
    <form action="process_candidature.php" method="POST" enctype="multipart/form-data" id="application-form">
        <input type="hidden" name="offre_id" value="<?= $offreId ?>">

        <section class="glass-card">
            <h2 class="orange-text">Informations Personnelles</h2>

            <div class="form-group">
                <label for="sex">Civilité *</label>
                <select id="sex" name="sexe" required>
                    <option value="">Sélectionnez</option>
                    <option value="homme">Monsieur</option>
                    <option value="femme">Madame</option>
                </select>
            </div>

            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" placeholder="Dupont" required>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" placeholder="Marie" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" placeholder="marie.dupont@example.com" required>
            </div>

            <div class="form-group">
                <label for="tel">Téléphone *</label>
                <input type="tel" id="tel" name="tel" placeholder="0612345678" required>
            </div>
        </section>

        <section class="glass-card">
            <h2 class="orange-text">Documents</h2>

            <div class="form-group">
                <label for="cv">CV * (PDF uniquement, max 5 Mo)</label>
                <input type="file" id="cv" name="cv" accept=".pdf" required>
            </div>

            <label>Lettre de motivation *</label>
            <div class="option-row">
                <input type="radio" id="lettre-upload" name="lettre-option" value="upload" checked>
                <label for="lettre-upload">Télécharger un PDF</label>
            </div>

            <div class="option-row">
                <input type="radio" id="lettre-redigee" name="lettre-option" value="redigee">
                <label for="lettre-redigee">Rédiger en ligne</label>
            </div>

            <input type="file" id="lettre" name="lettre" accept=".pdf" style="margin-top:10px;">
            <textarea id="lettre-redigee-area" name="lettre-redigee" placeholder="Votre lettre ici..." style="display:none; margin-top:10px;"></textarea>
        </section>

        <section class="glass-card">
            <div class="option-row">
                <input type="checkbox" id="consentement" name="consentement" required>
                <label for="consentement">J'accepte la politique de confidentialité. *</label>
            </div>
            <button type="submit">Soumettre ma candidature</button>
        </section>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const radioUpload  = document.getElementById('lettre-upload');
    const radioRedigee = document.getElementById('lettre-redigee');
    const inputFichier = document.getElementById('lettre');
    const areaTexte    = document.getElementById('lettre-redigee-area');

    function toggleLetter() {
        if (radioUpload.checked) {
            inputFichier.style.display = 'block';
            areaTexte.style.display    = 'none';
            areaTexte.required         = false;
        } else {
            inputFichier.style.display = 'none';
            areaTexte.style.display    = 'block';
            areaTexte.required         = true;
        }
    }

    radioUpload.addEventListener('change', toggleLetter);
    radioRedigee.addEventListener('change', toggleLetter);
});
</script>
</body>
</html>