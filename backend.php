<?php
// ===== DATABASE CONNECTION =====
$serveur         = 'localhost';
$nom_utilisateur = 'root';
$mot_de_passe    = '';
$base_de_donnees = 'projet';

try {
    $pdo = new PDO(
        "mysql:host=$serveur;dbname=$base_de_donnees;charset=utf8",
        $nom_utilisateur,
        $mot_de_passe,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Erreur BD: " . $e->getMessage()]));
}

// ===== AJAX ENDPOINTS =====

// 1 Fetch all
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $stmt = $pdo->query("SELECT * FROM personnes");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// 2Search
if (isset($_GET['action']) && $_GET['action'] === 'search' && isset($_GET['pseudo'])) {
    $pseudo = $_GET['pseudo'];
    $stmt = $pdo->prepare("SELECT * FROM personnes WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    $pseudo = $_POST['pseudo'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $nom    = $_POST['nom'] ?? '';
    $age    = $_POST['age'] ?? '';
    $ville  = $_POST['ville'] ?? '';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO personnes (pseudo, prenom, nom, age, ville)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$pseudo, $prenom, $nom, $age, $ville]);
        echo json_encode(['status' => 'success', 'message' => 'created']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $pseudo = $_POST['pseudo'] ?? '';
    $stmt = $pdo->prepare("DELETE FROM personnes WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    echo json_encode(['status' => 'success', 'message' => 'deleted']);
    exit;
}

// Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    $pseudo = $_POST['pseudo'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $nom    = $_POST['nom'] ?? '';
    $age    = $_POST['age'] ?? '';
    $ville  = $_POST['ville'] ?? '';

    $stmt = $pdo->prepare("
        UPDATE personnes
        SET prenom = ?, nom = ?, age = ?, ville = ?
        WHERE pseudo = ?
    ");
    $stmt->execute([$prenom, $nom, $age, $ville, $pseudo]);
    echo json_encode(['status' => 'success', 'message' => 'updated']);
    exit;
}
?>
