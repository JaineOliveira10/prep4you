document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('select[name="type"]');
    const clienteFields = document.getElementById('cliente-fields');

    function toggleClienteFields() {
        if(typeSelect.value === 'client') {
            clienteFields.style.display = 'block';
        } else {
            clienteFields.style.display = 'none';
        }
    }

    typeSelect.addEventListener('change', toggleClienteFields);

    // Mostra os campos se já estiver editando e tipo for cliente
    toggleClienteFields();

    const userName = document.getElementById('fname');
    const userEmail = document.getElementById('email');
    const clientName = document.getElementById('client_name');
    const clientEmail = document.getElementById('client_email');

    // Sempre que digitar no campo nome do usuário, atualiza o nome do cliente
    userName.addEventListener('input', () => {
        clientName.value = userName.value;
    });

    // Sempre que digitar no campo email do usuário, atualiza o email do cliente
    userEmail.addEventListener('input', () => {
        clientEmail.value = userEmail.value;
    });
});