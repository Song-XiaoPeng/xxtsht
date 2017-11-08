<?php
namespace app\api\controller\v1\User;
use \think\Request;

//授权登录相关操作
class Auth {
    /**
     * 账号登录
     * 
     * 
     */
    public function login () {
        $data = input('put.');
        
        return \think\Loader::model('AuthModel','logic\v1\user')->login($data);
    }
    
}