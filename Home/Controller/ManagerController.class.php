<?php
namespace Home\Controller;
use Home\Model\ManagerModel;
class ManagerController extends BaseController {
    /**
     * 后台首页
     */
    private  $manager;

    public function __construct()
    {
        parent::__construct();
        $this->manager=new ManagerModel();
    }

    /**
     * 获取带分页的学员信息
     * 带条件
     * $con 数组 可包含以下参数
     * page   页数 默认从1开始  page 推荐使用get方法传递
     * pageNum  默认是20  可以用 page_num指定
     */
    public function listManagers(){
        $dat=getParam();
        $res=$this->manager->listManagers($dat);
        $this->ajaxReturn($res);
    }

    /**
     * 添加 编辑普通管理员账号
     * @param $dat
     * $mid   管理者id   修改其他参数时必传
     * $manager_name    显示姓名    添加必填
     * $username        账号        添加必填
     * $password        密码        添加必填
     * $status          状态
     *
     * 返回素质三连
     * 完成
     */
    public function saveManager(){
        $dat=getParam();
        $res=$this->manager->saveManager($dat);
        $this->ajaxReturn($res);
    }

    public function getManagerAuth(){
        $dat=getParam();
        $res=$this->manager->getManagerAuth($dat['mid'],$dat['scid']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'获取指定管理者指定校区的管理权限'
        ]);
    }

    /**
     * 验证管理者TOKEN的有效性
     * mid      管理者mid
     * token    token 字符串码
     */
    public function checkToken(){
        $dat=getParam();
        $res=$this->manager->checkToken($dat['mid'],$dat['token']);
        $this->ajaxReturn($res);

    }

    /**
     * 获取指定管理组（指定校区）下的管理者列表
     *
     * @param $rgid
     * @param string $scid   scid可为空   则取该取的是该权限组下的管理者不论校区
     * @return mixed
     */
    public function getManagerByRoleGroup(){
        $dat=getParam();
        if(isset($dat['scid'])){
            $str="获取{$dat[rgid]}组，{$dat[scid]}校区的管理员列表";
        }else{
            $str="获取{$dat[rgid]}组管理员列表";
        }
        $res=$this->manager->getManagerByRoleGroup($dat['rgid'],$dat['scid']);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>$str,
            'data'=>$res
        ]);
    }

    /**
     * 添加管理的校区
     * 什么校区 配置什么样的管理角色
     * 例如A可以成为X校区的最高管理者
     * 也可以成为Y校区的普通管理者
     * $mid 管理者ID
     * $scid 学校ID
     * $rgid  角色组ID
     * $action add  delete  添加管理校区 或者 删除
     * 完成
     */
    public function saveManageSchool(){
        $dat=getParam();
        $res=$this->manager->saveManageSchool($dat['mid'],$dat['scid'],$dat['rgid'],$dat['action']);
        $this->ajaxReturn($res);

    }

    /**
     * 获取指定机构的下属机构，
     * 通过这个层级关系，这个机构可以下载下属机构的学员信息
     * $scid
     */
    public function getTree()
    {
        $dat=getParam();
        $data = M('school_relative')->join('school s on school_relative.scid=s.scid')->select();
        $tree = $this->manager->getTree($data,$dat['scid'] );
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$tree,
            'info'=>'获取下属学校管理关系'
        ]);
    }

    /**
     * 获取校区列表，带分页
     * $page  默认为1
     * $level  省级  市级  区级
     * $page_num  每页显示多少个
     * $status  是否有效
     * $scid    school_id  如果没有则搜索全部，有则搜索指定，
     * @param $con
     *
     * return array 素质三联  data为数组
     * 完成
     */
    public function getSchoolList(){
        $dat=getParam();
        $res=$this->manager->getSchoolList($dat);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'获取学校列表'
        ]);
    }

    /**
     * 获取指定管理者所管理的校区
     * @param $mid
     */
    public function getManageSchool(){
        $dat=getParam();
        $res=$this->manager->getSchoolList($dat['mid']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'获取指定管理者管理的校区'
        ]);
    }

    /**
     * @param $dat
     * $scid        学校id 修改其他参数必传
     * $level       招生级别    可选值为   省级 市级   区级   终端   新添加学校时必传
     * $school_name  学校名称    新添加学校时必传
     * $status      状态，是否有效
     *
     * 返回素质三连
     * 完成
     */
    public function saveSchool(){
        $dat=getParam();
        $res=$this->manager->saveSchool($dat);
        $this->ajaxReturn($res);
    }

}