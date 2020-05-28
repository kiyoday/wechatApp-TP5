<?php


namespace app\api\controller\V1;


use app\api\controller\BaseController;
use app\api\service\WxNotify;
use app\api\validate\IDMustBePostiveInt;
use app\api\service\Pay as PayService;
use think\Loader;

Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');

class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder'],
    ];

    //请求预订单信息
    public function getPreOrder($id=''){
        (new IDMustBePostiveInt())->goCheck();
        $pay = new PayService($id);
        return $pay->pay();
    }

    //支付回调：支付后 微信会调用这个接口，发送支付信息
    public function receiveNotify(){
        //通知频率为15/15/30/180/1800/1800/1800/1800/3600，单位:秒
        //1.检测库存量，超卖概率小
        //2.更新这个订单的status状态
        //3.减库存
        //如果成功处理 返回给微信 成功处理的信息 否则：返回没有成功处理

        //特点 post;  xml格式; 不会携带参数; 处理xml可以使用SDK
        $notify = new WxNotify();
        $notify->handle(new \WxPayConfig());
    }

    //转发消息 并断点调试
    public function transNotify(){
        $xmlData = file_get_contents('php://input');
        $result = curl_post_raw('http:/s.con/api/v1/pay/re_notify
            ?XDEBUG_SESSION_START=10000',xmlData);
    }
}