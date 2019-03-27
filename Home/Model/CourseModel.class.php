<?php
/**
 * Created by PhpStorm.
 * User: qhlxi
 * Date: 2019/1/15
 * Time: 23:11
 */

namespace Home\Model;
use Think\Model;

class CourseModel extends Model
{
    protected $tableName = 'active_info';
    protected $tablePrefix = '';


    /**
     * 添加/编辑一个单元instance
     * $unit_id  单元模板引用id
     * $status   是否有效 修改时使用
     * $puser    操作人
     * 完成
     */
    public function saveUnitInstance($dat){
        $instanceUnit=M('instance_unit');
        if($dat['instance_uid']){
            $needs=['unit_id','status','puser'];
            $save=checkParam($dat,$needs);
            return $instanceUnit->where(['instance_uid'=>$dat['instance_uid']])->save($save);
        }
        else{
            return $instanceUnit->add([
                'unit_id'=>$dat['unit_id'],
                'puser'=>$dat['puser']
            ]);
        }
    }

    /**
     * 添加/编辑一个单元模板
     * $uid       单元id  修改其他参数是使用
     * $unit_name 单元名称 添加时必填
     * $status   是否有效 修改时使用  默认为1 表示有效
     * $to_public_area  有效区域  默认可为空
     * $to_public_level 有效等级  默认可为空
     * 完成
     */
    public function saveUnit($dat){
        $unitInfo=M('unit_info');
        if($dat['uid']){
            $needs=['to_public_level','to_public_area','status','unit_name'];
            $save=checkParam($dat,$needs);
            return $unitInfo->where(['uid'=>$dat['uid']])->save($save);
        }
        else{
            return $unitInfo->add(['unit_name'=>$dat['unit_name']]);
        }
    }




    /**
     * 添加/编辑一个课程模板
     * $class_name 单元名称 添加时必填
     * $status   是否有效 修改时使用
     * $duration 课程时长
     * $puser  处理用户
     * 完成
     */
    public function saveClass($dat){
        $unitInfo=M('class_info');
        if($dat['id']){
            $needs=['class_name','duration','status','puser'];
            $save=checkParam($dat,$needs);
            return $unitInfo->where(['id'=>$dat['id']])->save($save);
        }
        else{
            return $unitInfo->add([
                'class_name'=>$dat['class_name'],
                'duration'=>$dat['duration'],
                'puser'=>$dat['puser']
            ]);
        }
    }


    /**
     * 添加/编辑一个课程模板
     * $instance_cid     数据库课程实例ID  修改其他参数时候必传
     * $class_id        class_info 中的模板id  添加时必填
     * $status          是否有效 修改时使用
     * $active_time     课程实际上课日期时间
     * $puser           处理用户
     * 完成
     */
    public function saveClassInstance($dat){
        $instanceClass=M('instance_class');
        if($dat['instance_cid']){
            $needs=['class_id','active_time','status','puser'];
            $save=checkParam($dat,$needs);
            return $instanceClass->where(['instance_cid'=>$dat['instance_cid']])->save($save);
        }
        else{
            return $instanceClass->add([
                'class_id'=>$dat['class_id'],
                //'duration'=>$dat['active_time'],//刚刚添加课程时候，上课时间是不定的
                'puser'=>$dat['puser']
            ]);
        }
    }

    /**
     * 添加/编辑一个活动模板
     * $aid             活动id  修改其他参数时必填
     * $active_name     活动名称 添加活动时必填
     * $status          是否有效
     * $puser           处理用户
     * 完成
     */
    public function saveActive($dat){
        $activeInfo=M('active_info');
        if($dat['aid']){
            $needs=['active_name','status','apuser'];
            $save=checkParam($dat,$needs);
            return $activeInfo->where(['aid'=>$dat['aid']])->save($save);
        }
        else{
            return $activeInfo->add([
                'active_name'=>$dat['active_name'],
                'apuser'=>$dat['apuser']
            ]);
        }
    }

    /**
     * 实例化一个活动
     * $instance_aid     数据库活动实例ID  修改其他参数时候必传
     * $active_id        活动模板id 从哪个模板来实例化一场活动
     * extend_name       活动扩展标题 添加时必填
     * start_date        活动开始时间
     * status            是否有效 修改时使用
     * belong            展开活动的机构
     * $puser            处理用户
     * 完成
     */
    public function saveActiveInstance($dat){
        $instanceActive=M('instance_active');
        if($dat['instance_aid']){
            $needs=['active_id','start_date','extend_name','belong','status','puser'];
            $save=checkParam($dat,$needs);
            return $instanceActive->where(['instance_aid'=>$dat['instance_aid']])->save($save);
        }
        else{
            return $instanceActive->add([
                'active_id'=>$dat['active_id'],
                'extend_name'=>$dat['extend_name'],
                //'start_date'=>$dat['start_date'],//刚刚添加活动时候，开始日期是不定的
                'belong'=>$dat['belong'],  //属于哪个机构的活动
                'puser'=>$dat['puser']
            ]);
        }
    }

    /**
     * 依照指定unit实例化一组instance_unit 和 instance_class 并加入到 instance_active 下面
     * $instance_aid   已经实例化完成的活动 完成扩展命名和所属机构
     * $uid             需要添加的单元组模板
     * $puser           操作人员
     */
    public function initUnitRelative($instance_aid,$uid,$puser){
        $instance_uid=$this->saveUnitInstance(['unit_id'=>$uid,'puser'=>$puser]);
        $unitClassRelative=$this->getUnitClassRelative($uid);
        //dump($unitClassRelative);
        foreach($unitClassRelative as $va){
            $instance_cid=$this->saveClassInstance(['class_id'=>$va['cid'],'puser'=>$puser]);
            $result[]=$this->saveInstanceRetives($instance_aid,$instance_uid,$instance_cid,'add');
        }
        //dump($result);
        return $result;
    }

    /**
     * 根据active模板 来实例化一场活动并顺序实例化单元等信息
     * @param $aid
     * @param $puser
     */
    public function initActiveRelative($scid,$aid,$active_name,$puser){
        $instance_aid=$this->saveActiveInstance(['extend_name'=>$active_name,'active_id'=>$aid,'belong'=>$scid,'puser'=>$puser]);
        $activeUnitRelative=$this->getActiveUnitRelative($aid);
        foreach ($activeUnitRelative as $va){
            $this->initUnitRelative($instance_aid,$va['uid'],$puser);
        }
        //dump($activeUnitRelative);

        $this->getActiveAllInfo($instance_aid);


    }


    /**
     * 添加 模板关系  单元 和 课程 之间的关系
     * rid  自增序列
     * uid  unit_info里的id
     * class_ids  数组   cid的数组  class_info 里的id
     * 完成
     */
    public function saveUnitClassRelative($uid,$class_ids){
        $relative=M('unit_class_relative');

        $cids=[];
        foreach ($class_ids as $va){
            $cids[]=$va['cid'];
            $adds[]=[
                'uid'=>$uid,
                'cid'=>$va['cid'],
                'class_status'=>$va['class_status'],
                'unit_class_site'=>$va['unit_class_site']
            ];
        }
        $con=[
            'uid'=>['eq',$uid],
            'cid'=>['IN',$cids]
        ];
        $relative->where($con)->delete();
        return $relative->addAll($adds);
    }

    /**
     * 通过 uid 来获取 单元课程模板关系
     * 以进行接下来的实例化
     * @param $uids
     */
    public function getUnitClassRelative($uids){
        $relative=M('unit_class_relative');
        return $relative->where(['uid'=>['IN',$uids]])->select();
    }

    /**
     * 通过 uids 来获取 单元课程模板关系
     * 以进行接下来的实例化
     * @param $uids
     */
    public function getUnitClassRelativeInfo($uids){
        $relative=M('unit_class_relative as ucr');
        $list=$relative
            ->join("class_info as ci on ucr.cid=ci.id")
            ->join('unit_info as ui on ucr.uid=ui.uid')
            ->where(['ucr.uid'=>['IN',$uids]])
            ->select();
        return $list;
    }


    /**
     * 添加 模板关系  活动 和 单元 之间的关系
     * rid  自增序列
     * aid  active_info里的id
     * $unit_ids  数组   uid的数组  unit_info 里的id
     * 包含 uid  active_unit_site  unit_status
     * 完成
     */
    public function saveActiveUnitRelative($aid,$unit_ids){
        $relative=M('active_unit_relative');
        $uids=[];
        foreach ($unit_ids as $va){
            $uids[]=$va['uid'];
            $adds[]=[
                'uid'=>$va['uid'],
                'aid'=>$aid,
                'unit_status'=>$va['unit_status'],
                'active_unit_site'=>$va['active_unit_site']
            ];
        }
        $con=[
            'aid'=>['eq',$aid],
            'uid'=>['IN',$uids]
        ];
        $relative->where($con)->delete();
        return $relative->addAll($adds);
    }

    /**
     * 通过 aid 来获取 活动课程模板和单元之间的关系
     * 以进行接下来的实例化
     * @param $aids
     * 完成
     */
    public function getActiveUnitRelative($aids){
        $relative=M('active_unit_relative');
        return $relative->where(['aid'=>['IN',$aids]])->select();
    }

    public function getActiveUnitRelativeInfo($aids){
        $relative=M('active_unit_relative as aur');
        $list=$relative
            ->join("active_info as ai on aur.aid=ai.aid")
            ->join('unit_info as ui on aur.uid=ui.uid')
            ->join("unit_class_relative as ucr on ucr.uid=aur.uid")
            ->join("class_info as ci on ci.id=ucr.cid")
            ->where(['aur.aid'=>['IN',$aids]])
            ->select();
        return $list;
    }

    /**
     * 修改活动实例、单元实例、课程实例之间的关系
     * @param $instance_aid
     * @param $instance_uid
     * @param $instance_cid
     * @param $action    可选值为 add 或者 delete
     * 完成
     */
    public function saveInstanceRelatives($instance_aid,$instance_uid,$instance_cid,$action){
        $relative=M("instance_relative");
        $exist=$relative->where([
            'instance_aid'=>$instance_aid,
            'instance_uid'=>$instance_uid,
            'instance_cid'=>$instance_cid,
        ])->find();
        if($exist==null){
            if($action=='add'){
                return $relative->add([
                    'instance_aid'=>$instance_aid,
                    'instance_uid'=>$instance_uid,
                    'instance_cid'=>$instance_cid,
                ]);
            }
        }else{
            if($action=='delete'){
                return $relative->where([
                    'instance_aid'=>$instance_aid,
                    'instance_uid'=>$instance_uid,
                    'instance_cid'=>$instance_cid,
                ])->save(['status'=>0]);
            }
        }
    }

    /**
     * 请求其他机构协助完成活动
     * aaid             数据库自增id   修改时必填
     * instance_aid     活动实例id
     * sid              机构id
     *
     */
    public function saveAssistActive($dat){

    }

    /**
     * 获取具体活动的全部单元实例，课程实例信息
     * @param $instance_aid
     * @return array
     */
    public function getActiveAllInfo($instance_aid){
        $info=M('instance_relative as r')
            ->join('instance_active ia on r.instance_aid=ia.instance_aid')
            ->join('instance_unit iu on r.instance_uid=iu.instance_uid')
            ->join('instance_class ic on r.instance_cid=ic.instance_cid')
            ->join('class_info ci on ic.class_id=ci.id')
            ->join('unit_info ui on iu.unit_id=ui.uid')
            ->join('active_info ai on ia.active_id=ai.aid')
            ->field("r.*,
            ic.class_id,ic.active_time,ic.status ics,
            ia.active_id,ia.status ias,ia.start_date,ia.belong,
            iu.unit_id,iu.status ius,
            ci.class_name,ci.duration,ui.unit_name,ui.to_public_level,ui.to_public_area,ai.active_name
            ")
            ->where(['r.instance_aid'=>$instance_aid,'r.status'=>1])
            ->select();
        //format格式  按照单元进行划分
        foreach ($info as $va){
            $_info['active']['active_id']=$va['active_id'];
            $_info['active']['i_active']=$va['instance_aid'];
            $_info['active']['ias']=$va['ias'];
            $_info['active']['belong']=$va['belong'];
            $_info['active']['start_date']=$va['start_date'];

            $_info['unit'][$va['instance_uid']]['i_unit']=$va['instance_unit'];
            $_info['unit'][$va['instance_uid']]['unit']=$va['unit_name'];
            $_info['unit'][$va['instance_uid']]['ius']=$va['ius'];
            $_info['unit'][$va['instance_uid']]['classes'][]=[
                'instance_cid' => $va['instance_cid'],
                'status' => $va['status'],
                'class_id' => $va['class_id'],
                'active_time' => $va['active_time'],
                'ics' => $va['ics'],
                'active_id' => $va['class_id'],
                'class_name' => $va['class_name'],
                'duration' => $va['duration'],
            ];
        }
        $res=[
            'origin'=>$info,
            'format'=>$_info
        ];

        //dump($info);
        return $res;
    }

    /**
     * 根据模板的aid 获取模板下所有单元模板、课程模板的关系
     * @param $aid
     */
    public function getActiveRelative($aid){
        $con=['aur.aid'=>$aid];
        $res=M('active_unit_relative as aur')
            ->join("join unit_info as ui on aur.uid=ui.uid")
            ->join("join unit_class_relative as ucr on ucr.uid=aur.uid")
            ->join("class_info as ci on ucr.cid=ci.id")
            ->where($con)
            ->order("aur.active_unit_site,ucr.unit_class_site ASC")
            ->field("aur.aid,aur.active_unit_site,aur.unit_status,
            ui.uid,ui.unit_name,ucr.unit_class_site,
            ci.class_name,ci.id as cid ,ci.duration
            ")
            //->select(['index'=>'cid']);
            ->select();
        return $res;

    }

    /**
     * 根据具体活动课的 instance_id 返回活动模板的aid,uid,cid;
     * @param $instance_cid
     */
    public function getAUCidByInstanceCid($instance_cid){
        $ir=M('instance_relative as r');
        $AUC=$ir->join("instance_active as ia on r.instance_aid=ia.instance_aid")
            //->join("active_info as ai on ia.aid=ai.aid")
            ->join('instance_unit as iu on iu.instance_uid=r.instance_uid')
            ->join('instance_class as ic on ic.instance_cid=r.instance_cid')
            ->where(['r.instance_cid'=>$instance_cid])
            ->field('ia.active_id,iu.unit_id,ic.class_id')
            ->find();
        return $AUC;
    }

    /**
     * 判断CID 在relative中的位子 由此得到前后课程
     * @param $cid
     * @param $active_relative    课程之间的关系 由上面的 getActiveRelative 得到的数组
     */
    public function getPreCourseAndAfterCourse($cid,$active_relative){
        //dump($cid);
        foreach ($active_relative as $k => $va){
            if($cid==$va['cid']){
                $current=$va;
                $before=$active_relative[$k-1]?$active_relative[$k-1]:null;
                $after=$active_relative[$k+1]?$active_relative[$k+1]:null;
            }
        }

        //$current=$active_relative[$cid];
        //$before=$active_relative[$cid-1];
        //$after=$active_relative[$cid+1];
        $siblingCourse=[
            'before'=>[],
            'after'=>[]

        ];
        if($before!=null){
            $siblingCourse['before']['data']=$before;
            if($before['uid']==$current['uid']){
                $siblingCourse['before']['same_unit']='yes';
            }
            else{
                $siblingCourse['before']['same_unit']='no';
            }
        }
        else{
            $siblingCourse['before']=null;
        }
        if($after!=null){
            $siblingCourse['after']['data']=$after;
            if($after['uid']==$current['uid']){
                $siblingCourse['after']['same_unit']='yes';
            }
            else{
                $siblingCourse['after']['same_unit']='no';
            }
        }
        else{
            $siblingCourse['after']=null;
        }

        $siblingCourse['current']=$current;
        return $siblingCourse;
    }

    /**
     * 分页显示课程模板
     * @param $dat
     * page,
     * page_num
     */
    public function listClasses($dat){
        $page=getCurrentPage($dat);
        $pageSize=getPageSize($dat);
        unset($dat['page'],$dat['page_num']);

        $ci=M('class_info');
        $total=$ci->count();
        $total_page=ceil($total/$pageSize);
        $classes=M('class_info')->page($page,$pageSize)->select();
        return [
            'content'=>$classes,
            'total'=>$total,
            'total_page'=>$total_page
        ];
    }
    /**
     * 分页显示单元模板
     * @param $dat
     * page,
     * page_num
     */
    public function listUnits($dat){
        $page=getCurrentPage($dat);
        $pageSize=getPageSize($dat);
        unset($dat['page'],$dat['page_num']);

        $ui=M('unit_info');
        $total=$ui->count();
        $total_page=ceil($total/$pageSize);
        $units=$ui->order("uid,status DESC")->page($page,$pageSize)->select(['index'=>'uid']);
        foreach($units as $k => $va){
            $units[$k]['classes']=[];
            $uids[]=$va['uid'];
        }
        $con['ucr.uid']=['IN',$uids];
        $classes=M('class_info as ci')
            ->join('unit_class_relative as ucr on ucr.cid=ci.id')
            ->where($con)
            ->field("ci.*,ucr.uid,ucr.cid,ucr.unit_class_site")
            ->order("unit_class_site ASC")
            ->select();
        //dump($classes);
        foreach ($classes as $k => $va){
            $units[$va['uid']]['classes'][]=$va;
        }
        return [
            'content'=>$units,
            'total'=>$total,
            'total_page'=>$total_page
        ];
    }
    /**
     * 分页显示活动模板
     * @param $dat
     *
     * page,
     * page_num
     */
    public function listActives($dat){
        $page=getCurrentPage($dat);
        $pageSize=getPageSize($dat);
        unset($dat['page'],$dat['page_num']);
        $ai=M('active_info');
        $total=$ai->count();
        $total_page=ceil($total/$pageSize);
        $actives=$ai->where($dat)->page($page,$pageSize)->select();
        //foreach($actives as $k => $va){
        //    $actives[$k]['units']=[];
        //    $aids[]=$va['aid'];
        //}
        //$con['ucr.uid']=['IN',$aids];
        //$classes=M('class_info as ci')
        //    ->join('unit_class_relative as ucr on ucr.cid=ci.id')
        //    ->where($con)
        //    ->field("ci.*,ucr.uid,ucr.cid")
        //    ->select();
        ////dump($classes);
        //foreach ($classes as $k => $va){
        //    $units[$va['uid']]['classes'][]=$va;
        //}
        //$actives=$ai
        //    ->join('active_unit_relative as aur on aur.aid=ai.aid')
        //    ->join('unit_info as ui on ui.uid=aur.uid')
        //    ->page($page,$pageSize)->select();
        return [
            'content'=>$actives,
            'total'=>$total,
            'total_page'=>$total_page
        ];
    }

    /**
     * 分页显示 INSTANCE活动
     * @param $dat
     * active_name
     * extend_name  模糊查询
     * start_date   开始日期
     * belong       所属机构
     * level        省级  市级   区级
     * page,
     * page_num
     */
    public function listInstanceActive($dat){
        $con=$dat;
        $page=getCurrentPage($dat);
        $pageNum=getPageSize($dat);
        //dump($page);
        unset($con['page'],$con['page_num']);
        if(isset($dat['active_name'])){
            unset($con['active_name']);
            $con['ai.active_name']=['LIKE',"%".$dat['active_name']."%"];
        }
        if(isset($dat['extend_name'])){
            unset($con['extend_name']);
            $con['ia.extend_name']=['LIKE',"%".$dat['extend_name']."%"];
        }
        //dump($con);
        $ia=M("instance_active as ia");
        $list=$ia->join("join active_info as ai on ia.active_id=ai.aid")
            ->join("school as s on s.scid=ia.belong")
            ->where($con)
            ->field("ia.*,ai.*,s.level,s.school_name,s.status as school_status")
            ->page($page,$pageNum)
            ->select();
        $total=$ia->join("join active_info as ai on ia.active_id=ai.aid")
            ->join("school as s on s.scid=ia.belong")
            ->where($con)
            ->count();
        $total_page=ceil($total/$pageNum);
        return [
            'content'=>$list,
            'total'=>$total,
            'total_page'=>$total_page,
            'page'=>$page,
            'pageNum'=>$pageNum
        ];

    }

    /**
     * 将具体的活动指派给 某些机构
     * @param $instance_aid
     * @param $scids            机构scid的数组
     * @param $puser            处理人
     */
    public function assistActive($instance_aid,$scids,$puser){
        $assistActive=M('assist_active');
        foreach($scids as $k => $va){
            $con=['instance_aid'=>$instance_aid,'scid'=>$va];
            $exist=$assistActive->where($con)->find();
            if($exist!=null){
                //存在活动
                $assistActive->where($con)->save(['status'=>1,'puser'=>$puser]);
            }
            else{
                $assistActive->add([
                    'instance_aid'=>$instance_aid,
                    'scid'=>$va,
                    'puser'=>$puser
                ]);
                //return [
                //    'success'=>true,
                //    'info'=>'处理成功',
                //    'data'=>$res
                //];
            }

        }
        return [
            'success'=>true,
            'info'=>'处理成功',
            'data'=>''
        ];
    }

    /**
     * 删除指定机构指定的协助活动
     * @param $instance_aid
     * @param $scids            机构scid的数组
     * @param $puser            处理人
     */
    public  function deleteAssistActive($instance_aid,$scid,$puser){
        $assistActive=M('assist_active');
        $con=[
            'instance_aid'=>$instance_aid,
            'scid'=>$scid,
            'status'=>1,
        ];
        $assistActive->where($con)->save(['status'=>1,'puser'=>$puser]);

    }

    /**
     * 获取指派活动的列表
     * @param $dat
     * scid             学校id
     * instance_aid     具体活动的aid
     * status           是否有效
     * puser            最后处理人
     * page             页数
     * page_num         每页显示数量
     */
    public function listAssistActive($dat){
        $assistActive=M('assist_active');
        $page=getCurrentPage($dat);
        $page_num=getPageSize($dat);
        $con=$dat;
        unset($con['page'],$con['page_num']);
        $total=$assistActive->where($con)->count();
        $total_page=ceil($total/$page_num);
        $list=$assistActive->where($con)->page($page,$page_num)->select();
        return [
                'total'=>$total,
                'total_page'=>$total_page,
                'content'=>$list,
            ];
    }
}