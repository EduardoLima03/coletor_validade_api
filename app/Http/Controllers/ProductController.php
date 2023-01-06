<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
            $data = Product::create($request->all());

            return response()->json(['success' => 'Produtos cadastrados com sucesso.'], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Product::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update($request->all());

        return $product;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Product::delete($id);
        return response()->json(["Product Id:"=> $id, "message:"=> "Success Delete"], 200);
    }

    public function findByCode(Request $request){
        //como vc passou a url assim product-by-code?code=1, tem que pegar o code de dentro do request

        $product = DB::select('select * from products where code = '.$request->code);

        //É sempre bom retornar tudo em json

        return response()->json($product, 200);
    }

    public function findByCode2($code){
        //como vc passou a url assim product-by-code?code=1, tem que pegar o code de dentro do request

        $product = DB::select('select * from products where code = '.$code);

        //É sempre bom retornar tudo em json

        return response()->json($product, 200);
    }

    public function saveAll(Request $request){
        $products = $request->all();

        foreach ($products as $key => $product) {
            Product::create($product);
        }

        return response()->json(['success' => 'Produtos cadastrados com sucesso.'], 200);
    }
}
