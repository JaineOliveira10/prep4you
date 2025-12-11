<x-app-layout :assets="$assets ?? []">

<link rel="stylesheet" href="{{ asset('css/shipments-import.css') }}">

<div>
   <div class="row">
      <div class="col-sm-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">Gerenciamento de Remessas</h4>
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
               <form method="GET" action="{{ route('shipments.manage-shipments') }}">
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
                           <option value="Has Pendency" {{ request('status') == 'Has Pendency' ? 'selected' : '' }}>Apresenta Pendência</option>
                           <option value="Packed" {{ request('status') == 'Packed' ? 'selected' : '' }}>Embalado</option>
                           <option value="Collected" {{ request('status') == 'Collected' ? 'selected' : '' }}>Coletado</option>
                        </select>
                     </div>

                     <div class="col-md-3">
                        <label for="distribution_center_id" class="pb-2">Centro de Distribuição</label>
                        <select name="distribution_center_id" id="distribution_center_id" class="form-select" onchange="this.form.submit()">
                           <option value="">Todos os centros</option>
                           @forelse($distribution_centers as $distribution_center)
                              <option value="{{ $distribution_center->id }}" 
                                 {{ request('distribution_center_id') == $distribution_center->id ? 'selected' : '' }}>
                                 {{ $distribution_center->name }}
                              </option>
                           @empty
                              <option disabled>Nenhum centro disponível</option>
                           @endforelse
                        </select>
                     </div>

                     <div class="col-md-3">
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
                        <a href="{{ route('shipments.manage-shipments') }}" class="btn btn-secondary w-100">Limpar Filtros</a>
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
                           <th>Dt Criação</th>
                           <th>Dt Remessa</th>
                           <th>Dt Coleta</th>
                           <th>Cliente</th>
                           <th>Status</th>
                           <th>Centro Dist.</th>
                           <th>Qtd</th>
                           <th>Vr Total</th>                         
                           <th style="min-width: 100px">Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($shipments as $shipment)
                        <tr>
                           <td>{{ \Carbon\Carbon::parse($shipment->creation_date)->format('d/m/Y') }}</td>
                           <td>{{ \Carbon\Carbon::parse($shipment->shipment_date)->format('d/m/Y') }}</td>
                           <td>{{ \Carbon\Carbon::parse($shipment->collection_date)->format('d/m/Y') }}</td>
                           <td>{{ $shipment->client ? $shipment->client->name : 'N/A' }}</td>
                           <td>
                              <select class="form-select form-select-sm status-select" 
                                    data-shipment-id="{{ $shipment->id }}"
                                    data-current-status="{{ $shipment->status }}">
                                  <option value="">{{ ucfirst(str_replace('-', ' ', $shipment->status)) }}</option>
                                  @php
                                      $validTransitions = match($shipment->status) {
                                          'Pending' => ['In Preparation'],
                                          'In Preparation' => ['Has Pendency', 'Packed'],
                                          'Has Pendency' => ['In Preparation'],
                                          'Packed' => ['Collected'],
                                          'Collected' => [],
                                          default => []
                                      };
                                      
                                      $statusLabels = [
                                          'Pending' => 'Pendente',
                                          'In Preparation' => 'Em Preparação',
                                          'Has Pendency' => 'Há Pendências',
                                          'Packed' => 'Embalado',
                                          'Collected' => 'Coletado'
                                      ];
                                  @endphp
                                  @foreach($validTransitions as $status)
                                      <option value="{{ $status }}">{{ $statusLabels[$status] ?? $status }}</option>
                                  @endforeach
                              </select>
                           </td>
                           <td>{{ $shipment->distributionCenter ? $shipment->distributionCenter->acronym : 'N/A' }}</td>
                           <td class="text-end">{{ number_format($shipment->total_items, 0, ',', '.') }}</td>
                           <td class="text-end">{{ number_format($shipment->total_value, 2, ',', '.') }}</td>
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
</x-app-layout>

@push('scripts')


