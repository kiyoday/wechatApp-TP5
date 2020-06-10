<?php


namespace app\api\controller;


use app\api\controller\BaseController;

class Index extends BaseController
{
    public function index(){
        return view('index');
    }
}