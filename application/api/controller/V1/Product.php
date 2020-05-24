<?php


namespace app\api\controller\V1;

use \app\api\model\Product as ProductModel;
use app\api\validate\Count;
use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\ProductException;

class Product
{
    /* *
     * 获得最新的15条产品数据
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

    /* *获得分类下的所有商品
     * @id 分类的id
     * @return 分类下所有商品
     * */
    public function getALLInCategory($id){
        (new IDMustBePostiveInt())->goCheck();
        $products = ProductModel::getProductsByCategoryID($id);
        if($products->isEmpty()){
            throw new ProductException();
        }
        $products = $products->hidden(['summary']);
        return $products;
    }
}