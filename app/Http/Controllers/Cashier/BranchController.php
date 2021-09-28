<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\AdminStore as Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller {

    public function index( Request $request ) {

        $user = Auth::user();
        $branch_id = $user->branch_id;
        $branche = Branch::where( 'id', $branch_id )->select( 'accept_orders' )->active()->first();
        $i = 0;

        if ( !empty( $branch_id ) ) {

            return new JsonResponse( [
                'status'=>1,
                'message' => 'done',
                'data' => $branche,
            ], 200 );
        }

        return new JsonResponse( [
            'status'=>1,
            'message' => 'fail',
            'data' => [],
        ], 200 );
    }

    public function change_accept_orders_status( Request $request ) {
        $input = $request->only( [
            'accept_orders'
        ] );
        $this->validate( $request, [
            'accept_orders' => 'required|integer|between:0,1',
        ] );
        $user = Auth::user();
        $branch_id = $user->branch_id;

        if ( !empty( $branch_id ) ) {

            $branche = Branch::findOrFail( $branch_id );
            $branche->accept_orders = $input['accept_orders'];
            $branche->save();

            return new JsonResponse( [
                'status'=>1,
                'message' => 'done',
                'data' => $branche,
            ], 200 );
        }

        return new JsonResponse( [
            'status'=>1,
            'message' => 'fail',
            'data' => [],
        ], 200 );
    }

}
