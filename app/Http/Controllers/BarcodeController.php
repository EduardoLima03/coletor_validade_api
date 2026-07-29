<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBarcodeRequest;
use App\Http\Requests\UpdateBarcodeRequest;
use App\Models\Barcode;
use App\Models\Product;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function __construct(Barcode $barcode)
    {
        $this->barcode = $barcode;
    }

    public function index()
    {
        return $this->barcode->with('product')->paginate(50);
    }

    public function store(StoreBarcodeRequest $request)
    {
        $barcode = $this->barcode->create($request->validated());
        return response()->json($barcode, 201);
    }

    public function show($id)
    {
        return $this->barcode->with('product')->findOrFail($id);
    }

    public function update(UpdateBarcodeRequest $request, $id)
    {
        $barcode = $this->barcode->findOrFail($id);
        $barcode->update($request->validated());
        return response()->json(['success' => 'EAN atualizado com sucesso.', 'data' => $barcode], 200);
    }

    public function destroy($id)
    {
        $barcode = $this->barcode->findOrFail($id);
        $barcode->delete();
        return response()->json(['message' => 'EAN deletado com sucesso.'], 200);
    }

    public function saveAll(Request $request)
    {
        $barcodes = $request->validate([
            '*.ean' => 'required|integer|unique:barcodes,ean',
            '*.product_id' => 'required|integer|exists:products,id',
        ]);

        $created = [];
        foreach ($barcodes as $barcode) {
            $created[] = Barcode::create($barcode);
        }

        return response()->json(['success' => 'EANs cadastrados com sucesso.', 'data' => $created], 201);
    }

    public function findByEan($ean)
    {
        $product = \DB::table('barcodes')
            ->join('products', 'products.id', 'barcodes.product_id')
            ->select('products.code', 'products.description', 'barcodes.ean')
            ->where('barcodes.ean', $ean)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Produto nao encontrado'], 404);
        }

        return response()->json($product, 200);
    }
}
