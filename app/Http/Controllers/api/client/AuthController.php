<?php

namespace App\Http\Controllers\api\client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPassword;

class AuthController extends Controller
{
    public function register(Request $request)
    {
    
        $validator= validator()->make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:clients',
            'neighborhood_id' => 'required',
            'phone' => 'required',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return responseJson(0, $validator->errors()->first(), $validator->errors());
        }

        $request->merge(['password' => bcrypt($request->password)]);
        $client = Client::create($request->all());
        $client->api_token = Str::random(60);
        $client->save();

        return responseJson(1, 'success', [
            'api_token' => $client->api_token,
            'client' => $client
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validator= validator()->make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:clients',
            'neighborhood_id' => 'required',
            'phone' => 'required',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return responseJson(0, $validator->errors()->first(), $validator->errors());
        }

        $request->merge(['password' => bcrypt($request->password)]);
        $client = $request->user()->update($request->all());

        if ($client) {
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

        $client = Client::where('email', $request->email)->first();
        if ($client) {
            if (Hash::check($request->password, $client->password)) {
                return responseJson(1, 'success', [
                    'api_token' => $client->api_token,
                    'client' => $client
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
        $client = Client::where('email', $request->email)->first();
        if ($client) {
            $code = rand(1111, 9999);
            $update = $client->update(['pin_code' => $code]);

            if ($update) {
                // send email
                Mail::to($client->email)
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

        $client = Client::where('email', $request->email)->first();
        if ($client->pin_code == $request->pin_code) {
            $request->merge(['password' => bcrypt($request->password)]);
            $update = $client->update([
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

        Token::where('token', $request->token)->where('tokenable_type','App\Models\Client')->delete();
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
