<?php
namespace Home\Controller;
use Think\Controller;

class BaseController extends Controller {
    protected $model;
    protected $pageNum=20;

    public function __construct(){
        parent::__construct('tjc');
        $this->model=M();
        $rootPath='/tp/tjc/Home/';
        $jsPath=$rootPath."Public/js/";
        $cssPath=$rootPath."Public/css/";
        $imagesPath=$rootPath.'Public/image/';
        $imgPath=$rootPath.'Public/Uploads';
        $adminlte='/AdminLTE/';
        $this->assign('jsPath',$jsPath);
        $this->assign('cssPath',$cssPath);
        $this->assign('imgPath',$imgPath);
        $this->assign('images',$imagesPath);
        $this->assign('adminlte',$adminlte);
        $this->assign('version',1.00);
        $this->sqlMonth=array(
            "Jan"=>'01',
            "Feb"=>'02',
            "Mar"=>'03',
            "Apr"=>'04',
            "May"=>'05',
            "Jun"=>'06',
            "Jul"=>'07',
            "Aug"=>'08',
            "Sep"=>'09',
            "Oct"=>'10',
            "Nov"=>'11',
            "Dec"=>'12'
        );

    }

    /**
     * 登陆完成后，储存基本信息
     * 储存权限
     */
    protected function saveLoginStatus(){

    }

    /**
     * 获取用户的
     */
    protected function getUserAuthList(){

    }

    protected function checkAuthority($auth1='',$auth2=''){
        $user=session();
    }

    protected function pages($maxPage,$currentPage,$target='_self',$id=''){
        /*
        *	$maxPage 最大页数
        *	$currentPage   当前页数
        *	$id   显示的位子   HTML的id
        *
        */
        if($maxPage==0){
            return;
        }
        //dump($maxPage);
        //dump($currentPage);
        $param=I('get.');

        unset($param['page']);
        $url=U('',$param,'');
        $str='<nav><ul class="pagination">';
        $str.="<li><a href='{$url}/page/1' target='$target'><span aria-hidden=\"true\">«</span></a></li>";
        if($currentPage!=1){
            $str.="<li><a target='$target' href='{$url}/page/".($currentPage-1)."'>PRE</a></li>";
        }
        if($maxPage>10){
            if($currentPage<6){
                for($i=1;$i<=6;$i++){
                    if($currentPage==$i){
                        $str.="<li class='active' ><a href='{$url}/page/".($i)."'>".$i."</a></li>";
                    }else{
                        $str.="<li  class='hidden-xs'><a  href='{$url}/page/".($i)."' target='$target'>".$i."</a></li>";
                    }
                }
                for($i=7;$i<=10;$i++){
                    $str.="<li  class='hidden-xs'><a href='{$url}/page/".($i)."' target='$target'>".$i."</a></li>";
                }
            }else if(($currentPage>=6)&&($currentPage<($maxPage-4))){
                for($i=5;$i>0;$i--){
                    $str.="<li  class='hidden-xs'><a target='$target' href='{$url}/page/".($currentPage-$i)."'>".($currentPage-$i)."</a></li>";
                }
                $str.="<li class='active'><a target='$target' href='{$url}/page/".($currentPage)."'>".$currentPage."</a></li>";
                for($i=1;$i<5;$i++){
                    $str.="<li  class='hidden-xs'><a target='$target' href='{$url}/page/".($currentPage+$i)."'>".($currentPage+$i)."</a></li>";
                }
            }else if((($maxPage-4)<=$currentPage)&&($currentPage<=$maxPage)){
                for($i=9;$i>=0;$i--){
                    if($currentPage==($maxPage-$i)){
                        $str.="<li class='active'><a target='$target' href='{$url}/page/".($maxPage-$i)."'>".($maxPage-$i)."</a></li>";
                    }else{
                        $str.="<li  class='hidden-xs'><a target='$target' href='{$url}/page/".($maxPage-$i)."'>".($maxPage-$i)."</a></li>";
                    }
                }
            }
        }else{
            for($i=1;$i<=$maxPage;$i++){
                if($currentPage==$i){
                    $str.="<li class='active'><a target='$target' href='{$url}/page/".($i)."'>".$i."</a></li>";
                }else{
                    $str.="<li  class='hidden-xs'><a target='$target' href='{$url}/page/".($i)."'>".$i."</a></li>";
                }
            }
        }
        if($currentPage!=$maxPage){
            $str.="<li><a target='$target' href='{$url}/page/".($currentPage+1)."'>NEXT</a></li>";
        }
        $str.="<li><a target='$target' href='{$url}/page/".($maxPage)."'><span aria-hidden=\"true\">»</span></a></li></ul></nav>";
        // document.getElementById(id).innerHtml=str;
        return $str;
    }





    /**
     * 获取当前页码  页码默认从1开始
     * @return int|mixed
     *


    public function getCurrentPage(){
        $page=I('param.page');
        if(!$page){
            $page=1;
        }
        return $page;
    }

    public function getCurrentPageSize(){
        $pageSize=I('param.page_num');
        if($pageSize){
            $this->pageNum=$pageSize;
        }

    }

     */

    protected function reJson($res){
        header('Content-Type:application/json; charset=utf-8');
        exit($res);
    }
}