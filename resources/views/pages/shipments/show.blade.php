<x-app-layout :assets="$assets ?? []">
   <div class="row">
      <div class="col-lg-12">
         <div class="card">
            <div class="card-body">
               <div class="d-flex flex-wrap align-items-center justify-content-between">
                  <div class="d-flex flex-wrap align-items-center">
                     <div class="d-flex flex-wrap align-items-center mb-3 mb-sm-0">
                        <h4 class="me-2 h4">{{ $shipment->name ?? $shipment->shipment_code }}</h4>
                        @php
                           $statusColors = [
                              'Pending' => 'warning',
                              'In Preparation' => 'info',
                              'Has Pendency' => 'danger',
                              'Packed' => 'secondary',
                              'Collected' => 'success',
                              'Invoice Generated' => 'secondary',
                              'Paid' => 'success'
                           ];
                           $statusColor = $statusColors[$shipment->status] ?? 'dark';
                        @endphp
                        <span class="badge bg-{{ $statusColor }}">
                           @if($shipment->status == 'Pending')
                              Pendente
                           @elseif($shipment->status == 'In Preparation')
                              Em Preparação
                           @elseif($shipment->status == 'Has Pendency')
                              Possui Pendências
                           @elseif($shipment->status == 'Packed')
                              Embalado
                           @elseif($shipment->status == 'Collected')
                              Coletado
                           @elseif($shipment->status == 'Invoice Generated')      
                              Gerado Fatura
                           @elseif($shipment->status == 'Paid')
                              Pago
                           @else
                              {{ $shipment->status }}
                           @endif</span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-lg-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">Informações da Remessa</h4>
               </div>            
               <div class="card-action">
                  <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href = document.referrer">Voltar</button>
               </div>
            </div>
            <div class="card-body">
               <!-- Informações Básicas -->
               <div class="row mb-4">
                  <div class="col-12">
                     <h6 class="text-primary border-bottom pb-2 mb-3">Informações Básicas</h6>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Cliente</label>
                        <p class="mb-0">{{ $shipment->client->name }}</p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Centro de Distribuição</label>
                        <p class="mb-0">{{ $shipment->distributionCenter->name }}</p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">ID da Remessa</label>
                        <p class="mb-0">{{ $shipment->shipment_code }}</p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Status</label>
                        <p class="mb-0">
                           <span class="badge bg-{{ $statusColor }}"> 
                              @if($shipment->status == 'Pending')
                                   Pendente
                              @elseif($shipment->status == 'In Preparation')
                                   Em Preparação
                              @elseif($shipment->status == 'Has Pendency')
                                   Possui Pendências
                              @elseif($shipment->status == 'Packed')
                                   Embalado
                              @elseif($shipment->status == 'Collected')
                                   Coletado
                              @elseif($shipment->status == 'Invoice Generated')      
                                   Gerado Fatura
                              @elseif($shipment->status == 'Paid')
                                    Pago  
                              @else
                                   <span class="badge bg-light text-dark">{{ $shipment->status }}</span>
                               @endif</span>
                        </p>
                     </div>
                  </div>
               </div>

               <!-- Datas -->
               <div class="row mb-4">
                  <div class="col-12">
                     <h6 class="text-primary border-bottom pb-2 mb-3">Datas</h6>
                  </div>
                  <div class="col-md-4">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Data de Criação</label>
                        <p class="mb-0">{{ \Carbon\Carbon::parse($shipment->creation_date)->format('d/m/Y') ?? 'Não informado' }}</p>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Data de Remessa</label>
                        <p class="mb-0">{{ \Carbon\Carbon::parse($shipment->shipment_date)->format('d/m/Y') ?? 'Não informado' }}</p>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Data de Coleta</label>
                        <p class="mb-0">{{ $shipment->collection_date ? \Carbon\Carbon::parse($shipment->collection_date)->format('d/m/Y') : 'Não informado' }}</p>
                     </div>
                  </div>
               </div>

               <!-- Informações de Quantidade e Valor -->
               <div class="row mb-4">
                  <div class="col-12">
                     <h6 class="text-primary border-bottom pb-2 mb-3">Informações de Quantidade e Valor</h6>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Total de Itens</label>
                        <p class="mb-0">{{ $shipment->total_items }}</p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Valor Total</label>
                        <p class="mb-0 text-success fw-bold">R$ {{ number_format($shipment->total_value, 2, ',', '.') }}</p>
                     </div>
                  </div>
               </div>

               @if($shipment->hasPendency() || $shipment->pendency_reason)
               <!-- Informações de Pendência -->
               <div class="row mb-4">
                  <div class="col-12">
                     <h6 class="text-primary border-bottom pb-2 mb-3">Informações de Pendência</h6>
                  </div>
                  <div class="col-12">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Motivo da Pendência</label>
                        <div class="bg-warning bg-opacity-10 p-3 border-start border-5 border-warning">
                           <p class="mb-0">{{ $shipment->pendency_reason ?? 'Não informado' }}</p>
                        </div>
                     </div>
                  </div>
               </div>
               @endif

               @if($shipment->observations)
               <!-- Observações -->
               <div class="row">
                  <div class="col-12">
                     <h6 class="text-primary border-bottom pb-2 mb-3">Observações</h6>
                     <div class="bg-secondary bg-opacity-10 p-3 border-start border-5 border-primary">
                        <p class="mb-0">{{ $shipment->observations }}</p>
                     </div>
                  </div>
               </div>
               @endif
            </div>
         </div>
      </div>

      <!-- Itens da Remessa -->
      <div class="col-lg-12">
         <div class="card">
            <div class="card-header">
               <div class="header-title">
                  <h4 class="card-title">Itens da Remessa</h4>
               </div>
            </div>
            <div class="card-body">
               @if($shipment->items->count() > 0)
                  <div style="overflow: visible;">
                     <table class="table table-striped">
                        <thead>
                           <tr>
                              <th style="width: 22%;">Produto</th>
                              <th style="width: 8%;">FSNKU</th>
                              <th style="width: 22%;">SKU</th>
                              <th style="width: 9%;">Tipo</th>
                              <th style="width: 5%;">Qtd Kit</th>
                              <th style="width: 7%;">Qtd</th>
                              <th style="width: 8%;">Preço Unit.</th>
                              <th style="width: 9%;">Valor Total</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach($shipment->items as $item)
                           <tr>
                              <td>
                                 @if($item->product)
                                    {{ $item->product->name }}
                                 @else
                                    {{ $item->name ?? 'Produto desconhecido' }}
                                 @endif
                              </td>
                              <td>{{ $item->fsnku ?? '-' }}</td>
                              <td>{{ $item->sku ?? '-' }}</td>
                              <td>
                                 @if($item->type === 'simple')
                                    Simples
                                 @elseif($item->type === 'kit')
                                    Kit
                                 @else
                                    S.Kit
                                 @endif
                              </td>
                              <td class="text-end">{{ $item->kit_units ?? '-' }}</td>
                              <td class="text-end">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                              <td class="text-end">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                              <td class="text-end">R$ {{ number_format($item->total_value, 2, ',', '.') }}</td>
                           </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
               @else
                  <p class="text-muted">Nenhum item cadastrado para esta remessa.</p>
               @endif
            </div>
         </div>
      </div>

      <!-- PDFs da Remessa -->
      <div class="col-lg-12">
         <div class="card">
            <div class="card-header">
               <div class="header-title">
                  <h4 class="card-title">PDFs da Remessa</h4>
               </div>
            </div>
            <div class="card-body">
               @if($shipment->pdfs->count() > 0)
                  <div class="table-responsive">
                     <table class="table table-striped">
                        <thead>
                           <tr>
                              <th>Nome do Arquivo</th>
                              <th>Tipo</th>
                              <th>Data de Upload</th>
                              <th>Ações</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach($shipment->pdfs as $pdf)
                           <tr>
                              <td>@if($pdf->type == 'individual_label') 
                                    Etiqueta Individual 
                                 @elseif($pdf->type == 'master_label')
                                    Etiqueta Master
                                 @elseif($pdf->type == 'invoice') 
                                    Nota Fiscal
                                 @else
                                    Arquivo
                                 @endif
                              </td>
                              <td>
                                 <span class="badge bg-light text-dark">PDF</span>
                              </td>
                              <td>{{ \Carbon\Carbon::parse($pdf->created_at)->format('d/m/Y H:i') }}</td>
                              <td>
                                 @if($pdf->path_pdf)
                                    <a href="{{ route('shipments.pdf.view', $pdf->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                       <i class="bi bi-download"></i> Download
                                    </a>
                                 @else
                                    <span class="text-muted">Arquivo não encontrado</span>
                                 @endif
                              </td>
                           </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
               @else
                  <p class="text-muted">Nenhum PDF cadastrado para esta remessa.</p>
               @endif
            </div>
         </div>
      </div>
   </div>
</x-app-layout>