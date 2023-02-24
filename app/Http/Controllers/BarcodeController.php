<?php

namespace App\Http\Controllers;

use App\Models\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarcodeController extends Controller
{
    public function __construct(Barcode $barcode) {
        $this->barcode = $barcode;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return $this->barcode->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->barcode->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->barcode->findOrFail($id);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function saveAll(Request $request){
        $barcodes = $request->all();

        foreach($barcodes as $key => $barcode){
            Product::create($barcode);
        }

        return response()->json(['success' => 'EANs cadastrados com sucesso.'], 200);
    }

    public function findByEan($ean){
        //Quando usa a consulta personalizada ele sempre retorna uma lista 
        //$product = DB::select('select p.code, p.description, b.ean  from barcodes as b inner join products as p on p.id = b.product_id where b.ean ='.$ean);

        //primeiro metodo
        $product = DB::table('barcodes')
                    ->join('products', 'products.id', 'barcodes.product_id')
                    ->select('products.code', 'products.description', 'barcodes.ean')
                    ->where('barcodes.ean', $ean)
                    ->get()
                    ->first();

        //$product = DB::select('select p.code, p.description, b.ean  from barcodes as b inner join products as p on p.id = b.product_id where b.ean ='.$ean fetch first 1 rows only)->get()->first();
        /*
            Esse metodo acredito que vc possa usar o get()->first() tbm ao final, so tem que testar, nao lembro de funciona. cara ai posso faça a consulta que retorne o primeiro item, so nao sei se o laravel reconece e retorna 1 unico ou inda vai joga em uma lista
            Nao, sempre que vc usar uma consulta personalida com where, ele retorna em uma lista, ai vc tem que pegar o primeiro elemento
            nao lebro se funciona dessa forma de cima, vc testa para saber
            mas da forma de cima funciona perfeitamente
        */


        //Se o produto náo existir retorna essa msg de erro com o codigo 404 not found
        if($product == null) {
            return response()->json(['Erro' => 'Produto nao encontrado'], 404);
        }

            // foi mal
        return response()->json($product, 200);
    }
}
