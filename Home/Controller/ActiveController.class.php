<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\CourseModel;
use Home\Model\ManagerModel;
class ActiveController extends BaseController {

    public function __construct()
    {
        parent::__construct();
        $this->course=new CourseModel();
        $this->manager=new ManagerModel();
    }



    public function saveClass(){
        $dat=getParam();

        $res= $this->course->saveClass($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'处理成功',
            'data'=>$res
        ]);
    }

    public function saveUnit(){
        $dat=getParam();
        $res= $this->course->saveUnit($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'处理成功',
            'data'=>$res
        ]);
    }

    public function saveActive(){
        $dat=getParam();
        $res= $this->course->saveActive($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'处理成功',
            'data'=>$res
        ]);
    }

    public function saveClassInstance(){
        $dat=getParam();
        $res= $this->course->saveClassInstance($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'处理成功',
            'data'=>$res
        ]);
    }

    public function saveUnitInstance(){
        $dat=getParam();
        $res= $this->course->saveUnitInstance($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'处理成功',
            'data'=>$res
        ]);
    }

    public function saveActiveInstance(){
        $dat=getParam();
        $res= $this->course->saveActiveInstance($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'处理成功',
            'data'=>$res
        ]);
    }

    /**
     * 实例化一场活动
     * scid         学校id
     * active_name  扩展活动名
     * aid          活动模板id
     * puser        添加人
     */
    public function initActiveRelative(){
        $dat=getParam();
        $active=$this->course->initActiveRelative($dat['scid'],$dat['aid'],$dat['active_name'],$dat['puser']);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'参考data',
            'data'=>$active
        ]);
    }


    /**
     * 添加 模板关系  单元 和 课程 之间的关系
     */
    public function saveUnitClassRelative(){
        $dat=getParam();
        $res=$this->course->saveUnitClassRelative($dat['uid'],$dat['class_ids']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'操作成功'
        ]);
    }

    /**
     * 通过 uid 来获取 单元课程模板关系
     */
    public function getUnitClassRelative(){
        $dat=getParam();
        $res=$this->course->getUnitClassRelative($dat['uid']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'获取成功'
        ]);
    }

    /**
     * 添加 模板关系  活动 和 单元 之间的关系
     */
    public function saveActiveUnitRelative(){
        $dat=getParam();
        $res=$this->course->saveActiveUnitRelative($dat['aid'],$dat['unit_ids']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'操作成功'
        ]);
    }

    public function getActiveUnitRelative(){
        $dat=getParam();
        $res=$this->course->getActiveUnitRelative($dat['aid']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'获取成功'
        ]);
    }

    /**
     * 修改活动实例、单元实例、课程实例之间的关系
     */
    public function saveInstanceRelatives(){
        $dat=getParam();
        $res=$this->course->saveInstanceRelatives($dat['instance_aid'],$dat['instance_uid'],$dat['instance_cid'],$dat['action']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'操作成功'
        ]);
    }

    public function listClasses(){
        $dat=getParam();
        $res= $this->course->listClasses($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'操作成功',
            'data'=>$res
        ]);
    }

    public function listUnits(){
        $dat=getParam();
        $res= $this->course->listUnits($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'操作成功',
            'data'=>$res
        ]);
    }

    public function listActives(){
        $dat=getParam();
        $res= $this->course->listActives($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'操作成功',
            'data'=>$res
        ]);
    }

    public function listInstanceActive(){
        $dat=getParam();
        $res= $this->course->listInstanceActive($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'操作成功',
            'data'=>$res
        ]);
    }

    /**
     * 获取具体活动的全部单元实例，课程实例信息
     * @param $instance_aid
     * @return array
     */
    public function getActiveAllInfo(){
        $dat=getParam();
        $res= $this->course->getActiveAllInfo($dat['instance_aid']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'获取具体活动信息'
        ]);
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
    public function listAssistActive(){
        $dat=getParam();
        $res= $this->course->listAssistActive($dat);
        $this->reJson($res);
    }

    /**
     * 将具体的活动指派给 某些机构
     * @param $instance_aid
     * @param $scids            机构scid的数组
     * @param $puser            处理人
     */
    public function assistActive(){
        $dat=getParam();
        $res= $this->course->assistActive($dat['instance_aid'],$dat['scids'],$dat['puser']);
        $this->reJson($res);
    }

    /**
     * 删除指定机构指定的协助活动
     * @param $instance_aid
     * @param $scids            机构scid的数组
     * @param $puser            处理人
     */
    public  function deleteAssistActive(){
        $dat=getParam();
        $res= $this->course->deleteAssistActive($dat['instance_aid'],$dat['scid'],$dat['puser']);
        $this->reJson($res);
    }
}