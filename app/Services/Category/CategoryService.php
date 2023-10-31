<?php

namespace App\Services\Category;

use App\Http\Requests\category\CreateCategoryRequest;
use App\Http\Requests\category\UpdateCategoryRequest;
use App\Jobs\category\CreateCategoryJob;
use App\Jobs\category\DeleteCategoryJob;
use App\Jobs\category\UpdateCategoryJob;
use App\Models\Category;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryService extends BaseService
{
    /**
     * @param Request
     * @return Response
     */
    public function getCategories($request): Response
    {
        $categories = Category::where('category_id', null)->paginate(\request('size'));

        $parentIDs_Son = array();

        if($request->children_id){

            $category = Category::where('id', $request->children_id)->first();

            while($category){

                $parentIDs_Son[] = $category->id;

                $category = Category::where('id', $category->category_id)->first();
            }

        }

        $this->getChildren($categories, $parentIDs_Son, $request->children_id, $request->load_more);

        return $this->customResponse(true, 'get Categories Success', $categories);
    }

    private function getChildren($categories, $parentIDs_Son, $children_id, $load_more = 1)
    {
        foreach ($categories as $key => $category){

            $category->key = $category->id;

            $category->data = [
                "name" => $category->name,
                "description" => $category->description,
                "category_id" => $category->category_id,
                "image_path" => $category->image_path,
                "created_at" => $category->created_at,
                "updated_at" => $category->updated_at,
            ];

            $index = array_search($category->id ,$parentIDs_Son);

            if($category->id == $children_id){

                $category->children = Category::where('category_id', $category->id)->limit($load_more * \request('capacity'))->get();

            }elseif($index){

                $count = Category::where('category_id', $category->id)
                    ->where('id', '<=' , $parentIDs_Son[$index-1])->count();

                $category->children = $count % 2 == 0
                    ? Category::where('category_id', $category->id)->limit($count)->get()
                    : Category::where('category_id', $category->id)->limit($count+1)->get();

            }else{

                $category->children = Category::where('category_id', $category->id)->limit(\request('capacity'))->get();
            }

            $this->getChildren($category->children, $parentIDs_Son, $children_id, $load_more);

            $categories[$key] = $category->only(['key','data','children']);
        }
        return $categories;
    }
}
