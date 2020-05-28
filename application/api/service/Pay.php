<?php


namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\lib\enum\OrderStatusEum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;

class Pay
{
    private $orderID;
    private $orderNO;

    function __construct($orderID){
        if(!$orderID){
            throw new Exception('订单号不允许为空');
        }
        $this->orderID = $orderID;
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

    }
    //预订单请求
    private function makeWxPreOrder(){

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
        if($order->status!=OrderStatusEum::UNPAID){
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
