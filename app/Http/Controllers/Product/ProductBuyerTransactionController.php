<?php

namespace App\Http\Controllers\Product;

use App\Product;
use App\Transaction;
use App\Transformers\TransactionTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('transform.input:' . TransactionTransformer::class)->only(['store']);
    }

    /**
     * @param Request $request
     * @param Product $product
     * @param User $buyer
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Product $product, User $buyer)
    {
        $rules = [
            'quantity' => 'required|integer|min:1'
        ];

        $this->validate($request, $rules);

        if ($buyer->id == $product->seller_id){
            return $this->errorResponse('구매자와 판매자가 같습니다.', 409);
        }

        if (!$buyer->isVerified()){
            return $this->errorResponse('구매자 인증이 필요합니다', 409);
        }

        if (!$product->seller->isVerified()){
            return $this->errorResponse('판재자 인증이 필요합니다', 409);
        }

        if (!$product->isAvailable()){
            return $this->errorResponse('유효한 제품이 아닙니다',409);
        }

        if ($product->quantity < $request->quantity){
            return $this->errorResponse('요청하신 수량 보다 제품이 부족합니다',409);
        }

        return DB::transaction(function() use($request,$product,$buyer){
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
            ]);

            return $this->showOne($transaction, 201);
        });


    }
}
