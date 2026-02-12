<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\MonthlyClosure;
use App\Services\MonthlyClosureService;
use App\Services\PixQrCodeService;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

class MonthlyClosureController extends Controller
{
    protected $monthlyClosureService;

    public function __construct(MonthlyClosureService $monthlyClosureService)
    {
        $this->monthlyClosureService = $monthlyClosureService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $yearMonth = $request->filled('year_month') ? $request->year_month : now()->format('Y-m');
        $clientId = $request->filled('client_id') ? (int)$request->client_id : null;

        $closures = $this->monthlyClosureService->getClosures($yearMonth, $clientId);
        $clients = Client::orderBy('name', 'asc')->get();


        return view('pages.monthly-closure.index', [
            'closures' => $closures,
            'clients' => $clients,
        ]);
    }

    /**
     * Verificar se já existe fechamento para um período e cliente
     */
    public function checkExisting(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2000',
                'month' => 'required|integer|min:1|max:12',
                'client_id' => 'required|exists:clients,id',
            ]);

            // Verificar se existe MonthlyClosure
            $closure = \DB::table('monthly_closures')
                ->where('year', $validated['year'])
                ->where('month', $validated['month'])
                ->first();

            if (!$closure) {
                return response()->json(['exists' => false]);
            }

            // Se existe closure, verificar se tem dados para este cliente
            $closureClient = \DB::table('monthly_closure_clients')
                ->where('closure_id', $closure->id)
                ->where('client_id', $validated['client_id'])
                ->first();

            return response()->json(['exists' => (bool) $closureClient]);
        } catch (\Exception $e) {
            \Log::error('Erro ao verificar fechamento: ' . $e->getMessage());
            return response()->json(['exists' => false], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'year_month' => 'required|date_format:Y-m',
                'client_id' => 'required|integer|exists:clients,id',
            ]);

            \Log::info('Iniciando criação de fechamento', $validated);
            
            $this->monthlyClosureService->create($validated);

            \Log::info('Fechamento criado com sucesso');

            return response()->json(['success' => true, 'message' => 'Fechamento criado com sucesso!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erro de validação', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validação falhou', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar fechamento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json(['error' => 'Erro ao criar fechamento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $success = $this->monthlyClosureService->delete((int)$id);

            if ($success) {
                // Se for AJAX, retornar JSON
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Fechamento deletado com sucesso! Remessas retornadas ao status anterior'
                    ]);
                }
                
                // Senão, retornar redirect
                return redirect()->route('monthly-closures.index')
                               ->with('success', 'Fechamento deletado com sucesso! Remessas retornadas ao status anterior');
            } else {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Fechamento não encontrado'
                    ], 404);
                }
                
                return redirect()->back()
                               ->with('error', 'Fechamento não encontrado');
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao deletar fechamento', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao deletar fechamento: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                           ->with('error', 'Erro ao deletar fechamento: ' . $e->getMessage());
        }
    }

    /**
     * Delete relationship between closure and client (not the entire closure)
     */
    public function destroyClient(string $closureId, string $clientId)
    {
        try {
            $closure = MonthlyClosure::find((int)$closureId);

            if (!$closure) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Fechamento não encontrado'
                    ], 404);
                }
                return redirect()->back()
                               ->with('error', 'Fechamento não encontrado');
            }

            \DB::beginTransaction();

            try {
                // Buscar dados das remessas com status antigo
                $shipmentStatuses = \DB::table('monthly_closure_shipments')
                    ->join('monthly_closure_clients', 'monthly_closure_clients.id', '=', 'monthly_closure_shipments.closure_client_id')
                    ->where('monthly_closure_clients.closure_id', $closureId)
                    ->where('monthly_closure_clients.client_id', (int)$clientId)
                    ->select('monthly_closure_shipments.shipment_id', 'monthly_closure_shipments.old_status')
                    ->get()
                    ->keyBy('shipment_id');

                \Log::info('Dados de status antigo recuperados para destroyClient', [
                    'closure_id' => $closureId,
                    'client_id' => $clientId,
                    'shipment_count' => $shipmentStatuses->count()
                ]);

                // Restaurar status antigo das remessas
                foreach ($shipmentStatuses as $shipmentId => $data) {
                    \DB::table('shipments')
                        ->where('id', $shipmentId)
                        ->update(['status' => $data->old_status]);
                }

                \Log::info('Remessas restauradas para status antigo ao remover cliente', [
                    'closure_id' => $closureId,
                    'client_id' => $clientId,
                    'updated_count' => $shipmentStatuses->count()
                ]);

                // Remover a relação cliente-fechamento
                $closure->clients()->detach((int)$clientId);

                \DB::commit();

                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Fechamento excluído com sucesso!'
                    ]);
                }

                return redirect()->route('monthly-closures.index')
                               ->with('success', 'Cliente removido do fechamento com sucesso!');
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao remover cliente do fechamento', [
                'closure_id' => $closureId,
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao excluir fechamento: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Erro ao remover cliente: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF do fechamento
     */
    public function download(string $id)
    {
        return response()->json(['message' => 'Download do PDF do fechamento ' . $id]);
    }

    /**
     * Obter detalhes do fechamento via AJAX
     */
    public function details(string $closure, string $closureClient)
    {
        try {
            $monthlyClosureClient = \DB::table('monthly_closure_clients')
                ->where('id', $closureClient)
                ->first();

            if (!$monthlyClosureClient) {
                return response()->json(['error' => 'Fechamento não encontrado'], 404);
            }

            $closure = MonthlyClosure::findOrFail($closure);
            $client = Client::findOrFail($monthlyClosureClient->client_id);

            $priceRange = \DB::table('price_ranges')
                ->join('clients', 'clients.price_table_id', '=', 'price_ranges.price_table_id')
                ->where('clients.id', $client->id)
                ->where('price_ranges.min_value', '<=', $monthlyClosureClient->total_simple_labels + $monthlyClosureClient->total_kit_labels)
                ->where('price_ranges.max_value', '>=', $monthlyClosureClient->total_simple_labels + $monthlyClosureClient->total_kit_labels)
                ->select('price_ranges.min_value', 'price_ranges.max_value', 'price_ranges.discount_simple', 'price_ranges.discount_kit')
                ->first();

            $minValue = $priceRange->min_value ?? 0;
            $maxValue = $priceRange->max_value ?? 0;
            $discountSimple = $priceRange->discount_simple ?? 0;
            $discountKit = $priceRange->discount_kit ?? 0;

            // Buscar remessas do fechamento
            $shipments = \DB::table('shipments')
                ->where('shipments.client_id', $client->id)
                ->whereYear('shipments.creation_date', $closure->year)
                ->whereMonth('shipments.creation_date', $closure->month)
                ->select(
                    'shipments.id',
                    'shipments.shipment_code',
                    'shipments.creation_date',
                    'shipments.total_items',
                    \DB::raw('SUM(shipment_items.total_value) as value')
                )
                ->join('shipment_items', 'shipments.id', '=', 'shipment_items.shipment_id')
                ->groupBy('shipments.id', 'shipments.shipment_code', 'shipments.creation_date', 'shipments.total_items')
                ->orderBy('shipments.creation_date', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'shipment_code' => $item->shipment_code,
                        'creation_date' => \Carbon\Carbon::parse($item->creation_date)->format('d/m/Y'),
                        'total_items' => $item->total_items,
                        'value' => $item->value,
                    ];
                });

            return response()->json([
                'month_year' => \Carbon\Carbon::createFromDate($closure->year, $closure->month, 1)->format('m/Y'),
                'client_name' => $client->name,
                'price_range' => $minValue . ' - ' . $maxValue . ' etiquetas',
                'discount_simple' => $discountSimple,
                'discount_kit' => $discountKit,
                'total_simple_labels' => $monthlyClosureClient->total_simple_labels,
                'total_kit_labels' => $monthlyClosureClient->total_kit_labels,
                'unit_price_simple' => $monthlyClosureClient->unit_price_simple,
                'unit_price_kit' => $monthlyClosureClient->unit_price_kit,
                'total_simple_value' => $monthlyClosureClient->total_simple_value,
                'total_kit_value' => $monthlyClosureClient->total_kit_value,
                'total_gross' => $monthlyClosureClient->total_gross,
                'total_discount' => $monthlyClosureClient->total_discount,
                'total_net' => $monthlyClosureClient->total_net,
                'shipments' => $shipments,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao carregar detalhes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Preview do novo fechamento (sem criar ainda)
     */
    public function previewClosure(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2000',
                'month' => 'required|integer|min:1|max:12',
                'client_id' => 'nullable|exists:clients,id',
            ]);

            $year = $validated['year'];
            $month = $validated['month'];
            $clientId = $validated['client_id'] ?? null;

            if ($clientId) {
                // Um cliente específico
                $data = $this->monthlyClosureService->calculateClosureData($year, $month, $clientId);
                $data['client_id'] = $clientId;
                return response()->json($data);
            } else {
                // Todos os clientes com remessas neste período
                $closures = $this->getClientsWithShipments($year, $month);
                
                if (empty($closures)) {
                    return response()->json(['error' => 'Nenhum cliente com remessas neste período'], 400);
                }

                // Processar cada cliente
                $closuresData = [];
                foreach ($closures as $clientId) {
                    try {
                        $data = $this->monthlyClosureService->calculateClosureData($year, $month, $clientId);
                        // Garantir que client_id está nos dados
                        $data['client_id'] = $clientId;
                        $closuresData[] = $data;
                    } catch (\Exception $e) {
                        \Log::warning("Erro ao processar cliente $clientId: " . $e->getMessage());
                        continue;
                    }
                }

                return response()->json($closuresData);
            }
        } catch (\Exception $e) {
            \Log::error('Erro em previewClosure: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obter clientes que possuem remessas em um período específico
     */
    private function getClientsWithShipments(int $year, int $month): array
    {
        $clients = \DB::table('shipments')
            ->whereYear('creation_date', $year)
            ->whereMonth('creation_date', $month)
            ->distinct()
            ->pluck('client_id')
            ->toArray();

        return $clients;
    }

    /**
     * Gerar PDF de preview do fechamento
     */
    public function previewPdf(Request $request)
    {
        try {
            // Suportar dois cenários:
            // 1. closure_id (quando vem da ação de download)
            // 2. year, month, client_id (quando vem da criação)
            
            $closure_id = $request->input('closure_id');
            
            if ($closure_id) {
                // Cenário 1: Buscar os dados pelo closure_id
                $closure = MonthlyClosure::find($closure_id);
                if (!$closure) {
                    return response()->json(['error' => 'Fechamento não encontrado'], 404);
                }
                
                // Como temos múltiplos clientes, precisamos do client_id
                $client_id = $request->input('client_id');
                if (!$client_id) {
                    return response()->json(['error' => 'client_id é obrigatório quando closure_id é fornecido'], 400);
                }
                
                $validated = [
                    'year' => $closure->year,
                    'month' => $closure->month,
                    'client_id' => $client_id,
                ];
            } else {
                // Cenário 2: Validar os parâmetros normais
                $validated = $request->validate([
                    'year' => 'required|integer|min:2000',
                    'month' => 'required|integer|min:1|max:12',
                    'client_id' => 'required|exists:clients,id',
                ]);
            }

            $data = $this->monthlyClosureService->calculateClosureData(
                $validated['year'],
                $validated['month'],
                $validated['client_id']
            );

            // Gerar QR Code com a chave PIX e valor líquido
            try {
                $pixQrCodeService = new PixQrCodeService();
                $qrCodeUrl = $pixQrCodeService->generatePixQrCode($data['total_net']);
                $data['qr_code_url'] = $qrCodeUrl;
                \Log::info('QR Code gerado para PDF', ['valor' => $data['total_net']]);
            } catch (\Exception $e) {
                \Log::error('Erro ao gerar QR Code PIX', ['error' => $e->getMessage()]);
                $data['qr_code_url'] = '';
            }

            $html = view('pages.monthly-closure.pdf', $data)->render();
            \Log::info('HTML renderizado', ['tamanho' => strlen($html)]);
            
            // Configurar opções do dompdf igual ao ProductLabelService
            $options = new Options();
            $options->set('isPhpEnabled', false);
            $options->set('isRemoteEnabled', false);
            $options->set('tempDir', storage_path('framework/dompdf'));
            $options->set('fontDir', resource_path('fonts'));
            $options->set('fontCache', storage_path('framework/fonts'));
            $options->set('chroot', base_path());
            $options->set('logOutputFile', storage_path('logs/dompdf.log'));
            
            // Adicionar charset UTF-8 no HTML
            if (strpos($html, '<head>') !== false) {
                $html = str_replace(
                    '<head>',
                    '<head><meta charset="UTF-8">',
                    $html
                );
            } else {
                $html = '<?xml version="1.0" encoding="UTF-8"?>' . $html;
            }
            
            $dompdf = new Dompdf($options);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->render();
            
            \Log::info('PDF gerado com sucesso');
            
            // Sanitizar nome do cliente removendo acentos
            $clientName = \Illuminate\Support\Str::slug($data['client_name'], '-');
            $filename = "fechamento_" . $clientName . "_" . str_replace('/', '-', $data['year_month']) . ".pdf";
            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF de preview: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Erro ao gerar PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Gerar PDF com todos os fechamentos de um mês (CNPJ, NOME, VALOR LÍQUIDO)
     */
    public function printPdf(Request $request)
    {
        try {
            $validated = $request->validate([
                'year_month' => 'required|date_format:Y-m',
            ]);

            $parts = explode('-', $validated['year_month']);
            $year = (int)$parts[0];
            $month = (int)$parts[1];

            // Buscar todos os fechamentos do mês
            $closures = MonthlyClosure::where('year', $year)
                ->where('month', $month)
                ->with('clients')
                ->get();

            if ($closures->isEmpty()) {
                return response()->json(['error' => 'Nenhum fechamento encontrado para este período'], 404);
            }

            // Montar dados para o PDF
            $closureData = [];
            $totalValue = 0;

            foreach ($closures as $closure) {
                foreach ($closure->clients as $client) {
                    $closureData[] = [
                        'cnpj' => $client->cnpj ?? 'N/A',
                        'name' => $client->name,
                        'total_net' => $client->pivot->total_net ?? 0,
                    ];
                    $totalValue += $client->pivot->total_net ?? 0;
                }
            }

            // Gerar HTML para o PDF
            $html = view('pages.monthly-closure.counter-pdf', [
                'closures' => $closureData,
                'total_value' => $totalValue,
                'year_month' => \Carbon\Carbon::createFromDate($year, $month, 1)->format('m/Y'),
            ])->render();

            // Adicionar charset UTF-8 no HTML
            if (strpos($html, '<head>') !== false) {
                $html = str_replace(
                    '<head>',
                    '<head><meta charset="UTF-8">',
                    $html
                );
            } else {
                $html = '<?xml version="1.0" encoding="UTF-8"?>' . $html;
            }

            // Configurar dompdf
            $options = new Options();
            $options->set('isPhpEnabled', false);
            $options->set('isRemoteEnabled', false);
            $options->set('tempDir', storage_path('framework/dompdf'));
            $options->set('fontDir', resource_path('fonts'));
            $options->set('fontCache', storage_path('framework/fonts'));
            $options->set('chroot', base_path());
            $options->set('logOutputFile', storage_path('logs/dompdf.log'));

            $dompdf = new Dompdf($options);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->render();

            $filename = "fechamentos_" . str_replace('/', '-', \Carbon\Carbon::createFromDate($year, $month, 1)->format('m/Y')) . ".pdf";
            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF de fechamentos: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Erro ao gerar PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Realizar pagamento do fechamento - marca remessas como "Pago" e seta paid_flag para true
     */
    public function performPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'closure_id' => 'required|integer|exists:monthly_closures,id',
                'client_id' => 'required|integer|exists:clients,id',
            ]);

            $this->monthlyClosureService->performPayment($validated['closure_id'], $validated['client_id']);

            return response()->json([
                'success' => true,
                'message' => 'Pagamento realizado com sucesso!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validação falhou', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao realizar pagamento: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Estornar pagamento do fechamento - volta remessas para "Gerado Fatura" e seta paid_flag para false
     */
    public function refundPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'closure_id' => 'required|integer|exists:monthly_closures,id',
                'client_id' => 'required|integer|exists:clients,id',
            ]);

            $this->monthlyClosureService->refundPayment($validated['closure_id'], $validated['client_id']);

            return response()->json([
                'success' => true,
                'message' => 'Pagamento estornado com sucesso!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validação falhou', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao estornar pagamento: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}