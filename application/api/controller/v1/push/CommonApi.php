<?php
/**
 * Created by PhpStorm.
 * User: SuperMan
 * Date: 2018/2/2
 * Time: 18:13
 */
namespace app\api\controller\v1\push;

use app\api\common\Auth;
use think\Loader;

class CommonApi extends Auth
{
    /**
     * 个人信息设置
     * $params sex 性别
     * $params autograph 个性签名
     * $params user_name 姓名
     *
     */
    public function backgroundProcess()
    {
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        return Loader::model('CommonApiLogic', 'logic\v1\push')->profileSetting($data);
    }
}