const showajoute = document.getElementById('showajoute');
const card = document.getElementById('cardAjout');
const ajout = document.getElementById('ajout');
const affichecacher = document.getElementById("affichecacher");
const table = document.getElementById("tableContainer");
const annuler = document.getElementById("annuler");

let afficher = true;

annuler.addEventListener('click', function() {
    card.classList.add('hidden');
    showajoute.classList.remove('hidden');
    clearInputValue();
});

affichecacher.addEventListener('click', function() {
    if (afficher) {
        this.innerText = "Cacher";
        table.style.display = "block";
        afficher = false;
        fetchPersons();
    } else {
        afficher = true;
        this.innerText = "Afficher";
        table.style.display = "none";
    }
});

ajout.addEventListener('click', function() {
    addPerson();
    card.classList.add('hidden');
    showajoute.classList.remove('hidden');
});

showajoute.addEventListener('click', function() {
    card.classList.remove('hidden');
    this.classList.add('hidden');
});

// ==== Notifications ====
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    notification.className = `notification ${type} show`;
    notification.textContent = message;
    setTimeout(() => notification.classList.remove('show'), 3000);
}

// ==== Render table ====
function renderTable(data, all) {
    if (data.length === 0) {
        showNotification('Aucun résultat trouvé', 'error');
        return;
    }

    let html = `
    <table>
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
    document.getElementById(all ? 'tableContainer' : 'tableContainer2').innerHTML = html;
}

// ==== Fetch all persons ====
function fetchPersons() {
    fetch('backend.php?action=fetch')
        .then(r => r.json())
        .then(data => {
            renderTable(data, true);
            showNotification('Liste actualisée');
        })
        .catch(() => showNotification('Erreur de chargement', 'error'));
}

// ==== Search ====
function searchPerson() {
    const pseudo = encodeURIComponent(document.getElementById('search-pseudo').value);
    fetch(`backend.php?action=search&pseudo=${pseudo}`)
        .then(r => r.json())
        .then(data => renderTable(data, false))
        .catch(() => showNotification('Erreur de recherche', 'error'));
}

// ==== Clear inputs ====
function clearInputValue() {
    ['new-pseudo','new-prenom','new-nom','new-age','new-ville'].forEach(id => document.getElementById(id).value = '');
}

// ==== Add person ====
function addPerson() {
    const p = document.getElementById('new-pseudo').value.trim();
    const pr = document.getElementById('new-prenom').value.trim();
    const n = document.getElementById('new-nom').value.trim();
    const a = document.getElementById('new-age').value.trim();
    const v = document.getElementById('new-ville').value.trim();

    if (!p || !pr || !n || !a || !v) return showNotification('Tous les champs sont requis.', 'error');
    if (isNaN(a) || a < 1) return showNotification('Âge invalide', 'error');

    fetch('backend.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `action=create&pseudo=${p}&prenom=${pr}&nom=${n}&age=${a}&ville=${v}`
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            showNotification('Personne ajoutée');
            fetchPersons();
            clearInputValue();
        } else showNotification(res.message, 'error');
    })
    .catch(() => showNotification('Erreur lors de l\'ajout', 'error'));
}

// ==== Delete person ====
function deletePerson(pseudo) {
    if (!confirm(`Supprimer "${pseudo}" ?`)) return;
    fetch('backend.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `action=delete&pseudo=${encodeURIComponent(pseudo)}`
    })
    .then(() => {
        showNotification('Personne supprimée');
        fetchPersons();
    })
    .catch(() => showNotification('Erreur suppression', 'error'));
}

// ==== Update person ====
function updatePerson(pseudo) {
    const pr = document.getElementById(`prenom-${pseudo}`).value;
    const n  = document.getElementById(`nom-${pseudo}`).value;
    const a  = document.getElementById(`age-${pseudo}`).value;
    const v  = document.getElementById(`ville-${pseudo}`).value;

    if (!pr || !n || !v || !a) return showNotification('Champs manquants', 'error');
    if (a < 1) return showNotification('Âge invalide', 'error');

    fetch('backend.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `action=update&pseudo=${pseudo}&prenom=${pr}&nom=${n}&age=${a}&ville=${v}`
    })
    .then(() => {
        showNotification('Modification enregistrée');
        fetchPersons();
    })
    .catch(() => showNotification('Erreur mise à jour', 'error'));
}
