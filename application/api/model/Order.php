<?php


namespace app\api\model;


class Order extends BaseModel
{
    protected $hidden = ['user_id','update_time','delete_time'];
    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    public function getSnapItemsAttr($value){
        if(empty($value)){
            return null;
        }else{
            return json_decode($value);
        }
    }

    public function getSnapAddressAttr($value){
        if(empty($value)){
            return null;
        }else{
            return json_decode($value);
        }
    }

    //用户订单列表 分页
    public static function getSummaryByUser($uid, $page=1, $size=15){
        //返回的是paginate对象
        $pagingDate = self::where('user_id','=',$uid)
                        ->order('create_time desc')
                        ->paginate($size, true, ['page'=>$page]);
        return $pagingDate;
    }

    //用户订单列表 分页
    public static function getSummaryByPage($page=1, $size=20){
        $pagingData = self::order('create_time desc')
            ->paginate($size, true, ['page' => $page]);
        return $pagingData ;
    }

    public function products(){
        return $this->belongsToMany('Product', 'order_product', 'product_id', 'order_id');
    }
}