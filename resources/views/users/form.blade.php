<x-app-layout :assets="$assets ?? []">
    <div>
        @php
            $id = $id ?? null;
            $data = $data ?? null;
            $profileImage = $profileImage ?? asset('images/avatars/01.png');
        @endphp

        <form 
            action="{{ $id ? route('users.update', $id) : route('users.store') }}" 
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
                                <h4 class="card-title">{{ $id ? 'Editar' : 'Novo' }} Usuário</h4>
                            </div>
                            <div class="card-action">
                                <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href = document.referrer">Voltar</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="new-user-info">
                                <div class="row">
                                    {{-- Name fields --}}
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="fname">Nome <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" id="fname" class="form-control" value="{{ old('name', $data->name ?? '') }}" placeholder="Nome completo" required>
                                    </div>

                                    {{-- E-mail --}}
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $data->email ?? '') }}" placeholder="exemplo@email.com" required>
                                    </div>

                                    {{-- Password --}}

                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="pass">Senha <span class="text-danger">*</span></label>
                                        <input type="password" name="password" id="pass" class="form-control" placeholder="Digite sua senha" {{ !$id ? 'required' : '' }} {{ $id ? 'readonly' : '' }} >
                                    </div>

                                    {{-- Tipo Usuário --}}
                                    <div class="form-group col-md-6">
                                       <label class="form-label">Tipo Usuário <span class="text-danger">*</span></label>
                                       @php
                                          // Pega o valor antigo do formulário ou do modelo (edit), padrão 'user'
                                          $userType = old('type', $data->type ?? 'user');
                                       @endphp
                                       <select name="type" class="form-control" required>
                                          <option value="">Selecione o tipo</option>
                                          @foreach($roles as $key => $role)
                                                <option value="{{ $key }}" {{ $userType === $key ? 'selected' : '' }}>{{ $role }}</option>
                                          @endforeach
                                       </select>
                                    </div>

                                    <hr class="hr-horizontal"> 

                                    <div id="cliente-fields" style="display: none; margin-top: 20px;">
                                       <h5>Dados do Cliente</h5>
                                       <div class="row mt-3">
                                         @if($id)
                                          <div class="form-group col-md-6">
                                                <label for="client_id">ID Sequencial</label>
                                                <input type="text" class="form-control" value="{{ $data->client->id ?? '' }}" readonly>
                                          </div>
                                          @endif

                                          <div class="form-group col-md-6">
                                                <label for="client_name">Nome<span class="text-danger">*</span></label>
                                                <input type="text" name="client_name" id="client_name" class="form-control" placeholder="Nome completo" value="{{ old('client_name', $data->client->name ?? '') }}" readonly>
                                          </div>

                                          <div class="form-group col-md-6">
                                                <label for="client_city">Município<span class="text-danger">*</span></label>
                                                <input type="text" name="city" id="client_city" class="form-control" placeholder="Nome do município" value="{{ old('city',  $data->client->city ?? '') }}" required>
                                          </div>

                                          <div class="form-group col-md-6">
                                               <label for="estado">Estado<span class="text-danger">*</span></label>
                                                @php
                                                    $selectedUf = old('uf', $data->client->uf ?? '');
                                                @endphp
                                                <select name="uf" id="uf" class="form-select" required>
                                                    <option value="">Selecione...</option>
                                                    @foreach($estados as $key => $nome)
                                                        <option value="{{ $key }}" {{ $selectedUf == $key ? 'selected' : '' }}>
                                                            {{ $nome }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                          </div>

                                          <div class="form-group col-md-6">
                                                <label for="client_telefone">Telefone</label>
                                                <input type="text" name="phone" id="client_phone" class="form-control" placeholder="(11) 99999-9999" value="{{ old('phone', $data->client->phone ?? '') }}">
                                          </div>

                                          <div class="form-group col-md-6">
                                                <label for="client_email">E-mail<span class="text-danger">*</span></label>
                                                <input type="email" name="client_email" id="client_email" class="form-control" placeholder="exemplo@email.com" value="{{ old('client_email', $data->client->email ?? '') }}" readonly>
                                          </div>

                                          {{-- Tabela de preço --}}
                                          <div class="form-group col-md-6">
                                             <label for="price_table_id">Tabela de Preço<span class="text-danger">*</span></label>
                                             @php
                                                $selectedPriceTable = old('price_table_id', $data->client->price_table_id ?? '');
                                             @endphp
                                             <select name="price_table_id" id="price_table_id" class="form-control" required>
                                                <option value="">Selecione uma tabela</option>
                                                @foreach($priceTables as $priceTable)
                                                      <option value="{{ $priceTable->id }}" {{ $selectedPriceTable == $priceTable->id ? 'selected' : '' }}>
                                                         {{ $priceTable->name }}
                                                      </option>
                                                @endforeach
                                             </select>
                                          </div>
                                       </div>
</div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">{{ $id ? 'Atualizar' : 'Adicionar' }} Usuário</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script src="{{ asset('js/users-form.js') }}"></script>

</x-app-layout>
