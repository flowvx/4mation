<?php
 
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('UPLOAD_CV_DIR',   __DIR__ . '/../uploads/cv/');
define('UPLOAD_LM_DIR',   __DIR__ . '/../uploads/lettres/');

require_once __DIR__ . '/db.php';

// --- GESTION SESSION ---
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function loginAdmin($admin) {
    startSecureSession();
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_nom'] = $admin['nom'];
    $_SESSION['admin_prenom'] = $admin['prenom'];
}

function requireAdmin() {
    startSecureSession();
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

// --- UTILITAIRES ---
function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }

function postStr($key) { return isset($_POST[$key]) ? trim($_POST[$key]) : ''; }

function getStr($key) { return isset($_GET[$key]) ? trim($_GET[$key]) : ''; }

function normalizeText($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $search  = ['à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ò','ó','ô','õ','ö','ù','ú','û','ü','ý'];
    $replace = ['a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','y'];
    $text = str_replace($search, $replace, $text);
    return preg_replace('/[^a-z0-9]/', '', $text);
}

// --- FONCTION D'UPLOAD PDF ---
function uploadPdf(array $file, string $targetDir): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erreur d\'upload (Code: ' . $file['error'] . ')');
    }

    if ($file['size'] > UPLOAD_MAX_SIZE) {
        throw new RuntimeException('Le fichier est trop lourd (Max 5 Mo).');
    }

    // Vérification de l'extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        throw new RuntimeException('Seuls les fichiers PDF sont acceptés.');
    }

    // Création du dossier si inexistant
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Nom unique pour éviter d'écraser des fichiers
    $newName = bin2hex(random_bytes(16)) . '.pdf';
    $destPath = rtrim($targetDir, '/') . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException('Échec de la sauvegarde du fichier.');
    }

    // On retourne le chemin relatif pour la BD (ex: uploads/cv/abc.pdf)
    return 'uploads/' . basename($targetDir) . '/' . $newName;
}

// --- MOTEUR ATS ---
function calculerScoreAts(int $offreId, string $lettre, string $cvFilename): array {
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT competences_cles FROM offres WHERE id = ?');
    $stmt->execute([$offreId]);
    $offre = $stmt->fetch();

    if (!$offre || empty($offre['competences_cles'])) {
        return ['score' => 0, 'analyse' => 'Aucune compétence clé définie.'];
    }

    $keywords = explode(',', $offre['competences_cles']);
    $found = [];
    $corpus = normalizeText($lettre . ' ' . $cvFilename);

    foreach ($keywords as $kw) {
        $kwClean = normalizeText(trim($kw));
        if (!empty($kwClean) && str_contains($corpus, $kwClean)) {
            $found[] = trim($kw);
        }
    }

    $totalCount = count($keywords);
    $foundCount = count($found);
    $score = ($totalCount > 0) ? ($foundCount / $totalCount) * 100 : 0;

    return [
        'score' => round($score, 2),
        'analyse' => "Mots-clés trouvés : " . implode(', ', $found) . " ($foundCount/$totalCount)"
    ];
}

// --- REDIRECTION & FLASH ---
function redirectWithMessage($url, $type, $message) {
    startSecureSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    header('Location: ' . $url);
    exit;
}

function renderFlash() {
    startSecureSession();
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $class = ($f['type'] === 'success') ? 'flash-success' : 'flash-error';
        echo '<div class="flash-message '.$class.'">'.h($f['message']).'</div>';
        unset($_SESSION['flash']);
    }
}

function logoutAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    } 
    $_SESSION = array();
    
    // On détruit la session
    session_destroy();
     
    header('Location: login.php');
    exit;
}