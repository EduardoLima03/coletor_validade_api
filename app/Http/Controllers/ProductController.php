<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with('barcodes')->paginate(50);
    }

    public function store(StoreProductRequest $request)
    {
        $data = Product::create($request->validated());
        return response()->json(['success' => 'Produto cadastrado com sucesso.', 'data' => $data], 201);
    }

    public function show($id)
    {
        return Product::with('barcodes')->findOrFail($id);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());
        return response()->json(['success' => 'Produto atualizado com sucesso.', 'data' => $product], 200);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->barcodes()->delete();
        $product->delete();
        return response()->json(['message' => 'Produto deletado com sucesso.'], 200);
    }

    public function findByCode(Request $request, $code = null)
    {
        $code = $code ?? $request->code;
        if (!$code) {
            return response()->json(['error' => 'Código é obrigatório.'], 400);
        }
        $product = Product::where('code', $code)->with('barcodes')->first();
        if (!$product) {
            return response()->json(['error' => 'Produto nao encontrado'], 404);
        }
        return response()->json($product, 200);
    }

    public function saveAll(Request $request)
    {
        $products = $request->validate([
            '*.code' => 'required|integer|unique:products,code',
            '*.description' => 'required|string|max:255',
        ]);

        $created = [];
        foreach ($products as $product) {
            $created[] = Product::create($product);
        }

        return response()->json(['success' => 'Produtos cadastrados com sucesso.', 'data' => $created], 201);
    }
}
