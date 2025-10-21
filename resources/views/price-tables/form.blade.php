<x-app-layout :assets="$assets ?? []">
    <div>
        @php
            $id = $id ?? null;
            $data = $data ?? null;
            $profileImage = $profileImage ?? asset('images/avatars/01.png');
        @endphp

        <form 
            action="{{ $id ? route('price-tables.update', $id) : route('price-tables.store') }}" 
            method="POST" 
            enctype="multipart/form-data"
        >
            @csrf
            @if($id)
                @method('PATCH')
            @endif

            <div class="row">
                <div class="col-xl-12 col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">{{ $id ? 'Editar' : 'Nova' }} Tabela de Preço</h4>
                            </div>
                            <div class="card-action">
                                <a href="{{ route('price-tables.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="new-price-tables-info">
                                <div class="row">
                                    {{-- Name fields --}}
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="fname">Nome: <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" id="fname" class="form-control" value="{{ old('name', $data->name ?? '') }}" placeholder="Digite o nome" required>
                                    </div>

                                    {{-- E-mail --}}
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="email">Email: <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $data->email ?? '') }}" placeholder="Digite o e-mail" required>
                                    </div>

                                    {{-- Password --}}

                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="pass">Senha: <span class="text-danger">*</span></label>
                                        <input type="password" name="password" id="pass" class="form-control" placeholder="Password">
                                    </div>

                                    <hr class="hr-horizontal"> 

                                    <div id="cliente-fields" style="display: none; margin-top: 20px;">
                                       <h5>Dados do Cliente</h5>
                                       <div class="row mt-3">
                                          <div class="form-group col-md-6">
                                                <label for="client_name">Nome:</label>
                                                <input type="text" name="client_name" id="client_name" class="form-control" placeholder="Nome do cliente" value="{{ old('client_name', $data->client->name ?? '') }}" disabled>
                                          </div>

                                          <div class="form-group col-md-6">
                                                <label for="client_city">Cidade:</label>
                                                <input type="text" name="city" id="client_city" class="form-control" placeholder="Cidade" value="{{ old('city',  $data->client->city ?? '') }}">
                                          </div>

                                          <div class="form-group col-md-6">
                                                <label for="client_uf">Estado (Sigla):</label>
                                                <input type="text" name="uf" id="client_uf" class="form-control" placeholder="Sigla Estado" value="{{ old('uf', $data->client->uf ?? '') }}">
                                          </div>

                                          <div class="form-group col-md-6">
                                                <label for="client_telefone">Telefone:</label>
                                                <input type="text" name="phone" id="client_phone" class="form-control" placeholder="Telefone" value="{{ old('phone', $data->client->phone ?? '') }}">
                                          </div>

                                          <div class="form-group col-md-6">
                                                <label for="client_email">E-mail:</label>
                                                <input type="email" name="client_email" id="client_email" class="form-control" placeholder="E-mail" value="{{ old('client_email', $data->client->email ?? '') }}" disabled>
                                          </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">{{ $id ? 'Atualizar' : 'Adicionar' }} Tabela de Preço</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>
