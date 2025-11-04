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
         <div class="card-header d-flex justify-content-between"">
            <div class="header-title">
               <h4 class="card-title">{{auth()->id() != $user->id ? 'Informações do usuário' : 'Meu Perfil'}}</h4>
            </div>            
             @if(auth()->user()->type == 'admin')
            <div class="card-action">
               <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href = document.referrer">Voltar</button>
               <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">Editar</a>
            </div>
             @endif
         </div>
         <div class="card-body">
            <!-- Informações Básicas -->
            <div class="row mb-4">
               <div class="col-12">
                  <h6 class="text-primary border-bottom pb-2 mb-3">Informações Básicas</h6>
               </div>
               <div class="col-md-6">
                  <div class="mb-3">
                     <label class="form-label fw-bold text-muted">Email</label>
                     <p class="mb-0">{{ $user->email }}</p>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="mb-3">
                     <label class="form-label fw-bold text-muted">Tipo de Usuário</label>
                     <p class="mb-0">
                        @if($user->type == 'admin')
                           <span class="badge bg-secondary">Administrador</span>
                        @else
                           <span class="badge bg-primary">Cliente</span>
                        @endif
                     </p>
                  </div>
               </div>
            </div>

            @if($user->type == 'client')
            <!-- Informações do Cliente -->
            <div class="row mb-4">
               <div class="col-12">
                  <h6 class="text-primary border-bottom pb-2 mb-3">Informações do Cliente</h6>
               </div>
               <div class="col-md-6">
                  <div class="mb-3">
                     <label class="form-label fw-bold text-muted">Telefone</label>
                     <p class="mb-0">{{ $user->client->phone ?? 'Não informado' }}</p>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="mb-3">
                     <label class="form-label fw-bold text-muted">Localização</label>
                     <p class="mb-0">{{ $user->client->city ?? 'Não informado' }} - {{ $user->client->uf ?? '' }}</p>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="mb-3">
                     <label class="form-label fw-bold text-muted">Tabela de Preço</label>
                     <p class="mb-0">
                        @if($user->client && $user->client->priceTable)
                           <span class="badge bg-success">{{ $user->client->priceTable->name }}</span>
                        @else
                           <span class="badge bg-secondary">Não definida</span>
                        @endif
                     </p>
                  </div>
               </div>
            </div>
            @endif
         </div>
         </div>
      </div>
      @if(auth()->id() == $user->id)
      <div class="col-lg-12">
         <div class="card">
         <div class="card-header">
            <div class="header-title">
               <h4 class="card-title">{{ __('ui.titles.change_password') }}</h4>
            </div>
         </div>
         <div class="card-body">
            <p>{{ __('ui.messages.change_password_instruction') }}</p>
            <small class="text-muted">{{ __('ui.messages.password_requirements') }}</small>
            
            <x-form-errors />
            
            <form action="{{ route('users.update-password', $user->id) }}" method="POST">
               @csrf
               @method('PATCH')
               <div class="row">
                  <div class="form-group col-md-6">
                     <label class="form-label" for="current_password">{{ __('ui.labels.current_password') }} <span class="text-danger">*</span></label>
                     <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="{{ __('ui.placeholders.enter_current_password') }}" required>
                     @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="form-group col-md-6"></div>
                  <div class="form-group col-md-6">
                     <label class="form-label" for="password">{{ __('ui.labels.new_password') }} <span class="text-danger">*</span></label>
                     <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('ui.placeholders.enter_new_password') }}" required>
                     @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="form-group col-md-6">
                     <label class="form-label" for="password_confirmation">{{ __('ui.labels.confirm_password') }} <span class="text-danger">*</span></label>
                     <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="{{ __('ui.placeholders.confirm_new_password') }}" required>
                     @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>
               <button type="submit" class="btn btn-primary mt-3">{{ __('ui.buttons.change_password') }}</button>
            </form>
         </div>
         </div>
      </div>
      @endif
   </div>

   @include('partials.components.share-offcanvas')
</x-app-layout>
