<header>
  <div class="container">
    <div class="topbar">
      <div class="brand">
        <div class="logo" aria-hidden="true">
          <img src="{{ asset('images/icons/icon-logo.png') }}" width="30" alt="Prep4You">
        </div>
        <span>Prep4You</span>
      </div>

      <nav aria-label="Navegação principal">
        <a href="#servicos">Serviços</a>
        <a href="#como-funciona">Como funciona</a>
        <a href="#depoimentos">Depoimentos</a>
        <a href="#contato">Contato</a>
      </nav>

      <a class="btn-login" href="{{ route('login') }}">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
        <span>Login</span>
      </a>
    </div>
  </div>
</header>
