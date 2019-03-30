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
        $checkIn=$this->student->pendingCheckInTimeError($dat['sid'],$auc['active_id'],$auc['class_id'],$dat['instance_cid']);
        $this->student->saveClassHistory($dat['sid'],$auc['active_id'],$auc['class_id'],$dat['instance_cid'],$checkIn['data']);
        $existClassHistory=$this->student->pendingClassHistory($dat['sid'],$auc['active_id'],$auc['class_id']);
        if($existClassHistory){
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

    }

    public function promoteCourseProgress(){
        $dat=getParam();
        $this->student->promoteCourseProgress($dat['sid'],$dat['aid'],$dat['uid'],$dat['cid']);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'强制设置课程进度',
        ]);
    }

    /**
     * 获取指定课程的上课历史纪录
     * page
     * page_num
     * sid              学生id
     * aid              活动模板id
     * cid              课程模板id
     * instance_cid     具体课程id
     * reset            是否被重置  0为已经被重置
     */
    public function getClassHistory(){
        $dat=getParam();
        $res=$this->student->getClassHistory($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'获取课程历史纪录',
            'data'=>$res
        ]);
    }

    /**
     * 学生考试成绩通过，升级
     * 由管理员进行调取操作升级
     */
    public function promoteGrade(){
        $dat=getParam();
        $res=$this->student->promoteGrade($dat['sid'],$dat['aid'],$dat['cid']);
        $current=$this->student->getCurrentCourse($dat['sids'],$dat['aid']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$current,
            'info'=>"获取当前课程进度"
        ]);
    }

    /**
     * 保存某个学生某个具体单元单元的考试成绩
     * @param $exid   考试记录的id    修改其他参数时传
     * @param $sid
     * @param $instance_aid
     * @param $instance_uid
     * @param $scored
     *
     */
    public function saveStudentExamScored(){
        $dat=getParam();
        $res=$this->student->saveStudentExamScored($dat);
        $this->ajaxReturn($res);
    }

    /**
     * 获取指定学生 指定具体单元的考试成绩
     * @param $sid
     * @param $instance_uids  数组
     */
    public function getStudentExamScoreds(){
        $dat=getParam();
        $res=$this->student->getStudentExamScoreds($dat['sid'],$dat['instance_uids']);
        $this->ajaxReturn($res);
    }

    /**
     * 获取一群学生 某个单元的考试成绩
     * @param $sids
     * @param $instance_uid
     */
    public function listStudentExamScored($sids,$instance_uid){
        $dat=getParam();
        $res=$this->student->listStudentExamScored($dat['sids'],$dat['instance_uid']);
        $this->ajaxReturn($res);
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
     * * check_type   打卡类型   上课   下课  这里需要根据时间和课程时长来计算是上课还是下课   补打的时候有手动选择
     * check_time       打卡时间
     * id_number        身份证号            注意 控制器里接受的是身份证号 然后去换取的 sid
     * descr            备注 非必填
     * instance_aid     哪一个具体的活动
     * instance_cid     哪一个具体的课程
     */
    public function recordClock(){
        $dat=getParam();
        $nDat=$dat;
        if(!$dat['sid']){
            $res=$this->student->getStudentByIdNumber($dat['id_number']);
            if(!$res['sid']){
                $this->ajaxReturn([
                    'success'=>false,
                    'info'=>'没有找到该学生，应该先录入该学生'
                ]);
            }
            $nDat['sid']=$res['sid'];
        }
        unset($nDat['id_number']);
        $res=$this->student->recordClock($nDat);
        $this->ajaxReturn($res);

    }



    public function getStudentByIdNumber(){
        $dat=getParam();
        $res=$this->student->getStudentByIdNumber($dat['id_number']);
        $this->ajaxReturn(['data'=>$res,'success'=>true,'info'=>'查询到学生']);
    }

    public function saveStudentInfo(){
        $dat=getParam();
        $res=$this->student->saveStudentInfo($dat);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'保存成功'
        ]);
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