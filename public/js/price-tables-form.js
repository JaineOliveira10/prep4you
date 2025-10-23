document.addEventListener('DOMContentLoaded', function() {
    let rangeIndex = 0;
    const addButton = document.getElementById('add-range');
    const container = document.getElementById('price-ranges-container');

    // Load existing ranges if editing
    if (window.existingRanges) {
        window.existingRanges.forEach(range => {
            addRangeRow(range.min_value, range.max_value, range.price, range.price_kit);
        });
    }

    function addRangeRow(minValue = '', maxValue = '', price = '', priceKit = '') {
        const newRange = `
            <div class="row mt-3 price-range-row">
                <div class="form-group col-md-2">
                    <label>De (qtd)</label>
                    <input type="number" name="ranges[${rangeIndex}][min_value]" class="form-control" placeholder="Ex: 1" min="0" value="${minValue}">
                </div>
                <div class="form-group col-md-2">
                    <label>à (qtd)</label>
                    <input type="number" name="ranges[${rangeIndex}][max_value]" class="form-control" placeholder="Ex: 499" min="0" value="${maxValue}">
                </div>
                <div class="form-group col-md-3">
                    <label>Valor Etiqueta (R$)</label>
                    <input type="text" name="ranges[${rangeIndex}][price]" class="form-control" placeholder="Ex: 0,80" value="${price}">
                </div>
                <div class="form-group col-md-3">
                    <label>Valor Kit (R$)</label>
                    <input type="text" name="ranges[${rangeIndex}][price_kit]" class="form-control" placeholder="Ex: 1,00" value="${priceKit}">
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-range w-100 py-2">
                        <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                            <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        Remover
                    </button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', newRange);
        rangeIndex++;
    }

    addButton.addEventListener('click', function() {
        addRangeRow();
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-range')) {
            Swal.fire({
                icon: 'warning',
                title: 'Confirmar Exclusão',
                text: 'Deseja realmente remover esta faixa de preço?',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    e.target.closest('.price-range-row').remove();
                }
            });
        }
    });
});