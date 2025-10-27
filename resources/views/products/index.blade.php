@push('scripts')

@endpush

<x-app-layout :assets="$assets ?? []">
<div>
   <div class="row">
      <div class="col-sm-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">Lista de Produtos</h4>
               </div>
               <div class="card-action">
                  @if(auth()->user()->type == 'client')
                     <a href="{{route('products.create')}}" class="btn btn-sm btn-primary" role="button">Novo Produto</a>
                  @endif
               </div>
            </div>
            @if(auth()->user()->role == 'admin')
               <div class="card-body pb-0">
                  <form method="GET" action="{{ route('products.index') }}">
                     <div class="row">
                        <div class="col-md-4">
                           <select name="client_id" class="form-control" onchange="this.form.submit()">
                              <option value="">Todos os clientes</option>
                              @foreach($clients as $client)
                                 <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                  </form>
               </div>
            @endif
            <div class="card-body px-0">
               <div class="table-responsive">
                  <table id="product-list-table" class="table table-striped" role="grid" data-toggle="data-table">
                     <thead>
                        <tr class="ligth">
                           <th>Nome</th>
                           <th>Tipo</th>
                           <th>ASIN</th>
                           <th>SKU</th>
                           <th style="min-width: 100px">Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($products as $product)
                        <tr>
                           <td>{{ $product->name }}</td>
                           <td>
                               @if($product->type == 'simple')
                                   <span class="badge bg-primary">Simples</span>
                               @elseif($product->type == 'kit')
                                   <span class="badge bg-warning">Kit</span>
                               @else
                                   <span class="badge bg-success">Super Kit</span>
                               @endif
                           </td>
                           <td>{{ $product->asin ?? '-' }}</td>
                           <td>{{ $product->sku ?? '-' }}</td>
                           <td>
                              @include('products.action', ['id' => $product->id])
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