<?php

require_once __DIR__ . '/includes/functions.php';

$pdo = getPDO();

$offreId = (int)getStr('offre_id', '0');
$offre = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $sexe = trim($_POST['sexe'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $cv = $_FILES['cv'] ?? null;
    $lettreOption = $_POST['lettre-option'] ?? 'upload';
    $lettre = $lettreOption === 'upload' ? ($_FILES['lettre'] ?? null) : trim($_POST['lettre-redigee'] ?? '');

    // Validation des champs obligatoires
    if (!$sexe) $errors[] = 'La civilité est obligatoire.';
    if (!$nom) $errors[] = 'Le nom est obligatoire.';
    if (!$prenom) $errors[] = 'Le prénom est obligatoire.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'L\'email est invalide.';
    if (!$tel || !preg_match('/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.\-]*\d{2}){4}$/',$tel)) $errors[] = 'Le numéro de téléphone est invalide.';

    // Validation des fichiers
    if (!$cv || $cv['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Le CV est obligatoire.';
    } elseif ($cv['type'] !== 'application/pdf') {
        $errors[] = 'Le CV doit être au format PDF.';
    }

    if ($lettreOption === 'upload') {
        if (!$lettre || $lettre['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'La lettre de motivation est obligatoire.';
        } elseif ($lettre['type'] !== 'application/pdf') {
            $errors[] = 'La lettre de motivation doit être au format PDF.';
        }
    } elseif (strlen($lettre) < 50) {
        $errors[] = 'La lettre de motivation rédigée doit contenir au moins 50 caractères.';
    }

    // Si aucune erreur, traitement des données
    if (empty($errors)) {
        // Sauvegarde des fichiers
        $cvPath = 'uploads/cv/' . uniqid() . '.pdf';
        move_uploaded_file($cv['tmp_name'], $cvPath);

        $lettrePath = null;
        if ($lettreOption === 'upload') {
            $lettrePath = 'uploads/lettres/' . uniqid() . '.pdf';
            move_uploaded_file($lettre['tmp_name'], $lettrePath);
        }

        // Insertion dans la base de données
        $stmt = $pdo->prepare('INSERT INTO candidatures (offre_id, sexe, nom, prenom, email, tel, cv, lettre, created_at) VALUES (:offre_id, :sexe, :nom, :prenom, :email, :tel, :cv, :lettre, NOW())');
        $stmt->execute([
            ':offre_id' => $offreId,
            ':sexe' => $sexe,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':tel' => $tel,
            ':cv' => $cvPath,
            ':lettre' => $lettreOption === 'upload' ? $lettrePath : $lettre,
        ]);

        // Redirection après succès
        header('Location: success.php');
        exit;
    }
}

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
    <link rel="stylesheet" href="css/formulaire.css?v=<?= time(); ?>">
</head>
<body>
<header>
    <h1 class="orange-text">Formulaire de Candidature</h1>
</header>

<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= h($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

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
    const radioUpload = document.getElementById('lettre-upload');
    const radioRedigee = document.getElementById('lettre-redigee');
    const inputFichier = document.getElementById('lettre');
    const areaTexte = document.getElementById('lettre-redigee-area');

    function toggleLetter() {
        if (radioUpload.checked) {
            inputFichier.style.display = 'block';
            inputFichier.required = false; // PDF lettre est optionnel
            areaTexte.style.display = 'none';
            areaTexte.required = false;
        } else {
            inputFichier.style.display = 'none';
            inputFichier.required = false;
            areaTexte.style.display = 'block';
            areaTexte.required = true;
        }
    }

    radioUpload.addEventListener('change', toggleLetter);
    radioRedigee.addEventListener('change', toggleLetter);
    toggleLetter();

    // Validation côté client (complément de la validation PHP)
    document.getElementById('application-form').addEventListener('submit', function (e) {
        const emailVal = document.getElementById('email').value.trim();
        const telVal = document.getElementById('tel').value.trim();
        const emailRgx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const telRgx = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.\-]*\d{2}){4}$/;

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