<x-guest-layout>
   <section class="login-content">
      <div class="row m-0 align-items-center bg-white vh-100">
         <div class="col-md-6">
            <div class="row justify-content-center">
               <div class="col-md-10">
                  <div class="card card-transparent shadow-none d-flex justify-content-center mb-0 auth-card">
                     <div class="card-body">
                        <div class="d-flex justify-content-center">
                           <a href="#" class="navbar-brand d-flex align-items-center mb-3">
                              <img src="{{asset('images/icons/logo.png')}}" width="200" alt="fb">
                           </a>  
                        </div>
                        <div class="d-flex justify-content-center">
                           <h4 class="logo-title mb-1">Faça o seu login</h4> 
                        </div>
                        
                        
                   
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <!-- Validation Errors -->
                        <x-auth-validation-errors class="mb-4" :errors="$errors" />
                        <form method="POST" action="{{ route('login') }}" data-toggle="validator">
                            {{csrf_field()}}
                           <div class="row">
                              <div class="col-lg-12">
                                 <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input id="email" type="email" name="email"  value="{{env('IS_DEMO') ? 'admin@exemplo.com' : old('email')}}"   class="form-control"  placeholder="admin@exemplo.com" required autofocus>
                                 </div>
                              </div>
                              <div class="col-lg-12">
                                 <div class="form-group">
                                    <label for="password" class="form-label">Password</label>
                                    <input class="form-control" type="password" placeholder="********"  name="password" value="{{ env('IS_DEMO') ? 'password' : '' }}" required autocomplete="current-password">
                                 </div>
                              </div>
                           </div>
                           <div class="d-flex justify-content-center">
                              <button type="submit" class="btn btn-warning">{{ __('Entrar') }}</button>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-6 d-md-block d-none bg-primary p-0 mt-n1 vh-100 overflow-hidden">
            <img src="{{asset('images/auth/01.png')}}" class="img-fluid gradient-main animated-scaleX" alt="Ilustração Autenticação">
         </div>
      </div>
   </section>
</x-guest-layout>
