// url du script php pour la suppression des rdv
const api = '../../php/rdv/SupprimerRdv.php';

// fonction pour afficher un message de succes ou d erreur
function showMsg(text, type) {
    // on met a jour le contenu du conteneur d id message
    $('#message').html(`<div class="msg ${type}">${text}</div>`);
}

// fonction pour charger les infos de l utilisateur connecte
function loadUserInfo() {
    // on envoie un appel ajax get avec action getUser
    $.getJSON(api, { action: 'getUser' })
        .done(res => {
            // si la reponse n est pas un succes
            if (!res.success) {
                // on affiche message utilisateur non authentifie
                $('#userInfo').text('utilisateur non authentifie');
            } else {
                // sinon on recupere les infos utilisateur
                const u = res.user;
                // on affiche id nom prenom role dans l element d id userInfo
                $('#userInfo').text(
                    `connecte : id ${u.id} – ${u.nom} ${u.prenom} (${u.role})`
                );
            }
        })
        .fail(() => {
            // en cas d erreur reseau on affiche un message
            $('#userInfo').text('erreur de chargement des infos utilisateur');
        });
}

// fonction pour charger la liste des rdv dans le tableau
function loadList() {
    // on envoie un appel ajax get avec action list
    $.getJSON(api, { action: 'list' })
        .done(res => {
            // si la reponse n est pas un succes
            if (!res.success) return showMsg(res.message, 'error');
            // on transforme chaque rdv en ligne de tableau
            const rows = res.data.map(r => `
        <tr>
          <td>${r.date}</td>
          <td>${r.heure}</td>
          <td>${r.coach_nom} ${r.coach_prenom}</td>
          <td>${r.client_nom} ${r.client_prenom}</td>
          <td>${r.statut}</td>
          <td><span class="delete" data-id="${r.id}">supprimer</span></td>
        </tr>
      `).join('');
            // on met a jour le corps du tableau avec les lignes generees
            $('#rdvTable tbody').html(rows);
        })
        .fail(() => showMsg('erreur reseau', 'error'));
}

// on ecoute le clic sur un element de classe delete dans le tableau
$('#rdvTable').on('click', '.delete', function() {
    // on recupere l id du rdv a supprimer depuis l attribut data-id
    const id = $(this).data('id');
    // on demande confirmation a l utilisateur
    if (!confirm('confirmer la suppression ?')) return;
    // on envoie un appel ajax post avec action delete et l id
    $.post(api, { action: 'delete', id })
        .done(res => {
            // on affiche le message recu (succes ou erreur)
            showMsg(res.message, res.success ? 'success' : 'error');
            // si suppression reussie on recharge la liste des rdv
            if (res.success) loadList();
        })
        .fail(() => showMsg('erreur suppression', 'error'));
});

// au chargement du document on appelle loadUserInfo et loadList
$(function(){
    loadUserInfo();
    loadList();
});
