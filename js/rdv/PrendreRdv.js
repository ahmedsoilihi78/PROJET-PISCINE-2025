// url du script php pour gerer les rdv
const backend = '../../php/rdv/PrendreRdv.php';

// objet pour stocker les informations des coachs
let coachMap = {};

// recuperation de la liste des coachs au chargement
fetch(`${backend}?action=getCoaches`)
    .then(r => r.json())
    .then(list => {
        const sel = document.getElementById('coachSelect');
        // pour chaque coach on ajoute une option dans la liste deroulante
        list.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = `${c.nom} ${c.prenom}`;
            // stockage du nom et prenom dans coachMap pour usage futur
            coachMap[c.id] = { nom: c.nom, prenom: c.prenom };
            sel.appendChild(opt);
        });
    });

// ecoute le changement de selection de coach pour charger le calendrier
document.getElementById('coachSelect').addEventListener('change', loadCalendar);

// fonction pour charger les informations et le planning du coach selectionne
function loadCalendar() {
    const coach_id = this.value;
    const container = document.getElementById('calendarContainer');
    const infoDiv = document.getElementById('coachInfo');

    // si aucun coach selectionne on vide tout et on quitte
    if (!coach_id) {
        container.innerHTML = '';
        infoDiv.textContent = '';
        return;
    }

    // recuperation des infos du coach (nom, prenom, specialite)
    fetch(`${backend}?action=getCoachInfo&coach_id=${coach_id}`)
        .then(r => r.json())
        .then(c => {
            // affichage du nom, prenom et specialite dans infoDiv
            infoDiv.textContent = `${c.nom} ${c.prenom} – ${c.specialite}`;
        });

    // recuperation du planning de la semaine pour ce coach
    fetch(`${backend}?action=getCalendar&coach_id=${coach_id}`)
        .then(r => r.json())
        .then(data => renderCalendar(data, coach_id));
}

// fonction pour afficher le calendrier avec les creneaux
function renderCalendar(data, coach_id) {
    const c = document.getElementById('calendarContainer');
    // on vide le conteneur avant de reconstruire le tableau
    c.innerHTML = '';
    const table = document.createElement('table');

    // creation de l en-tete avec les jours et dates
    const thead = document.createElement('thead');
    const trh = document.createElement('tr');
    trh.innerHTML = data.weekDates
        .map(w => `<th>${w.label}<br>${w.date}</th>`)
        .join('');
    thead.appendChild(trh);
    table.appendChild(thead);

    // creation du corps du tableau avec les creneaux horaires
    const tbody = document.createElement('tbody');
    data.slots.forEach(slot => {
        const tr = document.createElement('tr');
        // pour chaque jour de la semaine on ajoute une cellule
        data.weekDates.forEach(w => {
            const status = data.matrix[w.date][slot];
            const td = document.createElement('td');
            td.className = status;       // classe free, booked ou unavailable
            td.textContent = slot;       // affichage de l heure
            // si le creneau est libre on rend la cellule cliquable
            if (status === 'free') {
                td.addEventListener('click', () => {
                    // confirmation de la prise de rdv
                    if (confirm(`confirmer rdv le ${w.date} a ${slot} ?`)) {
                        bookSlot(coach_id, w.date, slot);
                    }
                });
            }
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    c.appendChild(table);
}

// fonction pour reserver un creneau en envoyant en post l id, date et heure
function bookSlot(coach_id, date, heure) {
    fetch(backend, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        // on envoie action, coach_id, date et heure dans le body
        body: new URLSearchParams({
            action: 'bookSlot',
            coach_id, date, heure
        })
    })
        .then(r => r.json())
        .then(res => {
            // si reservation reussie on affiche un message et on recharge le calendrier
            if (res.success) {
                alert('rdv confirme !');
                loadCalendar.call(document.getElementById('coachSelect'));
            } else {
                // sinon on affiche l erreur renvoyee par le serveur
                alert('erreur : ' + res.message);
            }
        });
}
