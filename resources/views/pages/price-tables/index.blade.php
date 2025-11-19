@push('scripts')

@endpush

<x-app-layout :assets="$assets ?? []">
<div>
   <div class="row">
      <div class="col-sm-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">Lista de Tabela de Preços</h4>
               </div>
               <div class="card-action">
                  <a href="{{route('price-tables.create')}}" class="btn btn-sm btn-primary" role="button">Nova Tabela de Preço</a>
               </div>
            </div>
            <div class="card-body px-0">
               <div class="table-responsive">
                  <table id="price-table-list-table" class="table table-striped" role="grid" data-toggle="data-table">
                     <thead>
                        <tr class="ligth">
                           <th>Nome</th>
                           <th>Descrição</th>
                           <th style="min-width: 100px">Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($priceTables as $priceTable)
                        <tr>
                           <td>{{ $priceTable->name }}</td>
                           <td>{{ $priceTable->description }}</td>
                           <td>
                              @include('pages.price-tables.action', ['id' => $priceTable->id])
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