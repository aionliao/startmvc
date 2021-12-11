<?php
namespace App\Home\Controller;
//use Startmvc\Lib\Controller;
use App\Common\BaseController;


class IndexController extends BaseController{
	
    public function indexAction()
    {
	    $data['title'] = '超轻量php框架-欢迎使用Startmvc';
	    $data['content'] = 'Hello StartMVC!';
	    $this->assign($data);

        $this->view();
    }
    public function __call($name,$arg)
    {
    	$this->content("走丢了。。。。。。。。");
    }
}
