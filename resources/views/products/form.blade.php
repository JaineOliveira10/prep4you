<x-app-layout :assets="$assets ?? []">
    <div>
        @php
            $id = $id ?? null;
            $data = $data ?? null;
            $isCopy = request()->routeIs('products.copy');
        @endphp
        <form 
            action="{{ $id && !$isCopy ? route('products.update', $id) : route('products.store') }}" 
            method="POST" 
            enctype="multipart/form-data"
        >
            @csrf
            @if($id && !$isCopy)
                @method('PATCH')
            @endif

            <div class="row">
                <div class="col-xl-12 col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">{{ $isCopy ? 'Copiar' : ($id ? 'Editar' : 'Novo') }} Produto</h4>
                            </div>
                            <div class="card-action">
                                <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href = document.referrer">Voltar</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="new-product-info">
                                @if($isCopy)
                                    <div class="alert alert-info mb-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Você está criando uma cópia do produto.
                                    </div>
                                @endif
                                
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
                                        <select name="client_id" id="client_id" class="form-control" required disabled>
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
                                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $data->name ?? '') }}" placeholder="Informe o nome do produto" required maxlength="50" {{ auth()->user()->type == 'admin' ? 'readonly' : '' }}>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label class="form-label" for="asin">ASIN</label>
                                        <input type="text" name="asin" id="asin" class="form-control" value="{{ old('asin', $isCopy ? '' : ($data->asin ?? '')) }}" maxlength="15" {{ auth()->user()->type == 'admin' ? 'readonly' : '' }}>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="form-label" for="fsnku">FSNKU <span class="text-danger">*</span></label>
                                        <input type="text" name="fsnku" id="fsnku" class="form-control" value="{{ old('fsnku', $isCopy ? '' : ($data->fsnku ?? '')) }}" maxlength="15" required {{ auth()->user()->type == 'admin' ? 'readonly' : '' }}>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="form-label" for="sku">SKU</label>
                                        <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku', $data->sku ?? '') }}" maxlength="40" {{ auth()->user()->type == 'admin' ? 'readonly' : '' }}>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="type" id="type_simple" value="simple" {{ old('type', $data->type ?? 'simple') == 'simple' ? 'checked' : '' }} {{ auth()->user()->type == 'client' && isset($data) && $data->type == 'super_kit' ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="type_simple">Item Simples</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="type" id="type_kit" value="kit" {{ old('type', $data->type ?? '') == 'kit' ? 'checked' : '' }} {{ auth()->user()->type == 'client' && isset($data) && $data->type == 'super_kit' ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="type_kit">Kit</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="type" id="type_super_kit" value="super_kit" {{ old('type', $data->type ?? '') == 'super_kit' ? 'checked' : '' }} {{ auth()->user()->type == 'client' ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="type_super_kit">Super Kit</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row kit-fields" style="{{ old('type', $data->type ?? 'simple') == 'simple' ? 'display: none;' : '' }}">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="kit_units">Unidades no Kit <span class="text-danger kit-required">*</span></label>
                                        <input type="number" name="kit_units" id="kit_units" class="form-control" value="{{ old('kit_units', $data->kit_units ?? '') }}" placeholder="Número de unidades" min="1" {{ auth()->user()->type == 'client' && isset($data) && $data->type == 'super_kit' ? 'readonly' : '' }}>
                                    </div>
                                    <div class="form-group col-md-6 super-kit-field" style="{{ old('type', $data->type ?? '') != 'super_kit' ? 'display: none;' : '' }}">
                                        <label class="form-label" for="unit_price">Preço Unitário <span class="text-danger super-kit-required">*</span></label>
                                        <input type="text" name="unit_price" id="unit_price" class="form-control money" value="{{ old('unit_price', isset($data->unit_price) ? number_format($data->unit_price, 2, ',', '.') : '') }}" placeholder="0,00" {{ auth()->user()->type == 'client' ? 'readonly' : '' }}>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="photo">Foto do Produto</label>
                                        <input type="file" name="photo" id="photo" class="form-control" accept="image/*" {{ auth()->user()->type == 'admin' ? 'disabled' : '' }}>
                                        @if(isset($data->photo_path) && $data->photo_path && !$isCopy)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $data->photo_path) }}" alt="Foto atual" class="img-thumbnail" style="max-width: 150px;">
                                                <small class="text-muted d-block">Foto atual</small>
                                            </div>
                                        @endif
                                        <div id="photo-preview" class="mt-2" style="display: none;">
                                            <img id="preview-image" src="" alt="Preview" class="img-thumbnail" style="max-width: 150px;">
                                            <small class="text-muted d-block">{{$id ? 'Prévia nova foto' : 'Foto do produto'}}</small>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="observation">Observação</label>
                                        <textarea name="observation" id="observation" class="form-control" rows="4" placeholder="Observações sobre o produto" maxlength="200">{{ old('observation', $data->observation ?? '') }}</textarea>
                                        <small class="text-muted">Máximo 200 caracteres</small>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">{{ $isCopy ? 'Criar Cópia' : ($id ? 'Atualizar' : 'Adicionar') }} Produto</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/products-form.js') }}"></script>
</x-app-layout>