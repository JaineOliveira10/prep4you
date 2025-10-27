<?php

namespace App\Interfaces;

interface ProductRepositoryInterface
{
    public function all();
    public function getByClient($clientId);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}