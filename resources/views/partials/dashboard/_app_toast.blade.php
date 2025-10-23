<script type="text/javascript">
    {{-- Success Message --}}
    @if (Session::has('success'))
    Swal.fire({
    icon: 'success',
    title: 'Sucesso',
    text: '{{ Session::get("success") }}',
    confirmButtonColor: "#f27916ff"
    });
    @endif
    {{-- Errors Message --}}
    @if (Session::has('error'))
    Swal.fire({
    icon: 'error',
    title: 'Oops!!!',
    text: '{{Session::get("error")}}',
    confirmButtonColor: "#f27916ff"
    });
    @endif
    @if(Session::has('errors') || ( isset($errors) && is_array($errors) && $errors->any()))
    Swal.fire({
    icon: 'error',
    title: 'Oops!!!',
    text: '{{Session::get("errors")->first() }}',
    confirmButtonColor: "#f27916ff"
    });
    @endif
    
    function confirmDelete(formId, message = 'Tem certeza que deseja excluir este item?') {
        Swal.fire({
            icon: 'warning',
            title: 'Confirmar Exclusão',
            text: message,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script>