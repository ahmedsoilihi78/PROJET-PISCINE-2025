const url = '../../php/authentification/EditerUser.php';

// Charge le formulaire (GET)
function loadForm(){
    $('#formContainer').load(url);
}

// Délégation : intercepter la soumission et envoyer en AJAX
$('#formContainer').on('submit', 'form', function(e){
    e.preventDefault();
    const $form = $(this);
    $.post(url, $form.serialize(), function(html){
        $('#formContainer').html(html);
    });
});

// Au chargement initial
$(loadForm);
