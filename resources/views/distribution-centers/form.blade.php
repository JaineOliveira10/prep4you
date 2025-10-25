<x-app-layout :assets="$assets ?? []">
    <div>
        @php
            $id = $id ?? null;
            $data = $data ?? null;
            $profileImage = $profileImage ?? asset('images/avatars/01.png');
        @endphp
        <form 
            action="{{ $id ? route('distribution-centers.update', $id) : route('distribution-centers.store') }}" 
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
                                <h4 class="card-title">{{ $id ? 'Editar' : 'Novo' }} Centro de Distribuição</h4>
                            </div>
                            <div class="card-action">
                                <a href="{{ route('distribution-centers.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="new-distribution-center-info">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="acronym">Sigla <span class="text-danger">*</span></label>
                                        <input type="text" name="acronym" id="acronym" class="form-control" value="{{ old('acronym', $data->acronym ?? '') }}" placeholder="Sigla do centro de distribuição" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label" for="name">Nome <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $data->name ?? '') }}" placeholder="Digite o nome" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">{{ $id ? 'Atualizar' : 'Adicionar' }} Centro de Distribuição</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>
