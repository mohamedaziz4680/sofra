<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Neighborhood;
use App\Models\Resturant;
use App\Models\Item;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Contact;
use App\Models\Offer;
use App\Models\Setting;

class GeneralsController extends Controller
{
    public function cities()
    {
        $cities = City::all();

        return responseJson(1, 'success', $cities);
    }

    public function neighborhoods(Request $request)
    {
        $neighborhoods = Neighborhood::where(function ($query) use ($request) {
            if ($request->has('city_id')) {
                $query->where('city_id', $request->city_id);
            }
        })->get();

        return responseJson(1, 'success', $neighborhoods);
    }

    public function resturants(Request $request)
    {
        $resturants = Resturant::where(function ($query) use ($request) {
            if ($request->has('city_id') || $request->has('name')) {
                $query->whereHas('neighborhood', function ($query) use($request){
                    $query->where('city_id', $request->city_id);
                });
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%'.$request->name.'%');
                });
            }
        })->paginate(20);
        return responseJson(1, 'success', $resturants);
    }

    public function items(Request $request)
    {
        $items = Item::where(function ($query) use ($request) {
            if ($request->has('resturant_id')) {
                $query->where('resturant_id', $request->resturant_id);
            }
        })->get();

        return responseJson(1, 'success', $items);
    }

    public function comments(Request $request)
    {
        $comments = Comment::where(function ($query) use ($request) {
            if ($request->has('comment_id')) {
                $query->where('comment_id', $request->comment_id);
            }
        })->get();

        return responseJson(1, 'success', $comments);
    }

    public function resturantDetails(Request $request)
    {
        $resturant= Resturant::where('id',$request->resturant_id)->first();

        return responseJson(1, 'success', $resturant);
    }

    public function listNotifications()
    {
        $notifications= Notification::paginate(20);
        return responseJson(1, 'success', $notifications);
    }

    public function contactUs(Request $request)
    {
        $validation = validator()->make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'body' => 'required',
            'type' => 'required|in:suggestion,inquiry,complaint'
        ]);

        if ($validation->fails()) {
            return responseJson(0, $validation->errors()->first(), $validation->errors());
        }

        $contactUs = Contact::create($request->all());
        return responseJson(1, 'succes send', $contactUs);
    }

    public function offers()
    {
        $offers= Offer::paginate(20);
        return responseJson(1,'success',$offers);
    }

    public function settings()
    {
        $settings= Setting::get();
        return responseJson(1,'success',$settings);
    }

    public function paymentMethods()
    {
        $methods = PaymentMethod::all();
        return responseJson(1,'success',$methods);
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->with('order')->latest()->paginate(20);
        return responseJson(1,'successs',$notifications);
    }

}
