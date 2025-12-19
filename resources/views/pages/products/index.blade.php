@push('scripts')
<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#product-list-table')) {
        $('#product-list-table').DataTable().destroy();
    }
    $('#product-list-table').DataTable({
        "pageLength": 100
    });
});
</script>
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
                           <th>Nome<br>SKU</th>
                           <th>Tipo</th>
                           <th>FSNKU</th>
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
                                 <div class="bg-light rounded d-flex align-items-center justify-content-center avatar-50">
                                    <i class="bi bi-image fs-1 text-muted"></i>
                                 </div>
                              @endif
                           </td>
                           <td>{{ $product->name }}<br>{{ $product->sku ?? '-' }}</td>
                           <td>
                               @if($product->type == 'simple')
                                   <span class="badge bg-primary">Simples</span>
                               @elseif($product->type == 'kit')
                                   <span class="badge bg-secondary">Kit</span>
                               @else
                                   <span class="badge bg-success">Super Kit</span>
                               @endif
                           </td>
                           <td>{{ $product->fsnku ?? '-' }}</td>
                           <td>
                              @include('pages.products.action', ['id' => $product->id])
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

<!-- Modal Imprimir Etiquetas -->
<div class="modal fade" id="labelModal" tabindex="-1" aria-labelledby="labelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="labelModalLabel">Imprimir Etiquetas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="labelForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Produto</label>
                        <input type="text" class="form-control" id="productName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label fw-bold">Quantidade de Etiquetas</label>
                        <input type="number" id="quantity" class="form-control" name="quantity" min="1" max="1000" placeholder="0" required>
                        <small class="text-muted">Mínimo 1, máximo 1000</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="width" class="form-label fw-bold">Largura (mm)</label>
                                <input type="number" id="width" class="form-control" name="width" min="10" max="200" placeholder="0" step="0.1" required>
                                <small class="text-muted">10 a 200mm</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="height" class="form-label fw-bold">Altura (mm)</label>
                                <input type="number" id="height" class="form-control" name="height" min="10" max="200" placeholder="0" step="0.1" required>
                                <small class="text-muted">10 a 200mm</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="generateBtn">
                        <span id="btnText">Gerar Etiquetas</span>
                        <span id="spinner" class="spinner-border spinner-border-sm ms-2" style="display:none;" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentProductId = null;

function openLabelModal(productId) {
    currentProductId = productId;
    
    // Buscar dados do produto
    fetch(`/products/${productId}/json`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.product) {
                document.getElementById('productName').value = data.product.name;
                document.getElementById('labelForm').action = `/products/${productId}/generate-labels`;
                const modal = new bootstrap.Modal(document.getElementById('labelModal'));
                modal.show();
            } else {
                throw new Error('Dados inválidos');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar dados do produto');
        });
}

document.getElementById('labelForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const generateBtn = document.getElementById('generateBtn');
    const spinner = document.getElementById('spinner');
    const btnText = document.getElementById('btnText');
    
    // Desabilitar botão e mostrar spinner
    generateBtn.disabled = true;
    spinner.style.display = 'inline-block';
    btnText.textContent = 'Gerando...';
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na geração do PDF');
        }
        return response.blob();
    })
    .then(blob => {
        // Criar URL e fazer download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `etiquetas_${document.getElementById('productName').value}_${new Date().getTime()}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        // Fechar modal
        bootstrap.Modal.getInstance(document.getElementById('labelModal')).hide();
        
        // Resetar formulário
        document.getElementById('labelForm').reset();
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao gerar PDF: ' + error.message);
    })
    .finally(() => {
        // Reabilitar botão
        generateBtn.disabled = false;
        spinner.style.display = 'none';
        btnText.textContent = 'Gerar PDF';
    });
});
</script>
@endpush
</x-app-layout>