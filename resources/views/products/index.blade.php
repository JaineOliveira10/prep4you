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
           
            <div class="card-body pb-2">
               <form method="GET" action="{{ route('products.index') }}">
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
                           <th>Imagem</th>
                           <th>Nome</th>
                           <th>Tipo</th>
                           <th>SKU</th>
                           <th style="min-width: 100px">Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($products as $product)
                        <tr>
                           <td>
                              @if($product->photo_path)
                                 <img src="{{ asset('storage/' . $product->photo_path) }}" alt="Product-Photo" class="img-fluid rounded avatar-50" style="object-fit: cover;">
                              @else
                                 <div class="bg-light rounded d-flex align-items-center justify-content-center avatar-100">
                                    <i class="bi bi-image fs-1 text-muted"></i>
                                 </div>
                              @endif
                           </td>
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