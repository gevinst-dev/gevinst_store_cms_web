<?php

namespace Modules\Marketing\Repositories;
use App\Models\User;
use Carbon\Carbon;
use Modules\Marketing\Entities\BulkSMS;
use App\Traits\SendSMS;
use Modules\GeneralSetting\Entities\SmsTemplate;

class BulkSMSRepository {
    use SendSMS;
    public function getAll(){
        return BulkSMS::latest();
    }
    public function store($data){
        $user_ids = "";
        if($data['send_to'] == 1){
            $user_ids = json_encode($data['all_user']);
        }
        if($data['send_to'] == 2){
            $user_ids = json_encode($data['role_user']);
            $single_role_id = $data['role'];
        }
        if($data['send_to'] == 3){
            $user_ids = json_encode(User::whereIn('role_id',$data['role_list'])->pluck('id'));
            $multiple_role_id = json_encode($data['role_list']);
        }
        $bulk_sms = new BulkSMS();
        $data['publish_date'] = Carbon::parse($data['publish_date'])->format('Y-m-d');
        $data['send_user_ids'] = $user_ids;
        $data['single_role_id'] = isset($single_role_id)?$single_role_id:null;
        $data['multiple_role_id'] = isset($multiple_role_id)?$multiple_role_id:null;
        $data['send_type'] = $data['send_to'];
        if (isModuleActive('FrontendMultiLang')) {
            $bulk_sms->fill($data)->save();
        }else{
            $bulk_sms->setTranslation('title','en',$data['title']);
            $bulk_sms->setTranslation('message','en',$data['message']);
            $bulk_sms->publish_date = $data['publish_date'];
            $bulk_sms->send_user_ids = $data['send_user_ids'];
            $bulk_sms->single_role_id = $data['single_role_id'];
            $bulk_sms->multiple_role_id = $data['multiple_role_id'];
            $bulk_sms->send_type = $data['send_to'];
            $bulk_sms->save();

        }
        return $bulk_sms;
    }
    public function update($data){
        $user_ids = "";
        if($data['send_to'] == 1){
            $user_ids = json_encode($data['all_user']);
        }
        if($data['send_to'] == 2){
            $user_ids = json_encode($data['role_user']);
            $single_role_id = $data['role'];
        }
        if($data['send_to'] == 3){
            $user_ids = json_encode(User::whereIn('role_id',$data['role_list'])->pluck('id'));
            $multiple_role_id = json_encode($data['role_list']);
        }
        $data['publish_date'] = Carbon::parse($data['publish_date'])->format('Y-m-d');
        $data['send_user_ids'] = $user_ids;
        $data['single_role_id'] = isset($single_role_id)?$single_role_id:null;
        $data['multiple_role_id'] = isset($multiple_role_id)?$multiple_role_id:null;
        $data['send_type'] = $data['send_to'];
        if (isModuleActive('FrontendMultiLang')) {
            $bulk_sms = BulkSMS::where('id',$data['id'])->first();
            return $bulk_sms->fill($data)->save();


        }else{
            $bulk_sms = BulkSMS::where('id',$data['id'])->first();
            $bulk_sms->setTranslation('title','en',$data['title']);
            $bulk_sms->setTranslation('message','en',$data['message']);
            $bulk_sms->publish_date = $data['publish_date'];
            $bulk_sms->send_user_ids = $data['send_user_ids'];
            $bulk_sms->single_role_id = $data['single_role_id'];
            $bulk_sms->multiple_role_id = $data['multiple_role_id'];
            $bulk_sms->send_type = $data['send_to'];
            return $bulk_sms->save();
        }

    }
    public function testSMS($data){
        $message = BulkSMS::findOrFail($data['id']);
        $this->sendSMS($data['phone'],$message->message,'User');
        return true;
    }
    public function getAllUser(){
        return User::where('username','!=',null)->get();
    }
    public function getUserByRole($id){
        return User::where('username','!=',null)->where('role_id',$id)->get();
    }
    public function deleteById($id){
        return BulkSMS::findOrFail($id)->delete();
    }
    public function editById($id){
        return BulkSMS::findOrFail($id);
    }
    public function getActiveTemplate(){
        return SmsTemplate::where('type_id', 17)->where('is_active', 1)->first();
    }
}
