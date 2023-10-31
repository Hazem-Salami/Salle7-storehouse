<?php

namespace App\Http\Middleware;

use App\Http\Traits\ResponseTrait;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;

class ProductExistence
{

    use ResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response | \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $product = Product::where('id', $request->id)->first();

        if($product) {

            $request->attributes->add(['product' => $product]);

            return $next($request);

        } else{
            return $this->customResponse(false, 'product Not Found', null, 400);
        }
    }
}
