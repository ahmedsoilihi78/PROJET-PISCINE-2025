const url = '../../php/authentification/EditerPassword.php';

// Charger le formulaire en GET
function loadForm() {
    $('#formContainer').load(url);
}

// Intercepter la soumission et renvoyer le HTML mis à jour
$('#formContainer').on('submit', 'form', function(e) {
    e.preventDefault();
    const $form = $(this);
    $.post(url, $form.serialize(), function(html) {
        $('#formContainer').html(html);
    });
});

// Initialisation
$(loadForm);
