<x-app-layout :assets="$assets ?? []">

<link rel="stylesheet" href="{{ asset('css/shipments-import.css') }}">

<div>
   <div class="row">
      <div class="col-sm-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">Lista de Remessas</h4>
               </div>
               <div class="card-action">
                  @if(auth()->user()->type == 'client')
                     <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">Importar Remessa</button>
                  @endif
                  @if(auth()->user()->type == 'client')
                     <a href="{{route('shipments.create')}}" class="btn btn-sm btn-primary" role="button">Nova Remessa</a>
                  @endif
               </div>
            </div>
           
            <div class="card-body pb-2">
               <form method="GET" action="{{ route('shipments.index') }}">
                  <div class="row">
                     <div class="col-md-3">
                        <label for="client_id" class="pb-2">Cliente</label>
                        <select name="client_id" id="client_id" class="form-select"  onchange="this.form.submit()"
                           {{ auth()->user()->type == 'client' ? 'disabled' : '' }}>
                           @if(auth()->user()->type == 'client')
                              <option value="{{ auth()->user()->client->id }}" selected>
                                 {{ auth()->user()->client->name }}
                              </option>
                           @else 
                              <option value="">Todos os clientes</option>
                           @endif

                           @foreach($clients as $client)
                              <option value="{{ $client->id }}" 
                                 {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                 {{ $client->name }}
                              </option>
                           @endforeach
                        </select>
                     </div>

                     <div class="col-md-3">
                        <label for="status" class="pb-2">Status</label>
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                           <option value="">Todos os status</option>
                           <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pendente</option>
                           <option value="In Preparation" {{ request('status') == 'In Preparation' ? 'selected' : '' }}>Em Preparação</option>
                           <option value="Packed" {{ request('status') == 'Packed' ? 'selected' : '' }}>Embalado</option>
                           <option value="Collected" {{ request('status') == 'Collected' ? 'selected' : '' }}>Coletado</option>
                           <option value="Invoice Generated" {{ request('status') == 'Invoice Generated' ? 'selected' : '' }}>Fatura Gerada</option>
                           <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Pago</option>
                           <option value="Presents Errors" {{ request('status') == 'Presents Errors' ? 'selected' : '' }}>Apresenta Erros</option>
                        </select>
                     </div>

                     <div class="col-md-6">
                        <label for="date_filter" class="pb-2">Filtrar por</label>
                        <select name="date_filter" id="date_filter" class="form-select" onchange="this.form.submit()">
                           <option value="created_at" {{ request('date_filter', 'created_at') == 'created_at' ? 'selected' : '' }}>Data da Criação</option>
                           <option value="shipment_date" {{ request('date_filter') == 'shipment_date' ? 'selected' : '' }}>Data da Remessa</option>
                           <option value="collection_date" {{ request('date_filter') == 'collection_date' ? 'selected' : '' }}>Data da Coleta</option>
                        </select>
                     </div>
                  </div>

                  <div class="row mt-3">
                     <div class="col-md-4">
                        <label for="date_from" class="pb-2">Data Inicial</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ request('date_from', now()->subDays(30)->format('Y-m-d')) }}">
                     </div>

                     <div class="col-md-4">
                        <label for="date_to" class="pb-2">Data Final</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="{{ request('date_to', now()->format('Y-m-d')) }}">
                     </div>

                     <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        <a href="{{ route('shipments.index') }}" class="btn btn-secondary w-100">Limpar Filtros</a>
                     </div>
                  </div>
               </form>
            </div>

            <hr class="hr-horizontal">
            
            <div class="card-body px-0">
               <div class="table-responsive">
                  <table id="product-list-table" class="table table-striped" role="grid" data-toggle="data-table">
                     <thead>
                        <tr class="ligth">
                           <th>ID Remessa</th>
                           <th>Qtd</th>
                           <th>Vr Total</th>
                           <th>Dt Remessa</th>
                           <th>Dt Coleta</th>
                           <th>Centro Dist.</th>
                           <th>Importado?</th>
                           <th>Status</th>
                           <th style="min-width: 100px">Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($shipments as $shipment)
                        <tr>
                           <td>{{ $shipment->shipment_code ? $shipment->shipment_code : 'N/A' }}</td>
                           <td class="text-end">{{ $shipment->total_items }}</td>
                           <td class="text-end">{{ number_format($shipment->total_value, 2, ',', '.') }}</td>
                           <td>{{ \Carbon\Carbon::parse($shipment->shipment_date)->format('d/m/Y') }}</td>
                           <td>{{ \Carbon\Carbon::parse($shipment->collection_date)->format('d/m/Y') }}</td>
                           <td>{{ $shipment->distributionCenter ? $shipment->distributionCenter->acronym : 'N/A' }}</td>
                           <td>{{ $shipment->imported_flag ? 'Sim' : 'Não' }}</td>
                           <td>
                               @if($shipment->status == 'Pending')
                                   <span class="badge bg-warning">Pendente</span>
                               @elseif($shipment->status == 'In Preparation')
                                   <span class="badge bg-info">Em Preparação</span>
                               @elseif($shipment->status == 'Packed')
                                   <span class="badge bg-primary">Embalado</span>
                               @elseif($shipment->status == 'Collected')
                                   <span class="badge bg-secondary">Coletado</span>
                               @elseif($shipment->status == 'Invoice Generated')
                                   <span class="badge bg-dark">Fatura Gerada</span>
                               @elseif($shipment->status == 'Paid')
                                   <span class="badge bg-success">Pago</span>
                               @else
                                   <span class="badge bg-light text-dark">{{ $shipment->status }}</span>
                               @endif
                           </td>
                           <td>
                              @include('pages.shipments.action', ['id' => $shipment->id, 'status' => $shipment->status])
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
</div>

<!-- Modal de Importação -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-xl">
      <div class="modal-content">
         <form id="importForm" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
               <h5 class="modal-title" id="importModalLabel">Importar Remessa</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <div id="uploadSection">
                  <div class="mb-3">
                     <label for="tsvFile" class="form-label">Arquivo TSV</label>
                     <input type="file" class="form-control" id="tsvFile" name="tsv_file" accept=".tsv" required>
                     <div class="form-text">Selecione um arquivo .tsv para importar a remessa</div>
                  </div>
               </div>
               <div id="previewSection" style="display: none;">
                  <h6>Dados da Remessa:</h6>
                  <div class="mb-3 mt-3">
                     <label for="shipmentDate" class="form-label">Data da Remessa</label>
                     <input type="date" class="form-control" id="shipmentDate" name="shipment_date" required>
                  </div>
                  <div id="previewData" class="mt-4"></div>
                  <div id="totalsContainer" class="row mt-3 mx-3" style="display: none;">
                     <div class="col-md-6 offset-md-6">
                        <div class="d-flex justify-content-between">
                              <strong>Total Itens:</strong>
                              <strong id="modal-total-items">0</strong>
                        </div>
                     </div>
                     <div class="col-md-6 offset-md-6">
                        <div class="d-flex justify-content-between">
                              <strong>Total Geral:</strong>
                              <strong id="modal-total-value">R$ 0,00</strong>
                        </div>
                     </div>
                  </div>
               </div>
               <div id="resultSection" style="display: none;"> 
                  <div class="alert alert-success"> 
                     <h6>Remessa criada com sucesso!</h6> 
                  </div> 
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" id="previewBtn" class="btn btn-info" style="display: none;">Visualizar</button>
               <button type="submit" id="importBtn" class="btn btn-primary">Importar</button>
               <button type="button" id="createBtn" class="btn btn-success" style="display: none;">Criar Remessa</button>
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
         </form>
      </div>
   </div>
</div>

</x-app-layout>

@push('scripts')
<script>
    window.shipmentRoutes = {
        calculateCollectionDate: '{{ route("shipments.calculate-collection-date") }}',
        preview: '{{ route("shipments.preview") }}',
        import: '{{ route("shipments.import") }}',
        registerProduct: '{{ route("products.store.ajax") }}',
        getProductPrice: '{{ route("shipments.get-product-price") }}',
    };
    window.csrfToken = '{{ csrf_token() }}';

   $('#importForm').on('submit', function(e) {
      e.preventDefault();

      let formData = new FormData(this);

      $.ajax({
         url: window.shipmentRoutes.import,
         method: 'POST',
         data: formData,
         contentType: false,
         processData: false,

         headers: {
               'X-CSRF-TOKEN': window.csrfToken,
               'Accept': 'application/json'
         },

         success: function(res) {
               $('#uploadSection').hide();
               $('#previewSection').hide();

               $('#resultSection').html(`
                  <div class="alert alert-success">
                     <h6>Remessa criada com sucesso!</h6>
                  </div>
               `).show();
         },

         error: function(xhr) {
               console.log("Erro recebido:", xhr);

               let msg = xhr.responseJSON?.error ?? 'Erro inesperado';

               $('#uploadSection').hide();
               $('#previewSection').hide();

               $('#resultSection').html(`
                  <div class="alert alert-danger">
                     <h6>${msg}</h6>
                  </div>
               `).show();
         }
      });
   });

   // Função para inicializar tooltips
   function initializeTooltips() {
       const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
       tooltipTriggerList.forEach(function (tooltipTriggerEl) {
           new bootstrap.Tooltip(tooltipTriggerEl, {
               trigger: 'hover'
           });
       });
   }

   // Inicializar ao carregar
   document.addEventListener('DOMContentLoaded', function() {
       setTimeout(initializeTooltips, 500);
   });

   // Reinicializar ao clicar em filtros
   document.querySelectorAll('button[type="submit"], a.btn-secondary').forEach(btn => {
       btn.addEventListener('click', function() {
           setTimeout(initializeTooltips, 1000);
       });
   });
</script>
<script src="{{ asset('js/shipments-import.js') }}"></script>


