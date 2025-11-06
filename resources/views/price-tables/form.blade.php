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
                                <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href = document.referrer">Voltar</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="new-price-tables-info">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="name">Nome <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $data->name ?? '') }}" placeholder="Digite o nome">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="description">Descrição</label>
                                        <input type="text" name="description" id="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description', $data->description ?? '') }}" placeholder="Descrição da tabela de preço">
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <hr class="hr-horizontal mb-3"> 

                                    <div id="price-ranges-fields">
                                       <h5>Faixas de Preço</h5>

                                       <div id="price-ranges-container" 
                                            data-existing-ranges='@if(old("ranges")){{ json_encode(collect(old("ranges"))->map(function($range, $index) { return ["min_value" => $range["min_value"] ?? "", "max_value" => $range["max_value"] ?? "", "price" => $range["price"] ?? "", "price_kit" => $range["price_kit"] ?? "", "index" => $index]; })) }}@elseif(isset($data) && $data->priceRanges){{ json_encode($data->priceRanges->map(function($range, $index) { return ["min_value" => $range->min_value, "max_value" => $range->max_value, "price" => number_format($range->price, 2, ",", ""), "price_kit" => number_format($range->price_kit, 2, ",", ""), "index" => $index]; })) }}@else[]@endif'>
                                           @if(old('ranges'))
                                               @foreach(old('ranges') as $index => $range)
                                                   <div class="row mt-3 price-range-row">
                                                       <div class="form-group col-md-2">
                                                           <label>Qtd. Inicial <span class="text-danger">*</span></label>
                                                           <input type="number" name="ranges[{{ $index }}][min_value]" class="form-control @error('ranges.'.$index.'.min_value') is-invalid @enderror" min="0" value="{{ $range['min_value'] ?? '' }}">
                                                           @error('ranges.'.$index.'.min_value')
                                                               <div class="invalid-feedback">{{ $message }}</div>
                                                           @enderror
                                                       </div>
                                                       <div class="form-group col-md-2">
                                                           <label>Qtd. Final <span class="text-danger">*</span></label>
                                                           <input type="number" name="ranges[{{ $index }}][max_value]" class="form-control @error('ranges.'.$index.'.max_value') is-invalid @enderror" min="0" value="{{ $range['max_value'] ?? '' }}">
                                                           @error('ranges.'.$index.'.max_value')
                                                               <div class="invalid-feedback">{{ $message }}</div>
                                                           @enderror
                                                       </div>
                                                       <div class="form-group col-md-3">
                                                           <label>Valor Etiqueta (R$) <span class="text-danger">*</span></label>
                                                           <input type="text" name="ranges[{{ $index }}][price]" class="form-control price-input @error('ranges.'.$index.'.price') is-invalid @enderror" value="{{ $range['price'] ?? '' }}">
                                                           @error('ranges.'.$index.'.price')
                                                               <div class="invalid-feedback">{{ $message }}</div>
                                                           @enderror
                                                       </div>
                                                       <div class="form-group col-md-3">
                                                           <label>Valor Kit (R$) <span class="text-danger">*</span></label>
                                                           <input type="text" name="ranges[{{ $index }}][price_kit]" class="form-control price-input @error('ranges.'.$index.'.price_kit') is-invalid @enderror" value="{{ $range['price_kit'] ?? '' }}">
                                                           @error('ranges.'.$index.'.price_kit')
                                                               <div class="invalid-feedback">{{ $message }}</div>
                                                           @enderror
                                                       </div>
                                                       <div class="form-group col-md-2">
                                                           <label>&nbsp;</label>
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
                                               @endforeach
                                           @endif
                                       </div>
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
        window.Laravel = {
            errors: @json($errors->getMessages())
        };
    </script>
    <script src="{{ asset('js/price-tables-form.js') }}"></script>
</x-app-layout>
