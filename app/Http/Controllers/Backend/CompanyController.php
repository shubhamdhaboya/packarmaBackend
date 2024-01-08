<?php
/*
    *	Developed by : Sagar Thokal - Mypcot Infotech 
    *	Project Name : RRPL 
    *	File Name : CompanyController.php
    *	File Path : app\Http\Controllers\Backend\CompanyController.php
    *	Created On : 10-02-2022
    *	http ://www.mypcot.com
*/
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;
use Yajra\DataTables\DataTables;
use URL;

class CompanyController extends Controller
{
    /**
       *   created by : Sagar Thokal
       *   Created On : 10-Feb-2022
       *   Uses :  To show company listing page
    */
    public function index(){
        return redirect('/webadmin/dashboard');
        // $data['company_add'] = checkPermission('company_add');
        // $data['company_view'] = checkPermission('company_view');
        // $data['company_edit'] = checkPermission('company_edit');
        // $data['company_status'] = checkPermission('company_status');
        // return view('backend/company/index',["data"=>$data]);
    }

    /**
       *   created by : Sagar Thokal
       *   Created On : 10-Feb-2022
       *   Uses :  display dynamic data in datatable for company page
       *   @param Request request
       *   @return Response
    */
    public function fetch(Request $request){
        if ($request->ajax()) {
        	try {
	            $query = Company::select('*')->orderBy('updated_at','desc');
	            return DataTables::of($query)
                    ->filter(function ($query) use ($request) {
                        
                        if ($request['search']['search_company_name'] && ! is_null($request['search']['search_company_name'])) {
                            $query->where('company_name', 'like', "%" . $request['search']['search_company_name'] . "%");
                        }

                        $query->get();
                    })
	                ->editColumn('company_name', function ($event) {
	                    return $event->company_name;
	                })
                    ->editColumn('company_image_url', function ($event) {
                        $imageUrl = ListingImageUrl('company',$event->company_thumb_image,'thumb');      
                        return ' <img src="'. $imageUrl .'" />';
                    })
	                ->editColumn('action', function ($event) {
                        $company_view = checkPermission('company_view');
                        $company_edit = checkPermission('company_edit');
	                    $company_status = checkPermission('company_status');
	                    $actions = '<span style="white-space:nowrap;">';
                        if ($company_view) {
                            $actions .= '<a href="company_view/' . $event->id . '" class="btn btn-primary btn-sm modal_src_data" data-size="large" data-title="View Company Details" title="View"><i class="fa fa-eye"></i></a>';
                        }
                        if($company_edit) {
                            $actions .= ' <a href="company_edit/'.$event->id.'" class="btn btn-success btn-sm src_data" title="Update"><i class="fa fa-edit"></i></a>';
                        }
                        if($company_status) {
                            if($event->status == '1') {
                                $actions .= ' <input type="checkbox" data-url="publishCompany" id="switchery'.$event->id.'" data-id="'.$event->id.'" class="js-switch switchery" checked>';
                            } else {
                                $actions .= ' <input type="checkbox" data-url="publishCompany" id="switchery'.$event->id.'" data-id="'.$event->id.'" class="js-switch switchery">';
                            }
                        }
                        return $actions;
	                }) 
	                ->addIndexColumn()
	                ->rawColumns(['company_name','company_image_url','action'])->setRowId('id')->make(true);
	        }
	        catch (\Exception $e) {
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
       *   created by : Sagar Thokal
       *   Created On : 10-Feb-2022
       *   Uses : To load Add company page
    */
    public function add() {
        return redirect('/webadmin/dashboard');
        // return view('backend/company/company_add');
    }

    /**
       *   created by : Sagar Thokal
       *   Created On : 10-Feb-2022
       *   Uses :  To load Edit company page
       *   @param int $id
       *   @return Response
    */
    public function edit($id) {
        return redirect('/webadmin/dashboard');
        // $data['data'] = Company::find($id);
        // return view('backend/company/company_edit',["data"=>$data]);
    }

    /**
     *   Created by : Pradyumn Dwivedi
     *   Created On : 05-April-2022
     *   Uses :  to load company view
     *   @param int $id
     *   @return Response
     */
    public function view($id)
    {
        return redirect('/webadmin/dashboard');
        // $data= Company::find($id);
        // if($data){
        //     $data->image_path = getFile($data->comapny_image,'company',true);
        // }
        // return view('backend/company/company_view', ["data"=>$data]);
    }
    /**
       *   created by : Sagar Thokal
       *   Created On : 10-Feb-2022
       *   Uses :  To store company details in table
       *   @param Request request
       *   @return Response
    */
    public function saveFormData(Request $request)
    {
    	$msg_data=array();
        $msg = "";
        $validationErrors = $this->validateRequest($request);
		if (count($validationErrors)) {
            \Log::error("Company Validation Exception: " . implode(", ", $validationErrors->all()));
        	errorMessage(implode("\n", $validationErrors->all()), $msg_data);
        }
        $isEditFlow = false;
        if(isset($_GET['id'])) {
            $isEditFlow = true;
            $response = Company::where([['company_name', strtolower($request->company_name)],['id', '<>', $_GET['id']]])->get()->toArray();
            if(isset($response[0])){
                errorMessage('Company Name Already Exist', $msg_data);
            }
            $tableObject = Company::find($_GET['id']);
            $msg = "Data Updated Successfully";
        } else {
            $tableObject = new Company;
            $response = Company::where([['company_name',strtolower($request->company_name)]])->get()->toArray();
            if(isset($response[0])){
                errorMessage('Company Name Already Exist', $msg_data);
            }
            $msg = "Data Saved Successfully";
        }
        if($isEditFlow){
            $tableObject->updated_by = session('data')['id'];
        }else{
            $tableObject->created_by = session('data')['id'];
        }
        $tableObject->company_name = $request->company_name;
        $tableObject->incorporation_year = date('Y');
        //FOR SEO
        $seoUrl = generateSeoURL($request->company_name,60);
        $tableObject->seo_url = $seoUrl;
        $tableObject->meta_title = $request->meta_title;
        $tableObject->meta_description = $request->meta_description;
        $tableObject->meta_keyword = $request->meta_keyword;

        $tableObject->save();
        $last_inserted_id = $tableObject->id;
        if($request->hasFile('attachment')) {
            $image = $request->file('attachment');
            $actualImage = saveSingleImage($image,'company',$last_inserted_id);
            $thumbImage = createThumbnail($image,'company',$last_inserted_id);
            $companyObj = Company::find($last_inserted_id);
            $companyObj->company_logo = $actualImage;
            $companyObj->company_thumb_logo = $thumbImage;
            $companyObj->save();
        }
        successMessage($msg , $msg_data);
    }

    /**
       *   created by : Sagar Thokal
       *   Created On : 10-Feb-2022
       *   Uses :  To publish or unpublish Company records
       *   @param Request request
       *   @return Response
    */
    public function updateStatus(Request $request)
    {
        $msg_data = array();
        $recordData = Company::find($request->id);
        $recordData->status = $request->status;
        $recordData->save();
        if($request->status == 1) {
        	successMessage('Published', $msg_data);
        }
        else {
        	successMessage('Unpublished', $msg_data);
        }
    }

    /**
       *   created by : Sagar Thokal
       *   Created On : 10-Feb-2022
       *   Uses :  Company Add|Edit Form Validation part will be handle by below function
       *   @param Request request
       *   @return Response
    */
    private function validateRequest(Request $request)
    {
        return \Validator::make($request->all(), [
            'company_name' => 'string|required',
            'attachment' => 'nullable|mimes:jpeg,png,jpg|max:'.config('global.SIZE.COMPANY'),
        ])->errors();
    }
}
