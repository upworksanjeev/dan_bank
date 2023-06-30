<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoinListRequest;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\UpdateCoinListRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\CoinList;
use App\Models\CoinCategory;

class CoinListController extends Controller
{
    public function index(PaginationRequest $request)
    {
        $per_page = 10;
        if($request->per_page)
         $per_page = $request->per_page;
        $coins = CoinList::query();
        $coins->with(['categories.category'])->orderBy('id', 'DESC');
        return response()->json($coins->paginate($per_page));
    }

    public function store(CoinListRequest $request)
    {
        if (!is_dir(public_path("assets/coin-lists/"))) mkdir(public_path("assets/coin-lists/"), 0777, true);

        $coin_list = new CoinList;
        $coin_list->coin_listing_id = (string) Str::uuid();
        $coin_list->amount = $request->amount;
        mkdir(public_path("assets/coin-lists/" . $coin_list->coin_listing_id), 0777, true);

        $logo_image = addFile($request->file('logo_image'), public_path("assets/coin-lists/" . $coin_list->coin_listing_id));
        $coin_list->image = $logo_image;
        $coin_list->addFlag(CoinList::FLAG_ACTIVE);
        if ($coin_list->save()) {
            if ($request->has('categories')) {
                foreach ($request->categories as $key => $value) {
                    $cats = new CoinCategory;
                    $cats->coin_category_id = (string) Str::uuid();
                    $cats->category_id = $value;
                    $cats->coin_listing_id = $coin_list->coin_listing_id;
                    $cats->save();
                }
            }
            return api_success('Coin Created!', $coin_list);
        }

        return api_error();
    }

    public function update(UpdateCoinListRequest $request, CoinList $coinList)
    {
        if ($request->has('categories')) {
            CoinCategory::where('coin_listing_id', $coinList->coin_listing_id)->delete();
            foreach ($request->categories as $key => $value) {
                $cats = new CoinCategory;
                $cats->coin_category_id = (string) Str::uuid();
                $cats->category_id = $value;
                $cats->coin_listing_id = $coinList->coin_listing_id;
                $cats->save();
            }
        }
        $coinList->amount = $request->amount;
        if ($request->hasFile('logo_image')) {
            if ($coinList->image) unlink(public_path("assets/coin-lists/" . $coinList->coin_listing_id. '/'. $coinList->image));

            $logo_image = addFile($request->file('logo_image'), public_path("assets/coin-lists/" . $coinList->coin_listing_id));
            $coinList->image = $logo_image;
        }

        if ($request->has('status')) {
            $coinList->removeFlag(CoinList::FLAG_ACTIVE);

            if ($request->status == 'active') $coinList->addFlag(CoinList::FLAG_ACTIVE);
        }
        if ($coinList->save()) return api_success('Coin Updated!', $coinList);

        return api_error();
    }
}