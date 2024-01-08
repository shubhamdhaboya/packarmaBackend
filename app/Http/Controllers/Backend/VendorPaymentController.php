<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\VendorPayment;
use App\Models\Order;
use App\Models\Vendor;
use Yajra\DataTables\DataTables;
use App\Models\MessageNotification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;


class VendorPaymentController extends Controller
{
    /**
     *   created by : Pradyumn Dwivedi
     *   Created On : 06-April-2022
     *   Uses :  To show vendor payment listing page  
     */
    public function index()
    {
        try {
            $data['vendor'] = Vendor::withTrashed()->Where('approval_status', '=', 'accepted')->orderBy('vendor_name', 'asc')->get();
            $data['paymentMode'] = paymentMode();
            $data['paymentStatus'] = paymentStatus();
            $data['paymentStatusType'] = paymentStatusType();
            $data['vendor_payment_add'] = checkPermission('vendor_payment_add');
            $data['vendor_payment_view'] = checkPermission('vendor_payment_view');
            if (isset($_GET['id'])) {
                $data['id'] = Crypt::decrypt($_GET['id']);
                $data['order'] = Order::find($data['id']);
            } else {
                $data['order'] = Order::all();
            }
            return view('backend/vendors/vendor_payment_list/index', $data);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect('404');
        }
    }

    /**
     *   created by : Pradyumn Dwivedi
     *   Created On : 06-April-2022
     *   Uses :  display dynamic data in datatable for vendor payment page  
     *   @param Request request
     *   @return Response    
     */
    public function fetch(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = VendorPayment::with('vendor')->orderBy('updated_at', 'desc')->withTrashed();
                return DataTables::of($query)
                    ->filter(function ($query) use ($request) {
                        if (isset($request['search']['search_order_id']) && !is_null($request['search']['search_order_id'])) {
                            $query->where('order_id', $request['search']['search_order_id']);
                        }
                        if (isset($request['search']['search_vendor_name']) && !is_null($request['search']['search_vendor_name'])) {
                            $query->where('vendor_id', $request['search']['search_vendor_name']);
                        }
                        if (isset($request['search']['search_payment_mode']) && !is_null($request['search']['search_payment_mode'])) {
                            $query->where('payment_mode', $request['search']['search_payment_mode']);
                        }
                        if (isset($request['search']['search_payment_status']) && !is_null($request['search']['search_payment_status'])) {
                            $query->where('payment_status', $request['search']['search_payment_status']);
                        }
                        $query->get();
                    })
                    ->editColumn('vendor_name', function ($event) {
                        $isVendorDeleted = isRecordDeleted($event->vendor->deleted_at);
                        if (!$isVendorDeleted) {
                            return $event->vendor->vendor_name;
                        } else {
                            return '<span class="text-danger text-center">' . $event->vendor->vendor_name . '</span>';
                        }
                    })
                    ->editColumn('order_id', function ($event) {
                        return $event->order_id;
                    })
                    ->editColumn('payment_mode', function ($event) {
                        return paymentMode($event->payment_mode);
                    })
                    ->editColumn('payment_status', function ($event) {
                        return paymentStatusType($event->payment_status);
                    })
                    ->editColumn('amount', function ($event) {
                        return $event->amount;
                    })
                    ->editColumn('transaction_date', function ($event) {
                        return date('d-m-Y', strtotime($event->transaction_date));
                    })
                    ->editColumn('action', function ($event) {
                        $vendor_payment_view = checkPermission('vendor_payment_view');
                        $actions = '<span style="white-space:nowrap;">';
                        if ($vendor_payment_view) {
                            $actions .= '<a href="vendor_payment_view/' . $event->id . '" class="btn btn-primary btn-sm modal_src_data" data-size="large" data-title="View Vendor Payment Details" title="View"><i class="fa fa-eye"></i></a>';
                        }
                        $actions .= '</span>';
                        return $actions;
                    })
                    ->addIndexColumn()
                    ->rawColumns(['vendor_name', 'order_id', 'payment_mode', 'payment_status', 'transaction_date', 'action'])->setRowId('id')->make(true);
            } catch (\Exception $e) {
                \Log::error("Something Went Wrong. Error: " . $e->getMessage());
                return response([
                    'draw'            => 0,
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                    'error'           => 'Something went wrong',
                ]);
            }
        }
    }

    /**
     *   created by : Pradyumn Dwivedi
     *   Created On : 06-April-2022
     *   Uses : To load Add vendor payment
     */
    public function add()
    {
        $data['vendor'] = Vendor::Where('approval_status', '=', 'accepted')->orderBy('vendor_name', 'asc')->get();
        $data['payment_details'] = [];
        if (isset($_GET['id'])) {
            $data['order'][] = Order::find($_GET['id']);
            $data['id'] = $_GET['id'];
            $data['vendorID'] = Order::where('id', '=', $data['id'])->pluck('vendor_id')->toArray();
            $vendorPayments = VendorPayment::with('vendor')->where("order_id", $_GET['id'])->get()->toArray();
            if (count($vendorPayments) > 0) {
                foreach ($vendorPayments as $k => $val) {
                    $vendorPayments[$k]['updated_datetime'] = date('d-m-Y H:i A', strtotime($val['updated_at']));
                    $vendorPayments[$k]['transaction_datetime'] = date('d-m-Y', strtotime($val['transaction_date']));
                    $vendorPayments[$k]['transaction_mode'] = paymentMode($val['payment_mode']);
                }
            }
            $data['payment_details'] = $vendorPayments;
        }
        $data['vendor_payment'] = VendorPayment::all();
        $data['paymentMode'] = paymentMode();
        $data['paymentStatusType'] = paymentStatusType();
        return view('backend/vendors/vendor_payment_list/vendor_payment_add', $data);
    }

    /**
     *   created by : Pradyumn Dwivedi
     *   Created On : 06-April-2022
     *   Uses : To load Add vendor payment form from order page
     */
    public function addPaymentFromOrder()
    {
        $data['vendor'] = Vendor::Where('approval_status', '=', 'accepted')->orderBy('vendor_name', 'asc')->get();
        $data['payment_details'] = [];
        if (isset($_GET['id'])) {

            $data['id'] = Crypt::decrypt($_GET['id']);
            $data['order'] = Order::find($data['id']);
            $data['vendorID'] = Order::where('id', $data['id'])->pluck('vendor_id')->toArray();
            $vendorPayments = VendorPayment::with('vendor')->where("order_id", $data['id'])->get()->toArray();
           
            if (count($vendorPayments) > 0) {
                foreach ($vendorPayments as $k => $val) {
                    $vendorPayments[$k]['updated_datetime'] = date('d-m-Y H:i A', strtotime($val['updated_at']));
                    $vendorPayments[$k]['transaction_datetime'] = date('d-m-Y', strtotime($val['transaction_date']));
                    $vendorPayments[$k]['transaction_mode'] = paymentMode($val['payment_mode']);
                }
            }
            $data['payment_details'] = $vendorPayments;
        }
        $data['vendor_payment'] = VendorPayment::all();
        $data['paymentMode'] = paymentMode();
        $data['paymentStatusType'] = paymentStatusType();
        return view('backend/vendors/vendor_payment_list/vendor_payment_add_order', $data);
    }

    /**
     *   created by : Pradyumn Dwivedi
     *   Created On : 06-April-2022
     *   Uses :  To store vendor payment in table
     *   @param Request request
     *   @return Response
     */
    public function saveVendorPaymentStatusData(Request $request)
    {

        $msg_data = array();
        $msg = "";
        $validationErrors = $this->validatePaymentRequest($request);
        if (count($validationErrors)) {
            \Log::error("Vendor Payment Status Validation Exception: " . implode(", ", $validationErrors->all()));
            errorMessage(implode("\n", $validationErrors->all()), $msg_data);
        }
        $orderData = Order::find($request->order_id);

        // $orderData = Order::find($_GET['id']);
        if (isset($request->order_id)) {
            $getKeys = true;
            $paymentStatusType = paymentStatusType('', $getKeys);
            $paymentMode = paymentMode('', $getKeys);
            if (in_array($request->payment_status, $paymentStatusType) && in_array($request->payment_mode, $paymentMode)) {
                // $tableObject = OrderPayment::find($_GET['id']);
                if ($request->payment_status == 'pending') {
                    errorMessage('Payment is Already in Pending Status, Please Select Another Status', $msg_data);
                } elseif ($request->amount == 0) {
                    errorMessage('Entered Amount Should be Greater Than Zero', $msg_data);
                } elseif (($request->payment_status == 'fully_paid') && ($request->amount != $orderData->vendor_pending_payment)) {
                    errorMessage('Please Enter Proper Amount for Selected Status.', $msg_data);
                } elseif (($request->payment_status == 'semi_paid') && ($request->amount == $orderData->vendor_pending_payment)) {
                    errorMessage('Please Select Proper Status for Entered Amount.', $msg_data);
                } elseif ($orderData->vendor_pending_payment >= $request->amount) {
                    $msg = "Payment Status Updated Successfully";
                } else {
                    errorMessage('Amount Should be Less Than or Equal To Pending Payment', $msg_data);
                }
            } else {
                errorMessage('Payment Status Does not Exists.', $msg_data);
            }
        }

        $tableObject  = new VendorPayment;
        $tableObject->vendor_id = $request->vendor;
        $tableObject->order_id = $request->order_id;
        $tableObject->payment_mode = $request->payment_mode;
        $tableObject->amount = $request->amount;
        $tableObject->payment_status = $request->payment_status;
        $tableObject->transaction_date = $request->transaction_date;
        if ($request->remark != '') {
            $tableObject->remark = $request->remark;
        } else {
            $tableObject->remark = '';
        }
        // print_r($request->vendor);exit;
        $tableObject->created_at = date('Y-m-d H:i:s');
        $tableObject->created_by =  session('data')['id'];
        $tableObject->save();
        //decreasing pending_payment by amount in order table
        $request_number =  number_format(($request->amount), 2, '.', '');
        $vendor_pending_payment =  number_format(($orderData->vendor_pending_payment), 2, '.', '');

        Order::where('id',  $request->order_id)->decrement('vendor_pending_payment', $request->amount);
        if (($request->payment_status == 'fully_paid') && ($request_number == $vendor_pending_payment)) {
            Order::where('id', $request->order_id)->update(['vendor_payment_status' => 'fully_paid']);
            
            // send fcm notification to vendor of after payment updated
            $can_send_fcm_notification =  DB::table('general_settings')->where('type', 'trigger_vendor_fcm_notification')->value('value');
            if ($can_send_fcm_notification == 1) {
                $this->callPaymentUpdatedFcmNotification($request->vendor, $request->order_id);
            }
            successMessage($msg, $msg_data);
        }

        // send fcm notification to vendor of after payment updated
        $can_send_fcm_notification =  DB::table('general_settings')->where('type', 'trigger_vendor_fcm_notification')->value('value');
        if ($can_send_fcm_notification == 1) {
            $this->callPaymentUpdatedFcmNotification($request->vendor, $request->order_id);
        }
        successMessage($msg, $msg_data);
    }

    /*
    *Created By: Pradyumn, 
    *Created At : 8-sept-2022, 
    *uses: Payment update fcm notification send to vendor
    */
    private function callPaymentUpdatedFcmNotification($vendor_id, $order_id)
    {
        $landingPage = 'VendorPayments';
        if ((!empty($vendor_id) && $vendor_id > 0) && (!empty($order_id) && $order_id > 0)) {
            $notificationData = MessageNotification::where([['user_type', 'vendor'], ['notification_name', 'vendor_payment_update'], ['status', 1]])->first();
            
            if (!empty($notificationData)) {
                $notificationData['type_id'] = $order_id;
                
                if (!empty($notificationData['notification_image']) &&\Storage::disk('s3')->exists('notification/vendor'. '/' . $notificationData['notification_image']) ) {
                    $notificationData['image_path'] = getFile($notificationData['notification_image'], 'notification/vendor');
                }

                if (empty($notificationData['page_name'])) {
                    $notificationData['page_name'] = $landingPage;
                }

                $formatted_id = getFormatid($order_id, 'orders');
                $notificationData['body'] = str_replace('$$order_id$$', $formatted_id, $notificationData['body']);
                $userFcmData = DB::table('vendors')->select('vendors.id', 'vendor_devices.fcm_id','vendor_devices.imei_no','vendor_devices.remember_token')
                    ->where([['vendors.id', $vendor_id], ['vendors.status', 1], ['vendors.fcm_notification', 1], ['vendors.approval_status', 'accepted'], ['vendors.deleted_at', NULL]])
                    ->leftjoin('vendor_devices', 'vendor_devices.vendor_id', '=', 'vendors.id')
                    ->get();
                if (!empty($userFcmData)) {
                    //modified by : Pradyumn Dwivedi, Modified at : 14-Oct-2022
                    $device_ids = array();
                    $imei_nos = array();
                    $i=0;
                    foreach ($userFcmData as $key => $val) {
                        if (!empty($val->remember_token)){
                            array_push($device_ids, $val->fcm_id);
                            array_push($imei_nos, $val->imei_no);
                        }
                    }
                    //modified by : Pradyumn Dwivedi, Modified at : 14-Oct-2022
                    //combine imei id and fcm as key value in new array
                    $devices_data =  array_combine($imei_nos, $device_ids);
                    sendFcmNotification($devices_data, $notificationData, 'vendor', $vendor_id);
                }
            }
        }
    }

    /**
     *   created by : Pradyumn Dwivedi
     *   Created On : 06-April-2022
     *   Uses :  To view vendor payment details
     *   @param int $id
     *   @return Response
     */
    public function view($id)
    {
        $data['data'] = VendorPayment::with('vendor')->find($id);
        $data['vendor'] = Vendor::all();
        $data['paymentMode'] = paymentMode();
        $data['paymentStatusType'] = paymentStatusType();
        return view('backend/vendors/vendor_payment_list/vendor_payment_view', $data);
    }

    /**
     *   created by : Sagar Thokal
     *   Created On : 06-04-2022
     *   Uses : vendor payment from vendor order id From AJAX call
     */
    public function getVendorOrders(Request $request)
    {
        $data['vendor_orders'] = Order::where("vendor_id", $request->vendor_id)->get();
        return response()->json($data);
    }

    /**
     *   created by : Pradyumn Dwivedi
     *   Created On : 07-04-2022
     *   Uses :get vendor payment history of selected  order from vendor payment by order id From AJAX call
     */
    public function getVendoPaymentDetails(Request $request)
    {
        $data = VendorPayment::with('vendor')->where("order_id", $request->order_id)->get()->toArray();
        if (count($data) > 0) {
            foreach ($data as $k => $val) {
                $data[$k]['updated_datetime'] = date('d-m-Y H:i A', strtotime($val['updated_at']));
                $data[$k]['transaction_datetime'] = date('d-m-Y', strtotime($val['transaction_date']));
                $data[$k]['transaction_mode'] = paymentMode($val['payment_mode']);
            }
        }
        $returnData['payment_details'] = $data;
        return response()->json($returnData);
    }

    /**
     *   created by : Pradyumn Dwivedi
     *   Created On : 06-April-2022
     *   Uses :  vendor payment status Form Validation part will be handle by below function
     *   @param Request request
     *   @return Response
     */
    private function validatePaymentRequest(Request $request)
    {
        return \Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'vendor' => 'required|integer',
            'payment_status' => 'required|string',
            'payment_mode' => 'required|string',
            'amount' => 'required|numeric',
            'transaction_date' => 'required|date_format:Y-m-d',
        ])->errors();
    }
}
