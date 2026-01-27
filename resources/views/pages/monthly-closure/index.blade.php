<x-app-layout :assets="$assets ?? []">

<div>
   <div class="row">
      <div class="col-sm-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">Fechamentos Mensais</h4>
               </div>
               <div class="card-action">
                  @if(auth()->user()->type == 'admin')
                     <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newClosureModal">Novo Fechamento</button>
                     <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#printClosureModal">Imprimir Fechamentos</button>
                  @endif
               </div>
            </div>
           
            <div class="card-body pb-2">
               <form method="GET" action="{{ route('monthly-closures.index') }}">
                  <div class="row">
                     <div class="col-md-4">
                        <label for="year_month" class="pb-2">Ano/Mês</label>
                        <input type="month" name="year_month" id="year_month" class="form-control" 
                           value="{{ request('year_month', now()->format('Y-m')) }}">
                     </div>

                     <div class="col-md-4">
                        <label for="client_id" class="pb-2">Cliente</label>
                        <select name="client_id" id="client_id" class="form-select">
                           <option value="">Todos os clientes</option>
                           @foreach($clients as $client)
                              <option value="{{ $client->id }}" 
                                 {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                 {{ $client->name }}
                              </option>
                           @endforeach
                        </select>
                     </div>

                     <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        <a href="{{ route('monthly-closures.index') }}" class="btn btn-secondary w-100">Limpar Filtros</a>
                     </div>
                  </div>
               </form>
            </div>

            <hr class="hr-horizontal">
            
            <div class="card-body px-0">
               <div class="table-responsive">
                  <table id="closure-list-table" class="table table-striped" role="grid" @if(count($closures) > 0) data-toggle="data-table" @endif>
                     <thead>
                        <tr class="ligth">
                           <th>Ano/Mês</th>
                           <th>Cliente</th>
                           <th class="text-end">Valor Bruto</th>
                           <th class="text-end">Desconto</th>
                           <th class="text-end">Valor Líquido</th>
                           <th style="min-width: 120px">Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        @if(count($closures) > 0)
                           @forelse($closures as $closure)
                              @if($closure->clients && count($closure->clients) > 0)
                                 @foreach($closure->clients as $client)
                                 <tr>
                                    <td>{{ \Carbon\Carbon::createFromDate($closure->year, $closure->month, 1)->format('m/Y') }}</td>
                                    <td>{{ $client->name ?? 'N/A' }}</td>
                                    <td class="text-end">R$ {{ number_format($client->pivot->total_gross, 2, ',', '.') }}</td>
                                    <td class="text-end">R$ {{ number_format($client->pivot->total_discount, 2, ',', '.') }}</td>
                                    <td class="text-end"><strong>R$ {{ number_format($client->pivot->total_net, 2, ',', '.') }}</strong></td>
                                    <td>
                                       @include('pages.monthly-closure.action')
                                    </td>
                                 </tr>
                                 @endforeach
                              @endif
                           @empty
                           <tr>
                              <td colspan="6" class="text-center py-4 text-muted">
                                 Nenhum fechamento encontrado
                              </td>
                           </tr>
                           @endforelse
                        @else
                        <tr>
                           <td colspan="6" class="text-center py-4 text-muted">
                              Nenhum fechamento encontrado
                           </td>
                        </tr>
                        @endif
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Modal de Novo Fechamento -->
<div class="modal fade" id="newClosureModal" tabindex="-1" aria-labelledby="newClosureModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-xl">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="newClosureModalLabel">Novo Fechamento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div id="step1Selection">
               <div class="mb-3">
                  <label for="year_month" class="form-label">Ano/Mês <span class="text-danger">*</span></label>
                  <input type="month" class="form-control" id="year_month" name="year_month" required onchange="document.getElementById('year_month').setAttribute('data-value', this.value); console.log('Input mudou para:', this.value);">
               </div>

               <div class="mb-3">
                  <label for="closure_client_id" class="form-label">Cliente <small class="text-muted">(Opcional - deixe em branco para gerar para todos os clientes)</small></label>
                  <select class="form-select" id="closure_client_id" name="client_id">
                     <option value="">Gerar para todos os clientes com remessas</option>
                     @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                     @endforeach
                  </select>
               </div>
            </div>

            <div id="step2Preview" style="display: none;">
               <div class="row mb-3">
                  <div class="col-md-12">
                     <strong>Ano/Mês</strong>
                     <div id="new-closure-month" class="mb-3"></div>
                  </div>
               </div>

               <hr>

               <div id="closuresContainer"></div>

               <hr>
            </div>

            <div id="loadingMessage" style="display: none;" class="text-center py-4">
               <div class="spinner-border" role="status">
                  <span class="visually-hidden">Carregando...</span>
               </div>
               <p class="mt-2">Carregando dados do fechamento...</p>
            </div>
         </div>
         <div class="modal-footer">
            <div id="step1Footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
               <button type="button" class="btn btn-primary" onclick="previewNewClosure()">Mostrar Fechamento</button>
            </div>
            <div id="step2Footer" style="display: none;">
               <button type="button" class="btn btn-secondary" onclick="backToStep1()">Voltar</button>
               <button type="button" class="btn btn-success" onclick="saveNewClosure()">Confirmar Fechamento</button>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Modal de Visualizar Fechamento -->
<div class="modal fade" id="viewClosureModal" tabindex="-1" aria-labelledby="viewClosureModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-xl">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="viewClosureModalLabel">Visualizar Fechamento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div id="viewClosureLoadingMessage" style="display: none;" class="text-center py-4">
               <div class="spinner-border" role="status">
                  <span class="visually-hidden">Carregando...</span>
               </div>
               <p class="mt-2">Carregando dados do fechamento...</p>
            </div>

            <div id="viewClosureContent">
               <div class="row mb-3">
                  <div class="col-md-6">
                     <strong>Ano/Mês</strong>
                     <div id="view-closure-month"></div>
                  </div>
                  <div class="col-md-6">
                     <strong>Cliente</strong>
                     <div id="view-closure-client"></div>
                  </div>
               </div>

               <hr>

               <div class="row mb-3">
                  <div class="col-md-4">
                     <strong>Etiquetas Simples</strong>
                     <div id="view-closure-simple-labels"></div>
                  </div>
                  <div class="col-md-4">
                     <strong>Etiquetas Kit</strong>
                     <div id="view-closure-kit-labels"></div>
                  </div>
                  <div class="col-md-4">
                     <strong>Etiquetas Super Kit</strong>
                     <div id="view-closure-superkit-labels"></div>
                  </div>
               </div>

               <div class="row mb-3">
                  <div class="col-md-4">
                     <strong>Unitário Simples</strong>
                     <div id="view-closure-unit-simple"></div>
                  </div>
                  <div class="col-md-4">
                     <strong>Unitário Kit</strong>
                     <div id="view-closure-unit-kit"></div>
                  </div>
                  <div class="col-md-4">
                     <strong>Unitário Super Kit</strong>
                     <div id="view-closure-unit-superkit">-</div>
                  </div>
               </div>

               <div class="row mb-3">
                  <div class="col-md-4">
                     <strong>Valor Simples</strong>
                     <div id="view-closure-simple-net"></div>
                  </div>
                  <div class="col-md-4">
                     <strong>Valor Kit</strong>
                     <div id="view-closure-kit-net"></div>
                  </div>
                  <div class="col-md-4">
                     <strong>Valor Super Kit</strong>
                     <div id="view-closure-superkit-value"></div>
                  </div>
               </div>

               <hr>

               <div class="row mb-3">
                  <div class="col-md-4">
                     <strong>Valor Bruto</strong>
                     <div id="view-closure-gross" class="h5"></div>
                  </div>
                  <div class="col-md-4">
                     <strong>Desconto</strong>
                     <div id="view-closure-discount" class="h5 text-danger"></div>
                  </div>
                  <div class="col-md-4">
                     <strong>Valor Líquido</strong>
                     <div id="view-closure-net" class="h5 text-success"></div>
                  </div>
               </div>

               <hr>

               <div class="row mb-3">
                  <div class="col-md-4">
                     <strong>Faixa de Preço</strong>
                     <div id="view-closure-price-range"></div>
                  </div>
                  <div class="col-md-4">
                     <strong>Desconto referente às etiquetas simples</strong>
                     <div id="view-closure-simple-discount"></div>
                  </div>
                  <div class="col-md-4">
                     <strong>Desconto referente às etiquetas kit</strong>
                     <div id="view-closure-kit-discount"></div>
                  </div>
               </div>

               <hr>

               <strong>Remessas Incluídas:</strong>
               <div id="view-closure-shipments" class="mt-2"></div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
         </div>
      </div>
   </div>
</div>

<!-- Modal de Imprimir Fechamentos -->
<div class="modal fade" id="printClosureModal" tabindex="-1" aria-labelledby="printClosureModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="printClosureModalLabel">Imprimir Fechamentos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="mb-3">
               <label for="print_year_month" class="form-label">Selecione o Período <span class="text-danger">*</span></label>
               <input type="month" class="form-control" id="print_year_month" name="print_year_month" 
                  value="{{ now()->format('Y-m') }}" required>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="printMonthlyClosures()">Gerar PDF</button>
         </div>
      </div>
   </div>
</div>

@push('css')
<style>
   /* Tornar modais scrolláveis com conteúdo grande */
   #newClosureModal .modal-body,
   #viewClosureModal .modal-body {
      max-height: calc(100vh - 200px);
      overflow-y: auto;
   }
   
   /* Garantir que tabelas em modais sejam responsivas */
   #closuresContainer .table {
      margin-bottom: 0;
   }
   
   #closuresContainer {
      overflow-x: auto;
   }
</style>
@endpush

@push('scripts')
<script>
   window.storeRoute = '{{ route("monthly-closures.store") }}';
   window.previewPdfRoute = '{{ route("monthly-closures.preview-pdf") }}';
   window.printPdfRoute = '{{ route("monthly-closures.print-pdf") }}';
</script>
<script src="{{ asset('js/monthly-closure.js') }}"></script>
@endpush

</x-app-layout>

