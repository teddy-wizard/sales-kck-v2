<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MsSalesPeople;
use App\MsSalesPersonMapping;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => false,'error' => $validator->errors()], 422);
        }

        if (! $token = JWTAuth::attempt($validator->validated())) {

            return response()->json(['result' => false, 'error' => 'Unauthorized'], 401);
        }

        $login_user = JWTAuth::user();
        if (in_array('4', $login_user->role) || in_array('5', $login_user->role)) {
            $salesperson = MsSalesPeople::where('userId', $login_user->id)->first();

            if($salesperson) {
                $user['id'] = $login_user->id;
                $user['username'] = $login_user->username;
                $user['name'] = $login_user->name;
                $user['email'] = $login_user->email;
                $user['role'] = $login_user->role;
                $user['salesAgentCode'] = $salesperson->code;
                $sales_agent_mappings = MsSalesPersonMapping::where('salespersonId', $salesperson->id)->get();
                $companies = [];
                foreach($sales_agent_mappings as $sales_agent_mapping) {
                    $ac_sales_agent = $sales_agent_mapping->acSalesAgentInfo;
                    if(isset($ac_sales_agent) && ($ac_sales_agent->active == 1) && ($ac_sales_agent->deleted == 0)) {
                        $company = [];
                        $company['code'] = $sales_agent_mapping->companyInfo->code;
                        $company['name'] = $sales_agent_mapping->companyInfo->name;
                        $company['salesAgent'] = $ac_sales_agent->salesAgent;
                        $company['sysType'] = $sales_agent_mapping->companyInfo->sysType;
                        $company['displayName'] = $sales_agent_mapping->companyInfo->displayName;
                        $company['address'] = $sales_agent_mapping->companyInfo->address;
                        $company['phone'] = $sales_agent_mapping->companyInfo->phone;
                        $company['fax'] = $sales_agent_mapping->companyInfo->fax;
                        $company['email'] = $sales_agent_mapping->companyInfo->email;
                        $company['website'] = $sales_agent_mapping->companyInfo->website;
                        $company['lbbNo'] = $sales_agent_mapping->companyInfo->lbbNo;
                        $company['gstRegNo'] = $sales_agent_mapping->companyInfo->gstRegNo;
                        $company['additionalInfo'] = $sales_agent_mapping->companyInfo->additionalInfo;
                        array_push($companies, $company);
                    }
                }

                $user['companies'] = $companies;

                $result = true;

                return response()->json(compact('result', 'user','token'));
            } else {
                return response()->json(['result' => false, 'error' => 'This is a non-sales user.'], 200);
            }

        } else {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth('api')->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        return response()->json(auth('api')->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }
}
