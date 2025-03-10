<?php

namespace Modules\Shipping\Repositories;
use Illuminate\Support\Facades\Event;
use Modules\MultiVendor\Events\SellerShippingRateEvent;
use Modules\Shipping\Entities\ShippingMethod;

class ShippingRepository
{
    public function getAll()
    {
        $user_id = getParentSellerId();
        $methods = ShippingMethod::where('request_by_user',$user_id)->whereHas('carrier', function($q){
            $q->where('status', 1);
        })->with(['carrier'])->get();
        if(!isModuleActive('ShipRocket')){
            $methods = $methods->filter(function($item) {
                if($item->carrier->slug != 'Shiprocket'){
                    return $item->id;
                }
            });
            if(isModuleActive('MultiVendor') && $methods->count() < 1){
                Event::dispatch(new SellerShippingRateEvent($user_id));
                return $this->getAll();
            }
            return $methods;
        }else{
            if(isModuleActive('MultiVendor') && $methods->count() < 1){
                Event::dispatch(new SellerShippingRateEvent($user_id));
                return $this->getAll();
            }
            return $methods;
        }
    }

    public function getRequestedSellerOwnShippingMethod()
    {
        $user_id = getParentSellerId();
        return ShippingMethod::where('request_by_user', $user_id)->where('is_approved', 0)->latest()->get();
    }

    public function getActiveAll()
    {
        $user_id = getParentSellerId();
        $methods = ShippingMethod::where('request_by_user',$user_id)->where('is_active', 1)->whereHas('carrier', function($q){
            $q->where('status', 1);
        })->with(['carrier'])->get();
        if(!isModuleActive('ShipRocket')){
            $methods = $methods->filter(function($item) {
                if($item->carrier->slug != 'Shiprocket'){
                    return $item->id;
                }
            });
        }else {
            $methods = $methods->filter(function($item) {
                if($item->carrier->slug == 'Shiprocket' && $item->carrier->carrierConfig->carrier_status != 1){
                    return $item->id;
                }
            });
        }
        return $methods;
    }

    public function getActiveByCarrier($id)
    {
        $user_id = getParentSellerId();
        $methods = ShippingMethod::where('request_by_user',$user_id)->where('is_active', 1)->where('carrier_id',$id)->whereHas('carrier', function($q){
            $q->where('status', 1);
        })->with(['carrier'])->get();

        if(!isModuleActive('ShipRocket')){
            $methods = $methods->filter(function($item) {
                if($item->carrier->slug != 'Shiprocket'){
                    return $item->id;
                }
            });
        }
        return $methods;
    }

    public function store(array $data)
    {

        $shipping_method = new ShippingMethod();
        $user_id = getParentSellerId();
        $data['request_by_user'] = $user_id;
        $data['is_approved'] = 1;
        $data['is_active'] = 1;
        $data['minimum_shopping'] = empty($data['minimum_shopping'])?0:$data['minimum_shopping'];
        if(isModuleActive('FrontendMultiLang')){
            $shipping_method->fill($data)->save();
        }else{
            $shipping_method->setTranslation('method_name','en',$data['method_name']);
            $shipping_method->cost_based_on = $data['cost_based_on'];
            $shipping_method->shipment_time = $data['shipment_time'];
            $shipping_method->cost = $data['cost'];
            $shipping_method->minimum_shopping = $data['minimum_shopping'];
            $shipping_method->is_active = $data['is_active'];
            $shipping_method->request_by_user = $data['request_by_user'];
            $shipping_method->is_approved = $data['is_approved'];
            $shipping_method->save();
        }

        return true;
    }

    public function find($id)
    {
        return ShippingMethod::findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $user_id = getParentSellerId();
        $method = ShippingMethod::where('id',$id)->where('request_by_user', $user_id)->first();
        $data['minimum_shopping'] = empty($data['minimum_shopping'])?0:$data['minimum_shopping'];
        if($method){

            if(isModuleActive('FrontendMultiLang')){
                $method->update($data);
            }else{
                $method->setTranslation('method_name','en',$data['method_name']);
                $method->cost_based_on = $data['cost_based_on'];
                $method->shipment_time = $data['shipment_time'];
                $method->cost = $data['cost'];
                $method->minimum_shopping = $data['minimum_shopping'];

                $method->save();
            }

            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $user_id = getParentSellerId();
        $shipping = ShippingMethod::where('id',$id)->where('request_by_user', $user_id)->first();
        $totals = ShippingMethod::where('request_by_user', $user_id)->pluck('id')->toArray();
        if($shipping){
            if(count($shipping->methodUse) > 0){
                return 'not_possible';
            }elseif(count($totals) < 2){
                return 'not_possible_for_1';
            }else{
                $shipping->delete();
                return 'possible';
            }
        }else{
            return 'invalid';
        }
    }

    public function updateStatus(array $data)
    {
        $user_id = getParentSellerId();
        $shipping_method = $this->find($data['id']);
        if($data['status'] == 0){
            $other_active_method = ShippingMethod::where('id', '!=', $data['id'])->where('request_by_user', $user_id)->where('is_active', 1)->pluck('id')->toArray();
            if(count($other_active_method) > 0){
                $shipping_method->is_active = $data['status'];
                $shipping_method->save();
            }else{
                return 'last shipping rate disable not posible';
            }
        }else{
            $shipping_method->is_active = $data['status'];
            $shipping_method->save();
        }
        return 1;
    }

    public function updateApproveStatus($data)
    {
        $shipping_method = $this->find($data['id']);
        $shipping_method->is_approved = $data['status'];
        $shipping_method->save();
    }
    public function getActiveAllForAPI(){
        $methods = ShippingMethod::where('request_by_user',1)->where('id', '>', 1)->where('is_active', 1)->whereHas('carrier', function($q){
            $q->where('status', 1);
        })->with(['carrier'])->get();
        if(!isModuleActive('ShipRocket')){
            $methods = $methods->filter(function($item) {
                if($item->carrier->slug != 'Shiprocket'){
                    return $item->id;
                }
            });
        }
        return $methods;
    }
}
