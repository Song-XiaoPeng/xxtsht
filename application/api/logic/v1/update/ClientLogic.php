<?php
namespace app\api\logic\v1\update;
use think\Model;
use think\Db;

class ClientLogic extends Model {
    /**
     * 客户端获取升级版本
	 * @return code 200->成功
	 */
    public function getVersion(){
        $res = Db::name('client_version')->order('version desc')->group('system_type')->cache(true,60)->select();

        $data = [];

        foreach($res as $k=>$v){
            $data[$v['system_type']] = $v;
        }

        return $data;
    }
}