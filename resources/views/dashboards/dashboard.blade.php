<x-app-layout :assets="$assets ?? []">   <div class="row mb-4">
      @if(auth()->user()->type === 'client')
      <!-- Card: Produtos sem Foto -->
      @if(isset($productsWithoutPhoto) && $productsWithoutPhoto > 0)
         <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
               <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-3">
                     <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        📸
                     </div>
                  </div>
                  <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.85rem; letter-spacing: 0.5px;">Produtos sem Foto</h6>
                  <h3 class="fw-bold mb-2" style="color: #ffc107;">{{ $productsWithoutPhoto ?? 0 }}</h3>
                  <p class="text-muted mb-2" style="font-size: 0.9rem;">Complete o seu cadastro de produtos.</p>
                  <a href="{{ route('products.index') }}" style="font-size: 0.9rem;">Clique aqui para adicionar fotos</a>
               </div>
            </div>
         </div>
      @endif

      <!-- Card: Remessas Pendentes -->
      @if(isset($remessesWithIssues) && $remessesWithIssues > 0)
      <div class="col-lg-3 col-md-6 mb-3">
         <div class="card border-0 shadow-sm h-100" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
            <div class="card-body p-4">
               <div class="d-flex align-items-center mb-3">
                  <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                     ⏳
                  </div>
               </div>
               <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.85rem; letter-spacing: 0.5px;">Remessas com pendências</h6>
               <h3 class="fw-bold mb-2" style="color: #dc3545;">{{ $remessesWithIssues ?? 0 }}</h3>
               <p class="text-muted mb-2" style="font-size: 0.9rem;">Aguardando correção.</p>
               <a href="{{ route('shipments.index') }}" style="font-size: 0.9rem;">Clique aqui para corrigir</a>
            </div>
         </div>
      </div>
      @endif

      <!-- Card: Fechamentos -->
      @if(isset($unpaidClosures) && $unpaidClosures > 0)
      <div class="col-lg-3 col-md-6 mb-3">
         <div class="card border-0 shadow-sm h-100" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
            <div class="card-body p-4">
               <div class="d-flex align-items-center mb-3">
                  <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                     📊
                  </div>
               </div>
               <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.85rem; letter-spacing: 0.5px;">Fechamentos</h6>
               <h3 class="fw-bold mb-2" style="color: #17a2b8;">0</h3>
               <p class="text-muted mb-2" style="font-size: 0.9rem;">Aguardando pagamento.</p>
               <a href="#" style="font-size: 0.9rem;">Clique aqui para realizar pagamento</a>
            </div>
         </div>
      </div>
      @endif
   </div>

   @endif

</x-app-layout>
