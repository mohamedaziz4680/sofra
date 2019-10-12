<?php

namespace App\Http\Controllers\api\resturant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Models\Resturant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPassword;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator= validator()->make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:resturants',
            'neighborhood_id' => 'required',
            'phone' => 'required',
            'category_id' => 'required',
            'minmum_order' => 'required',
            'delivery_fees' => 'required',
            'contact_phone' => 'required',
            'whatsapp' => 'required',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return responseJson(0, $validator->errors()->first(), $validator->errors());
        }

        if($request->hasFile('image')){
            // get just extension
            $extension = $request->file('image')->getClientOriginalExtension();
            // file name to store
            $fileNameToStore = rand(111111,999999).'_'.time().'.'.$extension;
            // upload image
            $path =  $request->file('image')->storeAs('public/images' , $fileNameToStore);
        } else {
            $fileNameToStore = 'no-image.jpg';
        }

        $request->merge(['password' => bcrypt($request->password)]);
        $resturant = Resturant::create($request->all());
        $resturant->api_token = Str::random(60);
        $resturant->image = 'storage/images/'.$fileNameToStore;
        $resturant->save();

        return responseJson(1, 'success', [
            'api_token' => $resturant->api_token,
            'resturant' => $resturant
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validator= validator()->make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'neighborhood_id' => 'required',
            'phone' => 'required',
            'category_id' => 'required',
            'minmum_order' => 'required',
            'delivery_fees' => 'required',
            'contact_phone' => 'required',
            'whatsapp' => 'required',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return responseJson(0, $validator->errors()->first(), $validator->errors());
        }

        $request->merge(['password' => bcrypt($request->password)]);
        $resturant = $request->user()->update($request->all());

        if ($resturant) {
            return responseJson(1, 'updated');
        } else {
            return responseJson(0, ' failed to update');
        }
    }

    public function login(Request $request)
    {
        $validator= validator()->make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return responseJson(0, $validator->errors()->first(), $validator->errors());
        }

        $resturant = Resturant::where('email', $request->email)->first();
        if ($resturant) {
            if (Hash::check($request->password, $resturant->password)) {
                return responseJson(1, 'success', [
                    'api_token' => $resturant->api_token,
                    'client' => $resturant
                ]);
            } else {
                return responseJson(0, 'fails');
            }
        }
    }

    public function resetPassword(Request $request)
    {
        $validator= validator()->make($request->all(), [
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return responseJson(0, $validator->errors()->first(), $validator->errors());
        }
        $resturant = Resturant::where('email', $request->email)->first();
        if ($resturant) {
            $code = rand(1111, 9999);
            $update = $resturant->update(['pin_code' => $code]);

            if ($update) {
                // send email
                Mail::to($resturant->email)
                    ->bcc("mohamed.aziz.4680@gmail.com")
                    ->send(new ResetPassword($code));

                return responseJson(1, "success", ['pin_code_for_test' => $code]);
            } else {
                return responseJson(0, 'failed');
            }
        } else {
            return responseJson(0, 'user not found');
        }
    }

    public function newPassword(Request $request)
    {
        $validator= validator()->make($request->all(), [
            'email' => 'required',
            'password' => 'required|confirmed',
            'pin_code' => 'required',
        ]);

        if ($validator->fails()) {
            return responseJson(0, $validator->errors()->first(), $validator->errors());
        }

        $resturant = Resturant::where('email', $request->email)->first();
        if ($resturant->pin_code == $request->pin_code) {
            $request->merge(['password' => bcrypt($request->password)]);
            $update = $resturant->update([
                'password' => $request->password,
                'pin_code' => null
            ]);

            if ($update) {
                return responseJson(1, 'password changed');
            } else {
                return responseJson(0, 'failed to change password');
            }
        } else {
            return responseJson(0, 'pin_code is not correct');
        }
    }

    public function registerToken(Request $request)
    {
        $validation = validator()->make($request->all(), [
            'token' => 'required',
            'platform' => 'required|in:android,ios'
        ]);

        if ($validation->fails()) {
            return responseJson(0, $validation->errors()->first(), $validation->errors());
        }

        Token::where('token', $request->token)->where('tokenable_type','App\Models\Resturant')->delete();
        $request->user()->tokens()->create($request->all());
        return responseJson(1, 'register success');
    }

    public function removeToken(Request $request)
    {
        $validation= validator()->make($request->all(), [
            'token' => 'required'
        ]);

        if ($validation->fails()) {
            return responseJson(0, $validation->errors()->first(), $validation->errors());
        }

        Token::where('token', $request->token)->delete();
        return responseJson(1, 'deleted success');
    }
}
