<?php

namespace Modules\FrontendCMS\Http\Controllers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use \Modules\FrontendCMS\Services\PricingService;
use Exception;
use Modules\FrontendCMS\Http\Requests\PricingRequest;
use Modules\GST\Entities\GstTax;
use Modules\UserActivityLog\Traits\LogActivity;

class PricingController extends Controller
{
    protected $pricingService;
    public function __construct(PricingService $pricingService)
    {
        $this->middleware('maintenance_mode');
        $this->middleware('prohibited_demo_mode')->only('store');
        $this->pricingService = $pricingService;
    }
    public function index()
    {
        try {
            $gst_taxes = GstTax::where('is_active',1)->get();
            $PricingList = $this->pricingService->getAll();
            return view('frontendcms::pricing.index', compact('PricingList','gst_taxes'));
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.operation_failed'));
            return back();
        }
    }
    public function get_pricing()
    {
        try {
            $pricingList = $this->pricingService->getAll();
            $option = '';
            foreach ($pricingList as $key => $pricing) {
                $option .= '<option value="'.$pricing->id .'">'. $pricing->name .'</option>';
            }
            $output = '';
            $output .= '<div class="primary_input mb-25">
                            <label class="primary_input_label" for="">'. __('seller.subscription_type') .' <span class="text-danger">*</span></label>
                            <select class="primary_select pricing_id" name="pricing_id" id="pricing_id">
                                '.$option.'
                            </select>
                        </div>';
            return response()->json($output);
        } catch (\Exception $e) {

        }
    }

    public function create()
    {
        try {
            return response()->json([
                'editHtml' => (string)view('frontendcms::pricing.components.create')
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }

    public function store(PricingRequest $request)
    {
        try {

            $this->pricingService->save($request->except("_token"));
            LogActivity::successLog('Pricing Status Added');
            return true;
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }

    public function edit($id)
    {
        try {
            $gst_taxes = GstTax::where('is_active',1)->get();
            $pricing = $this->pricingService->editById($id);
            return response()->json([
                'editHtml' => (string)view('frontendcms::pricing.components.edit',compact('gst_taxes')),
                'data' => $pricing,
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }

    public function update(Request $request)
    {

        try {
            $data = $request->except("_token");
            $this->pricingService->update($data);
            LogActivity::successLog('Pricing updated.');
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
        return  $this->loadTableData();
    }

    public function destroy(Request $request)
    {
        try {
            $this->pricingService->deleteById($request->id);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json([
                'status'    =>  false,
                'message'   =>  $e->getMessage()
            ]);
        }
        return $this->loadTableData();
    }

    public function status(Request $request)
    {
        try {
            $data = [
                'status' => $request->status == 1 ? 0 : 1
            ];
            $this->pricingService->statusUpdate($data, $request->id);
            LogActivity::successLog('Pricing Status Update.');
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
        return $this->loadTableData();
    }

    private function loadTableData()
    {
        try {
            $PricingList = $this->pricingService->getAll();
            return response()->json([
                'TableData' =>  (string)view('frontendcms::pricing.components.list', compact('PricingList'))
            ]);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.operation_failed'));
            return back();
        }
    }
}
