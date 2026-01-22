<?php

namespace App\Repositories;

use App\Models\MonthlyClosure;
use App\Interfaces\MonthlyClosureRepositoryInterface;
use Illuminate\Support\Collection;

class MonthlyClosureRepository implements MonthlyClosureRepositoryInterface
{
    protected $model;

    public function __construct(MonthlyClosure $model)
    {
        $this->model = $model;
    }

    /**
     * Obter todos os fechamentos com clientes
     */
    public function getAll(): Collection
    {
        return $this->model
            ->with('clients')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Filtrar por ano/mês
     */
    public function filterByYearMonth(int $year, int $month): Collection
    {
        return $this->model
            ->with('clients')
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Encontrar ou criar fechamento por ano/mês
     */
    public function findOrCreateByYearMonth(int $year, int $month): MonthlyClosure
    {
        return $this->model->firstOrCreate(
            ['year' => $year, 'month' => $month]
        );
    }

    /**
     * Encontrar por ID
     */
    public function findById(int $id): ?MonthlyClosure
    {
        return $this->model->with('clients')->find($id);
    }

    /**
     * Deletar por ID
     */
    public function delete(int $id): bool
    {
        $closure = $this->findById($id);
        
        if (!$closure) {
            return false;
        }

        return $closure->delete();
    }

    /**
     * Sincronizar clientes no fechamento
     */
    public function syncClients(MonthlyClosure $closure, array $clientData): void
    {
        $closure->clients()->syncWithoutDetaching($clientData);
    }
}
