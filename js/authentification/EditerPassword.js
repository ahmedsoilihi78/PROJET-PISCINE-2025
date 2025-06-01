// on definit le url du script php pour editer le mot de passe
const url = '../../php/authentification/EditerPassword.php';

// fonction pour charger le formulaire a l ouverture
function loadForm() {
    // on charge le contenu du formulaire depuis le url dans le conteneur
    $('#formContainer').load(url);
}

// on intercepte la soumission du formulaire et on met a jour le contenu en ajax
$('#formContainer').on('submit', 'form', function(e) {
    // on empeche le comportement par defaut pour ne pas recharger la page
    e.preventDefault();
    // on recupere le objet jquery du formulaire soumis
    const $form = $(this);
    // on envoie les donnees du formulaire en post et on recupere le html en reponse
    $.post(url, $form.serialize(), function(html) {
        // on remplace le contenu du conteneur par le html recu
        $('#formContainer').html(html);
    });
});

// on execute loadForm au chargement du document
$(loadForm);
