<x-app-layout>
    <div class="row">
        <div class="col-xl-6 col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Atualizar Senha - {{ $user->name }}</h4>
                    </div>
                    <div class="card-action">
                        <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href = document.referrer">Voltar</button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update-senha.post', $user->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <label class="form-label" for="password">Nova Senha <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Digite a nova senha" required>
                            @error('password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirmar Senha <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirme a nova senha" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Atualizar Senha</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>