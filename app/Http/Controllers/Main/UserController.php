<?php

namespace App\Http\Controllers\Main;

use App\AcSalesAgent;
use App\Branch;
use App\Company;
use App\Http\Requests\StoreUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\MsCompany;
use App\MsSalesArea;
use App\MsSalesPeople;
use App\MsSalesPersonMapping;
use App\UserRole;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        $isAdmin = false;
        if(Auth::user()->id == 1)
            $isAdmin = true;

        if(in_array('1', Auth::user()->role) || in_array('2', Auth::user()->role))
            $isAdmin = true;

        return view('main.user.index')
                ->with('users', $users)
                ->with('isAdmin', $isAdmin);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = MsCompany::get();
        $branches = MsSalesArea::get();
        $managers = UserRole::where('role_id', 5)->get();
        return view('main.user.add')
            ->with('companies', $companies)
            ->with('branches', $branches)
            ->with('managers', $managers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUser $request)
    {
        $request->validated();
        $user = new User();
        $user->username = $request->username;
        $user->name = $request->name;
        $user->email = $request->email;
        $roles = $request->role;
        if(isset($request->company));
            $user->company_ids = implode(",", $request->company);
        $user->password = bcrypt("newpassword123!@#");
        $user->status = 1;
        $user->save();

        foreach ($roles as $role) {
            $userRole = new UserRole();
            $userRole->user_id = $user->id;
            $userRole->role_id = $role;
            $userRole->save();
        }

        if (in_array('4', $user->role) || in_array('5', $user->role)) {

            $msSalesPeople = new MsSalesPeople();
            $msSalesPeople->userId = $user->id;
            $msSalesPeople->salesArea = $request->salesArea;
            $msSalesPeople->managerId = $request->managerId;
            $msSalesPeople->monthTarget = $request->monthTarget;
            if(isset($request->documentCode))
                $msSalesPeople->code = $request->documentCode;
            else
                $msSalesPeople->code = '';
            $msSalesPeople->save();
        }

        return redirect()->route('user.index')->with('message', 'It has been created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $companies = MsCompany::get();
        $branches = MsSalesArea::get();
        $managers = UserRole::where('role_id', 5)->get();
        $salespeople_id = MsSalesPeople::where('userId', $id)->first()->id;
        $salesPersonMappings = MsSalesPersonMapping::where('salespersonId', $salespeople_id)->get();

        return view('main.user.add')
            ->with('user', $user)
            ->with('companies', $companies)
            ->with('branches', $branches)
            ->with('managers', $managers)
            ->with('salesPersonMappings', $salesPersonMappings);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUser $request, $id)
    {
        $request->validated();
        $user = User::find($id);

        $user->username = $request->username;
        $user->name = $request->name;
        $user->email = $request->email;
        $new_roles = $request->role;
        $user->company_ids = implode(",", $request->company);
        $user->status = $request->status;
        $user->save();

        $old_roles = $user->role;
        sort($old_roles);
        sort($new_roles);

        if($old_roles != $new_roles) {
            $oldUserRole = UserRole::where('user_id', $id)->delete();
            foreach ($new_roles as $role) {
                $userRole = new UserRole();
                $userRole->user_id = $id;
                $userRole->role_id = $role;
                $userRole->save();
            }
        }

        if (in_array('4', $new_roles) || in_array('5', $new_roles)) {

            $msSalesPeople = MsSalesPeople::where('userId', $id)->first();
            if(!isset($msSalesPeople))
                $msSalesPeople = new MsSalesPeople();
            $msSalesPeople->salesArea = $request->salesArea;
            $msSalesPeople->managerId = $request->managerId;
            $msSalesPeople->monthTarget = $request->monthTarget;
            if(isset($request->documentCode))
                $msSalesPeople->code = $request->documentCode;
            else
                $msSalesPeople->code = '';
            $msSalesPeople->save();
        } else {
            $msSalesPeople = MsSalesPeople::where('userId', $id)->delete();
        }

        return redirect()->route('user.index')->with('message', 'It has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // User::destroy($id);
        return redirect()->route('user.index')->with('message', 'It has been deleted successfully.');
    }

    public function changePassword()
    {
        return view('main.user.change-password');
    }

    public function getCompanyByUser(Request $request)
    {
        $user_id = $request->user_id;
        $company_ids = User::find($user_id)->company_ids;
        $companyIDs = mb_split(',', $company_ids);
        $companies = MsCompany::whereIn('id', $companyIDs)->get();
        $opts = '<option value=""></option>';
        foreach($companies as $company){
            $opts .= '<option value="'.$company->id.'">'.$company->name.'</option>';
        }
        return response()->json($opts);
    }

    public function getSaleAgentsByCompany(Request $request)
    {
        $company_id = $request->company_id;
        $extId = MsCompany::find($company_id)->extId;
        $agents = AcSalesAgent::where('customerId', $extId)->where('active', 1)->where('deleted', 0)->orderBy('id', 'asc')->get();

        $opts = '';
        foreach($agents as $agent){
            $opts .= '<option value="'.$agent->id.'">'.$agent->salesAgent.'</option>';
        }
        return response()->json($opts);
    }

    public function addSaleAgentMapping(Request $request)
    {
        $user_id = $request->user_id;
        $company_id = $request->company_id;
        $agent_id = $request->agent_id;

        $salespersonId = MsSalesPeople::where('userId', $user_id)->first()->id;

        if(!isset($salespersonId)) {
            $data['status'] = false;
            return response()->json($data);
        }

        $salesPersonMapping = new MsSalesPersonMapping();
        $salesPersonMapping->salespersonId = $salespersonId;
        $salesPersonMapping->companyId = $company_id;
        $salesPersonMapping->acSalesAgentId = $agent_id;
        $salesPersonMapping->save();

        $companyName = MsCompany::find($company_id)->name;
        $agentName = AcSalesAgent::find($agent_id)->salesAgent;

        $data['row'] = '<tr><td>'.$companyName.'</td><td>'.$agentName.'</td><td><div class="table-data-feature text-center"><button href="#" class="sale-agent-remove" data-mappingId="'.$salesPersonMapping->id.'" data-toggle="tooltip" data-placement="top" title="Remove"><i class="zmdi zmdi-delete"></i>&nbsp; REMOVE</button></div></td></tr>';
        $data['status'] = true;

        return response()->json($data);
    }

    public function removeSaleAgentMapping(Request $request)
    {
        $mapping_id = $request->mapping_id;
        $mapping = MsSalesPersonMapping::find($mapping_id);
        if (isset($mapping)) {
            $data['status'] = true;
            $mapping->delete();
        } else {
            $data['status'] = false;
        }

        return response()->json($data);
    }

    public function resetPasswordByAdmin($user_id)
    {
        $user = User::find($user_id);
        if(isset($user)) {
            $user->password = bcrypt("newpassword123!@#");
            $user->save();
            $data['status'] = true;
        } else {
            $data['status'] = false;
        }

        return redirect()->route('user.index')->with('message', 'It has been reseted successfully.');
    }
}
