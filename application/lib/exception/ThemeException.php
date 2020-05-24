<?php


namespace app\lib\exception;


class ThemeException extends BaseException
{
    public $code=400;
    public $msg='请求的主题不存在，求检查主题ID';
    public $errorCode=30000;
}