<?php


namespace app\api\service;


use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\UserAddress;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use \app\api\model\Order as OrderModel;
use think\Db;
use think\Exception;

class Order
{
    //订单的商品列表：客户端传递过来的products参数 [二维数组]
    protected $oProducts;

    //真实的商品信息（包含库存量）
    protected $products;
    //对比订单和数据库 并生成订单
    public function place($uid, $oProducts){
        //对比$oProducts和$products
        //$products 数据库查询出来
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $this->uid = $uid;
        $status = $this->getOrderStatus();
        if(!$status['pass']){
            $status['order_id'] = -1;
            return $status;
        }
        //开始创建订单
        $orderSnap = $this->snapOrder($status);
        $order = $this->createOrder($orderSnap);
        $order['pass'] = true;
        return $order;
    }

    //创建订单
    private function createOrder($snap){
        Db::startTrans();
        try{
            $orderNo = $this->makeOrderNo();
            $order = new OrderModel();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatus']);
            $order->save();

            $orderID = $order->id;
            $create_time = $order->create_time;
            foreach ($this->oProducts as &$p) {
                $p['order_id'] = $orderID;
            }
            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->oProducts);
            Db::commit();
            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $create_time,
            ];
        }
        catch(Exception $ex){
            Db::rollback();
            throw $ex;
        }
    }

    //生成订单编号
    public static function makeOrderNo(){
        $yCode = array('A','B','C','D','E','F','G','H','I','J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) .
            date('d') . substr(time(), - 5) . substr(microtime(),2,5) .
            sprintf('%02d',rand(0, 99));
        return $orderSn;
    }

    //生成订单快照
    private function snapOrder($status){
        $snap = [
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatus' => [],
            'snapAddress' => '',
            'snapName' => '',
            'snapImg' => '',
        ];
        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];

        if(count($this->products) > 1){
            $snap['snapName'] .= '等';
        }
        return $snap;
    }

    private function getUserAddress(){
        $userAddress = UserAddress::where('user_id','=',$this->uid)
            ->find();
        if(!$userAddress){
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => 60001,
            ]);
        }
        return $userAddress->toArray();
    }

    //对外方法 预订单库存检查
    public function checkOrderStack($orderID){
        $oProducts = OrderProduct::where('order_id','=',$orderID)
            ->select();
        $this->oProducts = $oProducts;
        $this->products = $this->getProductStatus($oProducts);
        $status = $this->getOrderStatus();
        return $status;
    }
    //获取订单状态
    private function getOrderStatus(){
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatusArray' => [],//保存订单中所有商品的详细信息
        ];

        foreach ($this->oProducts as $oProduct){
            $pStatus = $this->getProductStatus(
                $oProduct['product_id'],$oProduct['count'],$this->products
            );
            if(!$pStatus['haveStock']){
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['count'];
            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;
    }

    /** 从数据库获取单个商品的状态
     * @param $oPID  订单请求商品的id
     * @param $oCount  订单请求的数量
     * @param $products 数据库商品详细信息
     * @return array pStatus
     */
    private function getProductStatus($oPID, $oCount, $products){
        $pIndex = -1;
        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'count' => 0,
            'name' => '',
            'totalPrice' => 0,
        ];
        for($i=0; $i<count($products); $i++){
            if($oPID == $products[$i]['id']){
                $pIndex = $i;
            }
        }
        //客户端递的product_id有可能根本不存在
        if($pIndex == -1){
            throw new OrderException([
                'msg' => 'id为'.$oPID.'的商品不存在，创建订单失败',
            ]);
        }else{
            $product = $products[$pIndex];
            $pStatus['id'] = $product['id'];
            $pStatus['name'] = $product['name'];
            $pStatus['count'] = $oCount;
            $pStatus['totalPrice'] = $product['price'] * $oCount;
            $pStatus['haveStock'] = ($product['stock']-$oCount>=0) ? true : false;
            return $pStatus;
        }
    }

    //根据订单信息查找真实的商品信息
    private function getProductsByOrder($oProducts){
        $oPIDs = [];
        foreach($oProducts as $item){
            array_push($oPIDs, $item['product_id']);
        }
        $products = Product::all($oPIDs)
            ->toArray();
        return $products;
    }
}