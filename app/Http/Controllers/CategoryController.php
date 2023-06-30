<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategroyRequest;
use App\Http\Requests\PaginationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Category;
use App\Models\CoinCategory;
use App\Models\CoinList;

class CategoryController extends Controller
{
    public function index(PaginationRequest $request)
    {
        $per_page = 20;
        if ($request->per_page)
            $per_page = $request->per_page;
        $all_cats = Category::orderBy('id', 'DESC')->paginate($per_page);
        return $all_cats;
    }

    public function create()
    {
    }


    public function store(CategroyRequest $request)
    {
        $category = new Category;
        $category->category_id = (string) Str::uuid();
        $category->title = $request->title;
        $category->addFlag(Category::FLAG_ACTIVE);
        if ($category->save()) return api_success('Category Uploaded!', $category);
        return api_error();
    }

    public function get_active(Request $request)
    {
        $categories_with_coins = Category::whereRaw('`flags` & ?=?', [Category::FLAG_ACTIVE, Category::FLAG_ACTIVE])->with(['coin_categories.coin_obj' => function ($q) {
            $q->whereRaw('`flags` & ?=?', [CoinList::FLAG_ACTIVE, CoinList::FLAG_ACTIVE])->orderBy('id', 'DESC');
        }])->orderBy('id', 'DESC')->get();

        return response()->json($categories_with_coins);
    }

    public function edit(Category $category)
    {
    }

    public function update(CategroyRequest $request, Category $category)
    {
        $category->title = $request->title;
        if ($request->has('status')) {
            $category->removeFlag(Category::FLAG_ACTIVE);
            if ($request->status == 'active')
                $category->addFlag(Category::FLAG_ACTIVE);
        }
        if ($category->save()) return api_success('Category Updated!', $category);
    }



    public function destroy(Category $category)
    {
        if (CoinCategory::where('category_id', $category->category_id)->delete()) {
            if ($category->delete()) return api_success1('Category Deleted Successfully');
            return api_error('Category did not Delete');
        } else
            return api_error();
    }
}
