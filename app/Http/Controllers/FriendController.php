<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Coin;
use App\Models\Friend;
use App\Models\User;

class FriendController extends Controller
{
    public function index(Request $request)
    {
        $friends = Friend::query();
        $per_page = 10;
        if ($request->has('per_page') && $request->filled('per_page')) $per_page = $request->per_page;

        if ($request->has('key') && $request->filled('key')) {
            if ($request->key == 'friends') {
                $friends->whereRaw('`flags` & ?=?', [Friend::FLAG_ACCEPTED, Friend::FLAG_ACCEPTED]);

            } else if ($request->key == 'pending') {
                $friends->whereRaw('`flags` & ?=?', [Friend::FLAG_PENDING, Friend::FLAG_PENDING]);

            } else if ($request->key == 'rejected') {
                $friends->whereRaw('`flags` & ?=?', [Friend::FLAG_REJECTED, Friend::FLAG_REJECTED]);

            }
        }
        if(request()->user != null) {
            $friends->where('user_one', request()->user->user_id)->orWhere('user_two', request()->user->user_id);
            $friends->with(['one_friend_profile', 'two_friend_profile']);
        }
        return api_success('Friends List', $friends->paginate($per_page));
    }

    public function store(Request $request, User $user)
    {
        $found = Friend::where(function ($q) use ($user) {
            $q->where('user_one', $user->user_id)->where('user_two', request()->user->user_id);

        })->orWhere(function ($q) use ($user) {
            $q->where('user_two', $user->user_id)->where('user_one', request()->user->user_id);

        })->first();
        if ($found) {
            if ($found->pending) return api_error('Your request is in pending right now!');

            if ($found->accepted) return api_error('You guys are already friends!');

            if ($found->rejected) return api_error('Other guy rejected your request already!');
        } else {

            $friend = new Friend;
            $friend->friend_id = (string) Str::uuid();
            $friend->user_one = request()->user->user_id;
            $friend->user_two = $user->user_id;
            $friend->addFlag(Friend::FLAG_PENDING);
            if ($friend->save()) {
                if ($user->device_token) {
                    $count = Friend::where(function ($q) use ($user) {
                        $q->where('user_two', $user->user_id);
            
                    })->whereRaw('`flags` & ?=?', [Friend::FLAG_PENDING, Friend::FLAG_PENDING])->count();

                    $count1 = Coin::where(function ($q) use ($user) {
                        $q->where('to', $user->user_id);
            
                    })->where(function ($q) {
                        $q->where('status', 'completed');
            
                    })->whereRaw('`flags` & ?!=?', [Coin::FLAG_IS_OPENED, Coin::FLAG_IS_OPENED])->count();

                    // $count1 = Coin::where('to', $user->user_id)->where('status', 'completed')->whereRaw('`flags` & ?!=?', [Coin::FLAG_IS_OPENED, Coin::FLAG_IS_OPENED])->count();

                    $data = [
                        "registration_ids" => [$user->device_token],
                        "notification" => [
                            "title" => 'Incoming friend request!',
                            "body" => request()->user->name. ' has sent you a friend request!',
                            "badge" => $count+$count1
                        ]
                    ];
                    notification_core($data);
                }
                return api_success1('Friend request sent successfully!');
            }
        }
        return api_error();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Friend  $friend
     * @return \Illuminate\Http\Response
     */
    public function show(Friend $friend)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Friend  $friend
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Friend $friend)
    {
        $friend->removeFlag(Friend::FLAG_ACCEPTED);
        $friend->removeFlag(Friend::FLAG_PENDING);
        $friend->removeFlag(Friend::FLAG_REJECTED);
        if ($request->status == 'accepted') {
            $friend->addFlag(Friend::FLAG_ACCEPTED);

        } else if ($request->status == 'rejected') {
            $friend->addFlag(Friend::FLAG_REJECTED);

        }
        $other_friend = $friend->user_two;
        if (request()->user->user_id == $friend->user_two) {
            $other_friend = $friend->user_one;

        }

        if ($friend->save()) {
            $found = Friend::whereRaw('`flags` & ?=?', [Friend::FLAG_ACCEPTED, Friend::FLAG_ACCEPTED])->where(function ($q) {
                $q->where('user_two', request()->user->user_id);
    
            })->orWhere(function ($q) {
                $q->where('user_one', request()->user->user_id);
    
            })->count();
            $user = User::where('user_id', request()->user->user_id)->first();
            $user->total_friends = $found;

            $found2 = Friend::whereRaw('`flags` & ?=?', [Friend::FLAG_ACCEPTED, Friend::FLAG_ACCEPTED])->where(function ($q) use ($other_friend) {
                $q->where('user_two', $other_friend);
    
            })->orWhere(function ($q)  use ($other_friend) {
                $q->where('user_one', $other_friend);
    
            })->count();
            $user2 = User::where('user_id', $other_friend)->first();
            $user2->total_friends = $found2;

            if ($user2->save() && $user->save()) return api_success('Request status updated!', $friend);
        }
        return api_error();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Friend  $friend
     * @return \Illuminate\Http\Response
     */
    public function destroy(Friend $friend)
    {
        //
    }
}
