<?php


namespace app\api\controller\V1;

use \app\api\model\Product as ProductModel;
use app\api\validate\Count;
use app\lib\exception\ProductException;

class Product
{
    /*
     * 由客户端传递数目，默认为15
     * */
    public function getRecent($count=15){
        (new Count())->goCheck();
        $product = ProductModel::getMostRecent($count);
        if($product->isEmpty()){
            throw new ProductException();
        }
        $product = $product->hidden(['summary']);
        return $product;
    }
}