document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('select[name="type"]');
    const clienteFields = document.getElementById('cliente-fields');
    const requiredClientFields = clienteFields.querySelectorAll('[required]');

    function toggleClienteFields() {
        if(typeSelect.value === 'client') {
            clienteFields.style.display = 'block';
            requiredClientFields.forEach(field => {
                field.setAttribute('required', 'required');
            });
        } else {
            clienteFields.style.display = 'none';
            requiredClientFields.forEach(field => {
                field.removeAttribute('required');
            });
        }
    }

    typeSelect.addEventListener('change', toggleClienteFields);

    toggleClienteFields();

    const userName = document.getElementById('fname');
    const userEmail = document.getElementById('email');
    const clientName = document.getElementById('client_name');
    const clientEmail = document.getElementById('client_email');

    userName.addEventListener('input', () => {
        clientName.value = userName.value;
    });

    userEmail.addEventListener('input', () => {
        clientEmail.value = userEmail.value;
    });

    const phoneInput = document.getElementById('client_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 6) {
                value = value.replace(/(\d{2})(\d{4})/, '($1) $2');
            } else if (value.length >= 2) {
                value = value.replace(/(\d{2})/, '($1) ');
            }
            
            e.target.value = value;
        });
    }

    // Máscara de CNPJ
    const cnpjInput = document.getElementById('cnpj');
    if (cnpjInput) {
        cnpjInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 14) {
                value = value.substring(0, 14);
            }
            
            if (value.length >= 12) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
            } else if (value.length >= 8) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})/, '$1.$2.$3/$4');
            } else if (value.length >= 5) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})/, '$1.$2.$3');
            } else if (value.length >= 2) {
                value = value.replace(/(\d{2})(\d{3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
        
        cnpjInput.addEventListener('keypress', function(e) {
            if (!/[\d]/.test(e.key)) {
                e.preventDefault();
            }
        });
    }
});