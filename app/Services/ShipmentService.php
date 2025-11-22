<?php

namespace App\Services;

use App\Repositories\ShipmentRepository;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Helpers\BusinessDaysHelper;
use App\Models\ShipmentPdf;
use App\Models\DistributionCenter;

class ShipmentService
{
    protected $shipmentRepository;

    public function __construct(ShipmentRepository $shipmentRepository)
    {
        $this->shipmentRepository = $shipmentRepository;
    }

    public function getAll()
    {
        return $this->shipmentRepository->all();
    }

    public function findById($id)
    {
        return $this->shipmentRepository->find($id);
    }

    public function create(array $data)
    {
        // Calcular collection_date automaticamente se não fornecida
        if (!isset($data['collection_date']) && isset($data['shipment_date'])) {
            $collectionDate = BusinessDaysHelper::addBusinessDays($data['shipment_date'], 3);
            $data['collection_date'] = $collectionDate->format('Y-m-d');
        }
        
        $shipment = $this->shipmentRepository->create([
            'shipment_date' => $data['shipment_date'],
            'collection_date' => $data['collection_date'],
            'status' => $data['status'] ?? 'Pending',
            'name' => $data['name'],
            'client_id' => $data['client_id'],
            'distribution_center_id' => $data['distribution_center_id'],
            'shipment_code' => $data['shipment_code'] ?? null,
            'imported_flag' => $data['imported_flag'] ?? false,
            'creation_date' => $data['creation_date'],
            'total_value' => floatval($data['total_value'] ?? 0),
            'total_items' => intval($data['total_items'] ?? 0),
        ]);

        // Criar itens da remessa se fornecidos
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (!empty($item['product_id']) && !empty($item['quantity'])) {
                    $product = \App\Models\Product::find($item['product_id']);
                    if ($product) {
                        ShipmentItem::create([
                            'shipment_id' => $shipment->id,
                            'product_id' => $item['product_id'],
                            'name' => $product->name,
                            'fsnku' => $product->fsnku,
                            'sku' => $product->sku,
                            'type' => $product->type,
                            'kit_units' => $product->kit_units,
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'] ?? 0,
                            'total_value' => $item['quantity'] * ($item['unit_price'] ?? 0)
                        ]);
                    }
                }
            }
        }

        // Processar PDFs se fornecidos
        if (isset($data['pdfs']) && is_array($data['pdfs'])) {
            foreach ($data['pdfs'] as $pdfData) {
                if (isset($pdfData['pdf']) && $pdfData['pdf'] && isset($pdfData['tipo']) && $pdfData['tipo']) {
                    $path = $pdfData['pdf']->store("shipments/{$shipment->id}", 'public');
                    \App\Models\ShipmentPdf::create([
                        'shipment_id' => $shipment->id,
                        'type' => $pdfData['tipo'],
                        'path_pdf' => $path,
                    ]);
                }
            }
        }

        return $shipment;
    }

    public function update($id, array $data)
    {
        // Buscar remessa atual para preservar creation_date
        $currentShipment = $this->shipmentRepository->find($id);
        
        // Calcular collection_date automaticamente se não fornecida
        if (!isset($data['collection_date']) && isset($data['shipment_date'])) {
            $collectionDate = BusinessDaysHelper::addBusinessDays($data['shipment_date'], 3);
            $data['collection_date'] = $collectionDate->format('Y-m-d');
        }
        
        $shipment = $this->shipmentRepository->update($id, [
            'shipment_date' => $data['shipment_date'],
            'collection_date' => $data['collection_date'],
            'status' => $data['status'] ?? 'Pending',
            'name' => $data['name'],
            'client_id' => $data['client_id'],
            'distribution_center_id' => $data['distribution_center_id'],
            'shipment_code' => $data['shipment_code'] ?? null,
            'imported_flag' => $data['imported_flag'] ?? false,
            'creation_date' => $currentShipment->creation_date,
            'total_value' => floatval($data['total_value'] ?? 0),
            'total_items' => intval($data['total_items'] ?? 0),
        ]);

        // Atualizar itens da remessa
        if (isset($data['items']) && is_array($data['items'])) {
            // Remover itens existentes
            ShipmentItem::where('shipment_id', $id)->delete();
            
            // Criar novos itens
            foreach ($data['items'] as $item) {
                if (!empty($item['product_id']) && !empty($item['quantity'])) {
                    $product = \App\Models\Product::find($item['product_id']);
                    if ($product) {
                        ShipmentItem::create([
                            'shipment_id' => $id,
                            'product_id' => $item['product_id'],
                            'name' => $product->name,
                            'fsnku' => $product->fsnku,
                            'sku' => $product->sku,
                            'type' => $product->type,
                            'kit_units' => $product->kit_units,
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'] ?? 0,
                            'total_value' => $item['quantity'] * ($item['unit_price'] ?? 0)
                        ]);
                    }
                }
            }
        }

        // Processar PDFs se fornecidos
        if (isset($data['pdfs']) && is_array($data['pdfs'])) {
            foreach ($data['pdfs'] as $pdfData) {
                if (isset($pdfData['pdf']) && $pdfData['pdf'] && isset($pdfData['tipo']) && $pdfData['tipo']) {
                    $path = $pdfData['pdf']->store("shipments/{$id}", 'public');
                    \App\Models\ShipmentPdf::create([
                        'shipment_id' => $id,
                        'type' => $pdfData['tipo'],
                        'path_pdf' => $path,
                    ]);
                }
            }
        }

        return $shipment;
    }

    public function delete($id)
    {
        $shipment = $this->shipmentRepository->find($id);
        
        if ($shipment) {
            // Excluir PDFs físicos
            foreach ($shipment->pdfs as $pdf) {
                if (\Storage::disk('public')->exists($pdf->path_pdf)) {
                    \Storage::disk('public')->delete($pdf->path_pdf);
                }
            }
            
            // Excluir pasta da remessa
            $shipmentFolder = "shipments/{$id}";
            if (\Storage::disk('public')->exists($shipmentFolder)) {
                \Storage::disk('public')->deleteDirectory($shipmentFolder);
            }
            
            // Excluir PDFs do banco
            ShipmentPdf::where('shipment_id', $id)->delete();
            
            // Excluir itens
            ShipmentItem::where('shipment_id', $id)->delete();
        }
        
        return $this->shipmentRepository->delete($id);
    }
    
    /**
     * Get product price based on client's price table
     */
    public function getProductPrice($productId, $clientId, $quantity = 1)
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) {
            return ['error' => 'Produto não encontrado'];
        }
        
        if ($product->type === 'super_kit') {
            $price = $product->unit_price ?? 0;
        } else {
            $client = \App\Models\Client::find($clientId);
            
            if (!$client || !$client->price_table_id) {
                $price = $product->unit_price ?? 0;
            } else {
                $priceRange = \App\Models\PriceRange::where('price_table_id', $client->price_table_id)
                    ->where('min_value', '<=', $quantity)
                    ->where('max_value', '>=', $quantity)
                    ->first();
                
                if ($priceRange) {
                    if ($product->type === 'kit') {
                        $price = $priceRange->price_kit ?? $priceRange->price;
                    } else {
                        $price = $priceRange->price;
                    }
                } else {
                    $priceRange = \App\Models\PriceRange::where('price_table_id', $client->price_table_id)
                        ->orderBy('min_value')
                        ->first();
                    
                    if ($priceRange) {
                        if ($product->type === 'kit') {
                            $price = $priceRange->price_kit ?? $priceRange->price;
                        } else {
                            $price = $priceRange->price;
                        }
                    } else {
                        $price = $product->unit_price ?? 0;
                    }
                }
            }
        }
        
        return [
            'price' => $price,
            'product' => $product
        ];
    }
    
    /**
     * Import shipment from TSV file
     */
    public function previewTsv($file)
    {
        try {
            $fileHandle = fopen($file->getRealPath(), 'r');

            $data = [];
            $products = [];

            $header = null;
            $startedProducts = false;

            while (($line = fgets($fileHandle)) !== false) {
                $clean = trim($line);
                $parts = explode("\t", $clean);

                if (!$startedProducts && count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $data[$key] = $value;
                    continue;
                }

                if (!$startedProducts && count($parts) > 2) {
                    $header = array_map('trim', $parts);
                    $startedProducts = true;
                    continue;
                }

                if ($startedProducts && count($parts) >= count($header)) {
                    $row = array_combine($header, $parts);

                    $products[] = [
                        'sku'   => $row['SKU do vendedor'] ?? '',
                        'name'  => $row['Título'] ?? '',
                        'asin'  => $row['Código ASIN'] ?? '',
                        'fsnku' => $row['FNSKU'] ?? '',
                        'qtd'   => isset($row['Enviado']) ? (int)$row['Enviado'] : 0,
                    ];

                    continue;
                }
            }

            fclose($fileHandle);

            return [
                'data' => $data,
                'products' => $products
            ];

        } catch (\Exception $e) {
            return ['error' => 'Erro ao processar arquivo: ' . $e->getMessage()];
        }
    }

    
    public function importFromTsv($file, $clientId, $shipmentDate = null, $productsData = [])
    {
        try {
            $fileHandle = fopen($file->getRealPath(), 'r');
            $data = [];

            while (($line = fgets($fileHandle)) !== false) {
                $parts = explode("\t", trim($line));

                if (count($parts) >= 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $data[$key] = $value;
                }
            }

            fclose($fileHandle);

            $acronym = $data['Enviar para'] ?? null;
            $dc = DistributionCenter::where('acronym', $acronym)->first();

            if (!$dc) {
                return ['error' => "Centro de distribuição '$acronym' não encontrado", 'code' => 422];
            }

            $totalItems = 0;
            $totalValue = 0;
            $items = [];
            
            foreach ($productsData as $productData) {
                $product = \App\Models\Product::where('client_id', $clientId)
                    ->where('fsnku', $productData['fsnku'])
                    ->first();
                    
                if ($product) {
                    $quantity = intval($productData['qtd'] ?? 0);
                    
                    $priceResult = $this->getProductPrice($product->id, $clientId, $quantity);
                    $unitPrice = $priceResult['price'] ?? 0;
                    
                    $items[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice
                    ];
                    
                    $totalItems += $quantity;
                    $totalValue += $quantity * $unitPrice;
                }
            }

            $shipmentData = [
                'shipment_code' => $data['ID do envio'] ?? null,
                'name' => $data['Nome'] ?? 'Remessa Importada',
                'client_id' => $clientId,
                'distribution_center_id' => $dc->id,
                'shipment_date' => $shipmentDate ?: now()->format('Y-m-d'),
                'status' => 'Pending',
                'creation_date' => now()->format('Y-m-d'),
                'total_value' => $totalValue,
                'total_items' => $totalItems,
                'imported_flag' => true,
                'items' => $items
            ];

            $shipment = $this->create($shipmentData);
            
            return [
                'shipment' => $shipment,
                'shipment_code' => $shipmentData['shipment_code']
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23505") {
                return [
                    'error' => "A remessa já foi importada anteriormente.",
                    'code' => 422
                ];
            }
            return [
                'error' => "Erro ao salvar a remessa.",
                'code' => 422
            ];
        } catch (\Exception $e) {
            return [
                'error' => "Erro ao processar arquivo: " . $e->getMessage(),
                'code' => 500
            ];
        }
    }
}
