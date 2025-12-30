<x-app-layout :assets="$assets ?? []">
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Manual de Administradores</A></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <aside class="col-md-3">
                        @include("pages.manual.partials.menu-admin")
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
