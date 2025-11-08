<?php

namespace App\Services;

use App\Models\Client;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAll()
    {
        return $this->userRepository->all();
    }

    public function findById($id)
    {
        return $this->userRepository->find($id);
    }

    public function create(array $data)
    {
        // Map first_name to name for the user model
        if (isset($data['first_name'])) {
            $data['name'] = $data['first_name'];
            unset($data['first_name']);
        }

        $data['password'] = Hash::make($data['password']);

        if ($data['type'] === 'client') {
            $client = Client::create([
                'name' => $data['name'],
                'city' => $data['city'] ?? '',
                'uf' => $data['uf'] ?? '',
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'price_table_id' => $data['price_table_id'] ?? null,
            ]);

            $data['client_id'] = $client->id;

            $data['client'] = [
                'name' => $client->name,
                'city' => $client->city,
                'uf' => $client->uf,
                'phone' => $client->phone,
                'email' => $client->email,
                'price_table_id' => $client->price_table_id,
            ];
        }

        return $this->userRepository->create($data);
    }

    public function update($id, array $data)
    {
        // Map first_name to name for the user model
        if (isset($data['first_name'])) {
            $data['name'] = $data['first_name'];
            unset($data['first_name']);
        }

        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user = $this->userRepository->find($id);
        
        if ($data['type'] === 'client' && $user->client) {
            $user->client->update([
                'name' => $data['name'],
                'city' => $data['city'] ?? '',
                'uf' => $data['uf'] ?? '',
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'price_table_id' => $data['price_table_id'] ?? null,
            ]);
        }

        return $this->userRepository->update($id, $data);
    }

    public function delete($id)
    {
        $user = $this->userRepository->find($id);
        
        // Verificar se o usuário tem produtos cadastrados
        if ($user->client && $user->client->products()->count() > 0) {
            throw new \Exception('Não é possível excluir este usuário pois possui produtos cadastrados.');
        }
        
        $result = $this->userRepository->delete($id);
        
        if ($user->client) {
            $user->client->delete();
        }
        
        return $result;
    }

    public function updatePassword($id, $currentPassword, $newPassword)
    {
        $user = $this->userRepository->find($id);
        
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception(__('passwords.current_incorrect'));
        }
        
        return $this->userRepository->update($id, [
            'password' => Hash::make($newPassword)
        ]);
    }

    public function updateName($id, $name)
    {
        $user = $this->userRepository->find($id);
        
        $this->userRepository->update($id, ['name' => $name]);
        
        if ($user->type === 'client' && $user->client) {
            $user->client->update(['name' => $name]);
        }
        
        return true;
    }
}
