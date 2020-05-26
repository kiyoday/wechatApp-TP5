<?php


namespace app\api\service;


use app\api\model\Product;
use app\lib\exception\OrderException;

class Order
{
    //订单的商品列表：客户端传递过来的products参数 [二维数组]
    protected $oProducts;

    //真实的商品信息（包含库存量）
    protected $products;
    //对比订单和数据库
    public function place($uid, $oProducts){
        //对比$oProducts和$products
        //$products 数据库查询出来
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $this->uid = $uid;
    }

    //获取订单状态
    private function getOrderStatus(){
        $status = [
            'pass' => true,
            'orderPrice' => 0,
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
        foreach($oProducts as $item){
            array_push($oPIDs, $item['product_id']);
        }
        $products = Product::all($oPIDs)
            ->visible(['id','price','stock','name','main_ing_url'])
            ->toArray();
        return $products;
    }
}