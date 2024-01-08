<?php

namespace App\Http\Controllers\vendorapi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorDevice;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Session;

class ChangePasswordController extends Controller
{
    /**
     * This API will be used to forgot vendor password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $msg_data = array();
        \Log::info("Change Password process, starting at: " . Carbon::now()->format('H:i:s:u'));
        try {
            $vendor_token = readVendorHeaderToken();

            if ($vendor_token) {
                $vendor_id = $vendor_token['sub'];
                // Request Validation
                $validationErrors = $this->validateForgotPassword($request);
                if (count($validationErrors)) {
                    \Log::error("Auth Exception: " . implode(", ", $validationErrors->all()));
                    errorMessage(__('auth.validation_failed'), $validationErrors->all());
                }
                $vendorData = Vendor::where('id', $vendor_id)->first();

                if ($vendorData->vendor_password != md5($vendorData->vendor_email . $request->current_password)) {
                    errorMessage(__('change_password.password_not_match'), $msg_data);
                }

                if ($vendorData->vendor_password == md5($vendorData->vendor_email . $request->new_password)) {
                    errorMessage(__('change_password.new_password_cannot_same_current_password'), $msg_data);
                }

                if ($request->new_password != $request->confirm_password) {
                    errorMessage(__('change_password.password_mismatch'), $msg_data);
                }



                $imei_no = $request->header('imei-no');
                $new_password = md5($vendorData->vendor_email . $request->new_password);
                Vendor::where('id', $vendor_id)->update(['vendor_password' => $new_password]);

                VendorDevice::where([['vendor_id', $vendor_id], ['imei_no', '!=', $imei_no]])->update(['remember_token' => NULL]);
                successMessage(__('change_password.changed'), $msg_data);
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            \Log::error("Change Password failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }

    /**
     * Validate request for forgot password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function validateForgotPassword(Request $request)
    {
        return \Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8',
        ])->errors();
    }
}
