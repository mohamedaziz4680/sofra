<?php

namespace App\Http\Controllers\api\client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;

class MainController extends Controller
{
    public function addComment(Request $request)
    {
        $validation = validator()->make($request->all(), [
            'rate' => 'required',
            'content' => 'required',
            'resturant_id' => 'required|exists:resturants,id',
        ]);

        if ($validation->fails()) {
            return responseJson(0, $validation->errors()->first(), $validation->errors());
        }

        $comment = $request->user()->comments()->create($request->all());

        return responseJson(1, 'success', $comment);
    }


    public function newOrder(Request $request)
    {
        $validation = validator()->make($request->all(), [
            'restaurant_id'     => 'required|exists:restaurants,id',
            'items.*.item_id'   => 'required|exists:items,id',
            'items.*.quantity'  => 'required',
            'address'           => 'required',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);
        if ($validation->fails()) {
            $data = $validation->errors();
            return responseJson(0, $validation->errors()->first(), $data);
        }
        $restaurant = Restaurant::find($request->restaurant_id);
        
        $order = $request->user()->orders()->create([
                'restaurant_id'     => $request->restaurant_id,
                'note'              => $request->note,
                'state'             => 'pending',
                'address'           => $request->address,
                'payment_method_id' => $request->payment_method_id,
          ]);
        $cost = 0;
        $delivery_cost = $restaurant->delivery_cost;
            foreach ($request->items as $i) {
                $item = Item::find($i);
                $readyItem = [
                    $i['item_id'] => [
                        'quantity' => $i['quantity'],
                        'price'    => $item->price,
                        'note'     => (isset($i['note'])) ? $i['note'] : '',
                    ]   
                ];
                $order->items()->attach($readyItem);
                $cost += ($item->price * $i['quantity']);
            }
        
        
        if ($cost >= $restaurant->minimum_order) {
            $total = $cost + $delivery_cost; 
            $commission = settings()->commission * $cost;
            $net = $total - settings()->commission;
            $update = $order->update([
                     'price'         => $cost,
                     'delivery_cost' => $delivery_cost,
                     'total'         => $total,
                     'commission'    => $commission,
                     'net'           => $net,
                 ]);
            //Notificatios
            $notification = $restaurant->notifications()->create([
                    'title' =>'لديك طلب جديد',
                    'content' =>$request->user()->name .  'لديك طلب جديد من العميل ',
                    'action' =>  'new-order',
                    'order_id' => $order->id,
            ]);
            $tokens = $restaurant->tokens()->where('token', '!=', '')->pluck('token')->toArray();
            
            if (count($tokens)) {
                public_path();
                $title = $notification->title;
                $content = $notification->content;
                $data =[
                    'order_id' => $order->id,
                    'user_type' => 'restaurant',
                ];
                $send = notifyByFirebase($title, $content, $tokens, $data);
                info("firebase result: " . $send);
            }
            /* notification */
            $data = [
                'order' => $order->fresh()->load('items')
            ];
            return responseJson(1, 'success', $data);
        } else {
            $order->items()->delete();
            $order->delete();
            return responseJson(0, 'الطلب لابد أن لا يكون أقل من ' . $restaurant->minimum_charger . 'جنيه');
        }
    }

    public function myOrders(Request $request)
    {
        $orders = $request->user()->orders()->where(function ($order) use ($request) {
            if ($request->has('state') && $request->state == 'completed') {
                $order->where('state', '!=', 'pending');
            } elseif ($request->has('state') && $request->state == 'current') {
                $order->where('state', '=', 'pending');
            }
        })->with('items','restaurant.categories','client')->latest()->paginate(20);
        return responseJson(1, 'success', $orders);
    }

    public function showOrder(Request $request)
    {
        $order = Order::with('items','restaurant.categories','client')->find($request->order_id);
        return responseJson(1, 'success', $order);
    }

    public function latestOrder(Request $request)
    {
        $order = $request->user()->orders()
                         ->with('restaurant', 'items')
                         ->latest()
                         ->first();
        if ($order) {
            return responseJson(1, 'success', $order);
        }
        return responseJson(0, 'failed');
    }

    public function confirmOrder(Request $request)
    {
        $order = $request->user()->orders()->find($request->order_id);
        if (!$order) {
            return responseJson(0, 'failed');
        }
        if ($order->state != 'accepted') {
            return responseJson(0, 'failed to accepted');
        }
        $order->update(['state' => 'delivered']);
        $restaurant = $order->restaurant;
        $restaurant->notifications()->create([
                 'title'   => 'Your order is delivered to client',
                 'content' => 'Order no. ' . $request->order_id . ' is delivered to client',
                 'action' => 'delivered order',
                 'order_id'   => $request->order_id,
             ]);
        $tokens = $restaurant->tokens()->where('token', '!=', '')->pluck('token')->toArray();
        $audience = ['include_player_ids' => $tokens];
        $contents = [
            'en' => 'Order no. ' . $request->order_id . ' is delivered to client'
        ];
        $send = notifyByFirebase($audience, $contents, [
            'user_type' => 'restaurant',
            'action'    => 'confirm-order-delivery',
            'order_id'  => $request->order_id,
        ]);
        return responseJson(1, 'success');
    }

    public function declineOrder(Request $request)
    {
        $order = $request->user()->orders()->find($request->order_id);
        if (!$order) {
            return responseJson(0, 'failed');
        }
        if ($order->state != 'accepted') {
            return responseJson(0, 'failed to refuse it');
        }
        if ($order->delivery_confirmed_by_client == -1) {
            return responseJson(1, 'failed to accept it');
        }
        $order->update(['state' => 'declined']);
        $restaurant = $order->restaurant;
        $restaurant->notifications()->create([
             'title'   => 'Your order delivery is declined by client',
             'content' => 'Delivery if order no. ' . $request->order_id . ' is declined by client',
             'action' => 'decline order',
             'order_id'   => $request->order_id,
         ]);
        $tokens = $restaurant->tokens()->where('token', '!=', '')->pluck('token')->toArray();
        $audience = ['include_player_ids' => $tokens];
        $contents = [
            'en' => 'Delivery if order no. ' . $request->order_id . ' is declined by client',
        ];
        $send = notifyByFirebase($audience, $contents, [
            'user_type' => 'restaurant',
            'action'    => 'decline-order-delivery',
            'order_id'  => $request->order_id,
        ]);
        return responseJson(1, 'تم رفض استلام الطلب');
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        return responseJson(1, 'success', $notifications);
    }
}
