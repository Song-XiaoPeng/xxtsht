<?php
namespace app\api\common;
use think\Request;
use think\Controller;
use think\Db;

class Auth extends Controller{
    protected $uid;

    protected $token;

    protected $company_id;

    protected $expiration_date;

    protected $user_type;

    public function __construct() {
        $header_token = Request::instance()->header('token');
        if(empty($header_token)){
            $this->token = input('get.token');
        }else{
            $this->token = $header_token;
        }

        if(empty($this->token)){
            $this->returnJson(6003,'token参数缺少');
        }

        $client = Request::instance()->header('client');
        if(empty($client)){
            $this->returnJson(6004,'client参数缺少');
        }

        $uid = Request::instance()->header('uid');
        if(empty($uid)){
            $this->returnJson(6005,'uid参数缺少');
        }

        if($client == 'ios' || $client == 'android'){
            $client = 'mobile';
        }

        //更新token时效判断正确性
        $cache_key = $client.$uid;
        if(empty(cache($cache_key))){
            $this->returnJson(6001,'token无效');
        }
        
        if(cache($cache_key)['token'] != $this->token){
            $this->returnJson(6001,'token无效');
        }

        cache($cache_key, cache($cache_key), 259200);

        $company_id = cache($cache_key)['company_id'];

        $user_type = cache($cache_key)['user_type'];
        
        $company_info = Db::name('company')->where(['company_id'=>$company_id])->cache(true,360)->find();

        if(strtotime(date('Y-m-d H:i:s')) > strtotime($company_info['expiration_date'])){
            $this->returnJson(6002,'账号已于'.$company_info['expiration_date'].'过期');
        }

        $this->company_id = $company_id;

        $this->uid = $uid;

        $this->expiration_date = $company_info['expiration_date'];

        $this->user_type = $user_type;
    }

    private function returnJson($code,$message){
        header("Content-Type: application/json");
        echo json_encode([
            'meta' => [
                'code' => $code,
                'message' => $message
            ],
            'body' => []
        ]);
        exit;
    }
}