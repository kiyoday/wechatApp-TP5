<?php


namespace app\api\service;

use app\api\model\Product;
use app\lib\enum\OrderStatusEnum;
use think\Db;
use think\Exception;
use think\Loader;
use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use think\Log;

//引入api即可，内部会加载notify
Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');

class WxNotify extends \WxPayNotify
{
    public function NotifyProcess($data, $config, &$msg){
        if ($data['result_code']=='SUCCESS'){
            Db::startTrans();
            $orderNo = $data['out_trade_no'];
            try{
                $order = OrderModel::where('order_id','=',$orderNo)
                    ->lock(true)
                    ->find();
                if($order->status == OrderStatusEnum::UNPAID){
                    $service = new OrderService();
                    $stockStatus = $service->checkOrderStack($order->id);
                    if($stockStatus['pass']){//有库存
                        $this->updateOrderStatus($order->id,true);
                        $this->reduceStock($stockStatus);
                    }else{//无库存 更新订单状态
                        $this->updateOrderStatus($order->id,false);
                    }
                }
                Db::commit();
                return true;
            }catch(Exception $ex){
                Log::error($ex);
                return false;
            }
        }else{//知晓微信处理失败
            return true;
        }
    }

    private function reduceStock($stockStatus){
        foreach($stockStatus['pStatusArray'] as $singlePStatus){
            //使用TP5提供的模型方法
            Product::where('id','=',$singlePStatus)
                ->setDec('stock',$singlePStatus['count']);
        }
    }

    private function updateOrderStatus($orderID,$success){
        $status = $success?OrderStatusEnum::PAID
            : OrderStatusEnum::PAID_BUT_OUT_OF;
        OrderModel::where('id','=',$orderID)
            ->update(['status'=>$status]);
    }
}