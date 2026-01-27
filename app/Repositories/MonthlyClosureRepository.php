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
        try {
            $closure = $this->findById($id);
            
            if (!$closure) {
                \Log::warning('Fechamento não encontrado para delete', ['id' => $id]);
                return false;
            }

            \DB::beginTransaction();

            try {
                // Buscar IDs dos clientes (não IDs das relações, mas dos clientes)
                $clientIds = \DB::table('monthly_closure_clients')
                    ->where('closure_id', $id)
                    ->pluck('client_id')
                    ->toArray();

                \Log::info('Iniciando delete do fechamento', [
                    'closure_id' => $id,
                    'year' => $closure->year,
                    'month' => $closure->month,
                    'client_ids' => $clientIds
                ]);

                // Atualizar remessas para status "coletado"
                if (!empty($clientIds)) {
                    $updated = \DB::table('shipments')
                        ->whereIn('client_id', $clientIds)
                        ->whereYear('creation_date', $closure->year)
                        ->whereMonth('creation_date', $closure->month)
                        ->update(['status' => 'Collected']);
                    
                    \Log::info('Remessas atualizadas com sucesso', [
                        'updated_count' => $updated,
                        'client_ids' => $clientIds,
                        'year' => $closure->year,
                        'month' => $closure->month
                    ]);
                }

                // Deletar o fechamento
                $result = $closure->delete();
                
                \DB::commit();
                
                \Log::info('Fechamento deletado com sucesso', ['id' => $id]);
                return $result;
                
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Erro ao deletar fechamento', [
                    'id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Erro geral ao deletar fechamento', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Sincronizar clientes no fechamento
     */
    public function syncClients(MonthlyClosure $closure, array $clientData): void
    {
        $closure->clients()->syncWithoutDetaching($clientData);
    }
}
