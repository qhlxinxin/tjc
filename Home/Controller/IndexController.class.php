<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\ManagerModel;
class IndexController extends BaseController {
    /**
     * 后台首页
     */
    private  $manager;

    public function __construct()
    {
        parent::__construct();
        $this->manager=new ManagerModel();
    }

    public function index(){
        $this->display('index');
    }

    public function checkUserAndPassword(){
        $dat=getParam();
        $res=$this->manager->checkUserAndPassword($dat['username'],$dat['password']);
        if($res['success']==true){
            $tokenInfo=$this->manager->makeToken($res['data']['mid']);
            $res['tokenInfo']=$tokenInfo;
        }
        $this->ajaxReturn($res);
        //else{
        //    $this->ajaxReturn($res);
        //}

    }


}