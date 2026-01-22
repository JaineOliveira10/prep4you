<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\MonthlyClosure;
use App\Services\MonthlyClosureService;
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
        $yearMonth = $request->filled('year_month') ? $request->year_month : null;
        $clientId = $request->filled('client_id') ? (int)$request->client_id : null;

        $closures = $this->monthlyClosureService->getClosures($yearMonth, $clientId);
        $clients = Client::all();

        return view('pages.monthly-closure.index', [
            'closures' => $closures,
            'clients' => $clients,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'year_month' => 'required|date_format:Y-m',
                'client_id' => 'required|exists:clients,id',
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
                return redirect()->route('monthly-closures.index')
                               ->with('success', 'Fechamento deletado com sucesso!');
            } else {
                return redirect()->back()
                               ->with('error', 'Fechamento não encontrado');
            }
        } catch (\Exception $e) {
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
                return redirect()->back()
                               ->with('error', 'Fechamento não encontrado');
            }

            $closure->clients()->detach((int)$clientId);

            return redirect()->route('monthly-closures.index')
                           ->with('success', 'Cliente removido do fechamento com sucesso!');
        } catch (\Exception $e) {
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
                'client_id' => 'required|exists:clients,id',
            ]);

            $data = $this->monthlyClosureService->calculateClosureData(
                $validated['year'],
                $validated['month'],
                $validated['client_id']
            );

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Erro em previewClosure: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Gerar PDF de preview do fechamento
     */
    public function previewPdf(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2000',
                'month' => 'required|integer|min:1|max:12',
                'client_id' => 'required|exists:clients,id',
            ]);

            $data = $this->monthlyClosureService->calculateClosureData(
                $validated['year'],
                $validated['month'],
                $validated['client_id']
            );

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
            
            $dompdf = new Dompdf($options);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->loadHtml($html);
            $dompdf->render();
            
            \Log::info('PDF gerado com sucesso');
            
            $filename = "fechamento_" . $data['client_name'] . "_" . str_replace('/', '-', $data['year_month']) . ".pdf";
            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF de preview: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Erro ao gerar PDF: ' . $e->getMessage()], 500);
        }
    }
}