@push('scripts')

@endpush

<x-app-layout :assets="$assets ?? []">
<div>
   <div class="row">
      <div class="col-sm-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">Lista de Usuários</h4>
               </div>
               <div class="card-action">
                  <a href="{{route('users.create')}}" class="btn btn-sm btn-primary" role="button">Novo Usuário</a>
               </div>
            </div>
            <div class="card-body px-0">
               <div class="table-responsive">
                  <table id="user-list-table" class="table table-striped" role="grid" data-toggle="data-table">
                     <thead>
                        <tr class="ligth">
                           <th>Nome</th>
                           <th>Email</th>
                           <th>Tipo</th>
                           <th>Data de Criação</th>
                           <th style="min-width: 100px">Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($users as $user)
                        <tr>
                           <td>{{ $user->name }}</td>
                           <td>{{ $user->email }}</td>
                           <td>
                              @php
                                 $type = $user->type ?? '';

                                 // Definindo cores
                                 $badgeClass = match($type) {
                                       'admin' => 'bg-secondary',
                                       'client' => 'bg-primary',
                                        default => 'bg-danger',
                                 };

                                 // Tradução do tipo
                                 $typeLabel = match($type) {
                                       'admin' => 'Administrador',
                                       'client' => 'Cliente',
                                       default => ucfirst($type),
                                 };
                              @endphp

                              <span class="badge {{ $badgeClass }}">{{ $typeLabel }}</span>
                           </td>

                           <td>{{ $user->created_at->format('d/m/Y') }}</td>
                           <td>
                              @include('users.action', ['id' => $user->id])
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</x-app-layout>