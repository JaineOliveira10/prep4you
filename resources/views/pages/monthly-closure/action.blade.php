<div class="flex align-items-center gap-2">
   <button type="button" 
      class="btn btn-sm btn-icon btn-info" 
      data-bs-toggle="tooltip" 
      title="Mostrar Fechamento"
      onclick="showClosureModal({{ $closure->year }}, {{ $closure->month }}, {{ $client->id }})">
      <span class="btn-inner">
         <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"></path>
         </svg>
      </span>
   </button>

   <a href="{{ route('monthly-closures.download', $closure->id) }}" 
      class="btn btn-sm btn-icon btn-success" 
      data-bs-toggle="tooltip" 
      title="Baixar PDF">
      <span class="btn-inner">
         <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.5 13L12 16.5M12 16.5L15.5 13M12 16.5V3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M2.5 12C2.5 6.75329 6.75329 2.5 12 2.5C17.2467 2.5 21.5 6.75329 21.5 12C21.5 17.2467 17.2467 21.5 12 21.5C10.3431 21.5 8.75407 21.1143 7.36687 20.4057C6.51962 20.0181 5.52477 20.2707 5.22561 21.0272C4.90181 21.8567 5.45543 22.8127 6.38694 23.1272C8.17127 23.8137 10.0502 24.1429 12 24.1429" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
         </svg>
      </span>
   </a>

   @if(auth()->user()->type == 'admin')
   <button type="button" 
      class="btn btn-sm btn-icon btn-danger" 
      data-bs-toggle="tooltip" 
      title="Excluir fechamento"
      onclick="deleteClosureConfirm({{ $closure->id }}, {{ $client->id }})">
      <span class="btn-inner">
         <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
            <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
         </svg>
      </span>
   </button>
   @endif
</div>

<form action="{{ route('monthly-closures.destroy-client', ['closure' => $closure->id, 'client' => $client->id]) }}" 
      id="delete-closure-{{ $closure->id }}-{{ $client->id }}" 
      method="post" style="display: none;">
   @method('delete')
   @csrf
</form>