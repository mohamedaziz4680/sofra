<?php

namespace App\Http\Controllers\api\resturant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Offer;

class MainController extends Controller
{
    public function categories()
    {
        $categories = Category::all();

        return responseJson(1, 'success', $categories);
    }

    public function addItem(Request $request)
    {
        $validator= validator()->make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'price_in_offer' => 'required',
            'time_to_ready' => 'required',
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

        $request->image = 'storage/images/'.$fileNameToStore;

        $item = Item::create($request->all());

        return responseJson(1, 'success', $item);
    }

    public function editItem(Request $request)
    {
        $validator= validator()->make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'price_in_offer' => 'required',
            'time_to_ready' => 'required',
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

        $request->image = 'storage/images/'.$fileNameToStore;

        $item = Item::find($request->item_id)->update($request->all());;


        return responseJson(1, 'success', $item);
    }

    public function deleteItem(Request $request)
    {
        $item = Item::find($request->item_id)->delete();
        return responseJson(1, 'deleted successfuly');
    }

    public function listItem(Request $request)
    {
        $items = Item::where('resturant_id', $request->resturant_id)->paginate(20);
        return responseJson(1, 'success', $items);
    }

    public function addOffer(Request $request)
    {
        $validator= validator()->make($request->all(), [
            'name' => 'required',
            'content' => 'required',
            'price' => 'required',
            'starting_at' => 'required',
            'ending_at' => 'required',
        ]);

        if ($validator->fails()) {
            return responseJson(0, $validator->errors()->first(), $validator->errors());
        }

        $offer = Offer::create($request->all());

        return responseJson(1, 'success', $offer);
    }

    public function editOffer(Request $request)
    {
        $validator= validator()->make($request->all(), [
            'name' => 'required',
            'content' => 'required',
            'price' => 'required',
            'starting_at' => 'required',
            'ending_at' => 'required',
        ]);

        if ($validator->fails()) {
            return responseJson(0, $validator->errors()->first(), $validator->errors());
        }

        $offer = Offer::find($request->offer_id)->update($request->all());;


        return responseJson(1, 'success', $offer);
    }

    public function deleteOffer(Request $request)
    {
        $offer = Offer::find($request->offer_id)->delete();
        return responseJson(1, 'deleted successfuly');
    }

    public function listOffer(Request $request)
    {
        $offer = Offer::where('resturant_id', $request->resturant_id)->paginate(20);
        return responseJson(1, 'success', $offer);
    }

    public function myOrders(Request $request)
    {
        $orders = $request->user()->orders()->where(function($order) use($request){
            if ($request->has('state') && $request->state == 'completed')
            {
                $order->where('state' , '!=' , 'pending');
            }elseif ($request->has('state') && $request->state == 'current')
            {
                $order->where('state' , '=' , 'accepted');
            }elseif ($request->has('state') && $request->state == 'pending')
            {
                $order->where('state' , '=' , 'pending');
            }
        })->with('client','items','restaurant.categories')->latest()->paginate(20);
        return responseJson(1,'success',$orders);
    }

    public function showOrder(Request $request)
    {
        $order= Order::with('items','client','restaurant.categories')->find($request->order_id);
        return responseJson(1,'success',$order);
    }

    public function acceptOrder(Request $request)
    {
        $order= $request->user()->orders()->find($request->order_id);
        if (!$order)
        {
            return responseJson(0,'failed');
        }
        if ($order->state == 'accepted')
        {
            return responseJson(1,'accepted');
        }
        $order->update(['state' => 'accepted']);
        $client = $order->client;
        $client->notifications()->create([
            'title' => 'Your order is accepted',
            'content' => 'Order no. '.$request->order_id.' is accepted',
            'action' => 'accept order',
            'order_id' => $request->order_id,
        ]);
        $tokens = $client->tokens()->where('token','!=','')->pluck('token')->toArray();
        $audience = ['include_player_ids' => $tokens];
        $contents = [
            'en' => 'Order no. '.$request->order_id.' is accepted',
        ];
        $send = notifyByFirebase($audience , $contents , [
            'user_type' => 'client',
            'action' => 'accept-order',
            'order_id' => $request->order_id,
            'restaurant_id' => $request->user()->id,
        ]);
        return responseJson(1,'success');
    }

    public function rejectOrder(Request $request)
    {
        $order= $request->user()->orders()->find($request->order_id);
        if (!$order)
        {
            return responseJson(0,'failed');
        }
        if ($order->state == 'rejected')
        {
            return responseJson(1,'rejected');
        }
        $order->update(['state' => 'rejected']);
        $client = $order->client;
        $client->notifications()->create([
            'title' => 'Your order is rejected',
            'content' => 'Order no. '.$request->order_id.' is rejected',
            'action' => 'reject order',
            'order_id' => $request->order_id,
        ]);
        $tokens = $client->tokens()->where('token','!=','')->pluck('token')->toArray();
        $audience = ['include_player_ids' => $tokens];
        $contents = [
            'en' => 'Order no. '.$request->order_id.' is rejected',
        ];
        $send = notifyByOneSignal($audience , $contents , [
            'user_type' => 'client',
            'action' => 'reject-order',
            'order_id' => $request->order_id,
            'restaurant_id' => $request->user()->id,
        ]);
        return responseJson(1,'success to reject the order');
    }

    public function confirmOrder(Request $request)
    {
        $order = $request->user()->orders()->find($request->order_id);
        if (!$order)
        {
            return responseJson(0,'failed');
        }
        if ($order->state != 'accepted')
        {
            return responseJson(0,'failed to accept');
        }
        $order->update(['state' => 'delivered']);
        $client = $order->client;
        $client->notifications()->create([
            'title' => 'Your order is delivered',
            'content' => 'Order no. '.$request->order_id.' is delivered to you',
            'order_id' => $request->order_id,
        ]);
        $tokens = $client->tokens()->where('token','!=','')->pluck('token')->toArray();
        $audience = ['include_player_ids' => $tokens];
        $contents = [
            'en' => 'Order no. '.$request->order_id.' is delivered to you',
            'ar' => 'تم تأكيد التوصيل للطلب رقم '.$request->order_id,
        ];
        $send = notifyByFirebase($audience , $contents , [
            'user_type' => 'client',
            'action' => 'confirm-order-delivery',
            'order_id' => $request->order_id,
            'restaurant_id' => $request->user()->id,
        ]);
        return responseJson(1,'success');
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        return responseJson(1,'تم التحميل',$notifications);
    }

    
}
