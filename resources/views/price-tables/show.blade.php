<x-app-layout :assets="$assets ?? []">
   <div class="row">
      <div class="col-lg-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">{{ $priceTable->name }}</h4>
                  <p class="mt-2">{{ $priceTable->description }}</p> 
               </div>
               <div class="card-action">
                  <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href = document.referrer">Voltar</button>
                  <a href="{{ route('price-tables.edit', $priceTable->id) }}" class="btn btn-sm btn-warning">Editar</a>
               </div>
            </div>
            <div class="card-body">
               <div class="mb-3">
                  
               </div>
               
               <h5 class="mb-3">Faixas de Preço</h5>
               @if($priceTable->priceRanges->count() > 0)
                  <div class="table-responsive">
                     <table class="table table-striped">
                        <thead>
                           <tr>
                              <th>De (qtd)</th>
                              <th>Até (qtd)</th>
                              <th>Valor Etiqueta</th>
                              <th>Valor Kit</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach($priceTable->priceRanges as $range)
                           <tr>
                              <td>{{ number_format($range->min_value, 0, ',', '.') }}</td>
                              <td>{{ number_format($range->max_value, 0, ',', '.') }}</td>
                              <td>R$ {{ number_format($range->price, 2, ',', '.') }}</td>
                              <td>R$ {{ number_format($range->price_kit, 2, ',', '.') }}</td>
                           </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
               @else
                  <p class="text-muted">Nenhuma faixa de preço cadastrada.</p>
               @endif
            </div>
         </div>
      </div>
   </div>
</x-app-layout>