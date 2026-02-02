<?php

namespace App\Services;

use App\Interfaces\MonthlyClosureRepositoryInterface;
use App\Models\Client;
use Illuminate\Support\Collection;

class MonthlyClosureService
{
    protected $repository;

    public function __construct(MonthlyClosureRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Obter closures com filtros opcionais
     */
    public function getClosures(?string $yearMonth = null, ?int $clientId = null): Collection
    {
        $closures = !empty($yearMonth) ? $this->filterByYearMonth($yearMonth) : $this->repository->getAll();

        if (!empty($clientId)) {
            $closures = $this->filterByClient($closures, $clientId);
        }

        return $closures;
    }

    /**
     * Filtrar closures por ano/mês
     */
    private function filterByYearMonth(string $yearMonth): Collection
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            return collect();
        }

        [$year, $month] = explode('-', $yearMonth);
        return $this->repository->filterByYearMonth((int)$year, (int)$month);
    }

    /**
     * Filtrar closures por cliente
     */
    private function filterByClient(Collection $closures, int $clientId): Collection
    {
        return $closures->filter(function ($closure) use ($clientId) {
            return $closure->clients->contains('id', $clientId);
        })->values();
    }

    /**
     * Criar novo fechamento executando procedure no banco
     */
    public function create(array $data): void
    {
        $validated = $this->validateClosureData($data);

        [$year, $month] = explode('-', $validated['year_month']);
        
        $clientId = $validated['client_id'];

        // Executar procedure close_client_month(client_id, year, month)
        try {
            \DB::statement('CALL close_client_month(?, ?, ?)', [$clientId, $year, $month]);
        } catch (\Exception $e) {
            throw new \Exception('Erro ao executar fechamento: ' . $e->getMessage());
        }
    }

    /**
     * Deletar fechamento
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Calcular dados do fechamento para preview e PDF
     * Busca dados armazenados no banco ou calcula para preview
     */
    public function calculateClosureData(int $year, int $month, int $clientId): array
    {
        $client = Client::findOrFail($clientId);

        // Tentar buscar dados já calculados e armazenados
        $closureClient = \DB::table('monthly_closure_clients')
            ->join('monthly_closures', 'monthly_closures.id', '=', 'monthly_closure_clients.closure_id')
            ->where('monthly_closures.year', $year)
            ->where('monthly_closures.month', $month)
            ->where('monthly_closure_clients.client_id', $clientId)
            ->first();

        if ($closureClient) {
            // Dados já existem no banco, usar valores armazenados
            return $this->buildClosureDataFromDatabase($client, $year, $month, $closureClient);
        }

        // Dados não existem, calcular para preview
        return $this->calculateClosureDataForPreview($client, $year, $month);
    }

    /**
     * Construir dados do fechamento a partir dos valores armazenados no banco
     * Nenhum cálculo, apenas busca e formatação
     */
    private function buildClosureDataFromDatabase(Client $client, int $year, int $month, $closureClient): array
    {
        // Buscar faixa de preço baseado na quantidade total de etiquetas
        $totalLabels = ($closureClient->total_simple_labels ?? 0) + ($closureClient->total_kit_labels ?? 0);
        
        $priceRange = \DB::table('price_ranges')
            ->join('clients', 'clients.price_table_id', '=', 'price_ranges.price_table_id')
            ->where('clients.id', $client->id)
            ->where('price_ranges.min_value', '<=', $totalLabels)
            ->where('price_ranges.max_value', '>=', $totalLabels)
            ->select('price_ranges.min_value', 'price_ranges.max_value')
            ->first();

        $priceRangeText = $priceRange 
            ? "{$priceRange->min_value} - {$priceRange->max_value} etiquetas"
            : '';

        // Buscar remessas incluídas
        $shipments = $this->getShipmentsForClosure($client->id, $year, $month);

        return [
            'year' => (int)$year,
            'month' => str_pad((int)$month, 2, '0', STR_PAD_LEFT),
            'year_month' => "{$month}/{$year}",
            'client_name' => $client->name,
            'total_simple_labels' => (int)($closureClient->total_simple_labels ?? 0),
            'total_kit_labels' => (int)($closureClient->total_kit_labels ?? 0),
            'total_superkit_labels' => (int)($closureClient->total_superkit_labels ?? 0),
            'unit_price_simple' => (float)($closureClient->unit_price_simple ?? 0),
            'unit_price_kit' => (float)($closureClient->unit_price_kit ?? 0),
            'total_simple_value' => (float)($closureClient->total_simple_value ?? 0),
            'total_kit_value' => (float)($closureClient->total_kit_value ?? 0),
            'total_superkit_value' => (float)($closureClient->total_superkit_value ?? 0),
            'total_simple_net' => (float)($closureClient->total_simple_net ?? 0),
            'total_kit_net' => (float)($closureClient->total_kit_net ?? 0),
            'total_discount_simple' => (float)($closureClient->total_discount_simple ?? 0),
            'total_discount_kit' => (float)($closureClient->total_discount_kit ?? 0),
            'total_gross' => (float)($closureClient->total_gross ?? 0),
            'total_discount' => (float)($closureClient->total_discount ?? 0),
            'total_net' => (float)($closureClient->total_net ?? 0),
            'price_range' => $priceRangeText,
            'shipments' => $shipments,
        ];
    }

    /**
     * Calcular dados para preview (antes de criar fechamento)
     * Busca e agrega apenas dados que já existem no banco
     */
    private function calculateClosureDataForPreview(Client $client, int $year, int $month): array
    {
        // Uma única query para agregar todos os dados dos shipment_items
        $aggregated = \DB::table('shipment_items')
            ->join('shipments', 'shipments.id', '=', 'shipment_items.shipment_id')
            ->where('shipments.client_id', $client->id)
            ->whereYear('shipments.creation_date', $year)
            ->whereMonth('shipments.creation_date', $month)
            ->select(
                \DB::raw('SUM(CASE WHEN shipment_items.type = \'simple\' THEN shipment_items.quantity ELSE 0 END) as total_simple_labels'),
                \DB::raw('SUM(CASE WHEN shipment_items.type = \'kit\' THEN shipment_items.quantity ELSE 0 END) as total_kit_labels'),
                \DB::raw('SUM(CASE WHEN shipment_items.type = \'super_kit\' THEN shipment_items.quantity ELSE 0 END) as total_superkit_labels'),
                \DB::raw('SUM(CASE WHEN shipment_items.type = \'simple\' THEN shipment_items.total_value ELSE 0 END) as total_simple_value'),
                \DB::raw('SUM(CASE WHEN shipment_items.type = \'kit\' THEN shipment_items.total_value ELSE 0 END) as total_kit_value'),
                \DB::raw('SUM(CASE WHEN shipment_items.type = \'super_kit\' THEN shipment_items.total_value ELSE 0 END) as total_superkit_value')
            )
            ->first();

        $totalLabels = ($aggregated->total_simple_labels ?? 0) + ($aggregated->total_kit_labels ?? 0);

        // Buscar preços unitários da tabela de preços
        $priceRange = \DB::table('price_ranges')
            ->join('clients', 'clients.price_table_id', '=', 'price_ranges.price_table_id')
            ->where('clients.id', $client->id)
            ->where('price_ranges.min_value', '<=', $totalLabels)
            ->where('price_ranges.max_value', '>=', $totalLabels)
            ->select('price_ranges.price', 'price_ranges.price_kit', 'price_ranges.min_value', 'price_ranges.max_value')
            ->first();

        $unitSimple = (float)($priceRange->price ?? 0);
        $unitKit = (float)($priceRange->price_kit ?? 0);
        $minValue = (int)($priceRange->min_value ?? 0);
        $maxValue = (int)($priceRange->max_value ?? 0);

        // Valores agregados do banco
        $totalSimpleLabels = (int)($aggregated->total_simple_labels ?? 0);
        $totalKitLabels = (int)($aggregated->total_kit_labels ?? 0);
        $totalSuperkitLabels = (int)($aggregated->total_superkit_labels ?? 0);
        $totalSimpleValue = (float)($aggregated->total_simple_value ?? 0);
        $totalKitValue = (float)($aggregated->total_kit_value ?? 0);
        $totalSuperkitValue = (float)($aggregated->total_superkit_value ?? 0);

        // Valores esperados (calculados no preview)
        $totalSimpleNet = $totalSimpleLabels * $unitSimple;
        $totalKitNet = $totalKitLabels * $unitKit;

        // Calcular descontos (diferença entre esperado e real)
        $totalDiscountSimple = abs($totalSimpleNet - $totalSimpleValue);
        $totalDiscountKit = abs($totalKitNet - $totalKitValue);
        $totalDiscount = $totalDiscountSimple + $totalDiscountKit;

        // Totais finais do preview
        $totalGross = $totalSimpleValue + $totalKitValue + $totalSuperkitValue;
        $totalNet = $totalGross - $totalDiscount;

        // Buscar remessas incluídas
        $shipments = $this->getShipmentsForClosure($client->id, $year, $month);

        return [
            'year' => (int)$year,
            'month' => str_pad((int)$month, 2, '0', STR_PAD_LEFT),
            'year_month' => "{$month}/{$year}",
            'client_name' => $client->name,
            'total_simple_labels' => $totalSimpleLabels,
            'total_kit_labels' => $totalKitLabels,
            'total_superkit_labels' => $totalSuperkitLabels,
            'unit_price_simple' => $unitSimple,
            'unit_price_kit' => $unitKit,
            'total_simple_value' => $totalSimpleValue,
            'total_kit_value' => $totalKitValue,
            'total_superkit_value' => $totalSuperkitValue,
            'total_simple_net' => $totalSimpleNet,
            'total_kit_net' => $totalKitNet,
            'total_discount_simple' => $totalDiscountSimple,
            'total_discount_kit' => $totalDiscountKit,
            'total_gross' => $totalGross,
            'total_discount' => $totalDiscount,
            'total_net' => $totalNet,
            'price_range' => "{$minValue} - {$maxValue} etiquetas",
            'shipments' => $shipments,
        ];
    }

    /**
     * Validar dados do fechamento
     */
    private function validateClosureData(array $data): array
    {
        return [
            'year_month' => $data['year_month'] ?? '',
            'client_id' => (int)($data['client_id'] ?? 0),
        ];
    }

    /**
     * Buscar remessas formatadas para um cliente em um período
     */
    private function getShipmentsForClosure(int $clientId, int $year, int $month): array
    {
        return \DB::table('shipments')
            ->where('client_id', $clientId)
            ->whereYear('creation_date', $year)
            ->whereMonth('creation_date', $month)
            ->select('id', 'shipment_code', 'creation_date', 'total_items')
            ->selectRaw('(SELECT SUM(total_value) FROM shipment_items WHERE shipment_id = shipments.id) as value')
            ->orderBy('creation_date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'shipment_code' => $item->shipment_code,
                    'creation_date' => \Carbon\Carbon::parse($item->creation_date)->format('d/m/Y'),
                    'total_items' => $item->total_items,
                    'value' => $item->value,
                ];
            })
            ->toArray();
    }

    /**
     * Realizar pagamento do fechamento - marca remessas como "Pago" e seta paid_flag para true
     */
    public function performPayment(int $closureId, int $clientId): void
    {
        // Obter o fechamento e verificar se já foi pago
        $closure = \App\Models\MonthlyClosure::findOrFail($closureId);
        $closureClient = $closure->clients()->where('client_id', $clientId)->first();

        if (!$closureClient) {
            throw new \Exception('Fechamento não encontrado para este cliente');
        }

        if ($closureClient->pivot->paid_flag) {
            throw new \Exception('Este fechamento já foi marcado como pago');
        }

        // Atualizar remessas associadas para status "Pago"
        \DB::table('shipments')
            ->where('client_id', $clientId)
            ->whereIn('id', function ($query) use ($closureId, $clientId) {
                $query->select('shipment_id')
                    ->from('monthly_closure_shipments')
                    ->whereIn('closure_client_id', function ($subQuery) use ($closureId, $clientId) {
                        $subQuery->select('id')
                            ->from('monthly_closure_clients')
                            ->where('closure_id', $closureId)
                            ->where('client_id', $clientId);
                    });
            })
            ->update(['status' => 'Paid']);

        // Atualizar paid_flag na tabela pivô
        \DB::table('monthly_closure_clients')
            ->where('closure_id', $closureId)
            ->where('client_id', $clientId)
            ->update(['paid_flag' => true]);
    }

    /**
     * Estornar pagamento do fechamento - volta remessas para "Gerado Fatura" e seta paid_flag para false
     */
    public function refundPayment(int $closureId, int $clientId): void
    {
        // Obter o fechamento e verificar se foi pago
        $closure = \App\Models\MonthlyClosure::findOrFail($closureId);
        $closureClient = $closure->clients()->where('client_id', $clientId)->first();

        if (!$closureClient) {
            throw new \Exception('Fechamento não encontrado para este cliente');
        }

        if (!$closureClient->pivot->paid_flag) {
            throw new \Exception('Este fechamento ainda não foi marcado como pago');
        }

        // Atualizar remessas associadas de volta para status "Gerado Fatura"
        \DB::table('shipments')
            ->where('client_id', $clientId)
            ->whereIn('id', function ($query) use ($closureId, $clientId) {
                $query->select('shipment_id')
                    ->from('monthly_closure_shipments')
                    ->whereIn('closure_client_id', function ($subQuery) use ($closureId, $clientId) {
                        $subQuery->select('id')
                            ->from('monthly_closure_clients')
                            ->where('closure_id', $closureId)
                            ->where('client_id', $clientId);
                    });
            })
            ->update(['status' => 'Invoice Generated']);

        // Atualizar paid_flag na tabela pivô
        \DB::table('monthly_closure_clients')
            ->where('closure_id', $closureId)
            ->where('client_id', $clientId)
            ->update(['paid_flag' => false]);
    }
}
