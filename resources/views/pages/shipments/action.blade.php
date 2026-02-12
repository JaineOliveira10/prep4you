<div class="flex align-items-center list-shipments-action">
    <a class="btn btn-sm btn-icon btn-info" data-bs-toggle="tooltip" title="Ver remessa" href="{{ route('shipments.show',$id) }}">
        <span class="btn-inner">
            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.5799 12C15.5799 13.98 13.9799 15.58 11.9999 15.58C10.0199 15.58 8.41992 13.98 8.41992 12C8.41992 10.02 10.0199 8.42 11.9999 8.42C13.9799 8.42 15.5799 10.02 15.5799 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M11.9998 20.27C15.5298 20.27 18.8198 18.19 21.1098 14.59C22.0098 13.18 22.0098 10.81 21.1098 9.4C18.8198 5.8 15.5298 3.72 11.9998 3.72C8.46984 3.72 5.17984 5.8 2.88984 9.4C1.98984 10.81 1.98984 13.18 2.88984 14.59C5.17984 18.19 8.46984 20.27 11.9998 20.27Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    </a>

    <a class="btn btn-sm btn-icon btn-warning" data-bs-toggle="tooltip" title="Editar remessa" href="#" onclick="checkStatusAndEdit(event, '{{ $id }}', '{{ $status }}')">
        <span class="btn-inner">
            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.4925 2.78906H7.75349C4.67849 2.78906 2.75049 4.96606 2.75049 8.04806V16.3621C2.75049 19.4441 4.66949 21.6211 7.75349 21.6211H16.5775C19.6625 21.6211 21.5815 19.4441 21.5815 16.3621V12.3341" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82812 10.921L16.3011 3.44799C17.2321 2.51799 18.7411 2.51799 19.6721 3.44799L20.8891 4.66499C21.8201 5.59599 21.8201 7.10599 20.8891 8.03599L13.3801 15.545C12.9731 15.952 12.4211 16.181 11.8451 16.181H8.09912L8.19312 12.401C8.20712 11.845 8.43412 11.315 8.82812 10.921Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M15.1655 4.60254L19.7315 9.16854" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    </a>

    @php
        $allowStatusChange = in_array($status, ['Pending', 'In Preparation', 'Has Pendency', 'Packed', 'Collected']);
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

    @if(($status == 'Collected' || $status == 'Invoice Generated' || $status == 'Paid') && auth()->user()->type == 'client')
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

    <a class="btn btn-sm btn-icon btn-primary" data-bs-toggle="tooltip" title="Baixar Ordem de Preparação" href="#" onclick="downloadPreparationOrder(event, '{{ $id }}')">
        <span class="btn-inner">
            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V9L13 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M13 2V9H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M12 13L12 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M15 16L12 19L9 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    </a>
    @endif

    <a class="btn btn-sm btn-icon btn-danger" data-bs-toggle="tooltip" title="Excluir remessa" href="#" onclick="checkStatusAndDelete(event, '{{ $id }}', '{{ $status }}', '{{ auth()->user()->type }}')">
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