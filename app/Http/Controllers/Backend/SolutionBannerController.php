<?php

/*
    *	Developed by : Mikiyas Birhanu
    *	Project Name : Packult
    *	File Name : SolutionBannerController.php
    *	File Path : app\Http\Controllers\Backend\SolutionBannerController.php
    *	Created On : 28-03-2022
    *	http ://www.mypcot.com
*/

namespace App\Http\Controllers\Backend;

use App\Exports\ExportBannerReports;
use App\Http\Controllers\Controller;
use App\Models\AppPage;
use App\Models\BannerClick;
use App\Models\BannerView;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\SolutionBanner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;
use Yajra\DataTables\DataTables;

class SolutionBannerController extends Controller
{
    /**
     *   created by : Pradyumn Dwivedi
     *   Created On : 28-Mar-2022
     *   Uses :  To show Banner listing page
     */

    public function index()
    {
        $data['add_solution_banner'] = checkPermission('add_banner');
        $data['solution_banner_edit'] = checkPermission('banner_edit');
        $data['solution_banner_status'] = checkPermission('banner_status');
        $data['solution_banner_view'] = checkPermission('banner_view');
        return view('backend/solution_banners/index', ["data" => $data]);
    }

    /**
     *   created by : Pradyumn Dwivedi
     *   Created On : 28-Mar-2022
     *   Uses :  display dynamic data in datatable for Banner page
     *   @param Request request
     *   @return Response
     */
    public function fetch(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = SolutionBanner::select('*')->orderBy('updated_at', 'desc');

                return DataTables::of($query)
                    ->filter(function ($query) use ($request) {
                        if (isset($request['search']['search_banner_title']) && !is_null($request['search']['search_banner_title'])) {
                            $query->where('title', 'like', "%" . $request['search']['search_banner_title'] . "%");
                        }
                        $query->get();
                    })
                    ->editColumn('title', function ($event) {
                        return $event->title;
                    })
                    ->editColumn('banner_image_url', function ($event) {
                        $imageUrl = ListingImageUrl('banner', $event->banner_thumb_image, 'thumb');
                        return ' <img src="' . $imageUrl . '" />';
                    })
                    ->editColumn('click_count', function ($event) {
                        $actions = '<span style="white-space:nowrap;">' . $event->click_count;
                        if ($event->click_count > 0)
                            $actions .= '<a href="' . route('solution_banner_clicks_report', ['id' => $event->id]) . '" class="btn ml-2 btn-primary btn-sm modal_src_data" data-size="large" data-title="Banner Click Report" title="View"><i class="fa fa-eye"></i></a>';

                        $actions .= '</span>';
                        return $actions;
                    })
                    ->editColumn('view_count', function ($event) {
                        $actions = '<span style="white-space:nowrap;">' . $event->view_count;
                        if ($event->view_count > 0)
                            $actions .= '<a href="' . route('solution_banner_views_report', ['id' => $event->id]) . '" class="btn ml-2 btn-primary btn-sm modal_src_data" data-size="large" data-title="Banner Views Report" title="View"><i class="fa fa-eye"></i></a>';

                        $actions .= '</span>';
                        return $actions;
                    })
                    ->editColumn('action', function ($event) {
                        $solution_banner_view = checkPermission('banner_view');
                        $solution_banner_edit = checkPermission('banner_edit');
                        $solution_banner_status = checkPermission('banner_status');
                        $actions = '<span style="white-space:nowrap;">';
                        if ($solution_banner_view) {
                            $actions .= '<a href="solution_banner_view/' . $event->id . '" class="btn btn-primary btn-sm modal_src_data" data-size="large" data-title="View Banner Details" title="View"><i class="fa fa-eye"></i></a>';
                        }
                        if ($solution_banner_edit) {
                            $actions .= ' <a href="solution_banner_edit/' . $event->id . '" class="btn btn-success btn-sm src_data" title="Update"><i class="fa fa-edit"></i></a>';
                        }
                        if ($solution_banner_status) {
                            if ($event->status == '1') {
                                $actions .= ' <input type="checkbox" data-url="publishBanners" id="switchery' . $event->id . '" data-id="' . $event->id . '" class="js-switch switchery" checked>';
                            } else {
                                $actions .= ' <input type="checkbox" data-url="publishBanners" id="switchery' . $event->id . '" data-id="' . $event->id . '" class="js-switch switchery">';
                            }
                        }
                        $actions .= '</span>';
                        return $actions;
                    })
                    ->addIndexColumn()
                    ->rawColumns(['title', 'link', 'description', 'banner_image_url', 'click_count', 'view_count', 'action', 'start_date_time', 'end_date_time'])->setRowId('id')->make(true);
            } catch (\Exception $e) {
                Log::error("Something Went Wrong. Error: " . $e->getMessage());
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
     *   Created On : 28-Mar-2022
     *   Uses : To load Add Banner page
     */
    public function add()
    {
        $appPages = AppPage::all();
        $products = Product::select(['id', 'product_name'])->get();

        $data['appPages'] = $appPages;
        $data['products'] = $products;
        return view('backend/solution_banners/solution_banner_add', $data);
    }

    /**
     *   Created by : Pradyumn Dwivedi
     *   Created On : 28-Mar-2022
     *   Uses :
     *   @param int $id
     *   @return Response
     */
    public function edit($id)
    {
        $data = SolutionBanner::find($id);
        $data->product_ids = $data->products->map->id;
        $data->page;
        $selectedPageId = $data->page ? $data->page->id : null;
        $data->selected_page_id = $selectedPageId;
        $appPages = AppPage::all();
        $products = Product::select(['id', 'product_name'])->get();
        $data->appPages = $appPages;
        $data->products = $products;
        if ($data) {
            $data->image_path = getFile($data->banner_image, 'banner', true);
        }
        return view('backend/solution_banners/solution_banner_edit', ["data" => $data]);
    }

    /**
     *    created by : Pradyumn Dwivedi
     *    Created On : 28-Mar-2022
     *   Uses :
     *   @param Request request
     *   @return Response
     */
    public function saveFormData(Request $request)
    {
        $msg_data = array();
        $msg = "";
        if (isset($_GET['id'])) {
            $validationErrors = $this->validateRequest($request);
        } else {
            $validationErrors = $this->validateNewRequest($request);
        }


        if (count($validationErrors)) {
            Log::error("Banner Validation Exception: " . implode(", ", $validationErrors->all()));
            errorMessage(implode("\n", $validationErrors->all()), $msg_data);
        }
        $isEditFlow = false;
        if (isset($_GET['id'])) {
            $isEditFlow = true;
            $response = SolutionBanner::where([['title', strtolower($request->title)], ['id', '<>', $_GET['id']]])->get()->toArray();
            if (isset($response[0])) {
                errorMessage(' Banner Title Already Exist', $msg_data);
            }
            $tableObject = SolutionBanner::find($_GET['id']);
            $msg = "Data Updated Successfully";
        } else {
            $tableObject = new SolutionBanner;
            $response = SolutionBanner::where([['title', strtolower($request->title)]])->get()->toArray();
            if (isset($response[0])) {
                errorMessage('Banner Title Already Exist', $msg_data);
            }
            $msg = "Data Saved Successfully";
        }
        $tableObject->title = $request->title;
        //FOR SEO
        $seoUrl = generateSeoURL($request->title, 60);
        $tableObject->seo_url = $seoUrl;
        $tableObject->meta_title = $request->meta_title;
        $tableObject->link = $request->link;
        // Check if app_page_id is null in the request
        if ($request->app_page_id === null) {
            // Set app_page_id to null before saving
            $tableObject->app_page_id = null;
        } else {
            // Set app_page_id based on the request
            $tableObject->app_page_id = $request->app_page_id;
        }
        $tableObject->start_date_time = $request->start_date_time;
        $tableObject->end_date_time = $request->end_date_time;
        $tableObject->description = $request->description;
        $tableObject->meta_description = $request->meta_description;
        $tableObject->meta_keyword = $request->meta_keyword;
        if ($isEditFlow) {
            $tableObject->updated_by = session('data')['id'];
        } else {
            $tableObject->created_by = session('data')['id'];
        }

        $tableObject->save();

        $tableObject->products()->sync($request->product_ids);
        $last_inserted_id = $tableObject->id;

        if ($request->hasFile('banner_image')) {
            $image = $request->file('banner_image');
            $actualImage = saveSingleImage($image, 'banner', $last_inserted_id);
            $thumbImage = createThumbnail($image, 'banner', $last_inserted_id, 'banner');
            $bannerObj = SolutionBanner::find($last_inserted_id);
            $bannerObj->banner_image = $actualImage;
            $bannerObj->banner_thumb_image = $thumbImage;
            $bannerObj->save();
        }
        successMessage($msg, $msg_data);
    }

    /**
     *   Created by : Pradyumn Dwivedi
     *   Created On : 28-Mar-2022
     *   Uses :  to load banners view
     *   @param int $id
     *   @return Response
     */
    public function view($id)
    {
        $data = SolutionBanner::find($id);
        $data->products;
        $data->page;
        if ($data) {
            $data->image_path = getFile($data->banner_image, 'banner', true);
        }
        return view('backend/solution_banners/solution_banner_view', ["data" => $data]);
    }


    public function exportImpressionData(Request $request)
    {

        $validateRequest = Validator::make(
            $request->all(),
            [
                'id' => 'exists:solution_banners,id'
            ],
        );

        if ($validateRequest->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateRequest->errors()
            ], 401);
        }

        $id = $request->id;
        $banner = SolutionBanner::find($request->id);

        $date = Carbon::now()->toDateTimeString();

        $fileName = $banner->title . '_' . str_replace('-', $date, '_') . '.xlsx';


        $startDate = Carbon::parse($banner->start_date_time ?? $banner->created_at)->toDateTimeString();
        $endDate = $banner->end_date_time ? Carbon::parse($banner->end_date_time)->toDateTimeString() : Carbon::now()->toDateTimeString();

        $views = BannerView::ofSolutionBanner($id)->get();
        $data = new stdClass;
        $data->id = $id;
        $data->start_date = $startDate;
        $data->end_date = $endDate;
        $data->data = $views->map(function ($click) {
            $newData = new stdClass;
            $newData->user_name = $click->user->name;
            $newData->email = $click->user->email;
            $newData->date = $click->created_at;
            return $newData;
        });

        $exportViews = new ExportBannerReports($data);


        return Excel::download($exportViews, $fileName);
    }

    /**
     *   Created by : Pradyumn Dwivedi
     *   Created On : 28-Mar-2022
     *   Uses :  to load banners view
     *   @param int $id
     *   @return Response
     */
    public function clickViews($id)
    {
        $clicks = BannerClick::ofSolutionBanner($id)->get();
        $data = $clicks->map(function ($click) {
            $newData = new stdClass;
            $newData->user_name = $click->user->name;
            $newData->email = $click->user->email;
            $newData->date = $click->created_at;
            return $newData;
        });


        return view('backend/solution_banners/solution_banner_clicks', ["data" => $data]);
    }

    /**
     *   Created by : Pradyumn Dwivedi
     *   Created On : 28-Mar-2022
     *   Uses :  to load banners view
     *   @param int $id
     *   @return Response
     */
    public function impressionViews($id)
    {

        $banner = SolutionBanner::find($id);
        $startDate = Carbon::parse($banner->start_date_time ?? $banner->created_at)->toDateTimeString();
        $endDate = $banner->end_date_time ? Carbon::parse($banner->end_date_time)->toDateTimeString() : Carbon::now()->toDateTimeString();

        $views = BannerView::ofSolutionBanner($id)->get();
        $data = new stdClass;
        $data->id = $id;
        $data->start_date = $startDate;
        $data->end_date = $endDate;
        $data->data = $views->map(function ($click) {
            $newData = new stdClass;
            $newData->user_name = $click->user->name;
            $newData->email = $click->user->email;
            $newData->date = $click->created_at;
            return $newData;
        });


        return view('backend/solution_banners/solution_banner_impressions', ["data" => $data]);
    }

    /**
     *   Created by : Pradyumn Dwivedi
     *   Created On : 28-Mar-2022
     *   Uses :  To publish or unpublish Banner records
     *   @param Request request
     *   @return Response
     */
    public function updateStatus(Request $request)
    {
        $msg_data = array();
        if ($request->status == 0) {
            $activeCount = SolutionBanner::where('status', 1)->get()->count();
            if ($activeCount == 1) {
                errorMessage('Last One Banner Must Be Active', $msg_data);
            }
        }
        $recordData = SolutionBanner::find($request->id);
        $recordData->status = $request->status;
        $recordData->save();
        if ($request->status == 1) {
            successMessage('Published', $msg_data);
        } else {
            successMessage('Unpublished', $msg_data);
        }
    }

    /**
     *   Created by : Pradyumn Dwivedi
     *   Created On : 28-Mar-2022
     *   Uses :  Banner Add|Edit Form Validation part will be handle by below function
     *   @param Request request
     *   @return Countable|array
     */
    private function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required|string',
            'banner_image' => 'mimes:jpeg,png,jpg|max:' . config('global.SIZE.BANNER'),
            'app_page_id' => [
                Rule::requiredIf(function () use ($request) {
                    return empty($request->link);
                }),
                'nullable',
                'exists:app_pages,id', // Make sure the selected app_page_id exists in the app_pages table
            ],
            'link' => [
                Rule::requiredIf(function () use ($request) {
                    return empty($request->app_page_id);
                }),
                'nullable',
                'url',
            ],
        ])->errors();
    }

    /**
     *   Created by : Pradyumn Dwivedi
     *   Created On : 28-Mar-2022
     *   Uses :  Banner Add|Edit Form Validation part will be handle by below function
     *   @param Request request
     *   @return Countable|array
     */
    private function validateNewRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required|string',
            'banner_image' => 'required|mimes:jpeg,png,jpg|max:' . config('global.SIZE.BANNER'),
            'app_page_id' => [
                Rule::requiredIf(function () use ($request) {
                    return empty($request->link);
                }),
                'nullable',
                'exists:app_pages,id', // Make sure the selected app_page_id exists in the app_pages table
            ],
            'link' => [
                Rule::requiredIf(function () use ($request) {
                    return empty($request->app_page_id);
                }),
                'nullable',
                'url',
            ],

        ])->errors();
    }
}
