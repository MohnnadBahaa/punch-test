<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Merchant as Merchant;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class MerchantController extends Controller
{
    // get all merchants
    public function index(Merchant $merchant)
    {
        $allMerchant = $merchant->orderBy('index_order', 'Asc')->get();
        if ($allMerchant) {
            return response()->json(['response' => $allMerchant, 'status' => 200]);
        } else {
            return response()->json(['message' => 'resources not found', 'status' => 404]);
        }
    }

    // create a new user Merchant
    public function register(Request $request)
    {
        // declare validation rules
        $rules = [
            'name' => 'required|max:100|unique:merchants',
            'index_order' => 'required|numeric'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // get the input data from req body
        $index_order = $request->input('index_order');
        $name = $request->input('name');

        // create new instance from Merchant model
        $merchant = new Merchant();


        $index_order = $request->input('index_order');
        $last_user = Merchant::select('id', 'index_order')->orderBy('index_order', 'desc')->first();
        $matched_user = Merchant::select('id', 'index_order')->where('index_order', $index_order)->first();

        return $this->order_algorth_on_add($index_order, $last_user, $matched_user, $merchant, $name);
    }

    public function update(Request $request, $id)
    {
        // check if the request body is empty
        if (count($request->all()) == 0) {
            return response()->json(["message" => 'request body can not be null', "status" => 400]);
        }

        $rules = [];
        $name = $request->input('name');
        $index_order = $request->input('index_order');

        if ($name) {
            $rules['name'] = 'required|max:100|unique:merchants';
        }
        if ($index_order) {
            $rules['index_order'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors(), "status" => 400]);
        }

        $last_user = Merchant::select('id', 'index_order')->orderBy('index_order', 'desc')->first();
        $matched_user = Merchant::select('id', 'index_order')->where('index_order', $index_order)->first();

        $merchant = Merchant::whereId($id);

        return $this->order_algorth_on_update($index_order, $last_user, $matched_user, $merchant, $name);
    }

    public function order_algorth_on_add($index_order, $last_user, $matched_user, $merchant, $name)
    {
        if ($index_order > $last_user['index_order']) {
            // index order > last  => index_order")
            $merchant->name = $name;
            $merchant->index_order = $index_order;
            $merchant->save();
            return response()->json(['message' => 'Merchant has been added', 'status' => 201]);
        } elseif ($index_order <= $last_user['index_order']) {
            // and is not matched  => index order
            if (!$matched_user['index_order']) {
                // index order < last  => index_order and not matched")
                $merchant->name = $name;
                $merchant->index_order = $index_order;
                $merchant->save();
                return response()->json(['message' => 'Merchant has been added', 'status' => 201]);
            }
            // and is  matched  => new = index order / old = last+1
            else {
                Merchant::whereId($matched_user['id'])->update(['index_order' => $last_user['index_order'] + 1]);
                $merchant->name = $name;
                $merchant->index_order = $matched_user['index_order'];
                $merchant->save();
                return response()->json(['message' => 'Merchant has been added', 'status' => 201]);
            }
        }
    }

    public function order_algorth_on_update($index_order, $last_user, $matched_user, $merchant, $name)
    {
        if ($index_order > $last_user['index_order']) {
            // index order > last  => index_order")
            $merchant->update(["name" => $name, "index_order" => $index_order]);
            return response()->json(['message' => 'Merchant has been updated', 'status' => 201]);
        } elseif ($index_order <= $last_user['index_order']) {
            if (!$matched_user['index_order']) {
                // index order < last  => index_order and not matched")
                $merchant->name = $name;
                $merchant->index_order = $index_order;
                $merchant->update(["name" => $name, "index_order" => $index_order]);
                return response()->json(['message' => 'Merchant has been updated', 'status' => 201]);
            } else {
                // and is  matched  => new = index order / old = last+1
                Merchant::whereId($matched_user['id'])->update(['index_order' => $last_user['index_order'] + 1]);
                $merchant->index_order = $matched_user['index_order'];
                $merchant->update(["name" => $name, "index_order" => $matched_user['index_order']]);
                return response()->json(['message' => 'Merchant has been updated', 'status' => 201]);
            }
        }
    }

    // link merchant with user
    public function subscribe(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'merc_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user_id = $request->input('user_id');
        $merc_id = $request->input('merc_id');

        $isRelationSynced = DB::table('merc_usr')->where(["user_id" => $user_id, "merc_id" => $merc_id])->get();

        if (count($isRelationSynced) == 0) {
            DB::table('merc_usr')->insert(["user_id" => $user_id, "merc_id" => $merc_id]);
            return response()->json(['message' =>
            'Merchant has been synced', 'status' => 201]);
        } else {
            return response()->json(['message' =>
            'Merchant has been synced before with this user', 'status' => 400]);
        }
    }
}