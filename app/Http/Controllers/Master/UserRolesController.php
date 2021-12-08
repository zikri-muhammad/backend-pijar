<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role as UserRole;

class UserRolesController extends Controller
{
    /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {}

    /**
     * Getting lists of Data.
     *
     * @return void
     */
    public function index($id = '', Request $request){

        $data = $request->all();
        $filters = filters($data);
        
        try {
            $data = UserRole::data($filters, $id);
        } catch (\Throwable $th) {
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        if ($data->count() > 0 ) {
            set_response('Success retrieve data', $data);
        } else {
            set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
        }
    } 
}
