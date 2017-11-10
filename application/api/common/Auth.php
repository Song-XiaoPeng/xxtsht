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

    public function __construct() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, token");

        $this->token = Request::instance()->header('token');

        $user_res = Db::name('login_token')->where(['token'=>$this->token])->cache(true,10)->find();
        if(!$user_res){
            $this->returnJson(6001,'token无效');
        }

        if (strtotime(date('Y-m-d H:i:s')) > strtotime($user_res['expiration_date'])) {
            $this->returnJson(3010,'账号已过期',['expiration_date'=>$user_res['expiration_date']]);
        }

        $this->company_id = $user_res['company_id'];

        $this->uid = $user_res['uid'];

        $this->expiration_date = $user_res['expiration_date'];
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