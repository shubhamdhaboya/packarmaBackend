<?php

namespace App\Http\Controllers\customerapi;

use App\Http\Controllers\Controller;
use App\Models\UserSubscriptionPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Response;

class SubscriptionApiController extends Controller
{
    /**
     * Created By : Pradyumn Dwivedi
     * Created at : 10-05-2022
     * Uses : Display a listing of the subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $msg_data = array();
        try {
            $token = readHeaderToken();
            if ($token) {
                $page_no = 1;
                $limit = 10;
                $orderByArray = ['subscriptions.subscription_type' => 'ASC'];
                $defaultSortByName = false;
                if (isset($request->page_no) && !empty($request->page_no)) {
                    $page_no = $request->page_no;
                }
                if (isset($request->limit) && !empty($request->limit)) {
                    $limit = $request->limit;
                }
                $offset = ($page_no - 1) * $limit;
                $data = Subscription::select('id', 'subscription_type', 'amount');
                $subscriptionData = Subscription::whereRaw("1 = 1");



                if ($request->subscription_id) {
                    $subscriptionData = $subscriptionData->where('id', $request->subscription_id);
                    $data = $data->where('id', $request->subscription_id);
                }
                if ($request->subscription_type) {
                    $subscriptionData = $subscriptionData->where('subscription_type', $request->subscription_type);
                    $data = $data->where('subscription_type', $request->subscription_type);
                }
                if (empty($subscriptionData->first())) {
                    errorMessage(__('subscription.subscription_not_found'), $msg_data);
                }
                if (isset($request->search) && !empty($request->search)) {
                    $data = fullSearchQuery($data, $request->search, 'subscription_type');
                }
                if ($defaultSortByName) {
                    $orderByArray = ['subscriptions.subscription_type' => 'ASC'];
                }
                $data = allOrderBy($data, $orderByArray);
                $total_records = $data->get()->count();
                $data = $data->limit($limit)->offset($offset)->get()->toArray();
                //subscription button flag start
                $i = 0;
                foreach ($data as $subs_listing) {
                    $renew_button = false;
                    $subscribe_button = true;
                    $data[$i]['renew_button'] = $renew_button;
                    $data[$i]['subscribe_button'] = $subscribe_button;
                    $i++;
                }
                //subscription button flag end
                if (empty($data)) {
                    errorMessage(__('subscription.subscription_not_found'), $msg_data);
                }
                $responseData['result'] = $data;
                $responseData['total_records'] = $total_records;
                successMessage(__('success_msg.data_fetched_successfully'), $responseData);
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            \Log::error("Subscription fetching failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }

    /**
     * Created By : Pradyumn Dwivedi
     * Created at : 03-06-2022
     * Uses : Buy new subscription and store data in table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function buy_subscription(Request $request)
    {
        $msg_data = array();
        try {
            $token = readHeaderToken();
            if ($token) {
                $payment_status = 'paid';
                $payment_mode = 'cash';
                $user_id = $token['sub'];
                $user = User::find($user_id);
                $subscription = Subscription::find($request->subscription_id);

                if (!empty($subscription)) {
                    if ($subscription->subscription_type == 'free') {
                        $free_subs = UserSubscriptionPayment::where('user_id', $user_id)->whereIn('subscription_id', Subscription::where('subscription_type', 'free')->pluck('id')->toArray())->first();

                        if (!empty($free_subs)) {
                            successMessage(__('subscription.already_availed'), $msg_data);
                        }
                    }

                    if ($subscription->subscription_type == 'monthly') {
                        $currentDateTime = Carbon::now()->toArray();
                        $subscription_start_date = $currentDateTime['formatted'];

                        $newDateTime = Carbon::now()->addDays(30)->toArray();
                        $subscription_end_date =  $newDateTime['formatted'];
                    }
                    if ($subscription->subscription_type == 'quarterly') {
                        $currentDateTime = Carbon::now()->toArray();
                        $subscription_start_date = $currentDateTime['formatted'];

                        $newDateTime = Carbon::now()->addDays(90)->toArray();
                        $subscription_end_date =  $newDateTime['formatted'];
                    }
                    if ($subscription->subscription_type == 'semi_yearly') {
                        $currentDateTime = Carbon::now()->toArray();
                        $subscription_start_date = $currentDateTime['formatted'];

                        $newDateTime = Carbon::now()->addDays(180)->toArray();
                        $subscription_end_date =  $newDateTime['formatted'];
                    }
                    if ($subscription->subscription_type == 'yearly') {
                        $currentDateTime = Carbon::now()->toArray();
                        $subscription_start_date = $currentDateTime['formatted'];

                        $newDateTime = Carbon::now()->addDays(360)->toArray();
                        $subscription_end_date =  $newDateTime['formatted'];
                    }
                    if ($subscription->subscription_type == 'free') {
                        $currentDateTime = Carbon::now()->toArray();
                        $subscription_start_date = $currentDateTime['formatted'];

                        $newDateTime = Carbon::now()->addDays($subscription->duration)->toArray();
                        $subscription_end_date =  $newDateTime['formatted'];
                    }
                    if ($user->subscription_end != null && $user->subscription_end > $subscription_start_date) {
                        $diff_days = strtotime($user->subscription_end) - strtotime($subscription_start_date);
                        // 1 day = 24 hours
                        // 24 * 60 * 60 = 86400 seconds
                        $interval = abs(round($diff_days / 86400));
                        $subscription_end_date = Carbon::createFromFormat('Y-m-d H:i:s', $subscription_end_date);
                        $subscription_end_date = $subscription_end_date->addDays($interval);
                    }
                    //data to enter in user table of selected user id
                    $subscription_request_data = array();
                    $subscription_request_data['subscription_id'] = $subscription->id;
                    $subscription_request_data['subscription_start'] = $subscription_start_date;
                    $subscription_request_data['subscription_end'] = $subscription_end_date;
                    $subscription_request_data['type'] = 'premium';

                    //update subscription data of user
                    $user->update($subscription_request_data);
                    $subscription_data = $user;
                    $subscribed = $subscription_data->toArray();
                    $subscription_data->created_at->toDateTimeString();
                    $subscription_data->updated_at->toDateTimeString();
                    \Log::info("Subscription, user subscribed successfully!");

                    //data to enter in subscription payment table
                    $subscription_payment_data = array();
                    $subscription_payment_data['user_id'] = $user_id;
                    $subscription_payment_data['subscription_id'] = $subscription->id;
                    $subscription_payment_data['amount'] = $subscription->amount;
                    $subscription_payment_data['subscription_type'] = $subscription->subscription_type;
                    $subscription_payment_data['payment_mode'] = $payment_mode;
                    $subscription_payment_data['payment_status'] = $payment_status;
                    $subscription_payment_data['created_by'] = $user_id;

                    //store subsciption payment details to subscription payment table
                    $subscription_payment = UserSubscriptionPayment::create($subscription_payment_data);
                    \Log::info("Subscribed user payment details entered successfully!");

                    $subscriptionPaymentData = $subscription_payment->toArray();
                    $subscription_payment->created_at->toDateTimeString();
                    $subscription_payment->updated_at->toDateTimeString();

                    // successMessage(__('subscription.subscription_payment_entry_created_successfully'), $subscriptionPaymentData);
                    successMessage(__('subscription.you_have_successfully_subscribed'), $subscribed);
                }
                errorMessage(__('subscription.subscription_not_found'), $msg_data);
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            \Log::error("Subscription fetching failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }

    /**
     * Created By : Pradyumn Dwivedi
     * Created at : 03-06-2022
     * Uses : Display a listing of my subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function my_subscription(Request $request)
    {
        $msg_data = array();
        try {
            $token = readHeaderToken();
            if ($token) {
                $show_renewal = 7;
                $user_id = $token['sub'];
                $orderByArray = ['subscriptions.subscription_type' => 'ASC'];
                $defaultSortByName = false;
                $data = DB::table('users')->select(
                    'users.subscription_id',
                    'users.type',
                    'subscriptions.subscription_type',
                    'users.subscription_start',
                    'users.subscription_end'
                )
                    ->leftjoin('subscriptions', 'users.subscription_id', '=', 'subscriptions.id')
                    ->where([['users.id', $user_id]]);

                $data = $data->first();
                //subscription listing
                $subscription_list = Subscription::select('id', 'subscription_type', 'credit_amount', 'amount');
                $subscriptionData = Subscription::whereRaw("1 = 1");

                // if(empty($data)) {
                //     errorMessage(__('subscription.subscription_not_found'), $msg_data);
                // }
                if ($request->subscription_id) {
                    $subscriptionData = $subscriptionData->where('id', $request->subscription_id);
                    $subscription_list = $subscription_list->where('id', $request->subscription_id);
                }
                if ($request->subscription_type) {
                    $subscriptionData = $subscriptionData->where('subscription_type', $request->subscription_type);
                    $subscription_list = $subscription_list->where('subscription_type', $request->subscription_type);
                }
                if (empty($subscriptionData->first())) {
                    errorMessage(__('subscription.subscription_not_found'), $msg_data);
                }
                if (isset($request->search) && !empty($request->search)) {
                    $subscription_list = fullSearchQuery($subscriptionData, $request->search, 'subscription_type');
                }
                if ($defaultSortByName) {
                    $orderByArray = ['subscriptions.subscription_type' => 'ASC'];
                }
                $subscription_list = allOrderBy($subscription_list, $orderByArray);

                $subscription_total_records = $subscription_list->get()->count();
                $subscription_list = $subscription_list->get()->toArray();
                //subscription button flag start
                $i = 0;
                $free_id = Subscription::where('subscription_type', 'free')->pluck('id')[0];
                foreach ($subscription_list as $subs_listing) {
                    $renew_button = false;
                    $subscribe_button = false;
                    if (!empty($data->subscription_id)) {
                        if ($data->subscription_end < Carbon::now()->addDays($show_renewal)->format('Y-m-d H:i:s')) {
                            if ($data->subscription_id == $subs_listing['id'] && $data->subscription_id != $free_id) {
                                $renew_button = true;
                            } else {
                                if ($subs_listing['id'] != $free_id) {
                                    $subscribe_button = true;
                                }
                            }
                        }
                        if ($data->subscription_end < Carbon::now()->format('Y-m-d H:i:s')) {
                            $data->subscription_type = null;
                            $data->subscription_start = null;
                            $data->subscription_end = null;
                        }
                    } else {
                        if ($data->subscription_id != $free_id)
                            $subscribe_button = true;
                    }
                    $subscription_list[$i]['renew_button'] = $renew_button;
                    $subscription_list[$i]['subscribe_button'] = $subscribe_button;
                    $i++;
                }
                //subscription button flag end
                if (empty($subscription_list)) {
                    errorMessage(__('subscription.subscription_not_found'), $msg_data);
                }
                $responseData['subscription_listing'] = $subscription_list;
                $responseData['total_records'] = $subscription_total_records;
                $responseData['my_subscription'] = $data;
                successMessage(__('success_msg.data_fetched_successfully'), $responseData);
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            \Log::error("Subscription fetching failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }

    /**
     *
     * Created By : Pradyumn Dwivedi
     * Created at : 30/05/2022
     * Uses : Validate request for Buy subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function validateBuySubscription(Request $request)
    {
        return \Validator::make($request->all(), [
            'subscription_id' => 'required|numeric',
        ])->errors();
    }
}
