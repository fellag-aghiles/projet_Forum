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
    die("Erreur de connexion BD : " . $e->getMessage());
}

// ===== AJAX ENDPOINTS =====
// 1) Fetch all records
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $stmt = $pdo->query("SELECT * FROM personnes");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// 2) Search by pseudo
if (isset($_GET['action']) && $_GET['action'] === 'search' && isset($_GET['pseudo'])) {
    $pseudo = $_GET['pseudo'];
    $stmt = $pdo->prepare("SELECT * FROM personnes WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// 3) Create a new record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    $pseudo = $_POST['pseudo']  ?? '';
    $prenom = $_POST['prenom']  ?? '';
    $nom    = $_POST['nom']     ?? '';
    $age    = $_POST['age']     ?? '';
    $ville  = $_POST['ville']   ?? '';

    $stmt = $pdo->prepare("
        INSERT INTO personnes (pseudo, prenom, nom, age, ville)
        VALUES (?, ?, ?, ?, ?)
    ");
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

// 4) Delete a record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $pseudo = $_POST['pseudo'] ?? '';
    $stmt = $pdo->prepare("DELETE FROM personnes WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    echo "deleted";
    exit;
}

// 5) Update a record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    $pseudo = $_POST['pseudo']  ?? '';
    $prenom = $_POST['prenom']  ?? '';
    $nom    = $_POST['nom']     ?? '';
    $age    = $_POST['age']     ?? '';
    $ville  = $_POST['ville']   ?? '';

    $stmt = $pdo->prepare("
        UPDATE personnes
           SET prenom = ?, nom = ?, age = ?, ville = ?
         WHERE pseudo = ?
    ");
    $stmt->execute([$prenom, $nom, $age, $ville, $pseudo]);
    echo "updated";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des personnes</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem;
            background-color: #f8f9fa;
            color: var(--dark);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light);
        }

        .form-group {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        label {
            width: 120px;
            font-weight: 500;
            color: var(--primary);
        }

        input[type="text"],
        input[type="number"] {
            flex: 1;
            padding: 0.5rem;
            border: 2px solid var(--light);
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--secondary);
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s ease, opacity 0.2s ease;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            border: 2px solid black;
        }

        .btn-primary {
            background-color: rgb(220, 230, 255);
            color: rgb(27, 27, 68);
            border : 1px solid rgb(27, 27, 68);
            border-radius : 30px;

        }

        .btn-success {
            background-color: rgb(226, 255, 206);
            color: rgb(18, 56, 18);
        }


        .btn-danger {
            background-color: rgb(255, 206, 206);
            color: rgb(68, 27, 27);
        }

        .btn-warning {
            background-color: rgb(251, 206, 255);
            color: rgb(68, 27, 68);
        }

        .table-container {
            overflow-x: auto;
            margin-top: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--light);
        }

        th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 2rem;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .notification.show {
            opacity: 1;
            transform: translateX(0);
        }

        .notification.success {
            background-color: var(--success);
        }

        .notification.error {
            background-color: var(--danger);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .hidden {
            display: none;
        }

        .background-video {
      position: fixed;
      top: 0;
      left: 0;
      min-width: 100%;
      min-height: 100%;
      object-fit: cover;
      z-index: -1;
    }
    </style>
</head>
<body>

<header><h1>Gestion des personnes</h1></header>

<video autoplay muted loop playsinline class="background-video">
    <source src="back1.mp4" type="video/mp4">
    Ton navigateur ne supporte pas la vidéo HTML5.
  </video>
        <div class="container">
            <div class="card">
            <div class="card-title">Rechercher par pseudo</div>
            <div class="form-group">
                <label>Pseudo:</label>
                <input id="search-pseudo" type="text">
                <button class="btn btn-primary" onclick="searchPerson()">Rechercher</button>
        
            </div>
            <div class="table-container" id="tableContainer2"></div>
        </div>


        <div class="card">
            <div class="card-title">Ajouter une personne</div>

            <div class="hidden" id = "cardAjout">
    

            <div class="form-group">
                <label>Pseudo:</label>
                <input id="new-pseudo" type="text">
            </div>
            <div class="form-group">
                <label>Prénom:</label>
                <input id="new-prenom" type="text">
            </div>
            <div class="form-group">
                <label>Nom:</label>
                <input id="new-nom" type="text">
            </div>
            <div class="form-group">
                <label>Âge:</label>
                <input id="new-age" type="number">
            </div>
            <div class="form-group">
                <label>Ville:</label>
                <input id="new-ville" type="text">
            </div>
            <button class="btn btn-success" id="ajout">Ajouter</button>
            <button class="btn" id="annuler">Annuler</button>
            </div>
            
            <button class="btn btn-primary" id = "showajoute">Afficher</button>
        </div>

        <div class="card">
        <div class="card-title">Afficher toutes les personnes</div>
            <button class="btn btn-primary" id ="affichecacher">Afficher</button>

        <div class="table-container" id="tableContainer"></div>
        </div>
        
    </div>

    <div id="notification" class="notification"></div>

    <script>

const showajoute = document.getElementById('showajoute');
        const card = document.getElementById('cardAjout');
        const ajout = document.getElementById('ajout');
        const affichecacher = document.getElementById("affichecacher");
        let afficher = true;
        const table = document.getElementById("tableContainer");
        const annuler  = document.getElementById("annuler");

        annuler.addEventListener('click', function() {
            card.classList.add('hidden');
            showajoute.classList.remove('hidden');
            clearInputValue();
        });

        affichecacher.addEventListener('click', function() {
            if ( afficher){
                this.innerText = "Cacher"
                table.style.display = "block";
                afficher = !afficher;
            fetchPersons();
        }
            else{
                
                afficher = !afficher;
                this.innerText = "Afficher"
                table.style.display = "none";

            }
        });

        ajout.addEventListener('click', function() {
            addPerson();
            card.classList.add('hidden');
            showajoute.classList.remove('hidden');
        });
        // Show the add person form when the button is clicked

        showajoute.addEventListener('click', function() {

           card.classList.remove('hidden');
           this.classList.add('hidden');
        });
        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.className = `notification ${type} show`;
            notification.textContent = message;
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Modified render function with enhanced table styling
        function renderTable(data, all) {
            if (data.length === 0) {


                showNotification('Aucun résultat trouvé', 'error');
                document.getElementById('tableContainer').innerHTML = '';
                return;
            }

            let html = '<table>';
            html += `
                <tr>
                    <th>Pseudo</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Âge</th>
                    <th>Ville</th>
                    <th>Actions</th>
                </tr>`;
            
            data.forEach(p => {
                html += `
                <tr>
                    <td>${p.pseudo}</td>
                    <td><input type="text" value="${p.prenom}" id="prenom-${p.pseudo}"></td>
                    <td><input type="text" value="${p.nom}" id="nom-${p.pseudo}"></td>
                    <td><input type="number" value="${p.age}" id="age-${p.pseudo}"></td>
                    <td><input type="text" value="${p.ville}" id="ville-${p.pseudo}"></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-warning" onclick="updatePerson('${p.pseudo}')">Modifier</button>
                            <button class="btn btn-danger" onclick="deletePerson('${p.pseudo}')">Supprimer</button>
                        </div>
                    </td>
                </tr>`;
            });
            html += '</table>';
             if (all) document.getElementById('tableContainer').innerHTML = html;
             else document.getElementById('tableContainer2').innerHTML = html
        }

        function fetchPersons() {
            fetch('?action=fetch')
                .then(r => r.json())
                .then(data => {
                    renderTable(data, true);
                    showNotification('Liste actualisée avec succès');
                })
                .catch(e => showNotification('Erreur de chargement', 'error'));
        }

        function searchPerson() {
            const pseudo = encodeURIComponent(document.getElementById('search-pseudo').value);
            fetch(`?action=search&pseudo=${pseudo}`)
                .then(r => r.json())
                .then(data => {
                    renderTable(data, false);
                    showNotification('Liste actualisée avec succès');
                })
                .catch(e => showNotification('Erreur de recherche', 'error'));
        }


        function clearInputValue(){
            document.getElementById('new-pseudo').value = "";
            document.getElementById('new-prenom').value = "";
            document.getElementById('new-nom').value ="";
            document.getElementById('new-age').value = "";
            document.getElementById('new-ville').value ="";

            
        }
        function addPerson() {
            const p = document.getElementById('new-pseudo').value.trim();
            const pr = document.getElementById('new-prenom').value.trim();
            const n = document.getElementById('new-nom').value.trim();
            const a = document.getElementById('new-age').value.trim();
            const v = document.getElementById('new-ville').value.trim();

              // Front-end validation
              if (!pr || !n || !a || !v) {
                showNotification('Tous les champs sont requis.', 'error');
                return;
            }

            if (isNaN(a) || a < 1) {
            showNotification('L’âge doit être plus de 0.', 'error');
            return;
}
            if (!/^[a-zA-ZÀ-ÖØ-öø-ÿ]+$/u.test(pr) || !/^[a-zA-ZÀ-ÖØ-öø-ÿ]+$/u.test(n)) {
                showNotification('Le prénom et le nom ne doivent contenir que des lettres.', 'error');
                return;
            }

            fetch('', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `action=create&pseudo=${encodeURIComponent(p)}&prenom=${encodeURIComponent(pr)}&nom=${encodeURIComponent(n)}&age=${encodeURIComponent(a)}&ville=${encodeURIComponent(v)}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    showNotification('Personne ajoutée avec succès');
                    fetchPersons();
                    ['new-pseudo','new-prenom','new-nom','new-age','new-ville'].forEach(id => document.getElementById(id).value = '');
                } else {
                    showNotification(res.message, 'error');
                }
            })
            .catch(() => showNotification('Erreur lors de l\'ajout', 'error'));
        }

        

        function deletePerson(pseudo) {
            if (!confirm(`Êtes-vous sûr de vouloir supprimer "${pseudo}" ?`)) return;
            fetch('', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `action=delete&pseudo=${encodeURIComponent(pseudo)}`
            }).then(() => {
                showNotification('Personne supprimée avec succès');
                fetchPersons();
            }).catch(e => showNotification('Erreur lors de la suppression', 'error'));
        }

        function updatePerson(pseudo) {
            const pr = document.getElementById(`prenom-${pseudo}`).value;
            const n  = document.getElementById(`nom-${pseudo}`).value;
            const a  = document.getElementById(`age-${pseudo}`).value;
            const v  = document.getElementById(`ville-${pseudo}`).value;

              // Front-end validation
              if (pr.trim() === '' || n.trim() === '' || v.trim() === '' || a === '') {
                showNotification('Tous les champs sont requis', 'error');
            return;
}


            console.log(a);
            if ( a < 1) {
            showNotification('L’âge doit être plus de 0', 'error');
            return;
}
            if (!/^[a-zA-ZÀ-ÖØ-öø-ÿ]+$/u.test(pr) || !/^[a-zA-ZÀ-ÖØ-öø-ÿ]+$/u.test(n)) {
                showNotification('Le prénom et le nom ne doivent contenir que des lettre.', 'error');
                return;
            }
            fetch('', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `action=update&pseudo=${encodeURIComponent(pseudo)}&prenom=${encodeURIComponent(pr)}&nom=${encodeURIComponent(n)}&age=${encodeURIComponent(a)}&ville=${encodeURIComponent(v)}`
            }).then(() => {
                showNotification('Modification enregistrée avec succès');
                console.log(a);
                fetchPersons();
            }).catch(e => showNotification('Erreur lors de la mise à jour', 'error'));
        }
    </script>
</body>
</html>
