<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User as User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use function GuzzleHttp\Promise\all;

class UserController extends Controller
{
    // get all users
    public function index(User $user)
    {
        $allUsers = $user->get();
        return response()->json(["response" => $allUsers, "status" => 200]);
    }

    // create a new user 
    public function register(Request $request)
    {
        // declare validation rules
        $rules = [
            'name' => 'required|max:100',
            'username' => 'required|max:100|unique:users',
            'email' => 'required|max:100|email|unique:users',
            'dob' => 'date',
            'gender' => Rule::in(['f', 'm']),
            'password' => 'required|max:100|min:8',
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails()){
            return response()->json(["errors" => $validator->errors(), "status" => 400]);
        }
        
        // create new instance from user model
        $user = new User();
        
        // get request body inputs data
        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->dob = $request->input('dob');
        $user->gender = $request->input('gender');
        $password = $request->input('password');
        $user->password = bcrypt($password);

        // insert a new user 
        $user->save();
        // return the created user
        return response()->json(["message" => 'user has been added', "status" => 201]);

    }

    // update a existing user 
    public function update(Request $request , $id)
    {
        // check if the request body is empty
        if( count($request->all()) == 0)
        {
            return response()->json(["message" => 
            'request body can not be null', "status" => 400]);
        }
        // declare rules array to validate inputs 
        $rules = [];
        // get request input data
        $name = $request->input('name');
        $username = $request->input('username');
        $email = $request->input('email');
        $dob = $request->input('dob');
        $gender = $request->input('gender');

        // add rules depands on the updated values
        if($name)
        {
            $rules['name'] = 'required|max:100';
        }
        if ($username) 
        {
            $rules['username'] = 'required|max:100|unique:users';
        }
        if ($email) 
        {
            $rules['email'] = 'required|max:100|email|unique:users';
        }
        if ($dob) 
        {
            $rules['dob'] = 'date';
        }
        if ($gender) 
        {
            $rules['gender'] = Rule::in(['f', 'm']);
        }
        
        // implemanting the rules depand on the updated values    
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json(["errors"=>$validator->errors(), "status"=>400]);
        }

        // update the user
        $updatedUser = User::whereId($id)->update($request->all());
        
        if ($updatedUser) 
        {
            return response()->json(['message'=> 'user has been updated','status'=> 200]);
        } else {
            return response()->json(['message' => 'internal server error', 'status' => 500]);
        }
    }

    public function userMerchantsList(Request $request, $id){
        $mers_usr = DB::table('merc_usr')
            ->join('users', 'users.id', '=', 'merc_usr.user_id')
            ->join('merchants', 'merchants.id', '=', 'merc_usr.merc_id')
            ->select(["users.id as usr_id","users.username", "users.email" ,"merchants.id as merc_id", "merchants.name", "merchants.index_order"])
            ->where(["user_id" => $id])
            ->orderBy('index_order', 'Asc')
            ->get();

        if (count($mers_usr) > 0) {
            return response()->json(["response" => $mers_usr, "status" => 200]);    
        } elseif(count($mers_usr) == 0){
            return response()->json(["response" => $mers_usr,"message"=> "no associated merchants with this user id `${id}`" ,"status" => 200]);
        } else {
            return response()->json(["message" => "not found", "status" => 404]);
        }
        
    }

}