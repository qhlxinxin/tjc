<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\StudentModel;
use Home\Model\CourseModel;
class StudentController extends BaseController {
    /**
     * 后台首页
     */
    private  $student;
    private  $course;

    public function __construct()
    {
        parent::__construct();
        $this->course=new CourseModel();
        $this->student=new StudentModel();
    }

    /**
     * 针对某个学生，对某个instance_cid（具体的课程）进行判断及升级
     * sid              学员id
     * instance_cid     具体课程的 instance_cid
     */
    public function preparePromoteCourseProgress(){
        $dat=getParam();
        $auc=$this->course->getAUCidByInstanceCid($dat['instance_cid']);
        $relative=$this->course->getActiveRelative($auc['active_id']);
        $courseArray=$this->course->getPreCourseAndAfterCourse($auc['class_id'],$relative);
        $checkIn=$this->student->pendingCheckInTimeError($dat['sid'],$auc['active_id'],$auc['class_id']);
        if($checkIn['data']){
            if($courseArray['after']!=null){
                if($courseArray['after']['same_unit']=='yes'){
                    $this->student->promoteCourseProgress($dat['sid'],$courseArray['after']['data']['aid'],$courseArray['current']['uid'],$courseArray['after']['data']['cid']);
                }
                else{
                    $this->student->readyToLevelUp($dat['sid'],$courseArray['current']['aid'],$courseArray['current']['uid'],$courseArray['current']['cid']);
                }
            }
            else{
                $this->student->readyToLevelUp($dat['sid'],$courseArray['current']['aid'],$courseArray['current']['uid'],$courseArray['current']['cid']);
            }
        }
    }

    /**
     * 学生考试成绩通过，升级
     * 由管理员进行调取操作升级
     */
    public function promoteGrade(){
        $dat=getParam();
        $res=$this->student->promoteGrade($dat['sid'],dat['aid'],$dat['cid']);
        $current=$this->student->getCurrentCourse($dat['sids'],$dat['aid']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$current,
            'info'=>"获取当前课程进度"
        ]);
    }

    public function addBook(){
        $dat=getParam();
        $res=$this->student->addBook($dat['sid'],$dat['book_code']);
        $this->ajaxReturn($res);
    }

    /**
     * 删除学生教材关系
     * @param $sid              学生sid
     * @param $book_code        电子教材编码/
     */
    public function removeBook(){
        $dat=getParam();
        $res=$this->student->removeBook($dat['sid'],$dat['book_code']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>'',
            'info'=>'删除成功'
        ]);
    }

    /**
     * 获取指定学生的电子教材编号
     * @param $sid
     * @return array
     */
    public function listStudentBook(){
        $dat=getParam();
        $res=$this->student->listStudentBook($dat['sid']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'获取指定学生的电子教材编号'
        ]);
    }

    /**
     * 记录打卡时间
     */
    public function recordClock(){
        $dat=getParam();
        $res=$this->student->addBook($dat);
        $this->ajaxReturn($res);

    }

    public function saveStudentInfo(){
        $dat=getParam();
        $res=$this->student->getStudentList($dat);
        $this->ajaxReturn($res);
    }

    /**
     * 根据条件 寻找学生列表
     */
    public function getStudentList(){
        $dat=getParam();
        $res=$this->student->getStudentList($dat);
        $this->ajaxReturn($res);
    }

    /**
     * 获取指定某些学生的某个活动进度课程
     * $sids        学生id的数组
     * $active_id   活动模板id
     */
    public function getCurrentCourse(){
        $dat=getParam();
        $res=$this->student->getCurrentCourse($dat['sids'],$dat['active_id']);
        $this->ajaxReturn($res);
    }

    /**
     * 学生转学，由一个机构发起，学生进入待转表里等待对方机构接受
     * @param $sid      学生的sid
     * @param $from_school     转出机构的scid
     * @param $to_school       接受机构的scid
     * @param $sender   转出操作者
     */
    public function sendStudent(){
        $dat=getParam();
        $res=$this->student->sendStudent($dat['sid'],$dat['from_school'],$dat['to_school'],$dat['sender']);
        $this->ajaxReturn($res);
    }

    /**
     * 接受学生转学，待转表里的finished 状态置1
     * @param $sid
     * @param $from_school
     * @param $to_school
     * @param $receiver   接受操作者
     */
    public function receiveStudent(){
        $dat=getParam();
        $res=$this->student->receiveStudent($dat['sid'],$dat['from_school'],$dat['to_school'],$dat['receiver']);
        $this->ajaxReturn($res);
    }



    public function exportStudents(){
        $dat=getParam();
        $this->student->exportStudents($dat);
        //$this->ajaxReturn($res);
    }

    /**
     * 列出指定机构待接受
     * @param string $dat
     * to_school        待接受机构的ID
     * from_school      发起转出机构的ID
     * finished         是否完成，默认为查询未完成的
     * 下面三个基本不会用到
     * sender           发起者mid
     * receiver         接收者mid
     * sid              学生ID  基本不会用到
     */
    public function listWaitReceiveStudents(){
        $dat=getParam();
        $res=$this->student->listWaitReceiveStudents($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'查询到指定学校的待转学生列表',
            'data'=>$res
        ]);
    }
}