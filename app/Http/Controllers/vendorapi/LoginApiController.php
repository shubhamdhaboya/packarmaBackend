<?php

namespace App\Http\Controllers\vendorapi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorDevice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Session;
use Response;

class LoginApiController extends Controller
{
    /**
     * This API will be used to login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $msg_data = array();
        $default_home_page = 'home';
        \Log::info("Logging in vendor, starting at: " . Carbon::now()->format('H:i:s:u'));
        try {
            // Request Validation
            $validationErrors = $this->validateLogin($request);
            if (count($validationErrors)) {
                \Log::error("Auth Exception: " . implode(", ", $validationErrors->all()));
                errorMessage(__('auth.validation_failed'), $validationErrors->all());
            }




            // $vendorData = DB::table('vendors')->select(
            //     'vendors.id',
            //     'vendors.vendor_name',
            //     'vendors.vendor_company_name',
            //     'vendors.vendor_email',
            //     'vendors.vendor_address',
            //     'vendors.pincode',
            //     'vendors.phone',
            //     'vendors.approval_status',
            //     'vendors.remember_token',
            //     'currencies.currency_name',
            //     'currencies.currency_symbol',
            //     'currencies.currency_code',
            //     'countries.phone_code',
            //     'countries.country_name',
            // )
            //     ->leftjoin('countries', 'vendors.phone_country_id', '=', 'countries.id')
            //     ->leftjoin('currencies', 'vendors.currency_id', '=', 'currencies.id')
            //     ->where([['vendor_email', $request->vendor_email], ['vendor_password', md5($request->vendor_email . $request->vendor_password)], ['vendors.status', '1'], ['vendors.is_verified', 'Y']])->first();



            // $vendorData = Vendor::where([['vendor_email', $request->vendor_email], ['vendor_password', md5($request->vendor_email . $request->vendor_password)], ['status', '1'], ['is_verified', 'Y']])->first();
            $vendorData = Vendor::with(['currency' => function ($query) {
                $query->select('id', 'currency_name', 'currency_symbol', 'currency_code');
            }])->with(['phone_country' => function ($query) {
                $query->select('id', 'phone_code', 'country_name');
            }])->where([['vendor_email', strtolower($request->vendor_email)], ['vendor_password', md5(strtolower($request->vendor_email) . $request->vendor_password)], ['is_verified', 'Y']])->first();

            // print_r($vendorData);
            // die();

            if (empty($vendorData)) {
                errorMessage(__('vendor.login_failed'), $msg_data);
            }

            if ($vendorData->approval_status == 'rejected') {
                errorMessage(__('vendor.rejected'), $msg_data);
            }

            if ($vendorData->approval_status == 'pending') {
                if (empty($vendorData->gstin)) {
                    $default_home_page = 'gst';
                } else {
                    errorMessage(__('vendor.approval_pending'), $msg_data);
                }
            }

            if ($vendorData->status == 0 && $vendorData->approval_status == 'accepted') {
                errorMessage(__('vendor.not_active'), $msg_data);
            }

            if ($vendorData->status == 0 && $vendorData->approval_status == 'accepted') {
                errorMessage(__('vendor.not_active'), $msg_data);
            }

            if (empty($vendorData->gst_certificate)) {
                $vendorData->gst_certificate =  getFile('default_vendor_gst_file.png', 'vendor_gst_certificate');
            }


            $fcm_id = NULL;
            if ($request->fcm_id && !empty($request->fcm_id)) {
                $fcm_id = $request->fcm_id;
            }

            // return $fcm_id;
            $notification_icon_flag = true;
            $imei_no = $request->header('imei-no');
            $vendor_token = JWTAuth::fromUser($vendorData);
            $vendors = Vendor::find($vendorData->id);
            $vendorData->last_login = $vendors->last_login = Carbon::now();
            $vendorData->remember_token  = $vendor_token;
            $vendorData->load_page = $default_home_page;
            $vendorData->notification_icon = $notification_icon_flag;
            $vendors->save();

            VendorDevice::updateOrCreate(
                ['vendor_id' => $vendorData->id, 'imei_no' => $imei_no],
                ['remember_token' => $vendor_token, 'fcm_id' => $fcm_id]
            );

            successMessage(__('vendor.logged_in_successfully'), $vendorData->toArray());
        } catch (\Exception $e) {
            \Log::error("Login failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }

    /**
     * Validate request for login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function validateLogin(Request $request)
    {
        return \Validator::make($request->all(), [
            'vendor_email' => 'required|email',
            'vendor_password' => 'required|string|min:8'
        ])->errors();
    }
}
