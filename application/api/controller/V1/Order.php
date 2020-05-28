<?php


namespace app\api\controller\V1;

use app\api\controller\BaseController;
use app\api\service\Token as TokenService;
use app\api\validate\OrderPlace;
use app\api\service\Order as OrderService;
use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\TokenException;
use think\Controller;

class Order extends BaseController
{
    //用户在选择商品后，向api提交包含它所选择商品的相关信息
    //api在接受到信息后，需要检查订单相关商品的库存量
    //1.有库存，把订单数据存入数据库中= 下单成功了，返回客户端消息，告诉客户端可以支付了
    //调用我们的支付接口，进行支付
    //2.还需要再次进行库存量检测
    //服务器这边就可以调用微信的支付接口进行支付
    //微信会返回给我们一个支付的结果(异步)
    //3.成功:也需要进行库存量的检查,进行库存量的扣除，失败:返回一个支付失败的结果


    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
    ];

    public function placeOrder(){
        (new OrderPlace())->goCheck();
        $products = input('post.products/a');//获取数组参数
        $uid = TokenService::getCurrentUid();

        $order = new OrderService();
        $status = $order->place($uid,$products);
        return $status;
    }
}