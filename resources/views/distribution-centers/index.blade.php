@push('scripts')

@endpush

<x-app-layout :assets="$assets ?? []">
<div>
   <div class="row">
      <div class="col-sm-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">Lista de Centros de Distribuição</h4>
               </div>
               <div class="card-action">
                  <a href="{{route('distribution-centers.create')}}" class="btn btn-sm btn-primary" role="button">Novo Centro de Distribuição</a>
               </div>
            </div>
            <div class="card-body px-0">
               <div class="table-responsive">
                  <table id="price-table-list-table" class="table table-striped" role="grid" data-toggle="data-table">
                     <thead>
                        <tr class="ligth">
                           <th>Sigla</th>
                           <th>Nome</th>
                           <th style="min-width: 100px">Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($distributionCenters as $distributionCenter)
                        <tr>
                           <td>{{ $distributionCenter->acronym }}</td>
                           <td>{{ $distributionCenter->name }}</td>
                           <td>
                              @include('distribution-centers.action', ['id' => $distributionCenter->id])
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