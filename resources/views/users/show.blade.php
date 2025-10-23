<x-app-layout :assets="$assets ?? []">
   <div class="row">
      <div class="col-lg-12">
         <div class="card">
            <div class="card-body">
               <div class="d-flex flex-wrap align-items-center justify-content-between">
                  <div class="d-flex flex-wrap align-items-center">
                     <div class="profile-img position-relative me-3 mb-3 mb-lg-0">
                        <img src="{{ $profileImage ?? asset('images/avatars/01.png')}}" alt="User-Profile" class="theme-color-default-img img-fluid rounded-pill avatar-100">
                        <img src="{{asset('images/avatars/avtar_1.png')}}" alt="User-Profile" class="theme-color-purple-img img-fluid rounded-pill avatar-100">
                        <img src="{{asset('images/avatars/avtar_2.png')}}" alt="User-Profile" class="theme-color-blue-img img-fluid rounded-pill avatar-100">
                        <img src="{{asset('images/avatars/avtar_4.png')}}" alt="User-Profile" class="theme-color-green-img img-fluid rounded-pill avatar-100">
                        <img src="{{asset('images/avatars/avtar_5.png')}}" alt="User-Profile" class="theme-color-yellow-img img-fluid rounded-pill avatar-100">
                        <img src="{{asset('images/avatars/avtar_3.png')}}" alt="User-Profile" class="theme-color-pink-img img-fluid rounded-pill avatar-100">
                     </div>
                     <div class="d-flex flex-wrap align-items-center mb-3 mb-sm-0">
                        <h4 class="me-2 h4">{{ $user->name ?? ''  }}</h4>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-lg-12">
         <div class="card">
         <div class="card-header">
            <div class="header-title">
               <h4 class="card-title">Meu Perfil</h4>
            </div>
         </div>
         <div class="card-body">
            <p>Aqui você pode visualizar e gerenciar as informações do seu perfil de usuário.</p>
            <div class="mb-1">Email: {{ $user->email }}</div>
            @if($user->type == 'client')
                <div class="mb-1">Telefone: {{ $user->client->phone }}</div>
                <div>Residem em: <span class="ms-3">{{ $user->client->city }} - {{ $user->client->uf }}</span></div>
            @endif
         </div>
         </div>
      </div>
      <div class="col-lg-12">
         <div class="card">
         <div class="card-header">
            <div class="header-title">
               <h4 class="card-title">Alterar Senha</h4>
            </div>
         </div>
         <div class="card-body">
            <p>Altere sua senha de acesso ao sistema.</p>
            <form action="{{ route('users.update-password', $user->id) }}" method="POST">
               @csrf
               @method('PATCH')
               <div class="row">
                  <div class="form-group col-md-6">
                     <label class="form-label" for="current_password">Senha Atual <span class="text-danger">*</span></label>
                     <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Digite sua senha atual" required>
                  </div>
                  <div class="form-group col-md-6"></div>
                  <div class="form-group col-md-6">
                     <label class="form-label" for="password">Nova Senha <span class="text-danger">*</span></label>
                     <input type="password" name="password" id="password" class="form-control" placeholder="Digite a nova senha" required>
                  </div>
                  <div class="form-group col-md-6">
                     <label class="form-label" for="password_confirmation">Confirmar Nova Senha <span class="text-danger">*</span></label>
                     <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirme a nova senha" required>
                  </div>
               </div>
               <button type="submit" class="btn btn-primary mt-3">Alterar Senha</button>
            </form>
         </div>
         </div>
      </div>
   </div>

   @include('partials.components.share-offcanvas')
</x-app-layout>
