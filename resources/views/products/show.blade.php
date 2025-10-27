<x-app-layout :assets="$assets ?? []">
   <div class="row">
      <div class="col-lg-12">
         <div class="card">
            <div class="card-body">
               <div class="d-flex flex-wrap align-items-center justify-content-between">
                  <div class="d-flex flex-wrap align-items-center">
                     <div class="profile-img position-relative me-3 mb-3 mb-lg-0">
                        @if($product->photo_path)
                           <img src="{{ asset('storage/' . $product->photo_path) }}" alt="Product-Photo" class="img-fluid rounded avatar-100" style="object-fit: cover;">
                        @else
                           <div class="bg-light rounded d-flex align-items-center justify-content-center avatar-100">
                              <i class="bi bi-image fs-1 text-muted"></i>
                           </div>
                        @endif
                     </div>
                     <div class="d-flex flex-wrap align-items-center mb-3 mb-sm-0">
                        <h4 class="me-2 h4">{{ $product->name }}</h4>
                        @if($product->type == 'simple')
                           <span class="badge bg-primary">Item Simples</span>
                        @elseif($product->type == 'kit')
                           <span class="badge bg-warning">Kit</span>
                        @else
                           <span class="badge bg-success">Super Kit</span>
                        @endif
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
                  <h4 class="card-title">Informações do Produto</h4>
               </div>            
               <div class="card-action">
                  <a href="{{ route('products.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                  @if(auth()->user()->type == 'client' || (auth()->user()->type == 'client' && $product->client_id == auth()->user()->client_id))
                     <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">Editar</a>
                  @endif
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
                        <p class="mb-0">{{ $product->client->name }}</p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Tipo</label>
                        <p class="mb-0">
                           @if($product->type == 'simple')
                              <span class="badge bg-primary">Item Simples</span>
                           @elseif($product->type == 'kit')
                              <span class="badge bg-warning">Kit</span>
                           @else
                              <span class="badge bg-success">Super Kit</span>
                           @endif
                        </p>
                     </div>
                  </div>
               </div>

               <!-- Códigos de Identificação -->
               <div class="row mb-4">
                  <div class="col-12">
                     <h6 class="text-primary border-bottom pb-2 mb-3">Códigos de Identificação</h6>
                  </div>
                  <div class="col-md-4">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">ASIN</label>
                        <p class="mb-0">{{ $product->asin ?? 'Não informado' }}</p>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">FSNKU</label>
                        <p class="mb-0">{{ $product->fsnku ?? 'Não informado' }}</p>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">SKU</label>
                        <p class="mb-0">{{ $product->sku ?? 'Não informado' }}</p>
                     </div>
                  </div>
               </div>

               @if($product->type != 'simple' || ($product->type == 'super_kit' && $product->unit_price))
               <!-- Informações do Kit -->
               <div class="row mb-4">
                  <div class="col-12">
                     <h6 class="text-primary border-bottom pb-2 mb-3">Informações do Kit</h6>
                  </div>
                  @if($product->type != 'simple')
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Unidades no Kit</label>
                        <p class="mb-0">{{ $product->kit_units }}</p>
                     </div>
                  </div>
                  @endif
                  @if($product->type == 'super_kit' && $product->unit_price)
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Preço Unitário</label>
                        <p class="mb-0 text-success fw-bold">R$ {{ number_format($product->unit_price, 2, ',', '.') }}</p>
                     </div>
                  </div>
                  @endif
               </div>
               @endif

               @if($product->observation)
               <!-- Observações -->
               <div class="row">
                  <div class="col-12">
                     <h6 class="text-primary border-bottom pb-2 mb-3">Observações</h6>
                     <div class="bg-warning bg-opacity-10 p-3 border-start border-5 border-warning">
                        <p class="mb-0">{{ $product->observation }}</p>
                     </div>
                  </div>
               </div>
               @endif
            </div>
         </div>
      </div>
   </div>
</x-app-layout>