<x-app-layout :assets="$assets ?? []">
    <div>
        @php
            $id = $id ?? null;
            $data = $data ?? null;
            $profileImage = $profileImage ?? asset('images/avatars/01.png');
        @endphp

        <form 
            action="{{ $id ? route('price-tables.update', $id) : route('price-tables.store') }}" 
            method="POST" 
            enctype="multipart/form-data"
        >
            @csrf
            @if($id)
                @method('PATCH')
            @endif

            <div class="row">
                <div class="col-xl-12 col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">{{ $id ? 'Editar' : 'Nova' }} Tabela de Preço</h4>
                            </div>
                            <div class="card-action">
                                <a href="{{ route('price-tables.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="new-price-tables-info">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="name">Nome <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $data->name ?? '') }}" placeholder="Digite o nome" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="description">Descrição</label>
                                        <input type="text" name="description" id="description" class="form-control" value="{{ old('description', $data->description ?? '') }}" placeholder="Descrição da tabela de preço">
                                    </div>

                                    <hr class="hr-horizontal mb-3"> 

                                    <div id="price-ranges-fields">
                                       <h5>Faixas de Preço</h5>
                                       <div id="price-ranges-container"></div>
                                       <button type="button" id="add-range" class="btn btn-secondary btn-sm mt-3 mb-4">+ Adicionar Faixa</button>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">{{ $id ? 'Atualizar' : 'Adicionar' }} Tabela de Preço</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let rangeIndex = 0;
        const addButton = document.getElementById('add-range');
        const container = document.getElementById('price-ranges-container');

        // Load existing ranges if editing
        @if(isset($data) && $data->priceRanges)
            @foreach($data->priceRanges as $range)
                addRangeRow({{ $range->min_value }}, {{ $range->max_value }}, '{{ number_format($range->price, 2, ',', '') }}', '{{ number_format($range->price_kit, 2, ',', '') }}');
            @endforeach
        @endif

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
                if (confirm('Deseja realmente remover esta faixa de preço?')) {
                    e.target.closest('.price-range-row').remove();
                }
            }
        });
    });
    </script>
</x-app-layout>
