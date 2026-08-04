<x-guest-layout>
   <section class="login-content">
      <div class="row m-0 align-items-center bg-white vh-100">
         <div class="col-md-6 p-0">
            <div class="card card-transparent auth-card shadow-none d-flex justify-content-center mb-0">
               <div class="card-body">
                  <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center mb-3">
                     <img src="{{ asset('images/icons/logo.png') }}" class="img-fluid" width="80" alt="Mail illustration">
                     <h4 class="logo-title ms-3">{{ env('APP_NAME') }}</h4>
                  </a>
                 
                  <h2 class="mt-3 mb-0">Verifique seu e-mail</h2>
                  <p class="cnf-mail mb-1">Enviamos um link de verificação para <strong>{{ auth()->user()->email }}</strong>. Abra seu e-mail e clique no link para ativar o acesso ao sistema.</p>

                  @if(session('success'))
                     <div class="alert alert-success mt-3">
                        {{ session('success') }}
                     </div>
                  @endif

                  <div class="d-inline-block w-100 mt-3">
                     <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">Reenviar link de verificação</button>
                     </form>
                  </div>

                  <div class="d-inline-block w-100 mt-3">
                     <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">Sair</button>
                     </form>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-6 d-md-block d-none bg-primary p-0 mt-n1 vh-100 overflow-hidden">
            <img src="{{ asset('images/auth/01.png') }}" class="img-fluid gradient-main animated-scaleX w-100" alt="Ilustração Autenticação">
         </div>
      </div>
   </section>
</x-guest-layout>
