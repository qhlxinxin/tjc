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
            $tokenInfo=$this->manager->makeToken($res['data']);
            $res['tokenInfo']=$tokenInfo;
        }
        $this->ajaxReturn($res);
        //else{
        //    $this->ajaxReturn($res);
        //}

    }

    /**
     * 退出登录
     */
    public function logout(){
        $dat=getParam();
        $this->manager->deleteToken($dat['mid'],$dat['token']);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'删除token'
        ]);
    }


}