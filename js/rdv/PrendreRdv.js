const backend = '../../php/rdv/PrendreRdv.php';

let coachMap = {};

fetch(`${backend}?action=getCoaches`)
    .then(r => r.json())
    .then(list => {
        const sel = document.getElementById('coachSelect');
        list.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = `${c.nom} ${c.prenom}`;
            coachMap[c.id] = { nom: c.nom, prenom: c.prenom };
            sel.appendChild(opt);
        });
    });

document.getElementById('coachSelect').addEventListener('change', loadCalendar);

function loadCalendar() {
    const coach_id = this.value;
    const container = document.getElementById('calendarContainer');
    const infoDiv = document.getElementById('coachInfo');

    if (!coach_id) {
        container.innerHTML = '';
        infoDiv.textContent = '';
        return;
    }

    fetch(`${backend}?action=getCoachInfo&coach_id=${coach_id}`)
        .then(r => r.json())
        .then(c => {
            infoDiv.textContent = `${c.nom} ${c.prenom} – ${c.specialite}`;
        });

    fetch(`${backend}?action=getCalendar&coach_id=${coach_id}`)
        .then(r => r.json())
        .then(data => renderCalendar(data, coach_id));
}

function renderCalendar(data, coach_id) {
    const c = document.getElementById('calendarContainer');
    c.innerHTML = '';
    const table = document.createElement('table');

    const thead = document.createElement('thead');
    const trh = document.createElement('tr');
    trh.innerHTML = data.weekDates
        .map(w => `<th>${w.label}<br>${w.date}</th>`)
        .join('');
    thead.appendChild(trh);
    table.appendChild(thead);

    const tbody = document.createElement('tbody');
    data.slots.forEach(slot => {
        const tr = document.createElement('tr');
        data.weekDates.forEach(w => {
            const status = data.matrix[w.date][slot];
            const td = document.createElement('td');
            td.className = status;
            td.textContent = slot;
            if (status === 'free') {
                td.addEventListener('click', () => {
                    if (confirm(`Confirmer RDV le ${w.date} à ${slot} ?`)) {
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

function bookSlot(coach_id, date, heure) {
    fetch(backend, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'bookSlot',
            coach_id, date, heure
        })
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert('RDV confirmé !');
                loadCalendar.call(document.getElementById('coachSelect'));
            } else {
                alert('Erreur : ' + res.message);
            }
        });
}
