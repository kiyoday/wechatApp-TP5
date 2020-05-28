<?php


namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use think\Loader;
use think\Log;

//PSR-4,PSR-0
//extend/WxPay/WxPay.Api.php
Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');

class Pay
{
    private $orderID;
    private $orderNO;
    private $config;

    function __construct($orderID){
        if(!$orderID){
            throw new Exception('订单号不允许为空');
        }
        $this->orderID = $orderID;
        $this->config = new \WxPayConfig();

    }

    public function pay(){
        //订单号不存在
        //订单号和用户不匹配
        //订单被支付过的
        //进行库存检测
        $this->checkOrderValid();
        $orderService = new Order();
        $status = $orderService->checkOrderStack($this->orderID);
        if(!$status['pass']){//库存量检查
            return $status;
        }
        return $this->makeWxPreOrder($status['orderPrice']);

    }
    //预订单请求
    private function makeWxPreOrder($totalPrice){
        //openid
        $openid = Token::getCurrentTokenVar('openid');
        if(!$openid){
            throw new TokenException();
        }
        $wxOrderData = new \WxPayUnifiedOrder();//无命名空间的类需要加 \ 引入
        $wxOrderData->SetOut_trade_no($this->orderNO);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalPrice*100);//以分作为单位
        $wxOrderData->SetBody('零食商贩');
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(config('secure.pay_back_url'));//回调接口

        return $this->getPaySignature($wxOrderData);
    }

    private function getPaySignature($wxOrderData){
        $wxOrder = \wxPayApi::unifiedOrder($this->config,$wxOrderData);
        if($wxOrder['return_code']!='SUCCESS'
            ||$wxOrder['result_code']!='SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
        }
        //prepay_id
        $this->recordPreOrder($wxOrder);
        $signature = $this->sign($wxOrder);
        return $signature;
    }

    //签名 timeStamp nonceStr package signType  paySign
    private function sign($wxOrder){
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());
        //生成随机字符串
        $rand = md5(time().mt_rand(0,1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        //生成签名
        $sign = $jsApiPayData->MakeSign($this->config);
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;

        unset($rawValues['appId']);//返回给客户端没用
        return $rawValues;

    }

    //保存预订单信息：prepayID
    private function recordPreOrder($wxOrder){
        OrderModel::where('id','=',$this->orderID)
            ->update(['prepay_id'=>$wxOrder['prepay_id']]);
    }

    private function checkOrderValid(){
        $order = OrderModel::where('id','=',$this->orderID)
            ->find();
        if(!$order){
            throw new OrderException();
        }
        if(!Token::isValidOperate($order->user_id)){
            throw new TokenException([
                'msg' => '订单与用户不匹配',
                'errorCode' => 10003
            ]);
        }
        if($order->status!=OrderStatusEnum::UNPAID){
            throw new OrderException([
                'msg'=>'订单已被支付',
                'errorCode' => 80003,
                'code' =>400,
            ]);
        }
        $this->orderNO = $order->order_no;
        return true;
    }
}
