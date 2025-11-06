document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const kitFields = document.querySelector('.kit-fields');
    const superKitField = document.querySelector('.super-kit-field');
    const kitUnitsInput = document.getElementById('kit_units');
    const unitPriceInput = document.getElementById('unit_price');

    function toggleFields() {
        const selectedType = document.querySelector('input[name="type"]:checked').value;
        
        if (selectedType === 'simple') {
            kitFields.style.display = 'none';
            kitUnitsInput.removeAttribute('required');
            unitPriceInput.removeAttribute('required');
        } else {
            kitFields.style.display = '';
            kitUnitsInput.setAttribute('required', 'required');
            
            if (selectedType === 'super_kit') {
                superKitField.style.display = '';
                unitPriceInput.setAttribute('required', 'required');
            } else {
                superKitField.style.display = 'none';
                unitPriceInput.removeAttribute('required');
            }
        }
    }

    typeRadios.forEach(radio => {
        radio.addEventListener('change', toggleFields);
    });
    
    toggleFields();

    // Máscara para campo de preço
    if (unitPriceInput) {
        unitPriceInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2) + '';
            value = value.replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = value;
        });
    }

    // Preview da imagem
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview');
    const previewImage = document.getElementById('preview-image');

    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    photoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                photoPreview.style.display = 'none';
            }
        });
    }
});