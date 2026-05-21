<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barcode;
use App\Models\Product;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Barcode::with('product');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ean', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($q) use ($search) {
                      $q->where('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                  });
            });
        }

        $barcodes = $query->orderBy('ean')->paginate(15)->withQueryString();

        return view('admin.barcodes.index', compact('barcodes', 'search'));
    }

    public function create()
    {
        $products = Product::orderBy('code')->get();
        return view('admin.barcodes.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ean' => 'required|integer|unique:barcodes,ean',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $barcode = Barcode::create($data);

        AuditLog::log('create', 'barcode', $barcode->id, "Criou EAN {$barcode->ean} para produto #{$barcode->product_id}");

        return redirect()->route('admin.barcodes.index')
            ->with('success', 'Código de barras cadastrado com sucesso.');
    }

    public function show($id)
    {
        $barcode = Barcode::with('product')->findOrFail($id);
        return view('admin.barcodes.show', compact('barcode'));
    }

    public function edit($id)
    {
        $barcode = Barcode::findOrFail($id);
        $products = Product::orderBy('code')->get();
        return view('admin.barcodes.edit', compact('barcode', 'products'));
    }

    public function update(Request $request, $id)
    {
        $barcode = Barcode::findOrFail($id);

        $data = $request->validate([
            'ean' => 'required|integer|unique:barcodes,ean,' . $barcode->id,
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $barcode->update($data);

        AuditLog::log('update', 'barcode', $barcode->id, "Atualizou EAN {$barcode->ean} para produto #{$barcode->product_id}");

        return redirect()->route('admin.barcodes.index')
            ->with('success', 'Código de barras atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $barcode = Barcode::findOrFail($id);
        $ean = $barcode->ean;
        $barcode->delete();

        AuditLog::log('delete', 'barcode', $id, "Deletou EAN {$ean}");

        return redirect()->route('admin.barcodes.index')
            ->with('success', 'Código de barras deletado com sucesso.');
    }
}
