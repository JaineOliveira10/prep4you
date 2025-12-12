<div class="flex align-items-center list-shipments-action">
    <a class="btn btn-sm btn-icon btn-warning" data-bs-toggle="tooltip" title="Editar remessa" href="#" onclick="checkStatusAndEdit(event, '{{ $id }}', '{{ $status }}')">
        <span class="btn-inner">
            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.4925 2.78906H7.75349C4.67849 2.78906 2.75049 4.96606 2.75049 8.04806V16.3621C2.75049 19.4441 4.66949 21.6211 7.75349 21.6211H16.5775C19.6625 21.6211 21.5815 19.4441 21.5815 16.3621V12.3341" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82812 10.921L16.3011 3.44799C17.2321 2.51799 18.7411 2.51799 19.6721 3.44799L20.8891 4.66499C21.8201 5.59599 21.8201 7.10599 20.8891 8.03599L13.3801 15.545C12.9731 15.952 12.4211 16.181 11.8451 16.181H8.09912L8.19312 12.401C8.20712 11.845 8.43412 11.315 8.82812 10.921Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M15.1655 4.60254L19.7315 9.16854" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    </a>

    <!-- Botão para alterar status (aparece apenas se o status permite) -->
    @php
        $allowStatusChange = in_array($status, ['Pending', 'In Preparation', 'Has Pendency', 'Packed']);
    @endphp

    @if($allowStatusChange && auth()->user()->type == 'admin')
        <a class="btn btn-sm btn-icon btn-info" data-bs-toggle="tooltip" title="Alterar status" href="#" onclick="showStatusChangeModal(event, '{{ $id }}', '{{ $status }}')">
            <span class="btn-inner">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.5 10.5H15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M8.5 15.5H15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M9 3.5H15C19.4183 3.5 22.5 6.58172 22.5 11V13C22.5 17.4183 19.4183 20.5 15 20.5H9C4.58172 20.5 1.5 17.4183 1.5 13V11C1.5 6.58172 4.58172 3.5 9 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </span>
        </a>
    @endif

    <!-- Botão para baixar comprovante de coleta (apenas se status é Coletado) -->
    @if($status == 'Collected' && auth()->user()->type == 'client')
        <a class="btn btn-sm btn-icon btn-success" data-bs-toggle="tooltip" title="Baixar comprovante de coleta" href="{{ route('shipments.download-proof', $id) }}">
            <span class="btn-inner">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.5 13L12 16.5M12 16.5L15.5 13M12 16.5V3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M2.5 12C2.5 6.75329 6.75329 2.5 12 2.5C17.2467 2.5 21.5 6.75329 21.5 12C21.5 17.2467 17.2467 21.5 12 21.5C10.3431 21.5 8.75407 21.1143 7.36687 20.4057C6.51962 20.0181 5.52477 20.2707 5.22561 21.0272C4.90181 21.8567 5.45543 22.8127 6.38694 23.1272C8.17127 23.8137 10.0502 24.1429 12 24.1429" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </span>
        </a>
    @endif

    @if(auth()->user()->type == 'admin')
    <a class="btn btn-sm btn-icon btn-success" data-bs-toggle="tooltip" title="Baixar PDFs" href="#" onclick="downloadShipmentPdfs(event, '{{ $id }}')">
        <span class="btn-inner">
            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.5 13L12 16.5M12 16.5L15.5 13M12 16.5V3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M2.5 12C2.5 6.75329 6.75329 2.5 12 2.5C17.2467 2.5 21.5 6.75329 21.5 12C21.5 17.2467 17.2467 21.5 12 21.5C10.3431 21.5 8.75407 21.1143 7.36687 20.4057C6.51962 20.0181 5.52477 20.2707 5.22561 21.0272C4.90181 21.8567 5.45543 22.8127 6.38694 23.1272C8.17127 23.8137 10.0502 24.1429 12 24.1429" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    </a>
    @endif

    <a class="btn btn-sm btn-icon btn-danger" data-bs-toggle="tooltip" title="Excluir remessa" href="#" onclick="checkStatusAndDelete(event, '{{ $id }}', '{{ $status }}')">
        <span class="btn-inner">
            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    </a>
    <form action="{{route('shipments.destroy',$id)}}" id="shipments-delete-{{$id}}" method="post">
        @method('delete')
        @csrf()
    </form>
</div>

<!-- Modal para alterar status -->
<div class="modal fade" id="statusChangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alterar Status da Remessa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="statusChangeForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="shipmentId" name="shipment_id">
                    <input type="hidden" id="newStatus" name="status">
                    <input type="hidden" name="_method" value="PATCH">
                    
                    <!-- Campo para motivo da pendência -->
                    <div id="pendencyReasonField" style="display: none;">
                        <label for="pendencyReason" class="form-label">Motivo da Pendência *</label>
                        <textarea class="form-control" id="pendencyReason" name="pendency_reason" rows="4" placeholder="Informe o motivo da pendência"></textarea>
                    </div>

                    <!-- Campo para comprovante de coleta -->
                    <div id="collectionProofField" style="display: none;">
                        <label for="collectionProof" class="form-label">Comprovante de Coleta (PDF ou Foto) *</label>
                        <input type="file" class="form-control" id="collectionProof" name="collection_proof" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">Formatos aceitos: PDF, JPG, JPEG, PNG</small>
                    </div>

                    <!-- Mensagem de confirmação para transições simples -->
                    <div id="confirmationMessage" style="display: none;">
                        <p id="confirmationText"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Alterar Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/shipments-action.js') }}"></script>