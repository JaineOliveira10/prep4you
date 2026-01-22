<?php

namespace App\Interfaces;

use App\Models\MonthlyClosure;
use Illuminate\Support\Collection;

interface MonthlyClosureRepositoryInterface
{
    public function getAll(): Collection;

    public function filterByYearMonth(int $year, int $month): Collection;

    public function findOrCreateByYearMonth(int $year, int $month): MonthlyClosure;

    public function findById(int $id): ?MonthlyClosure;

    public function delete(int $id): bool;

    public function syncClients(MonthlyClosure $closure, array $clientData): void;
}
