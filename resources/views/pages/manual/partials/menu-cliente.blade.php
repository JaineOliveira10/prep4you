<div class="list-group my-3">
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'visao-geral' ? 'active' : '' }}" 
       href="{{ route('manual.show-client', ['pagina' => 'visao-geral']) }}">
        Visão Geral
    </a>
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'gerenciamento-remessas' ? 'active' : '' }}" 
       href="{{ route('manual.show-client', ['pagina' => 'gerenciamento-remessas']) }}">
        Gerenciamento de Remessas
    </a>
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'cadastro-produtos' ? 'active' : '' }}" 
       href="{{ route('manual.show-client', ['pagina' => 'cadastro-produtos']) }}">
        Produtos
    </a>
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'trocar-senha-usuario' ? 'active' : '' }}" 
       href="{{ route('manual.show-client', ['pagina' => 'trocar-senha-usuario']) }}">
        Trocar Senha do Usuário
    </a>
</div>