<x-app-layout :assets="$assets ?? []">
    <div>
        @php
            $id = $id ?? null;
            $data = $data ?? null;
        @endphp
        <form 
            action="{{ $id ? route('shipments.update', $id) : route('shipments.store') }}" 
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
                                <h4 class="card-title">{{ $id ? 'Editar' : 'Nova' }} Remessa</h4>
                            </div>
                            <div class="card-action">
                                <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href = document.referrer">Voltar</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="new-shipment-info">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="name">Nome da Remessa</label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $data->name ?? '') }}" placeholder="Digite o nome da remessa" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="creation_date">Data de Criação</label>
                                        <input type="date" name="creation_date" id="creation_date" class="form-control" value="{{ old('creation_date', $data->creation_date ?? now()->format('Y-m-d')) }}" disabled>
                                    </div>
                                    @if(auth()->user()->type === 'admin')
                                    <div class="form-group col-md-4">
                                        <label class="form-label" for="client_id">Cliente <span class="text-danger">*</span></label>
                                        <select name="client_id" id="client_id" class="form-select" required>
                                            <option value="">Selecione um cliente</option>
                                            @foreach(\App\Models\Client::all() as $client)
                                                <option value="{{ $client->id }}" {{ old('client_id', $data->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                                    {{ $client->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @else
                                        <input type="hidden" name="client_id" value="{{ auth()->user()->client->id }}">
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="shipment_date">Data da Remessa <span class="text-danger">*</span></label>
                                        <input type="date" name="shipment_date" id="shipment_date" class="form-control" value="{{ old('shipment_date', $data->shipment_date ?? '') }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="collection_date">Data da Coleta</label>
                                        <input type="date" name="collection_date" id="collection_date" class="form-control" value="{{ old('collection_date', $data->collection_date ?? '') }}" disabled>
                                        <small class="text-muted">Será calculada como 3 dias úteis após a data da remessa</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="distribution_center_id">Centro de Distribuição <span class="text-danger">*</span></label>
                                        <select name="distribution_center_id" id="distribution_center_id" class="form-select" required>
                                            <option value="">Selecione um centro</option>
                                            @foreach(\App\Models\DistributionCenter::all() as $center)
                                                <option value="{{ $center->id }}" {{ old('distribution_center_id', $data->distribution_center_id ?? '') == $center->id ? 'selected' : '' }}>
                                                    {{ $center->acronym }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="status" class="form-select" required {{auth()->user()->type == 'client' ? 'disabled' : ''}}>
                                            <option value="Pending" {{ old('status', $data->status ?? 'Pending') == 'Pending' ? 'selected' : '' }}>Pendente</option>
                                            <option value="In Preparation" {{ old('status', $data->status ?? '') == 'In Preparation' ? 'selected' : '' }}>Em Preparação</option>
                                            <option value="Packed" {{ old('status', $data->status ?? '') == 'Packed' ? 'selected' : '' }}>Embalado</option>
                                            <option value="Collected" {{ old('status', $data->status ?? '') == 'Collected' ? 'selected' : '' }}>Coletado</option>
                                            <option value="Invoice Generated" {{ old('status', $data->status ?? '') == 'Invoice Generated' ? 'selected' : '' }}>Fatura Gerada</option>
                                            <option value="Paid" {{ old('status', $data->status ?? '') == 'Paid' ? 'selected' : '' }}>Pago</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Itens da Remessa -->
            <div class="row mt-4">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Itens da Remessa</h4>
                            </div>
                            <div class="card-action">
                                <button type="button" class="btn btn-sm btn-success" id="add-item">Adicionar Item</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <style>
                                    #items-table {
                                        table-layout: fixed;
                                        width: 100%;
                                    }
                                    #items-table td {
                                        padding: 0.15rem !important;
                                        vertical-align: middle;
                                    }
                                    #items-table th {
                                        padding: 0.3rem 0.15rem !important;
                                        font-size: 0.8rem;
                                    }
                                    #items-table .form-control,
                                    #items-table .form-select {
                                        padding: 0.3rem 0.4rem;
                                        font-size: 0.85rem;
                                        height: 36px;
                                        border-width: 1px;
                                    }
                                    #items-table .btn-sm {
                                        padding: 0.2rem 0.4rem;
                                        font-size: 0.75rem;
                                    }
                                </style>
                                <table class="table table-striped" id="items-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 25%;">Produto</th>
                                            <th style="width: 10%;">FSNKU</th>
                                            <th style="width: 10%;">SKU</th>
                                            <th style="width: 8%;">Tipo</th>
                                            <th style="width: 8%;">Qtd Kit</th>
                                            <th style="width: 8%;">Qtd</th>
                                            <th style="width: 12%;">Preço Unit.</th>
                                            <th style="width: 12%;">Valor Total</th>
                                            <th style="width: 7%;">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-tbody">
                                        @if(isset($data) && $data->items)
                                            @foreach($data->items as $index => $item)
                                                <tr data-index="{{ $index }}">
                                                    <td>
                                                        <select name="items[{{ $index }}][product_id]" class="form-select product-select" required>
                                                            <option value="">Selecione um produto</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                                    {{ $product->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td><input type="text" class="form-control fsnku" value="{{ $item->product->fsnku }}" disabled></td>
                                                    <td><input type="text" class="form-control sku" value="{{ $item->product->sku }}" disabled></td>
                                                    <td><input type="text" class="form-control type" value="{{ $item->product->type === 'simple' ? 'Simples' : ($item->product->type === 'kit' ? 'Kit' : 'S.Kit') }}" disabled></td>
                                                    <td><input type="text" class="form-control kit-units" value="{{ $item->product->kit_units }}" disabled></td>
                                                    <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" value="{{ $item->quantity }}" min="1" required></td>
                                                    <td><input type="number" name="items[{{ $index }}][unit_price]" class="form-control unit-price" value="{{ $item->unit_price }}" step="0.01" readonly></td>
                                                    <td><input type="number" class="form-control total-value" value="{{ $item->total_value }}" step="0.01" disabled></td>
                                                    <td><button type="button" class="btn btn-sm btn-danger remove-item">Remover</button></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6 offset-md-6">
                                    <div class="d-flex justify-content-between">
                                        <strong>Total Geral:</strong>
                                        <strong id="grand-total">R$ 0,00</strong>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">{{ $id ? 'Atualizar' : 'Criar' }} Remessa</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/shipments-form.js') }}"></script>
    <script>
    // Cálculo da data de coleta
    document.getElementById('shipment_date').addEventListener('change', function() {
        const shipmentDate = this.value;
        const collectionDateField = document.getElementById('collection_date');
        
        if (shipmentDate) {
            fetch('{{ route("shipments.calculate-collection-date") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    shipment_date: shipmentDate
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.collection_date) {
                    collectionDateField.value = data.collection_date;
                }
            })
            .catch(error => {
                console.error('Erro ao calcular data de coleta:', error);
            });
        }
    });

    // Inicializar gerenciador de itens
    document.addEventListener('DOMContentLoaded', function() {
        const itemsManager = new ShipmentItemsManager(
            @json($products ?? []),
            {
                getProductPrice: '{{ route("shipments.get-product-price") }}',
                getProductsByClient: '{{ route("shipments.get-products-by-client") }}'
            },
            '{{ csrf_token() }}',
            '{{ auth()->user()->type }}',
            '{{ auth()->user()->client->id ?? "" }}'
        );
    });
    </script>
</x-app-layout>
