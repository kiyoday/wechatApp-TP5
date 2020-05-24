<?php


namespace app\api\controller\V1;

use app\api\model\Category as CategoryModel;
use app\lib\exception\CategoryException;

class Category
{
    public function getAllCategories(){
        $categories = CategoryModel::with('img')
            ->select();
        if($categories->isEmpty()){
            throw new CategoryException();
        }
        return $categories;
    }
}