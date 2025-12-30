<x-app-layout :assets="$assets ?? []">
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Manual de Clientes</A></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <aside class="col-md-3">
                        @include("pages.manual.partials.menu-cliente")
                    </aside>

                    <main class="col-md-9 manual">
                        {!! $content !!}
                    </main>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
