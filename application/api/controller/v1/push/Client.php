<?php
namespace app\api\controller\v1\push;
use app\api\common\Auth;

class Client extends Auth{
	public function test(){
		return \think\Loader::model('ClientLogic','logic\v1\push')->test();
	}
}