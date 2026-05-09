<?php
 

require_once __DIR__ . '/includes/functions.php';

$pdo = getPDO();

$offreId = (int)getStr('offre_id', '0');
$offre   = null;

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
    <link rel="stylesheet" href="css/formulaire.css">
</head>
<body>
<header>
    <h1>Formulaire de Candidature</h1>
</header>
<main>

    <?php renderFlash(); ?>

    <!-- INFOS DE L'OFFRE (pré-remplies depuis la BDD) -->
    <section id="offer-information">
        <p>Veuillez remplir le formulaire ci-dessous pour postuler à une offre de stage ou d'alternance.
           Assurez-vous de fournir des informations complètes et de télécharger les documents requis (CV et lettre
           de motivation) au format PDF.</p>
        <p>Tous les champs obligatoires sont marqués *.</p>

        <?php if ($offre): ?>
            <p><strong>Intitulé du poste :</strong>
               <span id="offer-title"><?= h($offre['titre']) ?></span></p>
            <p><strong>Entreprise :</strong>
               <span><?= h($offre['entreprise_nom']) ?></span></p>
            <p><strong>Type de poste :</strong>
               <span id="offer-type"><?= h($offre['type_contrat']) ?></span></p>
            <p><strong>Lieu :</strong>
               <span id="offer-location"><?= h($offre['lieu']) ?></span></p>
            <?php if ($offre['niveau_etude']): ?>
            <p><strong>Niveau requis :</strong>
               <span id="offer-level"><?= h($offre['niveau_etude']) ?></span></p>
            <?php endif; ?>
        <?php else: ?>
            <p style="color:var(--orange)">
                ⚠️ Aucune offre sélectionnée.
                <a href="offres.php" style="color:var(--orange-light);text-decoration:underline;">
                    Choisir une offre
                </a>
            </p>
        <?php endif; ?>
    </section>

    <!-- FORMULAIRE -->
    <form action="process_candidature.php" method="POST" enctype="multipart/form-data" id="application-form">

        <!-- Champ caché pour l'ID de l'offre -->
        <input type="hidden" name="offre_id" value="<?= $offreId ?>">

        <section>
            <h2>Informations Personnelles</h2>

            <label for="sex">Civilité *</label>
            <select id="sex" name="sexe" required>
                <option value="">Sélectionnez votre civilité</option>
                <option value="homme">Monsieur</option>
                <option value="femme">Madame</option>
            </select>

            <label for="nom">Nom *</label>
            <input type="text" id="nom" name="nom" placeholder="Dupont" required
                   autocomplete="family-name">

            <label for="prenom">Prénom *</label>
            <input type="text" id="prenom" name="prenom" placeholder="Marie" required
                   autocomplete="given-name">

            <label for="email">Email *</label>
            <input type="email" id="email" name="email" placeholder="marie.dupont@example.com" required
                   autocomplete="email">

            <label for="tel">Téléphone *</label>
            <input type="tel" id="tel" name="tel" placeholder="0612345678" required
                   autocomplete="tel">
        </section>

        <section>
            <h2>Documents</h2>

            <label for="cv">CV * (PDF uniquement, max 5 Mo)</label>
            <input type="file" id="cv" name="cv" accept=".pdf" required>

            <label>Lettre de motivation *</label>

            <div class="row-flex" style="margin-bottom:8px;">
                <input type="radio" id="lettre-upload" name="lettre-option" value="upload" checked>
                <label for="lettre-upload">Télécharger un fichier PDF</label>
            </div>

            <div class="row-flex" style="margin-bottom:16px;">
                <input type="radio" id="lettre-redigee" name="lettre-option" value="redigee">
                <label for="lettre-redigee">Rédiger en ligne</label>
            </div>

            <!-- Upload fichier (affiché par défaut) -->
            <input type="file" id="lettre" name="lettre" accept=".pdf"
                   style="display:block">

            <!-- Zone de texte (cachée par défaut) -->
            <textarea id="lettre-redigee-area" name="lettre-redigee"
                      placeholder="Rédigez votre lettre de motivation ici (minimum 50 caractères)..."
                      style="display:none"></textarea>
        </section>

        <section>
            <p>En soumettant ce formulaire, vous acceptez que vos données personnelles soient utilisées
               conformément à notre <a href="MentionLegal.html">Politique de Confidentialité</a>.</p>
            <div class="row-flex">
                <input type="checkbox" id="consentement" name="consentement" required>
                <label for="consentement">
                    J'accepte les conditions d'utilisation et la politique de confidentialité. *
                </label>
            </div>
            <button type="submit">Soumettre ma candidature</button>
        </section>
    </form>
</main>

<script>
// Gestion dynamique lettre de motivation
document.addEventListener('DOMContentLoaded', function () {
    const radioUpload  = document.getElementById('lettre-upload');
    const radioRedigee = document.getElementById('lettre-redigee');
    const inputFichier = document.getElementById('lettre');
    const areaTexte    = document.getElementById('lettre-redigee-area');

    function toggleLetter() {
        if (radioUpload.checked) {
            inputFichier.style.display = 'block';
            inputFichier.required      = false; // PDF lettre est optionnel
            areaTexte.style.display    = 'none';
            areaTexte.required         = false;
        } else {
            inputFichier.style.display = 'none';
            inputFichier.required      = false;
            areaTexte.style.display    = 'block';
            areaTexte.required         = true;
        }
    }

    radioUpload.addEventListener('change', toggleLetter);
    radioRedigee.addEventListener('change', toggleLetter);
    toggleLetter();

    // Validation côté client (complément de la validation PHP)
    document.getElementById('application-form').addEventListener('submit', function (e) {
        const emailVal = document.getElementById('email').value.trim();
        const telVal   = document.getElementById('tel').value.trim();
        const emailRgx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const telRgx   = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.\-]*\d{2}){4}$/;

        if (!emailRgx.test(emailVal)) {
            e.preventDefault();
            alert('L\'adresse email n\'est pas valide.');
            return;
        }
        if (!telRgx.test(telVal)) {
            e.preventDefault();
            alert('Le numéro de téléphone n\'est pas valide (ex: 0612345678).');
            return;
        }
    });
});
</script>
</body>
</html>
;;;;;;;;;;;;;;;;;;;;;;;;;;/* Thème et Variables */
:root {
  --primary-orange: #ff9800;
  --primary-hover: #e68900;
  --bg-dark: #0f0f0f;
  --glass-bg: rgba(255, 255, 255, 0.03);
  --glass-border: rgba(255, 255, 255, 0.1);
  --text-main: #f5f5f5;
  --text-muted: #a0a0a0;
  --radius: 12px;
  --transition: all 0.3s ease;
}

/* Base Layout */
body {
  background-color: var(--bg-dark);
  color: var(--text-main);
  font-family:
    "Inter",
    -apple-system,
    sans-serif;
  line-height: 1.6;
  margin: 0;
}

main {
  max-width: 800px;
  margin: 40px auto;
  padding: 0 20px;
}

/* Glassmorphism Card */
.glass-card {
  background: var(--glass-bg);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius);
  padding: 30px;
  margin-bottom: 24px;
}

/* Typography */
h1,
h2 {
  letter-spacing: -0.02em;
}

.orange-text {
  color: var(--primary-orange);
}

/* Form Elements */
.form-group {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
}

label {
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 8px;
  color: var(--text-muted);
}

input[type="text"],
input[type="email"],
input[type="tel"],
select,
textarea {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--glass-border);
  border-radius: 8px;
  color: white;
  padding: 12px 16px;
  font-size: 1rem;
  outline: none;
  transition: var(--transition);
}

input:focus,
select:focus,
textarea:focus {
  border-color: var(--primary-orange);
  background: rgba(255, 255, 255, 0.08);
}

/* Checkbox & Radio Customization */
.option-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
  cursor: pointer;
}

/* Submit Button */
button[type="submit"] {
  background: var(--primary-orange);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 14px 28px;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  width: 100%;
  margin-top: 20px;
}

button:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

/* Flash Messages */
.flash-message {
  padding: 14px;
  border-radius: 8px;
  margin-bottom: 24px;
  font-size: 0.9rem;
}

.flash-error {
  background: rgba(229, 57, 53, 0.15);
  border: 1px solid #e53935;
  color: #ef9a9a;
}
.flash-success {
  background: rgba(67, 160, 71, 0.15);
  border: 1px solid #43a047;
  color: #a5d6a7;
}