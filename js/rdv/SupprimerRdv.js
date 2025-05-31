const api = '../../php/rdv/SupprimerRdv.php';

function showMsg(text, type) {
    $('#message').html(`<div class="msg ${type}">${text}</div>`);
}

function loadUserInfo() {
    $.getJSON(api, { action: 'getUser' })
        .done(res => {
            if (!res.success) {
                $('#userInfo').text('Utilisateur non authentifié');
            } else {
                const u = res.user;
                $('#userInfo').text(
                    `Connecté : ID ${u.id} – ${u.nom} ${u.prenom} (${u.role})`
                );
            }
        })
        .fail(() => {
            $('#userInfo').text('Erreur de chargement des infos utilisateur');
        });
}

function loadList() {
    $.getJSON(api, { action: 'list' })
        .done(res => {
            if (!res.success) return showMsg(res.message, 'error');
            const rows = res.data.map(r => `
            <tr>
              <td>${r.date}</td>
              <td>${r.heure}</td>
              <td>${r.coach_nom} ${r.coach_prenom}</td>
              <td>${r.client_nom} ${r.client_prenom}</td>
              <td>${r.statut}</td>
              <td><span class="delete" data-id="${r.id}">Supprimer</span></td>
            </tr>
          `).join('');
            $('#rdvTable tbody').html(rows);
        })
        .fail(() => showMsg('Erreur réseau', 'error'));
}

$('#rdvTable').on('click', '.delete', function(){
    const id = $(this).data('id');
    if (!confirm('Confirmer la suppression ?')) return;
    $.post(api, { action: 'delete', id })
        .done(res => {
            showMsg(res.message, res.success ? 'success' : 'error');
            if (res.success) loadList();
        })
        .fail(() => showMsg('Erreur suppression', 'error'));
});

$(function(){
    loadUserInfo();
    loadList();
});