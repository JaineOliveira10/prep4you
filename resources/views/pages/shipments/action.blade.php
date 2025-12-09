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

<script>
function checkStatusAndEdit(event, id, status) {
    event.preventDefault();
    const allowedStatuses = ['Pending', 'Apresenta Errors'];
    
    if (!allowedStatuses.includes(status)) {
        Swal.fire({
            icon: 'warning',
            title: 'Ação não permitida',
            text: `Você só pode editar remessas com status "Pendente" ou "Apresenta Erros".`,
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    window.location.href = `/shipments/${id}/edit`;
}

function downloadShipmentPdfs(event, id) {
    event.preventDefault();
    
    // Redireciona para a rota de download
    window.location.href = `/shipments/${id}/download-pdfs`;
}

function checkStatusAndDelete(event, id, status) {
    event.preventDefault();
    const allowedStatuses = ['Pending', 'Apresenta Errors'];
    
    if (!allowedStatuses.includes(status)) {
        Swal.fire({
            icon: 'warning',
            title: 'Ação não permitida',
            text: `Você só pode excluir remessas com status "Pendente" ou "Apresenta Erros".`,
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    confirmDelete('shipments-delete-' + id, 'Deseja realmente excluir esta remessa?');
}
</script>
