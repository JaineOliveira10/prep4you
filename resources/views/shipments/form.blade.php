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
                                <button type="submit" class="btn btn-primary mt-3">{{ $id ? 'Atualizar' : 'Criar' }} Remessa</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
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
    </script>
</x-app-layout>
