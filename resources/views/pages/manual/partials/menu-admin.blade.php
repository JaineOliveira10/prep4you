<div class="list-group">
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'visao-geral' ? 'active' : '' }}" 
       href="{{ route('manual.show-admin', ['pagina' => 'visao-geral']) }}">
        Visão Geral
    </a>
    @if(auth()->user()->type === 'admin')
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'usuarios-administradores' ? 'active' : '' }}" 
       href="{{ route('manual.show-admin', ['pagina' => 'usuarios-administradores']) }}">
        Usuários Administradores
    </a>
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'tabelas-de-preco' ? 'active' : '' }}" 
       href="{{ route('manual.show-admin', ['pagina' => 'tabelas-de-preco']) }}">
        Tabelas de Preço
    </a>
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'clientes' ? 'active' : '' }}" 
       href="{{ route('manual.show-admin', ['pagina' => 'clientes']) }}">
        Clientes
    </a>
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'centro-distribuicao' ? 'active' : '' }}" 
       href="{{ route('manual.show-admin', ['pagina' => 'centro-distribuicao']) }}">
        Centro de Distribuição (CD)
    </a>
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'trocar-senha-usuario' ? 'active' : '' }}" 
       href="{{ route('manual.show-admin', ['pagina' => 'trocar-senha-usuario']) }}">
        Trocar Senha do Usuário
    </a>
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'produtos' ? 'active' : '' }}" 
       href="{{ route('manual.show-admin', ['pagina' => 'produtos']) }}">
        Produtos
    </a>
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'impressao-etiquetas' ? 'active' : '' }}" 
       href="{{ route('manual.show-admin', ['pagina' => 'impressao-etiquetas']) }}">
        Impressão de Etiquetas
    </a>
    <a class="list-group-item list-group-item-action {{ request()->route('pagina') === 'gerenciamento-remessas' ? 'active' : '' }}" 
       href="{{ route('manual.show-admin', ['pagina' => 'gerenciamento-remessas']) }}">
        Gerenciamento de Remessas
    </a>
    @endif
</div>