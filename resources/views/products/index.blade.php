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
                  <a href="{{route('products.create')}}" class="btn btn-sm btn-primary" role="button">Novo Produto</a>
               </div>
            </div>
            <div class="card-body px-0">
               <div class="table-responsive">
                  <table id="product-list-table" class="table table-striped" role="grid" data-toggle="data-table">
                     <thead>
                        <tr class="ligth">
                           <th>Nome</th>
                           <th>Descrição</th>
                           <th style="min-width: 100px">Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($products as $products)
                        <tr>
                           <td>{{ $products->name }}</td>
                           <td>{{ $products->type }}</td>
                           <td>
                              @include('products.action', ['id' => $products->id])
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