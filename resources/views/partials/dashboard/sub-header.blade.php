

<div class="iq-navbar-header" style="height: 215px;">
    <div class="container-fluid iq-container">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1>Bem-vindo {{ auth()->user()->name ?? 'Usuário'  }}!</h1>
                        <p>Sistema de gerenciamento de remessas para o Amazon FBA.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="iq-header-img">
        <img src="{{asset('images/dashboard/top-header2.png')}}" alt="header" class="theme-color-yellow-img img-fluid w-100 h-100 animated-scaleX">
    </div>
</div>