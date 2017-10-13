<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Product;
use App\Seller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Seller $seller
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        $products = $seller->products;

        return $this->showAll($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param User $seller
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $seller)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer|min:1',
            'image' => 'required|image',
        ];

        $this->validate($request, $rules);

        $data = $request->all();

        $data['status'] = Product::UNAVAILABLE_PRODUCT;
        $data['image'] = $request->image->store('');
        $data['seller_id'] = $seller->id;

        $product = Product::create($data);

        return $this->showOne($product);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Seller $seller
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $rules = [
            'quantity' => 'integer|min:1',
            'status' => 'in:'. Product::AVAILABLE_PRODUCT . ',' . Product::UNAVAILABLE_PRODUCT,
            'image' => 'image',
        ];

        $this->validate($request, $rules);

        $this->checkSeller($seller, $product);

        $product->fill(array_filter($request->only(
            ['name',
             'description',
             'quantity',
            ]
        )));


        if ($request->has('status')){
            $product->status = $request->status;

            if ($product->isAvailable() && $product->categories()->count() ==0 ){
                return $this->errorResponse('제품상태를 바꾸려면 적어도 하나 이상의 카테고리가 있어야합니다', 409);
            }
        }

        if ($product->isClean()){
            return $this->errorResponse('업데이트 할 값을 입력해주세요', 422);
        }

        $product->save();

        return $this->showOne($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Seller $seller
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller, Product $product)
    {
        $this->checkSeller($seller, $product);

        $product->delete();
        Storage::delete($product->image);

        return $this->showOne($product);
    }

    public function checkSeller(Seller $seller, Product $product)
    {
        if ($seller->id != $product->seller_id) {
            throw new HttpException(422, '해당 제품은 판매자의 제품이 아닙니다');
        }
    }
}
