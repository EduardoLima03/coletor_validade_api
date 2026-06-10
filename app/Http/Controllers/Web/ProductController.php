<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Product::with('barcodes');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('barcodes', function ($q) use ($search) {
                      $q->where('ean', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->orderBy('code')->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|integer|unique:products,code',
            'description' => 'required|string|max:255',
        ]);

        $product = Product::create($data);

        AuditLog::log('create', 'product', $product->id, "Criou produto [{$product->code}] {$product->description}");

        return redirect()->route('admin.products.index')
            ->with('success', 'Produto cadastrado com sucesso.');
    }

    public function show($id)
    {
        $product = Product::with('barcodes')->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $returnUrl = request('return_url', route('admin.products.index'));
        $product = Product::findOrFail($id);
        return view('admin.products.edit', compact('product', 'returnUrl'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'code' => 'required|integer|unique:products,code,' . $product->id,
            'description' => 'required|string|max:255',
        ]);

        $product->update($data);

        AuditLog::log('update', 'product', $product->id, "Atualizou produto [{$product->code}] {$product->description}");

        $returnUrl = $request->return_url ?? route('admin.products.index');

        return redirect($returnUrl)
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $desc = "[{$product->code}] {$product->description}";
        $product->barcodes()->delete();
        $product->delete();

        AuditLog::log('delete', 'product', $id, "Deletou produto {$desc}");

        $returnUrl = $request->return_url ?? route('admin.products.index');

        return redirect($returnUrl)
            ->with('success', 'Produto deletado com sucesso.');
    }
}
