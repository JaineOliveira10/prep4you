<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
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

        return view('users.form', compact('roles', 'priceTables'));
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
         $user = User::with('client.priceTable')->findOrFail($id);

        return view('users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::with('client.priceTable')->findOrFail($id);

        $roles = [
            'client' => 'Cliente',
            'admin' => 'Administrador'
         ];

        $priceTables = PriceTable::all();

        return view('users.form',  ['id' => $id, 'data' => $user, 'roles' => $roles, 'priceTables' => $priceTables]);
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

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $this->userService->updatePassword($id, $request->current_password, $request->password);
            return redirect()
                ->route('users.show', $id)
                ->withSuccess(__('Senha atualizada com sucesso.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('users.show', $id)
                ->withError($e->getMessage());
        }
    }
}
