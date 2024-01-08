<?php

namespace App\Http\Controllers\customerapi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CustomerEnquiry;
use App\Models\User;
use App\Models\Product;
use App\Models\VendorQuotation;
use App\Models\RecommendationEngine;
use App\Models\UserCreditHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\FacadesValidator;
use Illuminate\Validation\Rule;
use Response;
use stdClass;

class CustomerEnquiryApiController extends Controller
{
    /**
     * Created By : Pradyumn Dwivedi
     * Created at : 17-05-2022
     * Uses : Display a listing of the customer enquiry listing.
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
                $user_id = $token['sub'];
                // return User::find($user_id);
                // $user_id = $request->user_id;
                $page_no = 1;
                $limit = 10;
                $orderByArray = ['customer_enquiries.id' => 'DESC',];
                $defaultSortByName = false;

                if (isset($request->page_no) && !empty($request->page_no)) {
                    $page_no = $request->page_no;
                }
                if (isset($request->limit) && !empty($request->limit)) {
                    $limit = $request->limit;
                }
                $offset = ($page_no - 1) * $limit;
                $main_table = 'customer_enquiries';

                $customerEnquiryData = CustomerEnquiry::where('user_id', $user_id)->where('is_shown', 1)->whereIn('quote_type', ['enquired', 'map_to_vendor', 'accept_cust']);


                if ($request->enquiry_id) {
                    $customerEnquiryData = $customerEnquiryData->where('id', $request->enquiry_id);
                    // $data = $data->where($main_table . '' . '.id', $request->enquiry_id);
                }
                if ($request->product_id) {
                    $productId = $request->product_id;
                    $customerEnquiryData = $customerEnquiryData->where('product_id', $productId);

                    // $data = $data->where($main_table . '' . '.product_id', $request->product_id);
                }
                if (empty($customerEnquiryData->first())) {
                    errorMessage(__('customer_enquiry.customer_enquiry_not_found'), $msg_data);
                }
                if (isset($request->search) && !empty($request->search)) {
                    // $data = fullSearchQuery($data, $request->search, 'description');
                }
                if ($defaultSortByName) {
                    $orderByArray = ['products.product_name' => 'ASC'];
                }

                $total_records = $customerEnquiryData->get()->count();
                $customerEnqueries = $customerEnquiryData->orderBy('created_at', 'desc')->limit($limit)->offset($offset)->get();

                // $customerEnqueries = $customerEnquiryData->get();

                foreach ($customerEnqueries as $enquery) {
                    $enquery->category;
                    $enquery->product;
                    $enquery->measurement_unit;
                    $enquery->sub_category;
                    $enquery->storage_condition;
                    $enquery->packaging_machine;
                    $enquery->product_form;
                    $enquery->packing_type;
                    $enquery->packaging_treatment;
                    $enquery->credit;

                    $enquery->enquiry_id = getFormatid($enquery->id, 'customer_enquiries');
                    $quotationCount = VendorQuotation::where([['user_id', $user_id], ['customer_enquiry_id', $enquery->id]])
                        ->whereIn('enquiry_status', ['quoted', 'viewed'])->get()->count();
                    $enquery->quotation_count = $quotationCount;
                    if ($enquery->product_weight == 0) {
                        $enquery->product_weight = null;
                        $enquery->measurement_unit_id = null;
                    }
                    if ($enquery->entered_shelf_life == 0) {
                        $enquery->entered_shelf_life = null;
                        $enquery->entered_shelf_life_unit = null;
                    }

                    $recommendationEngines = $enquery->recommendationEngines()->select(['recommendation_id as packaging_solution_id', 'engine_name', 'structure_type', 'display_shelf_life', 'min_order_quantity', 'min_order_quantity_unit'])->get();
                    if (count($recommendationEngines) == 0) {
                        $recommendationEngines = RecommendationEngine::where('id', $enquery->recommendation_engine_id)->select(['id as packaging_solution_id', 'engine_name', 'structure_type', 'display_shelf_life', 'min_order_quantity', 'min_order_quantity_unit'])->get();
                    }

                    $enquery->recommendationEngines  = $recommendationEngines;
                }

                $responseData['result'] = $customerEnqueries;
                $responseData['total_records'] = $total_records;
                successMessage(__('success_msg.data_fetched_successfully'), $responseData);
                return $customerEnqueries;
                $data = DB::table('customer_enquiries')->select(
                    'customer_enquiries.id',
                    'customer_enquiries.category_id',
                    'categories.category_name',
                    'customer_enquiries.sub_category_id',
                    'sub_categories.sub_category_name',
                    'customer_enquiries.product_id',
                    'products.product_name',
                    'customer_enquiries.product_weight',
                    'customer_enquiries.measurement_unit_id',
                    'customer_enquiries.product_quantity',
                    'measurement_units.unit_symbol',
                    'customer_enquiries.shelf_life',
                    'customer_enquiries.entered_shelf_life',
                    'customer_enquiries.entered_shelf_life_unit',
                    'customer_enquiries.storage_condition_id',
                    'storage_conditions.storage_condition_title',
                    'customer_enquiries.packaging_machine_id',
                    'packaging_machines.packaging_machine_name',
                    'customer_enquiries.product_form_id',
                    'product_forms.product_form_name',
                    'customer_enquiries.packing_type_id',
                    'packing_types.packing_name',
                    'customer_enquiries.packaging_treatment_id',
                    'packaging_treatments.packaging_treatment_name',
                    'customer_enquiries.recommendation_engine_id',

                    'customer_enquiries.packaging_material_id',
                    'customer_enquiries.quote_type',
                    'user_credit_histories.*',
                    'customer_enquiries.created_at'
                )
                    ->leftjoin('categories', 'categories.id', '=', 'customer_enquiries.category_id')
                    ->leftjoin('sub_categories', 'sub_categories.id', '=', 'customer_enquiries.sub_category_id')
                    ->leftjoin('products', 'products.id', '=', 'customer_enquiries.product_id')
                    ->leftjoin('measurement_units', 'measurement_units.id', '=', 'customer_enquiries.measurement_unit_id')
                    ->leftjoin('storage_conditions', 'storage_conditions.id', '=', 'customer_enquiries.storage_condition_id')
                    ->leftjoin('packaging_machines', 'packaging_machines.id', '=', 'customer_enquiries.packaging_machine_id')
                    ->leftjoin('product_forms', 'product_forms.id', '=', 'customer_enquiries.product_form_id')
                    ->leftjoin('packing_types', 'packing_types.id', '=', 'customer_enquiries.packing_type_id')
                    ->leftjoin('packaging_treatments', 'packaging_treatments.id', '=', 'customer_enquiries.packaging_treatment_id')

                    ->leftjoin('user_credit_histories', 'user_credit_histories.enquery_id', '=', 'customer_enquiries.id')
                    ->where('customer_enquiries.user_id', $user_id);
                // ->whereIn('customer_enquiries.quote_type', ['enquired', 'map_to_vendor', 'accept_cust']);
                // return $data;

                $customerEnquiryData = CustomerEnquiry::whereRaw("1 = 1");
                if ($request->enquiry_id) {
                    $customerEnquiryData = $customerEnquiryData->where($main_table . '' . '.id', $request->enquiry_id);
                    $data = $data->where($main_table . '' . '.id', $request->enquiry_id);
                }
                if ($request->product_id) {
                    $customerEnquiryData = $customerEnquiryData->where($main_table . '' . '.product_id', $request->product_id);
                    $data = $data->where($main_table . '' . '.product_id', $request->product_id);
                }
                if (empty($customerEnquiryData->first())) {
                    errorMessage(__('customer_enquiry.customer_enquiry_not_found'), $msg_data);
                }
                if (isset($request->search) && !empty($request->search)) {
                    $data = fullSearchQuery($data, $request->search, 'description');
                }
                if ($defaultSortByName) {
                    $orderByArray = ['products.product_name' => 'ASC'];
                }
                $data = allOrderBy($data, $orderByArray);
                $total_records = $data->get()->count();
                $data = $data->limit($limit)->offset($offset)->get()->toArray();
                // return $total_records;

                $i = 0;
                foreach ($data as $row) {
                    return $row;
                    $data[$i]->enquiry_id = getFormatid($row->id, 'customer_enquiries');
                    $quotationCount = VendorQuotation::where([['user_id', $user_id], ['customer_enquiry_id', $row->id]])
                        ->whereIn('enquiry_status', ['quoted', 'viewed'])->get()->count();
                    $data[$i]->quotation_count = $quotationCount;
                    if ($row->product_weight == 0) {
                        $data[$i]->product_weight = null;
                        $data[$i]->measurement_unit_id = null;
                    }
                    if ($row->entered_shelf_life == 0) {
                        $data[$i]->entered_shelf_life = null;
                        $data[$i]->entered_shelf_life_unit = null;
                    }

                    $currentEnquery = CustomerEnquiry::find($row->id);
                    if ($currentEnquery)
                        $data[$i]->recommendation_engines = $currentEnquery->recommendationEngines()->select(['engine_name', 'structure_type', 'display_shelf_life', 'min_order_quantity', 'min_order_quantity_unit'])->get();
                    $i++;
                }
                if (empty($data)) {
                    errorMessage(__('customer_enquiry.customer_enquiry_not_found'), $msg_data);
                }
                $responseData['result'] = $data;
                $responseData['total_records'] = $total_records;
                successMessage(__('success_msg.data_fetched_successfully'), $responseData);
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unkown error occured',
                'error' => $e->getMessage()
            ], 500);
            Log::error("Customer Enquiry fetching failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }

    /**
     * Created By : Pradyumn Dwivedi
     * Created at : 16-05-2022
     * Uses : Store newly created customer enquiry data in table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $msg_data = array();
        $isSubscribed = true;

        Log::info("Initiating Customer Enquiry process, starting at: " . Carbon::now()->format('H:i:s:u'));
        try {
            $token = readHeaderToken();
            if ($token) {
                $user_id = $token['sub'];
                // Request Validation
                $user_id = $request->user_id;

                $userSubscriptionCheck = User::find($user_id);
                $subscriptionEndDate = $userSubscriptionCheck->subscription_end;
                $todaysDate = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now());

                if (($userSubscriptionCheck->subscription_id == 0) || ($subscriptionEndDate < $todaysDate)) {
                    $isSubscribed = false;
                    $msg_data['is_subscribed'] = $isSubscribed;
                    errorMessage(__('user.no_active_subscription'), $msg_data);
                }


                $validationErrors = $this->validateEnquiry($request);
                if (count($validationErrors)) {
                    Log::error("Auth Exception: " . implode(", ", $validationErrors->all()));
                    errorMessage($validationErrors->all(), $validationErrors->all());
                }

                $recommendantionIds = $request->recommendation_engine_ids;

                // if (isset($request->product_quantity)) {
                //     $minOrderQuantityDataDB = RecommendationEngine::whereIn('id', $recommendantionIds)->where('min_order_quantity', '>', $request->product_quantity)->select('min_order_quantity')->first();
                //     if ($minOrderQuantityDataDB) {
                //         errorMessage(__('customer_enquiry.product_quantity_should_be_greater_than_minimum_order_quantity'), $msg_data);
                //     }
                // }

                $shelf_life = config('global.DEFAULT_SHELF_LIFE');
                $shelf_life_unit = config('global.DEFAULT_SHELF_LIFE_UNIT');
                if ($request->shelf_life) {
                    $shelf_life = $request->shelf_life;
                }

                if ($request->shelf_life_unit) {
                    $shelf_life_unit = $request->shelf_life_unit;
                }


                $request['user_id'] = $user_id;
                $request['entered_shelf_life'] = $shelf_life;
                $request['entered_shelf_life_unit'] = $shelf_life_unit;
                $request['recommendation_engine_id'] = $recommendantionIds[0];


                if ($shelf_life_unit == 'months') {
                    $request['shelf_life'] = $shelf_life * config('global.MONTH_TO_MULTIPLY_SHELF_LIFE');
                } else {
                    $request['shelf_life'] =  $shelf_life;
                }
                // print_r($request->all());
                // die;
                // Store a new enquiry

                $similarEnqueryCount = CustomerEnquiry::where([
                    'user_id' => $request['user_id'],
                    'category_id' => $request['category_id'],
                    'sub_category_id' => $request['sub_category_id'],
                    'product_id' => $request['product_id'],
                    'product_quantity' => $request['product_quantity'],
                    'packing_type_id' => $request['packing_type_id'],
                    'packaging_material_id' => $request['packaging_material_id'],
                ])->first();

                if ($similarEnqueryCount) {
                    $similarEnqueryCount->enquiry_id = getFormatid($similarEnqueryCount->id, 'customer_enquiries');
                    $similarEnqueryCount->is_subscribed = $isSubscribed;
                    successMessage(__('customer_enquiry.customer_enquiry_placed_successfully'), $similarEnqueryCount->toArray());
                } else {
                    $enquiryData = CustomerEnquiry::create($request->all());
                    $enquiryData->enquiry_id = getFormatid($enquiryData->id, 'customer_enquiries');
                    $enquiryData->is_subscribed = $isSubscribed;
                    $enquiryData->recommendationEngines()->attach($recommendantionIds);



                    $this->deductCredit($enquiryData->user_id, $enquiryData->id);

                    Log::info("Customer Enquiry Created successfully");
                    successMessage(__('customer_enquiry.customer_enquiry_placed_successfully'), $enquiryData->toArray());
                }
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unkown error occured',
                'error' => $e->getMessage()
            ], 500);
            Log::error("Customer enquiry creation failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }

    private function deductCredit($userId, $enqueryId)
    {
        try {
            $creditAmountToDeduct = 1;
            $user = User::select('id', 'current_credit_amount')->where('id', $userId)->first();
            $currentCredit = $user->current_credit_amount;

            if ($currentCredit == 0) {
                $creditAmountToDeduct = 0;
            }

            $remaingCredit = $currentCredit - $creditAmountToDeduct;
            $user->update([
                'current_credit_amount' => $remaingCredit
            ]);
            // $user->save();
            UserCreditHistory::create(
                [
                    'user_id' => $userId,
                    'amount' => $creditAmountToDeduct,
                    'enquery_id' => $enqueryId,
                    'reason' => __('my_profile.enquery_result_credit_deduct'),
                    'action' => 'deduct'
                ]
            );
        } catch (\Throwable $th) {
            //throw $th;
        }
    }


    /**
     * Validate request for Customer Enquiry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Countable|array
     */
    private function validateEnquiry(Request $request)
    {
        return Validator::make($request->all(), [
            'category_id' => 'required|numeric',
            'sub_category_id' => 'required|numeric',
            'product_id' => 'required|numeric',
            'recommendation_engine_ids' => 'required|array',
            'recommendation_engine_ids.*' => 'exists:recommendation_engines,id',
            'packaging_material_id' => 'required|numeric',
            'product_quantity' => 'required|numeric',
            'packing_type_id' => 'required|numeric',


            // 'shelf_life' => 'required|integer|between:1,10000',
            // 'shelf_life_unit' => 'required',
            'product_weight' => 'nullable|numeric',
            'measurement_unit_id' => 'nullable|numeric',
            'storage_condition_id' => 'nullable|numeric',
            'packaging_machine_id' => 'nullable|numeric',
            'product_form_id' => 'nullable|numeric',
            'packaging_treatment_id' => 'nullable|numeric',
            // 'product_quantity' => 'required|numeric',
        ])->errors();
        return Validator::make($request->all(), [
            'category_id' => 'required|numeric',
            'sub_category_id' => 'required|numeric',
            'product_id' => 'required|numeric',
            // 'shelf_life' => 'required|integer|between:1,10000',
            // 'shelf_life_unit' => 'required',
            'product_weight' => 'required|numeric',
            'measurement_unit_id' => 'required|numeric',
            'storage_condition_id' => 'required|numeric',
            'packaging_machine_id' => 'required|numeric',
            'product_form_id' => 'required|numeric',
            'packing_type_id' => 'required|numeric',
            'packaging_treatment_id' => 'required|numeric',
            'recommendation_engine_id' => 'required|numeric',
            // 'product_quantity' => 'required|numeric',
            'product_quantity' => 'nullable|numeric',
            'packaging_material_id' => 'required|numeric',
        ])->errors();
    }

    /**
     * Created By : Pradyumn Dwivedi
     * Created at : 29-sept-2022
     * Uses : Store newly created customer enquiry by product packaging solution data in table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function productEnquiryStore(Request $request)
    {
        $msg_data = array();
        $isSubscribed = true;

        Log::info("Initiating Product Customer Enquiry process, starting at: " . Carbon::now()->format('H:i:s:u'));
        try {
            $token = readHeaderToken();
            if ($token) {
                $user_id = $token['sub'];
                // Request Validation

                $userSubscriptionCheck = User::find($user_id);
                $subscriptionEndDate = $userSubscriptionCheck->subscription_end;
                $todaysDate = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now());

                // check user is subscribed or not
                if (($userSubscriptionCheck->subscription_id == 0) || ($subscriptionEndDate < $todaysDate)) {
                    $isSubscribed = false;
                    $msg_data['is_subscribed'] = $isSubscribed;
                    errorMessage(__('user.no_active_subscription'), $msg_data);
                }


                $validationErrors = $this->validateProductEnquiry($request);
                if (count($validationErrors)) {
                    Log::error("Auth Exception: " . implode(", ", $validationErrors->all()));
                    errorMessage($validationErrors->all(), $validationErrors->all());
                }
                $recommendantionIds = $request->packaging_solution_ids;
                //get recommendation engine(packaging solution) data from table
                $packagingSolutionData = RecommendationEngine::where('id', $request->packaging_solution_id)->first();

                // if (isset($request->product_quantity)) {
                //     $minOrderQuantityDataDB = RecommendationEngine::whereIn('id', $recommendantionIds)->where('min_order_quantity', '>', $request->product_quantity)->select('min_order_quantity')->first();
                //     if ($minOrderQuantityDataDB) {
                //         errorMessage(__('customer_enquiry.product_quantity_should_be_greater_than_minimum_order_quantity'), $msg_data);
                //     }
                // }
                // //checking min order quantity
                // if (isset($request->product_quantity) && ($request->product_quantity < $packagingSolutionData->min_order_quantity)) {
                //     errorMessage(__('customer_enquiry.product_quantity_should_be_greater_than_minimum_order_quantity'), $msg_data);
                // }

                //checking min order quantity
                if (isset($request->product_weight)) {
                    if ($request->product_weight < $packagingSolutionData->min_weight || $request->product_weight > $packagingSolutionData->max_weight)
                        errorMessage(__('customer_enquiry.product_weight_should_be_in_between_min_and_max_weight'), $msg_data);
                }

                //getting shelf life and shelf life unit from config
                $shelf_life = config('global.DEFAULT_SHELF_LIFE');
                $shelf_life_unit = config('global.DEFAULT_SHELF_LIFE_UNIT');
                if ($packagingSolutionData->display_shelf_life) {
                    $shelf_life = $packagingSolutionData->display_shelf_life;
                }
                //get product data
                $productData = Product::select('sub_category_id', 'unit_id')->where('id', $packagingSolutionData->product_id)->first();

                //getting data from recommendation engine table
                $request['user_id'] = $user_id;
                $request['category_id'] = $packagingSolutionData->category_id;
                $request['sub_category_id'] = $productData->sub_category_id;
                $request['product_id'] = $packagingSolutionData->product_id;
                $request['shelf_life'] = $shelf_life;
                $request['entered_shelf_life'] = $shelf_life;
                $request['entered_shelf_life_unit'] = $shelf_life_unit;
                $request['measurement_unit_id'] = $productData->unit_id;
                $request['storage_condition_id'] = $packagingSolutionData->storage_condition_id;
                $request['packaging_machine_id'] = $packagingSolutionData->packaging_machine_id;
                $request['product_form_id'] = $packagingSolutionData->product_form_id;
                $request['packing_type_id'] = $packagingSolutionData->packing_type_id;
                $request['packaging_treatment_id'] = $packagingSolutionData->packaging_treatment_id;
                $request['recommendation_engine_id'] = $request->packaging_solution_id;
                $request['packaging_material_id'] = $packagingSolutionData->packaging_material_id;
                $request['status'] = '1';



                if ($shelf_life_unit == 'months') {
                    $request['shelf_life'] = $shelf_life * config('global.MONTH_TO_MULTIPLY_SHELF_LIFE');
                } else {
                    $request['shelf_life'] =  $shelf_life;
                }

                $similarEnqueryCount = CustomerEnquiry::where([
                    'user_id' => $request['user_id'],
                    'category_id' => $request['category_id'],
                    'sub_category_id' => $request['sub_category_id'],
                    'product_id' => $request['product_id'],
                    'product_quantity' => $request['product_quantity'],
                    'packing_type_id' => $request['packing_type_id'],
                    'packaging_material_id' => $request['packaging_material_id'],
                ])->first();

                if ($similarEnqueryCount) {
                    $similarEnqueryCount->enquiry_id = getFormatid($similarEnqueryCount->id, 'customer_enquiries');
                    $similarEnqueryCount->is_subscribed = $isSubscribed;
                    successMessage(__('customer_enquiry.customer_enquiry_placed_successfully'), $similarEnqueryCount->toArray());
                } else {

                    // Store a new enquiry
                    $enquiryData = CustomerEnquiry::create($request->all());
                    $enquiryData->enquiry_id = getFormatid($enquiryData->id, 'customer_enquiries');
                    $enquiryData->is_subscribed = $isSubscribed;
                    $recommendantionIds = $request->packaging_solution_ids;
                    $enquiryData->recommendationEngines()->attach($recommendantionIds);
                    $this->deductCredit($enquiryData->user_id, $enquiryData->id);

                    Log::info("Product Customer Enquiry Created successfully");
                    successMessage(__('customer_enquiry.customer_enquiry_placed_successfully'), $enquiryData->toArray());
                }
            } else {
                errorMessage(__('auth.authentication_failed'), $msg_data);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unkown error occured',
                'error' => $e->getMessage()
            ], 500);
            Log::error("Customer enquiry creation failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
    }


    public function searchHistory(Request $request)
    {
        $msg_data = array();

        try {

            $validateRequest = Validator::make(
                $request->all(),
                [
                    'user_id' => ['required', Rule::exists('users', 'id')],
                ],
            );

            if ($validateRequest->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateRequest->errors()
                ], 401);
            }


            $userId = $request->user_id;
            $history = CustomerEnquiry::where('user_id', $userId)->get(
                [
                    'id',
                    'user_id',
                    'description',
                    'enquiry_type',
                    'category_id',
                    'category_id',
                    'sub_category_id',
                    'product_id',
                    'shelf_life',
                    'entered_shelf_life_unit',
                    'product_weight',
                    'measurement_unit_id',
                    'created_at',
                    'updated_at',
                    'status'
                ]
            );

            foreach ($history as $hist) {
                $credit = UserCreditHistory::where('enquery_id', $hist->id)->select('id')->first();
                $hist->credit_id = $credit ? $credit->id : null;
            }

            $msg_data['result'] = $history;

            successMessage(__('subscription.user_search_history_fetched'), $msg_data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unkown error occured',
                'error' => $e->getMessage()
            ], 500);
            Log::error("Adding credit failed: " . $e->getMessage());
            errorMessage(__('auth.something_went_wrong'), $msg_data);
        }
        // return $user;,
    }




    /**
     * Created By Pradyumn Dwivedi
     * Created at : 29-Sept-2022
     * Uses : Validate product customer enquiry request
     *
     * Validate request for Customer Enquiry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Countable|array
     */
    private function validateProductEnquiry(Request $request)
    {
        return Validator::make($request->all(), [
            'packaging_solution_id' => 'required|integer',
            'packaging_solution_ids' => 'required|array',
            'packaging_solution_ids.*' => 'exists:recommendation_engines,id',
            'product_quantity' => 'required|numeric',
            'product_weight' => 'sometimes|numeric',
        ])->errors();
    }
}
