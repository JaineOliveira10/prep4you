@push('scripts')

@endpush

<x-app-layout :assets="$assets ?? []">
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
                     <a href="{{route('shipments.create')}}" class="btn btn-sm btn-primary" role="button">Nova Remessa</a>
                  @endif
               </div>
            </div>
           
            <div class="card-body pb-2">
               <form method="GET" action="{{ route('shipments.index') }}">
                  <div class="row">
                     <div class="col-md-12">
                        <label id="client_id" class="pb-2">Cliente</label>
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
                  </div>
               </form>
            </div>

            <hr class="hr-horizontal">
            
            <div class="card-body px-0">
               <div class="table-responsive">
                  <table id="product-list-table" class="table table-striped" role="grid" data-toggle="data-table">
                     <thead>
                        <tr class="ligth">
                           <th>Nome Remessa</th>
                           <th>Qtd</th>
                           <th>Vr Total</th>
                           <th>Dt Remessa</th>
                           <th>Dt Coleta</th>
                           <th>Centro Dist.</th>
                           <th>Status</th>
                           <th style="min-width: 100px">Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($shipments as $shipment)
                        <tr>
                           <td>{{ $shipment->name ? $shipment->name : 'N/A' }}</td>
                           <td>{{ $shipment->total_items }}</td>
                           <td>R$ {{ number_format($shipment->total_value, 2, ',', '.') }}</td>
                           <td>{{ \Carbon\Carbon::parse($shipment->shipment_date)->format('d/m/Y') }}</td>
                           <td>{{ \Carbon\Carbon::parse($shipment->collection_date)->format('d/m/Y') }}</td>
                           <td>{{ $shipment->distributionCenter ? $shipment->distributionCenter->acronym : 'N/A' }}</td>
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
                              @include('shipments.action', ['id' => $shipment->id])
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