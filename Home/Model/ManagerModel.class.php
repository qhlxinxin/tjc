<?php
/**
 * Created by PhpStorm.
 * User: qhlxi
 * Date: 2019/1/15
 * Time: 23:11
 */

namespace Home\Model;
use Think\Model;
header('Content-type:text/html;charset=utf8');

class ManagerModel extends Model
{
    protected $tableName = 'school_manager';
    protected $tablePrefix = '';
    private $schoolRelative;
    //校区管理及角色表
    private $managerSchoolTabel='manager_role_school';

    //学校表
    private $schoolTable='school';

    public function __construct()
    {
        parent::__construct();
        //$this->managerSchoolTabel=M('manager_role_school');
        $this->schoolRelative=M('school_relative');
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
    public function saveManager($dat){

        if($dat['mid']){
            $needs=['manager_name','status','username','password'];
            $save=checkParam($dat,$needs);
            return
                [
                    'success'=>true,
                    'data'=>$this->where(['mid'=>$dat['mid']])->save($save)
                ];
        }
        else{
            $existManger=$this->where(['username'=>$dat['username']])->find();
            if($existManger){
                return ['success'=>false,'info'=>'已经存在该用户'];
            }
            else{
                return [
                    'success'=>true,
                    'data'=>$this->add([
                        'manager_name'=>$dat['manager_name'],
                        'username'=>$dat['username'],
                        'password'=>$dat['password'],
                    ])
                ];
            }
        }
    }


    /**
     * 登陆
     * @param $username
     * @param $password
     * @return array
     */
    public function checkUserAndPassword($username,$password){
        $manager=$this->where([
            'username'=>$username,
            'password'=>$password
        ])->find();
        if($manager==null){
            return [
                'success'=>false,
                'info'=>'没有找到该用户或者密码错误',
            ];
        }
        else{
            if($manager['status']==0){
                return [
                    'success'=>false,
                    'info'=>'管理账号已被停止使用',
                ];
            }
            else{
                //session('user_login',$manager['mid']);
                return [
                    'success'=>true,
                    'data'=>$manager['mid'],
                    'info'=>'登陆成功'
                ];
            }
        }
    }



    public function makeToken($mid=0){
        $str='whatsupbuddy';
        $time=time();
        $key=$str.$time.$mid.$_COOKIE['PHPSESSID'];
        $token=md5($key);
        $expireTime=time()+7200;
        

        $ti=M('token_info');
        $con=[
            'mid'=>$mid,
            'token'=>$token
        ];
        $tokenInfo=$ti->where($con)->find();
        if($tokenInfo==null){
            $ti->add([
                'mid'=>$mid,
                'token'=>$token,
                'expire_time'=>$expireTime
            ]);
        }
        else{
            $ti->where($con)->save(['expire_time'=>$expireTime]);
        }
        $tokenInfo=$ti->where($con)->find();
        return [
            'success'=>true,
            'info'=>'返回token信息',
            'data'=>$tokenInfo
        ];
    }

    /**
     * 验证管理者TOKEN的有效性
     * mid      管理者mid
     * token    token 字符串码
     */
    public function checkToken($mid,$token){
        $ti=M('token_info');
        $myExpireTime=time();
        $con=[
            'mid'=>$mid,
            'token'=>$token,
            'expire_time'=>['gt',$myExpireTime]
        ];
        $exist=$ti->where($con)->find();
        if($exist!=null){
            //token 没过期
            $ti->where($con)->save(['expire_time'=>time()+7200]);
            $tokenInfo=$ti->where($con)->find();
            return [
                'success'=>true,
                'info'=>'token验证通过',
                'data'=>$tokenInfo
            ];
        }else{
            return [
                'success'=>false,
                'info'=>'token验证失败'
            ];
        }

    }

    /**
     *
     */
    public function deleteToken($mid,$token){
        $ti=M('token_info');
        $con=[
            'mid'=>$mid,
            'token'=>$token
        ];
        $ti->where($con)->delete();

    }

    /**
     * 获取指定管理组（指定校区）下的管理者列表
     *
     * @param $rgid
     * @param string $scid
     * @return mixed
     */
    public function getManagerByRoleGroup($rgid,$scid=''){
        $con=[
            'm.rgid'=>$rgid
        ];
        if($scid){
            $con['scid']=$scid;
        }
        return M('manager_role_school as m')
            ->join("left join school_manager as sm on m.mid=sm.mid")
            ->join('left join school as s on s.scid=m.scid')
            ->field("m.*,sm.manager_name,mrc.rgid,sm.status,sm.username,s.school_name,s.level")
            ->where($con)
            ->select();
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
    public function saveManageSchool($mid,$scid,$rgid,$action='add'){
        $managerRoleSchool=M('manager_role_school');
        $dat=[
            'mid'=>$mid,
            'scid'=>$scid,
            'rgid'=>$rgid
        ];
        $con=[
            'mid'=>$mid,
            'scid'=>$scid,
        ];
        $exist=$managerRoleSchool->where($con)->find();
        //dump($exist);
        if($action=='add'){
            if($exist==null){
                $r=$managerRoleSchool->add($dat);
            }
        }
        elseif($action=='delete'){
            if($exist){
                $r=$managerRoleSchool->where($dat)->delete();
            }
        }
        $res=[
            'success'=>true,
            'data'=>$r
        ];
        return $res;
    }

    /**
     * 根据条件获取manager
     * $con 数组 可包含以下参数
     * page   页数 默认从1开始  page 推荐使用get方法传递
     * page_num  默认是20  可以用 page_num指定
     * 其他慢慢加
     */
    public function listManagers($con){
        $total=$this->where($con)->count();
        $page=getCurrentPage($con);
        $pageNum=getPageSize($con);
        unset($con['page'],$con['page_num']);
        //总页数
        $totalPages=$total/$pageNum;
        $content=$this
            ->join('left join manager_role_school as mrc on mrc.mid=school_manager.mid')
            ->join('left join school as s on mrc.scid=s.scid')
            ->field("school_manager.*,mrc.scid,s.school_name,s.level,s.status as school_status")
            ->where($con)
            ->limit($page,$pageNum)->select();
        $result=[
            'success'=>true,
            'data'=>[
                'con'=>$con,
                'page'=>$page,
                'page_num'=>$pageNum,
                'total_page'=>$totalPages,
                'total'=>$total,
                'content'=>$content
            ],
            'info'=>'查询成功'
        ];
        return $result;
    }

    /**
     * 获取指定管理者指定校区 的管理权限
     */
    public function getManagerAuth($mid,$scid){
        $con=[
            'mid'=>$mid,
            'scid'=>$scid
        ];
        $data=M('manager_role_school as mrs')
            ->join('role_group as rg on mrs.rgid=rg.rgid')
            ->join('role_group_auth as rga on mrs.rgid=rga.rgid')
            ->join('auth_dict as ad on rga.adid=ad.adid')
            ->where($con)
            ->select();
        $res['data']=$data;
        foreach ($data as $va){
            $res['auths'][]=$va['auth_code'];
        }
        return $res;
    }

    /**
     * 暂时弃用
     *
     * 如果 scid为空 则从省级代理开始获取全部的管理层级关系
     * @param string $scid  学校id
     *
     */
    public function getSchoolRelative(array $scid=[], &$tree=[],$level=0){
        $level=$level+1;
        if(count($scid)==0){
            $con['has_parent']=0;
            //$con['parent_id']=null;
        }
        else{
            $con['parent_id']=['IN',$scid];
        }
        $dat=$this->schoolRelative->join('school s on school_relative.scid=s.scid')
            ->where($con)
            ->select();
        $subIds=[];
        foreach($dat as $va){
            $subIds[]=$va['scid'];
            $tree[$va['parent_id']]['level']=$level;
            $tree[$va['parent_id']]['parent_id']=$va['parent_id'];
            $tree[$va['parent_id']]['school_name']=$va['school_name'];
            $tree[$va['parent_id']]['scid']=$va['scid'];
            $tree[$va['parent_id']]['nodes'][$va['scid']]=$va;
        }
        //dump($subIds);
        if(count($subIds)){
            $this->getSchoolRelative($subIds,$tree,$level);
        }
        //ksort($tree);
        //dump($tree);
        //$new['root']=array_shift();
        //foreach($tree as $k => $v){

            //if($k!=0){
            //
            //}
            //else{
            //    $new['0']=$va;
            //}
        //}

        return $tree;
    }

    /**
     * 保存/修改 机构关系
     * @param $dat
     * rid   学校关系id，修改其他参数时必填，通常一个scid应该只会对应一个rid
     * scid     机构id
     * parent_id    上级管理机构的scid
     * has_parent   是否有上级   0为没有上级   1为有上级  传parent_id 的时候  has_parent 应该为1
     */
    public function saveSchoolRelative($dat){
        $schoolRelative=M('school_relative');

        if(isset($dat['rid'])){
            $con=[
                'rid'=>$dat['rid']
            ];
            unset($dat['rid']);
            $schoolRelative->where($con)->save($dat);
        }else{
            //新添加机构关系
            $con=[
                'scid'=>$dat['scid']
            ];
            $exist=$schoolRelative->where($con)->find();
            if($exist!=null){
                if($dat['parent_id']){
                    $hasParent=1;
                }else{
                    $hasParent=0;
                }
                $schoolRelative->where($con)->save(['parent_id'=>$dat['parent_id'],'has_parent'=>$hasParent]);
            }else{
                $schoolRelative->add($dat);
            }
        }
        return [
            'success'=>true,
            'info'=>'保存成功'
        ];
    }

    /**
     * 删除学校的关系
     * @param $rid      关系id
     */
    public function deleteSchoolRelative($rid){
        $schoolRelative=M('school_relative');
        $con=[
            'rid'=>$rid
        ];
        $schoolRelative->where($con)->delete();
        return [
            'success'=>true,
            'info'=>'处理成功'
        ];

    }
    /**
     * 生成机构关系树
     * & $data school_relative中关系的引用
     * $pid  从哪个校区开始
     */
    public function  getTree(&$data, $pId)
    {
        $tree = [];
        foreach($data as $k => $v)
        {
            if($v['parent_id'] == $pId)
            {
                //父亲找到儿子
                $v['parent_id'] = $this->getTree($data, $v['scid']);
                $tree[] = $v;
                //unset($data[$k]);
            }
        }
        return $tree;
    }

    /**
     * $tree 来自 getTree
     * 输出HTML模式 前台使用模板 则转换为JS版本
     */
    public function procHtml($tree)
    {
        $html = '';
        foreach($tree as $t)
        {
            if($t['parent_id'] == '')
            {
                $html .= "<li>{$t['school_name']}</li>";
            }
            else
            {
                $html .= "<li>".$t['school_name'];
                $html .= $this->procHtml($t['parent_id']);
                $html = $html."</li>";
            }
        }
        return $html ? '<ul>'.$html.'</ul>' : $html ;
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
     * @return array 素质三联  data为数组
     * 完成
     */
    public function getSchoolList($dat){
        $con=$dat;
        $page=getCurrentPage($con);
        $pageNum=getPageSize($con);
        unset($con['page'],$con['page_num']);
        $schoolTable=M('school');
        $total=$schoolTable->where($con)->count();
        $total_page=ceil($total/$pageNum);
        return [
            'content'=>$schoolTable->where($con)
                        ->page($page,$pageNum)
                        ->select(),
            'total'=>$total,
            'total_page'=>$total_page,
            'page'=>$page,
            'page_num'=>$pageNum
        ];

    }

    /**
     * 获取指定管理者所管理的校区
     * @param $mid
     */
    public function getManageSchool($mid){
        $managerRoleSchool=M('manager_role_school');
        return $managerRoleSchool
            ->join('school on manager_role_school.scid=school.scid')
            ->join('role_group on manager_role_school.rgid=role_group.rgid')
            ->where(['mid'=>$mid])->select();
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
    public function saveSchool($dat){
        $schoolTable=M('school');
        if($dat['scid']){
            $needs=['school_name','status','level'];
            $save=checkParam($dat,$needs);
            return
                [
                    'success'=>true,
                    'info'=>'修改机构成功',
                    'data'=>$schoolTable->where(['scid'=>$dat['scid']])->save($save)
                ];
        }
        else{
            $existManger=$schoolTable->where(['school_name'=>$dat['school_name']])->find();
            if($existManger!=null){
                return ['success'=>false,'info'=>'已经存在该机构'];
            }
            else{
                return [
                    'success'=>true,
                    'info'=>'添加机构成功',
                    'data'=>$schoolTable->add([
                        'school_name'=>$dat['school_name'],
                        'level'=>$dat['level']
                    ])
                ];
            }
        }
    }


}