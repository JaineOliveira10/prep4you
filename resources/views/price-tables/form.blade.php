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

    @if(isset($data) && $data->priceRanges)
        <script>
            window.existingRanges = {!! json_encode($data->priceRanges->map(function($range) {
                return [
                    'min_value' => $range->min_value,
                    'max_value' => $range->max_value,
                    'price' => number_format($range->price, 2, ',', ''),
                    'price_kit' => number_format($range->price_kit, 2, ',', '')
                ];
            })) !!};
        </script>
    @endif
    <script src="{{ asset('js/price-tables-form.js') }}"></script>
</x-app-layout>
