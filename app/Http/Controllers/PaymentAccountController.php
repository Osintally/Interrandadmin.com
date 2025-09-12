<?php

namespace App\Http\Controllers;

use App\PaymentAccount;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PaymentAccountController extends Controller
{
    protected $commonUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @param  ModuleUtil  $moduleUtil
     * @return void
     */
    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $payment_accounts = PaymentAccount::where('business_id', $business_id)
                ->select(['id', 'name', 'account_type', 'account_number', 'note', 'is_closed', 'created_at']);

            return DataTables::of($payment_accounts)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    
                    if (auth()->user()->can('account.update')) {
                        $html .= '<a href="' . action([PaymentAccountController::class, 'edit'], [$row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a>';
                    }
                    
                    if (auth()->user()->can('account.delete')) {
                        $html .= '<button data-href="' . action([PaymentAccountController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_payment_account_button"><i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '</button>';
                    }
                    
                    $html .= '</div>';
                    return $html;
                })
                ->editColumn('account_type', function ($row) {
                    return PaymentAccount::account_name($row->account_type);
                })
                ->editColumn('is_closed', function ($row) {
                    return $row->is_closed ? '<span class="label label-danger">' . __("lang_v1.closed") . '</span>' : '<span class="label label-success">' . __("lang_v1.active") . '</span>';
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'is_closed'])
                ->make(true);
        }

        return view('payment_account.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        if (!auth()->user()->can('account.create')) {
            abort(403, 'Unauthorized action.');
        }

        $account_types = PaymentAccount::account_types();

        return view('payment_account.create', compact('account_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('account.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $request->validate([
            'name' => 'required|string|max:255',
            'account_type' => 'required|string',
            'account_number' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        try {
            $payment_account = PaymentAccount::create([
                'business_id' => $business_id,
                'name' => $request->name,
                'account_type' => $request->account_type,
                'account_number' => $request->account_number,
                'note' => $request->note,
                'created_by' => auth()->user()->id,
                'is_closed' => 0
            ]);

            $output = [
                'success' => true,
                'msg' => __("lang_v1.payment_account_added_successfully")
            ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        if (!auth()->user()->can('account.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $payment_account = PaymentAccount::where('business_id', $business_id)->findOrFail($id);

        return view('payment_account.show', compact('payment_account'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        if (!auth()->user()->can('account.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $payment_account = PaymentAccount::where('business_id', $business_id)->findOrFail($id);
        $account_types = PaymentAccount::account_types();

        return view('payment_account.edit', compact('payment_account', 'account_types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('account.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $payment_account = PaymentAccount::where('business_id', $business_id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'account_type' => 'required|string',
            'account_number' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        try {
            $payment_account->update([
                'name' => $request->name,
                'account_type' => $request->account_type,
                'account_number' => $request->account_number,
                'note' => $request->note,
            ]);

            $output = [
                'success' => true,
                'msg' => __("lang_v1.payment_account_updated_successfully")
            ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('account.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $payment_account = PaymentAccount::where('business_id', $business_id)->findOrFail($id);

        try {
            $payment_account->delete();

            $output = [
                'success' => true,
                'msg' => __("lang_v1.payment_account_deleted_successfully")
            ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }
}
