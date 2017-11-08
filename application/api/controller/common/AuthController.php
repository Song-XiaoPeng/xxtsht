<?php
namespace app\api\controller\common;
use think\Request;
use think\Controller;

class AuthController extends Controller{
    public $uid;

    public $token;

    public $company_id;

    // public function __construct() {
    //     header("Access-Control-Allow-Origin: *");
    //     header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, token");

    //     $data = ['name' => 'thinkphp', 'status' => '1'];
    //     return json($data);
    //     exit;
    // }

    protected $beforeActionList = [
        'first'
    ];

    protected function first(){
        dump(123);
    }
}