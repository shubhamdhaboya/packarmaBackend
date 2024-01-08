<?php

namespace App\Http\Controllers\vendorapi;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorDevice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;

class MyProfileController extends Controller
{
    /**
     * Use to show Vendor My profile.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {

        $msg_data = array();
        \Log::info("My Profile Show, starting at: " . Carbon::now()->format('H:i:s:u'));
        try {
            $vendor_token = readVendorHeaderToken();
            if ($vendor_token) {
                $vendor_id = $vendor_token['sub'];


                $data = Vendor::select('vendor_name', 'vendor_company_name', 'vendor_email', 'phone', 'whatsapp_no', 'phone_country_id', 'whatsapp_country_id', 'approval_status')
                    ->with(['phone_country' => function ($query) {
                        $query->select('id', 'country_name', 'phone_code');
                    }])
                    ->with(['whatsapp_country' => function ($query) {
                        $query->select('id', 'country_name', 'phone_code');
                    }])
                    ->where([['id', $vendor_id]])->first();
                if (empty($data)) {
                    errorMessage(__('my_profile.not_found'), $msg_data);
                }
                if ($data->approval_status != 'accepted') {
                    $flags = array(
                        "my_address" => false,
                        "change_password" => false,
                        "about_us" => false,
                        "help_and_support" => false,
                        "terms_and_condition" => false,
                        "privacy_policy" => false,
                        "edit_vendor" => false,
                        "delete_vendor" => false,
                        "logout" => true,
                        "notification_icon" => false,
                    );
                } else {
                    $flags = array(
                        "my_address" => true,
                        "change_password" => true,
                        "about_us" => true,
                        "help_and_support" => true,
                        "terms_and_condition" => true,
                        "privacy_policy" => true,
                        "edit_vendor" => true,
                        "delete_vendor" => true,
                        "logout" => true,
                        "notification_icon" => true,
                    );
                }



                $msg_data['result'] = $data;
                $msg_data['flags'] = $flags;

                $fcm_id = NULL;
                if ($request->fcm_id && !empty($request->fcm_id)) {
                    $fcm_id = $request->fcm_id;
                }
                $imei_no = $request->header('imei-no');

                VendorDevice::updateOrCreate(
                    ['vendor_id' => $vendor_id, 'imei_no' => $imei_no],
                    ['fcm_id' => $fcm_id]
                );

                successMessage(__('my_profile.info_fetch'), $msg_data);
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            \Log::error("Fetching Info failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }



    /**
     * Use to update vendor My profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $msg_data = array();
        \Log::info("My Profile Update, starting at: " . Carbon::now()->format('H:i:s:u'));
        try {
            $vendor_token = readVendorHeaderToken();
            if ($vendor_token) {
                $vendor_id = $vendor_token['sub'];
                $vendorValidationErrors = $this->validateSignup($request);
                if (count($vendorValidationErrors)) {
                    \Log::error("Auth Exception: " . implode(", ", $vendorValidationErrors->all()));
                    errorMessage(__('auth.validation_failed'), $vendorValidationErrors->all());
                }
                \Log::info("Vendor Update Start!");



                $checkVendor = Vendor::select('approval_status', 'vendor_email', 'phone', 'status')->where([['id', $vendor_id]])->first();

                if (isset($request->vendor_email) && (strtolower($request->vendor_email) != strtolower($checkVendor->vendor_email))) {
                    errorMessage(__('vendor.email_cant_update'), $msg_data);
                }

                if (isset($request->phone) && ($request->phone != $checkVendor->phone)) {
                    errorMessage(__('vendor.phone_cant_update'), $msg_data);
                }


                if ($checkVendor->approval_status == 'rejected') {
                    errorMessage(__('vendor.rejected'), $msg_data);
                }

                if ($checkVendor->approval_status == 'pending') {
                    errorMessage(__('vendor.approval_pending'), $msg_data);
                }

                if ($checkVendor->status == 0 && $checkVendor->approval_status == 'accepted') {
                    errorMessage(__('vendor.not_active'), $msg_data);
                }


                $checkVendor = Vendor::where('id', $vendor_id)->first();
                $checkVendor->update($request->all());
                $vendorData = $checkVendor;
                $vendor = $vendorData->toArray();

                $vendorData->created_at->toDateTimeString();
                $vendorData->updated_at->toDateTimeString();
                \Log::info("Existing vendor updated with email id: " . $request->vendor_email . " and phone number: " . $request->phone);
                successMessage(__('vendor.update_successfully'), $vendor);
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            \Log::error("My Profile Update failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }

    public function destroy()
    {
        $msg_data = array();
        \Log::info("Delete Vendor Account, starting at: " . Carbon::now()->format('H:i:s:u'));
        try {
            $vendor_token = readVendorHeaderToken();
            if ($vendor_token) {
                $vendor_id = $vendor_token['sub'];

                \Log::info("Delete Vendor Start!");

                Vendor::destroy($vendor_id);
                // VendorDevice::destroy(['vendor_id', $vendor_id]);
                VendorDevice::where('vendor_id', $vendor_id)->delete();
                \Log::info("Vendor deleted successfully! ");
                successMessage(__('vendor.delete_successfully'), $msg_data);
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            \Log::error("Vendor Delete failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }


    /**
     * Use to Check Vendor Status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function checkVendorStatus(Request $request)
    {

        $msg_data = array();
        $default_home_page = 'home';
        \Log::info("Check Vendor Status, starting at: " . Carbon::now()->format('H:i:s:u'));
        try {
            $vendor_token = readVendorHeaderToken();
            if ($vendor_token) {
                $vendor_id = $vendor_token['sub'];


                $vendorData = Vendor::with(['currency' => function ($query) {
                    $query->select('id', 'currency_name', 'currency_symbol', 'currency_code');
                }])->with(['phone_country' => function ($query) {
                    $query->select('id', 'phone_code', 'country_name');
                }])->where([['id', $vendor_id], ['is_verified', 'Y']])->first();

                if (empty($vendorData)) {
                    errorMessage(__('vendor.not_found'), $msg_data);
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

                if (empty($vendorData->gst_certificate)) {
                    $vendorData->gst_certificate =  getFile('default_vendor_gst_file.png', 'vendor_gst_certificate');
                }

                $vendorData->load_page = $default_home_page;
                successMessage(__('vendor.status_fetched'), $vendorData->toArray());
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            \Log::error("Check Vendor status failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }



    /**
     * Use to update vendor fcm_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateFcmId(Request $request)
    {
        $msg_data = array();
        \Log::info("Fcm Id Update, starting at: " . Carbon::now()->format('H:i:s:u'));
        try {
            $vendor_token = readVendorHeaderToken();
            if ($vendor_token) {
                $vendor_id = $vendor_token['sub'];
                $vendorValidationErrors = $this->validateUpdateFcmId($request);
                if (count($vendorValidationErrors)) {
                    \Log::error("Auth Exception: " . implode(", ", $vendorValidationErrors->all()));
                    errorMessage(__('auth.validation_failed'), $vendorValidationErrors->all());
                }
                \Log::info("Fcm Id Update Start!");

                $fcm_id = NULL;
                if ($request->fcm_id && !empty($request->fcm_id)) {
                    $fcm_id = $request->fcm_id;
                }
                $imei_no = $request->header('imei-no');
                if (!empty($imei_no)) {
                    VendorDevice::updateOrCreate(
                        ['vendor_id' => $vendor_id, 'imei_no' => $imei_no],
                        ['fcm_id' => $fcm_id]
                    );
                    successMessage(__('vendor.update_successfully'), $msg_data);
                } else {
                    successMessage(__('vendor.imei_not_found'), $msg_data);
                }
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            \Log::error("Fcm Id Update failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }



    private function validateSignup(Request $request)
    {
        return \Validator::make($request->all(), [
            'vendor_name' => 'required|string',
            'vendor_company_name' => 'required|string',
            'phone_country_id' => 'required|numeric',
            'phone' => 'required|numeric|digits:10',
            'vendor_email' => 'required|email',

        ])->errors();
    }


    private function validateUpdateFcmId(Request $request)
    {
        return \Validator::make($request->all(), [
            'fcm_id' => 'required'

        ])->errors();
    }

    /**
     * Created By Pradyumn Dwivedi
     * Created at : 17/10/2022
     * Uses : To delete vendor remember token to NULL after logout for specific device id
     *  @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logoutVendorUpdateToken(Request $request)
    {
        $msg_data = array();
        \Log::info("Loging out vendor from device, starting at: " . Carbon::now()->format('H:i:s:u'));
        try {
            $token = readVendorHeaderToken();
            if ($token) {
                $vendor_id = $token['sub'];
                $imei_no = $request->header('imei-no');
                \Log::info("Vendor Remember token update NULL start for  device id : ".$imei_no);
                VendorDevice::where([['vendor_id',$vendor_id],['imei_no', $imei_no]])->update(['remember_token'=> NULL]);
                \Log::info("Vendor remember token as NULL updated successfully for device id: ".$imei_no);
                successMessage(__('vendor.logged_successfully'), $msg_data);
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            \Log::error("Vendor Remember token update failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }
}
