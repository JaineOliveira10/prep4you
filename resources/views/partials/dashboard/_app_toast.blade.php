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
</script>