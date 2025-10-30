<x-app-layout :assets="$assets ?? []">
    <div>
        @php
            $id = $id ?? null;
            $data = $data ?? null;
        @endphp
        <form 
            action="{{ $id ? route('products.update', $id) : route('products.store') }}" 
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
                                <h4 class="card-title">{{ $id ? 'Editar' : 'Novo' }} Produto</h4>
                            </div>
                            <div class="card-action">
                                <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href = document.referrer">Voltar</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="new-product-info">
                                @if($errors->any())
                                    <div class="alert alert-danger mb-3">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                               

                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label class="form-label" for="client_id">Cliente <span class="text-danger">*</span></label>
                                        <select name="client_id" id="client_id" class="form-control" required {{ auth()->user()->type == 'client' ? 'disabled' : '' }}>
                                            @if(auth()->user()->type == 'admin')
                                                <option value="">Selecione um cliente</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" {{ old('client_id', $data->client_id ?? '') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                                @endforeach
                                            @else
                                                <option value="{{ auth()->user()->client_id }}" selected>{{ auth()->user()->client->name }}</option>
                                                <input type="hidden" name="client_id" value="{{ auth()->user()->client_id }}">
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label class="form-label" for="name">Nome <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $data->name ?? '') }}" placeholder="Digite o nome do produto" required maxlength="50">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label class="form-label" for="asin">ASIN</label>
                                        <input type="text" name="asin" id="asin" class="form-control" value="{{ old('asin', $data->asin ?? '') }}" placeholder="Ex: B0FDRCTH8H" maxlength="15">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="form-label" for="fsnku">FSNKU</label>
                                        <input type="text" name="fsnku" id="fsnku" class="form-control" value="{{ old('fsnku', $data->fsnku ?? '') }}" placeholder="Ex: X004SC7I0R" maxlength="15">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="form-label" for="sku">SKU</label>
                                        <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku', $data->sku ?? '') }}" placeholder="Ex: 0054-Generico-BolsaTermica-Azul" maxlength="40">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="type" id="type_simple" value="simple" {{ old('type', $data->type ?? 'simple') == 'simple' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="type_simple">Item Simples</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="type" id="type_kit" value="kit" {{ old('type', $data->type ?? '') == 'kit' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="type_kit">Kit</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="type" id="type_super_kit" value="super_kit" {{ old('type', $data->type ?? '') == 'super_kit' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="type_super_kit">Super Kit</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row kit-fields" style="{{ old('type', $data->type ?? 'simple') == 'simple' ? 'display: none;' : '' }}">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="kit_units">Unidades no Kit <span class="text-danger kit-required">*</span></label>
                                        <input type="number" name="kit_units" id="kit_units" class="form-control" value="{{ old('kit_units', $data->kit_units ?? '') }}" placeholder="Número de unidades" min="1">
                                    </div>
                                    <div class="form-group col-md-6 super-kit-field" style="{{ old('type', $data->type ?? '') != 'super_kit' ? 'display: none;' : '' }}">
                                        <label class="form-label" for="unit_price">Preço Unitário <span class="text-danger super-kit-required">*</span></label>
                                        <input type="text" name="unit_price" id="unit_price" class="form-control money" value="{{ old('unit_price', isset($data->unit_price) ? number_format($data->unit_price, 2, ',', '.') : '') }}" placeholder="0,00">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="photo">Foto do Produto</label>
                                        <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                                        @if(isset($data->photo_path) && $data->photo_path)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $data->photo_path) }}" alt="Foto atual" class="img-thumbnail" style="max-width: 150px;">
                                                <small class="text-muted d-block">Foto atual</small>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="observation">Observação</label>
                                        <textarea name="observation" id="observation" class="form-control" rows="4" placeholder="Observações sobre o produto" maxlength="200">{{ old('observation', $data->observation ?? '') }}</textarea>
                                        <small class="text-muted">Máximo 200 caracteres</small>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">{{ $id ? 'Atualizar' : 'Adicionar' }} Produto</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
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
        });
    </script>
</x-app-layout>