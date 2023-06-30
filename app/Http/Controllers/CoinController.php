<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoinRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Cartalyst\Stripe\Laravel\Facades\Stripe;

use App\Http\Requests\DeductAmount as DeductAmountRequest;
use App\Http\Requests\PaginationRequest;
use App\Models\CoinMedia;
use App\Models\Coin;
use App\Models\Friend;
use App\Models\User;
use App\Models\Setting;
use App\Models\Transaction;

class CoinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = 20;
        if ($request->has('per_page') && $request->filled('per_page')) $per_page =  $request->per_page;

        $coins = Coin::query();
        $coins->where('to', request()->user->user_id)->where('status', 'completed');
        $coins->with(['given_friend', 'medias', 'sender']);
        $coins->orderBy('id', 'DESC');
        return api_success('My Coins', $coins->paginate($per_page));
    }

    public function from_coins_list(Request $request)
    {
        $per_page = 20;
        if ($request->has('per_page') && $request->filled('per_page')) $per_page =  $request->per_page;

        $coins = Coin::query();
        $coins->where('from', request()->user->user_id)->where('status', 'completed');
        $coins->with(['given_friend', 'medias', 'sender']);
        $coins->orderBy('id', 'DESC');
        return api_success('My Coins', $coins->paginate($per_page));
    }

    public function getcoins(PaginationRequest $request){
        $per_page = 10;
        if ($request->per_page)
            $per_page = $request->per_page;
        $coins = Coin::when($request->search, function ($query) use ($request) {
            $query->where('event_name', 'LIKE', '%' . $request->search . '%');
        })->with('given_friend','sender')
            ->orderBy('created_at', 'desc')
            ->paginate($per_page);
            return response()->json($coins);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!is_dir(public_path("assets/coins/"))) {
            mkdir(public_path("assets/coins/"), 0777, true);
        }

        $coin = new Coin;
        $coin->coin_id = (string) Str::uuid();
        $coin->from = request()->user->user_id;
        $coin->to = $request->friend_id;
        if ($request->has('latitude') && $request->filled('latitude')) $coin->from_latitude = $request->latitude;
        if ($request->has('longitude') && $request->filled('longitude')) $coin->from_longitude = $request->longitude;
        if ($request->has('reason') && $request->filled('reason')) $coin->reason = $request->reason;
        if ($request->has('location_name') && $request->filled('location_name')) $coin->location_name = $request->location_name;
        if ($request->has('message') && $request->filled('message')) $coin->message = $request->message;
        mkdir(public_path("assets/coins/" . $coin->coin_id), 0777, true);
        
        if ($request->has('coin_image')) {
            $name = rand(9999, 99999);
            $extension = $request->coin_image->getClientOriginalExtension();
            $fileNameToStore = $name . '.' . $extension;
            $request->coin_image->move(public_path("assets/coins/" . $coin->coin_id), $fileNameToStore);
            $coin->coin_image = $fileNameToStore;
        }
        $coin->event_name = $request->event_name;
        $coin->amount = (Double) $request->amount;
        if ($request->amount == 0) {
            $coin->status = 'completed';

        } else {
            $coin->status = 'pending';

        }

        if ($coin->save()) {
            if ($coin->status == 'completed') {
                $user = User::where('user_id', $coin->to)->first();
                if ($user->device_token) {
                    $count = Coin::where(function ($q) use ($coin) {
                        $q->where('to', $coin->to);
            
                    })->where(function ($q) {
                        $q->where('status', 'completed');
            
                    })->whereRaw('`flags` & ?!=?', [Coin::FLAG_IS_OPENED, Coin::FLAG_IS_OPENED])->count();

                    $count1 = Friend::where(function ($q) use ($coin) {
                        $q->where('user_two', $coin->to);
            
                    })->whereRaw('`flags` & ?=?', [Friend::FLAG_PENDING, Friend::FLAG_PENDING])->count();

                    $data = [
                        "registration_ids" => [$user->device_token],
                        "notification" => [
                            "title" => 'Incoming Coin!',
                            "body" => request()->user->name. ' has sent you a coin!',
                            "badge" => $count+$count1
                        ]
                    ];
                    notification_core($data);
                }
            }
            if ($request->has('media')) {
                $image_array = array('jpeg', 'jpg', 'png');
                $video_array = array('mp4');
                $audio_array = array('wma', 'aac', 'wav', 'mp3', 'm4a');
                foreach ($request->media as $value) {
                    if (in_array(strtolower($value->getClientOriginalExtension()), $image_array)) {
                        $name = rand(9999, 99999);
                        $extension = $value->getClientOriginalExtension();
                        $fileNameToStore = $name . '.' . $extension;
                        $value->move(public_path("assets/coins/" . $coin->coin_id), $fileNameToStore);

                        $coin_media = new CoinMedia;
                        $coin_media->coin_media_id = (string) Str::uuid();
                        $coin_media->coin_id = $coin->coin_id;
                        $coin_media->media_url = $fileNameToStore;
                        $coin_media->type = 'image';
                        $coin_media->save();
                    } else if (in_array(strtolower($value->getClientOriginalExtension()), $video_array)) {
                        $name = rand(9999, 99999);
                        $extension = $value->getClientOriginalExtension();
                        $fileNameToStore = $name . '.' . $extension;
                        $value->move(public_path("assets/coins/" . $coin->coin_id), $fileNameToStore);

                        $coin_media = new CoinMedia;
                        $coin_media->coin_media_id = (string) Str::uuid();
                        $coin_media->coin_id = $coin->coin_id;
                        $coin_media->media_url = $fileNameToStore;
                        $coin_media->type = 'video';
                        $coin_media->save();

                    } else if (in_array(strtolower($value->getClientOriginalExtension()), $audio_array)) {
                        $name = rand(9999, 99999);
                        $extension = $value->getClientOriginalExtension();
                        $fileNameToStore = $name . '.' . $extension;
                        $value->move(public_path("assets/coins/" . $coin->coin_id), $fileNameToStore);

                        $coin_media = new CoinMedia;
                        $coin_media->coin_media_id = (string) Str::uuid();
                        $coin_media->coin_id = $coin->coin_id;
                        $coin_media->media_url = $fileNameToStore;
                        $coin_media->type = 'audio';
                        $coin_media->save();
                    }
                }
            }
        }
        $coin = Coin::where('coin_id', $coin->coin_id)->with(['given_friend', 'medias', 'sender'])->first();
        return api_success('Coin created!', $coin);
    }

    public function create_payment_intent (Request $request, Coin $coin)
    {
        $stripe_key = config('credentials.stripe_key');
        if ($coin->status != 'completed') {
            $receiver = User::where('user_id', $coin->to)->first();
            $settings = Setting::all();
            foreach ($settings as $set_key => $set_value) {
                if ($set_value->title == 'fees_type') {
                    $fees_type = $set_value->values;

                } else if ($set_value->title == 'fee_percent') {
                    $fees_percentage = $set_value->values;

                } else if ($set_value->title == 'stripe_fees_type') {
                    $stripe_fees_type = $set_value->values;

                } else if ($set_value->title == 'stripe_fee_percent') {
                    $stripe_fees_percentage = $set_value->values;

                } else if ($set_value->title == 'stripe_fees_on_stripe_fees_type') {
                    $stripe_fees_on_stripe_fees_type = $set_value->values;

                } else if ($set_value->title == 'stripe_fees_on_stripe_fees_value') {
                    $stripe_fees_on_stripe_fees_value = $set_value->values;

                }
            }

            $transaction = new Transaction;
            $transaction->transaction_id = (string) Str::uuid();
            $transaction->coin_id = $coin->coin_id;
            $transaction->status = 'pending';
            $transaction->total_amount = $coin->amount;
            $transaction->platform_percentage = $fees_percentage;
            $transaction->fees_type = $fees_type;
            $transaction->stripe_fees_percentage = $stripe_fees_percentage;
            $transaction->stripe_fees_type = $stripe_fees_type;
            $transaction->stripe_fee_on_stripe_fee_type = $stripe_fees_on_stripe_fees_type;
            $transaction->stripe_fee_on_stripe_fee_value = $stripe_fees_on_stripe_fees_value;

            if ($fees_type == 'percentage') {
                $vatTaxFinal = ($coin->amount/100) * $fees_percentage;
                $vatTaxFinal = ($vatTaxFinal + $coin->amount) - $coin->amount;

            } else {
                $vatTaxFinal = $fees_percentage;

            }
            $transaction->platform_share = $vatTaxFinal;

            if ($stripe_fees_type == 'percentage') {
                $total_amount = $coin->amount+$vatTaxFinal;

                $stripeVatTaxFinal = ($total_amount/100) * $stripe_fees_percentage;
                $stripeVatTaxFinal = ($stripeVatTaxFinal + $total_amount) - $total_amount;

            } else {
                $stripeVatTaxFinal = $stripe_fees_percentage;

            }
            $transaction->stripe_fees_share = $stripeVatTaxFinal+0.30;

            if ($stripe_fees_on_stripe_fees_type == 'percentage') {
                $new_stripe_fee_final = ($transaction->stripe_fees_share/100) * $stripe_fees_on_stripe_fees_value;
                $new_stripe_fee_final = ($new_stripe_fee_final + $transaction->stripe_fees_share) - $transaction->stripe_fees_share;

            } else {
                $new_stripe_fee_final = $stripe_fees_on_stripe_fees_value;

            }
            $transaction->stripe_fee_on_stripe_fee_share = $new_stripe_fee_final;

            $transaction->save();
            $amoount = number_format($transaction->total_amount+$transaction->platform_share+$transaction->stripe_fees_share+$transaction->stripe_fee_on_stripe_fee_share, 2);
            $amoount = $amoount*100;


            $stripe = new \Stripe\StripeClient($stripe_key);
            try {
                $payment_intent_response = $stripe->paymentIntents->create([
                    'amount' => $amoount,
                    'currency' => 'USD',
                    'automatic_payment_methods' => ['enabled' => 'true'],
                    'customer' => request()->user->customer_id
                ]);

            } catch (\Stripe\Exception\ApiErrorException $e) {
                return api_error_array([
                    "status" => $e->getHttpStatus(),
                    "type" => $e->getError()->type,
                    "code" => $e->getError()->code,
                    "param" => $e->getError()->param,
                    "message" => $e->getError()->message,
                ]);

            }
            $transaction->payment_intent_response = json_encode($payment_intent_response);
            $transaction->save();

            return api_success('Payment Intent Created!', $transaction);
        }
        return api_error('This coin is already sent!');
    }

    public function deduct_amount(DeductAmountRequest $request, Transaction $transaction)
    {
        $stripe_key = config('credentials.stripe_key');
        if ($transaction->status == 'pending' && $request->status == 'completed') {
            $stripe = new \Stripe\StripeClient($stripe_key);
            $payment_intent = json_decode($transaction->payment_intent_response);
            $coin = Coin::where('coin_id', $transaction->coin_id)->with(['given_friend', 'medias', 'sender'])->first();
            $receiver = User::where('user_id', $coin->to)->first();
            // $payment_intent_response = $stripe->paymentIntents->retrieve('pi_3M1rZ5Kf6BrUKvIN1KTf3Ztp');
            $payment_intent_response = $stripe->paymentIntents->retrieve($payment_intent->id);

            if (isset($payment_intent_response->charges) && isset($payment_intent_response->charges->data) && isset($payment_intent_response->charges->data[0]) && isset($payment_intent_response->charges->data[0]->status)) {
                if ($payment_intent_response->charges->data[0]->status == 'succeeded') {
                    $amoount = number_format($transaction->total_amount+$transaction->platform_share+$transaction->stripe_fees_share+$transaction->stripe_fee_on_stripe_fee_share, 2);
                    $amoount = $amoount*100;
                    try {
                        $charge_response = $stripe->charges->update($payment_intent_response->charges->data[0]->id,
                            [
                                'metadata' => [
                                    'coin_amount' => $transaction->total_amount .' USD',
                                    'ducky_bank_fees' => $transaction->platform_share .' USD',
                                    'stripe_fees' => $transaction->stripe_fees_share .' USD',
                                    'total_amount' => $amoount/100 .' USD',
                                    'stripe_fees_on_stripe_fees' => $transaction->stripe_fee_on_stripe_fee_share .' USD'
                                ]
                            ]
                        );

                        $transaction->stripe_response = json_encode($charge_response);
                        $transaction->addFlag(Transaction::FLAG_CHARGE_SUCCESSFULL);
                        $transaction->save();
                    } catch (\Stripe\Exception\ApiErrorException $e) {
                        $transaction->status = 'cancelled';
                        $transaction->save();
                        return api_error_array([
                            "status" => $e->getHttpStatus(),
                            "type" => $e->getError()->type,
                            "code" => $e->getError()->code,
                            "param" => $e->getError()->param,
                            "message" => $e->getError()->message,
                        ]); 
                    }

                    try {
                        $response2 = $stripe->transfers->create([
                            'amount' => $transaction->total_amount*100,
                            'currency' => 'USD',
                            'destination' => $receiver->stripe_account_id,
                            "source_transaction" => $charge_response->id,
                        ]);
        
                        $transaction->status = 'completed';
                        $transaction->addFlag(Transaction::FLAG_TRANSFER_SUCCESSFULL);
                        $transaction->transfer_response = json_encode($response2);
                        $transaction->save();

                        $coin->status = 'completed';
                        $coin->save();
                        $user = User::where('user_id', $coin->to)->first();
                        if ($user->device_token) {
                            // $count = Coin::where('to', $coin->to)->where('status', 'completed')->whereRaw('`flags` & ?!=?', [Coin::FLAG_IS_OPENED, Coin::FLAG_IS_OPENED])->count();
                            
                            $count = Coin::where(function ($q) use ($coin) {
                                $q->where('to', $coin->to);
                    
                            })->where(function ($q) {
                                $q->where('status', 'completed');
                    
                            })->whereRaw('`flags` & ?!=?', [Coin::FLAG_IS_OPENED, Coin::FLAG_IS_OPENED])->count();
                            
                            $count1 = Friend::where(function ($q) use ($coin) {
                                $q->where('user_two', $coin->to);
                    
                            })->whereRaw('`flags` & ?=?', [Friend::FLAG_PENDING, Friend::FLAG_PENDING])->count();

                            $data = [
                                "registration_ids" => [$user->device_token],
                                "notification" => [
                                    "title" => 'Incoming Coin!',
                                    "body" => request()->user->name. ' has sent you a coin!',
                                    "badge" => $count+$count1
                                ]
                            ];
                            notification_core($data);
                        }
                        return api_success('Gift Sent Successfully!', $coin);
        
                    } catch (\Stripe\Exception\ApiErrorException $e) {
                        $transaction->status = 'cancelled';
                        $transaction->removeFlag(Transaction::FLAG_TRANSFER_SUCCESSFULL);
                        $transaction->save();

                        return api_error_array([
                            "status" => $e->getHttpStatus(),
                            "type" => $e->getError()->type,
                            "code" => $e->getError()->code,
                            "param" => $e->getError()->param,
                            "message" => $e->getError()->message,
                        ]);
                    }
                } else {
                    $transaction->status = 'cancelled';
                    $transaction->removeFlag(Transaction::FLAG_TRANSFER_SUCCESSFULL);
                    $transaction->removeFlag(Transaction::FLAG_CHARGE_SUCCESSFULL);
                    $transaction->save();
                    return api_error('This transaction is not completed!', 400);

                }
            } else {
                $transaction->status = 'cancelled';
                $transaction->removeFlag(Transaction::FLAG_TRANSFER_SUCCESSFULL);
                $transaction->removeFlag(Transaction::FLAG_CHARGE_SUCCESSFULL);
                $transaction->save();
                return api_error('This transaction is not completed!', 400);
            }
        }
        return api_error('This coin is already sent!');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Coin  $coin
     * @return \Illuminate\Http\Response
     */
    public function open_coin(Request $request, Coin $coin)
    {
        if (!$coin->is_opened) {
            $coin->addFlag(Coin::FLAG_IS_OPENED);
            $coin->save();
        }
        return api_success('Coin is opened status updated!', $coin);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Coin  $coin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coin $coin)
    {
        //
    }
}
