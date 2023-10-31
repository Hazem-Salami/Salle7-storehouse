<?php

namespace App\Services\Product;

use App\Http\Requests\product\CreateProductRequest;
use App\Http\Requests\product\UpdateProductRequest;
use App\Http\Traits\Base64Trait;
use App\Http\Traits\FilesTrait;
use App\Jobs\product\CreateProductJob;
use App\Jobs\product\DeleteProductJob;
use App\Jobs\product\UpdateProductJob;
use App\Jobs\revenue\AddRevenueJob;
use App\Jobs\wallets\WithdrawWalletJob;
use App\Models\Constant;
use App\Models\Product;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProductService extends BaseService
{
    use Base64Trait, FilesTrait;

    /**
     * @param CreateProductRequest
     * @return Response
     */
    public function createProduct(CreateProductRequest $request): Response
    {
        DB::beginTransaction();

        $category = $request->get('category');

        $user = User::find(auth()->user()->id);

        $ratio = (float) Constant::where('key', 'piece profit ratio')->first()->value;
        $revenue = $request->quantity? (($request->price * $ratio) / 100) * $request->quantity : ($request->price * $ratio) / 100;

        if (!$user->wallet)
            return $this->customResponse(false, 'لا يوجد لديك محفظة، الرجاء إنشاء محفظة');
        if ($user->wallet->amount - $revenue < 0)
            return $this->customResponse(false, 'لا يوجد رصيد كافي في محفظتك، الرجاء شحن المحفظة');

        $file = $request->file('product_photo');

        if ($file != null) {
            $product_photo = [$this->base64Encode($file), $file->getClientOriginalExtension()];

            $path = $this->storeFile($file, 'product_photo');
        }

        $product = $user->products()->create([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $category->id,
            'product_code' => $request->product_code,
            'made' => $request->made,
            'price' => $request->price,
            'image_path' => $path,
            'quantity' => $request->quantity? $request->quantity : 1,
        ]);

        $product->product_photo = $product_photo;

        $category = $product->category;
        $product->category_name = $category->name;

        $product->user_email = $user->email;

        try {

            CreateProductJob::dispatch($product->toArray())->onQueue('admin');
            CreateProductJob::dispatch($product->toArray())->onQueue('main');
            WithdrawWalletJob::dispatch(["user_email" => $user->email, 'charge' => $revenue])->onQueue('store');
            AddRevenueJob::dispatch(["user_email" => $user->email, 'revenue' => $revenue])->onQueue('admin');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->customResponse(false, 'Bad Internet', null, 504);
        }
        DB::commit();

        return $this->customResponse(true, 'Create Product Success',
            $product->only(['id','name','description','made','price','product_code','quantity','image_path','category_id','user_id','created_at','updated_at']));
    }

    public function updateProduct(UpdateProductRequest $request): Response
    {
        DB::beginTransaction();

        $product = $request->get('product');
        $user = User::find(auth()->user()->id);

        if($request->quantity > $product->quantity){

            $difference = $request->quantity > $product->quantity;

            $ratio = (float) Constant::where('key', 'piece profit ratio')->first()->value;
            $revenue = (($request->price * $ratio) / 100) * $difference;

            if (!$user->wallet)
                return $this->customResponse(false, 'لا يوجد لديك محفظة، الرجاء إنشاء محفظة');
            if ($user->wallet->amount - $revenue < 0)
                return $this->customResponse(false, 'لا يوجد رصيد كافي في محفظتك، الرجاء شحن المحفظة');

            try {

                WithdrawWalletJob::dispatch(["user_email" => $user->email, 'charge' => $revenue])->onQueue('store');
                AddRevenueJob::dispatch(["user_email" => $user->email, 'revenue' => $revenue])->onQueue('admin');

            } catch (\Exception $e) {
                DB::rollBack();
                return $this->customResponse(false, 'Bad Internet', null, 504);
            }
        }

        if($product->user_id == $user->id) {

            $old_made = $product->made;
            $old_product_code = $product->product_code;

            $product_photo = null;

            if ($request->has('product_photo')) {

                $file = $request->file('product_photo');

                if ($file != null) {
                    $product_photo = [$this->base64Encode($file), $file->getClientOriginalExtension()];

                    $image_path = $this->storeFile($file, 'product_photo');

                    $this->destoryFile($product->image_path);

                    $product->image_path = $image_path;
                }
            }

            $product->update($request->all());
            $product->save();

            $product->product_photo = $product_photo;
            $product->old_made = $old_made;
            $product->old_product_code = $old_product_code;

            $category = $product->category;
            $product->category_name = $category? $category->name : null;

            $product->user_email = $user->email;

            try {

                UpdateProductJob::dispatch($product->toArray())->onQueue('admin');
                UpdateProductJob::dispatch($product->toArray())->onQueue('main');

            } catch (\Exception $e) {
                DB::rollBack();
                return $this->customResponse(false, 'Bad Internet', null, 504);
            }
            DB::commit();

            return $this->customResponse(true, 'update Product Success',
                $product->only(['id','name','description','made','price','product_code','quantity','image_path','category_id','user_id','created_at','updated_at']));

        } else{
            return $this->customResponse(false, 'no Permission', null, 400);
        }
    }

    public function deleteProduct(Request $request): Response
    {
        DB::beginTransaction();

        $user = User::find(auth()->user()->id);

        $product = $request->get('product');

        $this->destoryFile($product->image_path);

        $product->delete();

        $product->user_email = $user->email;

        try {

            DeleteProductJob::dispatch($product->toArray())->onQueue('admin');
            DeleteProductJob::dispatch($product->toArray())->onQueue('main');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->customResponse(false, 'Bad Internet', null, 504);
        }
        DB::commit();

        return $this->customResponse(true, 'delete Product Success');
    }

    public function getProductsCategory(Request $request): Response
    {
        $category = $request->get('category');

        $products = Product::where('category_id', $category->id)->paginate(\request('size'));

        return $this->customResponse(true, 'get Products Success', $products);
    }

    public function getProducts(Request $request): Response
    {
        $user = User::find(auth()->user()->id);

        $products = Product::where('user_id', $user->id)->paginate(\request('size'));

        return $this->customResponse(true, 'get Products Success', $products);
    }
}
