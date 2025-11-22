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
                <div class="col-xl-12">
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
                            <!-- Dados da Remessa -->
                            <div class="new-shipment-info">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="name">ID da Remessa</label>
                                        <input type="text" name="shipment_code" id="shipment_code" class="form-control" value="{{ old('shipment_code', $data->shipment_code ?? '') }}" placeholder="Digite o id da remessa">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="name">Nome da Remessa</label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $data->name ?? '') }}" placeholder="Digite o nome da remessa">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="creation_date">Data de Criação</label>
                                        <input type="date" name="creation_date" id="creation_date" class="form-control" value="{{ old('creation_date', $data->creation_date ?? now()->setTimezone('America/Sao_Paulo')->format('Y-m-d')) }}" disabled>
                                        <input type="hidden" name="creation_date" value="{{ old('creation_date', $data->creation_date ?? now()->setTimezone('America/Sao_Paulo')->format('Y-m-d')) }}">
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
                                        <input type="hidden" name="collection_date" value="{{ old('collection_date', $data->collection_date ?? '') }}">
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
                                        @if(auth()->user()->type == 'client')
                                            <input type="hidden" name="status" value="{{ old('status', $data->status ?? 'Pending') }}">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Itens da Remessa -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Itens da Remessa</h5>
                                <button type="button" class="btn btn-sm btn-success" id="add-item">Adicionar Item</button>
                            </div>
                            <div style="overflow: visible;">

                                <table class="table table-striped" id="items-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 22%;">Produto</th>
                                            <th style="width: 8%;">FSNKU</th>
                                            <th style="width: 22%;">SKU</th>
                                            <th style="width: 9%;">Tipo</th>
                                            <th style="width: 5%;">Qtd Kit</th>
                                            <th style="width: 7%;">Qtd</th>
                                            <th style="width: 8%;">Preço Unit.</th>
                                            <th style="width: 9%;">Valor Total</th>
                                            <th style="width: 7%;">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-tbody">
                                        @php
                                            $oldItems = old('items', []);
                                            $items = !empty($oldItems) ? $oldItems : (isset($data) && $data->items ? $data->items->toArray() : []);
                                        @endphp
                                        @if(!empty($items))
                                            @foreach($items as $index => $item)
                                                @php
                                                    $itemData = is_array($item) ? (object)$item : $item;
                                                    $selectedProduct = null;
                                                    if(isset($itemData->product_id)) {
                                                        $selectedProduct = $products->firstWhere('id', $itemData->product_id);
                                                    }
                                                @endphp
                                                <tr data-index="{{ $index }}">
                                                    <td>
                                                        <div class="position-relative">
                                                            <input type="text" class="form-control product-search" placeholder="Digite nome, SKU, FSNKU ou ASIN..." autocomplete="off" value="{{ $selectedProduct->name ?? '' }}">
                                                            <input type="hidden" name="items[{{ $index }}][product_id]" class="product-id" value="{{ $itemData->product_id ?? '' }}">
                                                            <div class="product-results position-absolute w-100 bg-white border rounded shadow-sm" style="z-index: 9999; max-height: 200px; overflow-y: auto; display: none; top: 100%;"></div>
                                                        </div>
                                                    </td>
                                                    <td><input type="text" class="form-control fsnku" value="{{ $selectedProduct->fsnku ?? ($itemData->fsnku ?? '') }}" disabled></td>
                                                    <td><input type="text" class="form-control sku" value="{{ $selectedProduct->sku ?? ($itemData->sku ?? '') }}" disabled></td>
                                                    <td><input type="text" class="form-control type_product" value="{{ $selectedProduct ? ($selectedProduct->type === 'simple' ? 'Simples' : ($selectedProduct->type === 'kit' ? 'Kit' : 'S.Kit')) : (isset($itemData->type) ? ($itemData->type === 'simple' ? 'Simples' : ($itemData->type === 'kit' ? 'Kit' : 'S.Kit')) : '') }}" disabled></td>
                                                    <td><input type="text" class="form-control kit-units" value="{{ $selectedProduct->kit_units ?? ($itemData->kit_units ?? '') }}" disabled></td>
                                                    <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" value="{{ $itemData->quantity ?? '' }}" min="1" required></td>
                                                    <td><input type="number" name="items[{{ $index }}][unit_price]" class="form-control unit-price" value="{{ $itemData->unit_price ?? '' }}" step="0.01" readonly></td>
                                                    <td><input type="number" class="form-control total-value" value="{{ $itemData->total_value ?? '' }}" step="0.01" disabled></td>
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
                                        <strong>Total Itens:</strong>
                                        <strong id="grand-total-items-display">0</strong>
                                        <input type="hidden" name="total_items" id="grand-total-items-input" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6 offset-md-6">
                                    <div class="d-flex justify-content-between">
                                        <strong>Total Geral:</strong>
                                        <strong id="grand-total">R$ 0,00</strong>
                                        <input type="hidden" name="total_value" id="grand-total-input" value="0">
                                    </div>
                                </div>
                            </div>


                            
                            <hr class="my-4">
                            
                        <!-- PDFs da Remessa -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Anexar arquivos</h5>
                            <button type="button" class="btn btn-sm btn-success" id="add-pdf">Adicionar PDF</button>
                        </div>
                        <!-- Upload de PDFs -->
                        <div class="mb-4">
                            <div id="pdf-uploads">
                                @php
                                    $oldPdfs = old('pdfs', []);
                                    // Na edição, sempre começa vazio. Na criação, usa old() se houver
                                    $pdfs = !empty($oldPdfs) ? $oldPdfs : [];
                                @endphp
                                @if(!empty($pdfs))
                                    @foreach($pdfs as $index => $pdf)
                                    @php
                                        $pdfData = is_array($pdf) ? (object)$pdf : $pdf;
                                    @endphp
                                    <div class="pdf-upload-item mb-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">Tipo de PDF</label>
                                                <select name="pdfs[{{ $index }}][tipo]" class="form-select">
                                                    <option value="">Selecione o tipo</option>
                                                    <option value="individual_label" {{ (isset($pdfData->tipo) && $pdfData->tipo == 'individual_label') ? 'selected' : '' }}>Etiqueta Individual</option>
                                                    <option value="master_label" {{ (isset($pdfData->tipo) && $pdfData->tipo == 'master_label') ? 'selected' : '' }}>Etiqueta Master</option>
                                                    <option value="invoice" {{ (isset($pdfData->tipo) && $pdfData->tipo == 'invoice') ? 'selected' : '' }}>Nota Fiscal</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Arquivo PDF</label>
                                                <input type="file" name="pdfs[{{ $index }}][pdf]" class="form-control" accept=".pdf">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-danger d-block remove-pdf" style="{{ count($pdfs) > 1 ? '' : 'display: none;' }}">Remover</button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                <div class="pdf-upload-item mb-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Tipo de PDF</label>
                                            <select name="pdfs[0][tipo]" class="form-select">
                                                <option value="">Selecione o tipo</option>
                                                <option value="individual_label">Etiqueta Individual</option>
                                                <option value="master_label">Etiqueta Master</option>
                                                <option value="invoice">Nota Fiscal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Arquivo PDF</label>
                                            <input type="file" name="pdfs[0][pdf]" class="form-control" accept=".pdf">
                                        </div>
                                        <div class="col-md-2 d-flex justify-content-end align-items-end">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger d-block remove-pdf" style="display: none;">Remover</button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                            
                            <hr class="my-4">
                            
                            <!-- Botão de Ação -->
                            <div >
                                <button type="submit" class="btn btn-primary">{{ $id ? 'Atualizar' : 'Criar' }} Remessa</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Lista de PDFs existentes -->
        @if(isset($data) && $data->pdfs && $data->pdfs->count() > 0)
        <div class="row mt-4">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Arquivos Enviados</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Arquivo</th>
                                        <th>Data Upload</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data->pdfs as $pdf)
                                    <tr>
                                        <td>
                                            @if($pdf->type == 'individual_label')
                                                <span class="badge bg-primary">Etiqueta Individual</span>
                                            @elseif($pdf->type == 'master_label')
                                                <span class="badge bg-info">Etiqueta Master</span>
                                            @elseif($pdf->type == 'invoice')
                                                <span class="badge bg-success">Nota Fiscal</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ asset('storage/' . $pdf->path_pdf) }}" target="_blank" class="text-decoration-none">
                                                <i class="fas fa-file-pdf text-danger"></i> Visualizar PDF
                                            </a>
                                        </td>
                                        <td>{{ $pdf->created_at->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a class="btn btn-sm btn-icon btn-danger" onclick="confirmDelete('pdf-delete-{{$pdf->id}}', 'Deseja realmente excluir este PDF?')" data-bs-toggle="tooltip" title="Excluir PDF" href="#">
                                                <span class="btn-inner">
                                                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                                        <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulários de delete dos PDFs (fora do formulário principal) -->
        @foreach($data->pdfs as $pdf)
        <form action="{{ route('shipments.pdf.destroy', $pdf->id) }}" id="pdf-delete-{{$pdf->id}}" method="POST" style="display: none;">
            @method('DELETE')
            @csrf
        </form>
        @endforeach
        @endif
    </div>

    <link rel="stylesheet" href="{{ asset('css/shipments-form.css') }}">
    <script src="{{ asset('js/shipments-form.js') }}"></script>
    <script src="{{ asset('js/shipments-pdf.js') }}"></script>
    <script>
        window.products = @json($products ?? []);
        window.shipmentRoutes = {
            calculateCollectionDate: '{{ route("shipments.calculate-collection-date") }}',
            getProductPrice: '{{ route("shipments.get-product-price") }}',
            getProductsByClient: '{{ route("shipments.get-products-by-client") }}'
        };
        window.csrfToken = '{{ csrf_token() }}';
        window.userType = '{{ auth()->user()->type }}';
        window.clientId = '{{ auth()->user()->client->id ?? "" }}';
    </script>

</x-app-layout>
