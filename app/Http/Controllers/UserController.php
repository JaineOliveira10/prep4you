<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use App\Models\PriceTable;
use App\Models\Client;
use App\Services\UserService;
use App\DataTables\UsersDataTable;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->getAll();
        $assets = ['data-table'];
        return view('users.index', compact('users', 'assets'));
    }

    public function create()
    {
        $roles = [
            'client' => 'Cliente',
            'admin' => 'Administrador'            
        ];

        $priceTables = PriceTable::all();

        $estados = \App\Models\Client::ESTADOS;

        return view('users.form', compact('roles', 'priceTables', 'estados'));
    }

    public function store(UserRequest $request)
    {
        $this->userService->create($request->validated());

        return redirect()
            ->route('users.index')
            ->withSuccess(__('Usuário criado com sucesso.'));
    }

    public function show($id)
    {
        // Se o usuário é cliente, só pode ver seu próprio perfil
        if (strtolower(auth()->user()->type) === 'client' && auth()->id() != $id) {
            return redirect()->route('users.show', auth()->id());
        }
        
        $user = User::with('client.priceTable')->findOrFail($id);

        $priceTables = PriceTable::all();

        return view('users.show', compact('user', 'priceTables'));
    }

    public function edit($id)
    {
        $user = User::with('client.priceTable')->findOrFail($id);

        $roles = [
            'client' => 'Cliente',
            'admin' => 'Administrador'
         ];

         $estados = \App\Models\Client::ESTADOS;

        $priceTables = PriceTable::all();

        return view('users.form',  ['id' => $id, 'data' => $user, 'roles' => $roles, 'priceTables' => $priceTables, 'estados' => $estados]);
    }

    public function update(UserRequest $request, $id)
    {
        $this->userService->update($id, $request->validated());

        return redirect()
            ->route('users.index')
            ->withSuccess(__('Usuário atualizado com sucesso.'));
    }

    public function destroy($id)
    {
        $this->userService->delete($id);

        return redirect()
            ->route('users.index')
            ->withSuccess(__('Usuário removido com sucesso.'));
    }

    public function updatePassword(UpdatePasswordRequest $request, $id)
    {
        // Se o usuário é cliente, só pode alterar sua própria senha
        if (strtolower(auth()->user()->type) === 'client' && auth()->id() != $id) {
            abort(403, __('messages.access_denied'));
        }

        try {
            $this->userService->updatePassword($id, $request->current_password, $request->password);
            return redirect()
                ->route('users.show', $id)
                ->withSuccess(__('passwords.updated_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->route('users.show', $id)
                ->withError($e->getMessage());
        }
    }
}
