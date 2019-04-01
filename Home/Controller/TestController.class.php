<?php
namespace Home\Controller;
use Home\Model\CourseModel;
use Home\Model\ManagerModel;
use Home\Model\StudentModel;
use Think\Controller;
class TestController extends BaseController {
    private $course;
    private $manager;
    private $student;
    public function __construct()
    {
        parent::__construct();
        $this->course=new CourseModel();
        $this->manager=new ManagerModel();
        $this->student=new StudentModel();
    }

    /**
     * 后台首页
     */
    public function index(){

    }

    public function test(){
        $param=[
            'name'=>'lizheng',
            'code'=>'wh571822'
        ];
        $needs=[
            'name'
        ];
        $res=checkParam($param,$needs);
        dump($res);
    }


    //测试编辑单元模板
    public function testSaveUnit(){
        $res=$this->course->saveUnit(['unit_name'=>'测试新的单元']);
        dump($res);
    }

    //测试编辑课程模板
    public function testSaveClass(){
        $res=$this->course->saveClass(['class_name'=>'其他的的测试课程','duration'=>120,'puser'=>'lizheng']);
        dump($res);
    }

    //测试活动模板
    public function testSaveActive(){

    }

    public function testListUnits(){
        $res=$this->course->listUnits([2,3]);
        dump($res);
    }

    public function testInstanceActive(){

        $instance_aid=$this->course->saveActiveInstance(['extend_name'=>'我的测试活动','puser'=>'lizheng','belong'=>'1']);
        dump($instance_aid);
    }

    public function testSaveUnitClassRelative(){
        $res=$this->course->saveUnitClassRelative(2,[4,5,6]);
        dump($res);
    }

    public function testSaveActiveUnitRelative(){
        $res=$this->course->saveActiveUnitRelative(1,[1,2]);
        dump($res);
    }

    public function testInitUnitRelative(){

        $this->course->initUnitRelative(1,1,'lizheng');
    }

    public function testInitActiveRelative(){
        $this->course->initActiveRelative(1,1,'三场神奇活动','lizheng');
    }

    public function testGetActiveAllInfo(){
        $res=$this->course->getActiveAllInfo(4);
        dump($res);
    }

    public function testGetActiveRelative(){
        $res=$this->course->getActiveRelative(1);
        dump($res);
    }

    public function testPreparePromoteCourseProgress(){
        $auc=$this->course->getAUCidByInstanceCid('2');
        //dump($auc);
        $relative=$this->course->getActiveRelative($auc['active_id']);
        //dump($relative);
        $courseArray=$this->course->getPreCourseAndAfterCourse($auc['class_id'],$relative);
        dump($courseArray);
        $checkIn=$this->student->pendingCheckInTimeError(1,$auc['active_id'],$auc['class_id'],9);
        dump($checkIn);

        if($courseArray['after']!=null){
            if($courseArray['after']['same_unit']=='yes'){
                $this->student->promoteCourseProgress(1,$courseArray['after']['data']['aid'],$courseArray['current']['uid'],$courseArray['after']['data']['cid']);
            }
            else{
                $this->student->readyToLevelUp(1,$courseArray['current']['aid'],$courseArray['current']['uid'],$courseArray['current']['cid']);
            }
        }
        else{
            $this->student->readyToLevelUp(1,$courseArray['current']['aid'],$courseArray['current']['uid'],$courseArray['current']['cid']);
        }
        exit;
    }

    public function testListInstanceActive(){
        $dat=[
            //'page'=>getCurrentPage(),
            //'page_num'=>getPageSize(),
            'extend_name'=>'神奇'
        ];

        $res=$this->course->listInstanceActive($dat);
        dump($res);
    }

    public  function testListActives(){
        $res=$this->course->listActives([]);
        dump($res);
    }

    public function testGetActiveUnitRelativeInfo(){
        $res=$this->course->getActiveUnitRelativeInfo([1]);
        dump($res);
    }


    public function testListAssistActive(){
        $res=$this->course->listAssistActive(['instance_aid'=>4,'to_school'=>3,'status'=>0]);
        dump($res);
    }

    /*********************** test Manager *****************************/

    public function testGetManagerByRoleGroup(){
        $res=$this->manager->getManagerByRoleGroup(5);
        dump($res);
    }

    public function testGetSchoolList(){
        $res=$this->manager->getSchoolList(['page'=>1,'page_size'=>10]);
        dump($res);

    }


    public function testGetSchoolRelative(){
        $tree=$this->manager->getSchoolRelative();
        //dump($tree);
        ksort($tree);
        $new=array_shift($tree);
        dump($new);
        foreach($tree as $k =>$va){

        }
    }

    public function testGetTree(){
        $data=M('school_relative')->join('school s on school_relative.scid=s.scid')->select();
        $tree=$this->manager->getTree($data,1);

        echo $this->manager->procHtml($tree);

    }

    //测试根据mid获取管理校区的信息
    public function testGetManageSchool(){
        echo "<h2>我管理的校区</h2>";
        $res=$this->manager->getManageSchool(1);
        dump($res);

    }

    //测试添加 编辑用户基础信息
    public function testSaveManager(){
        $res=$this->manager->saveManager(['mid'=>2,'manager_name'=>'打工小弟','username'=>'huangcheng','password'=>'124456','status'=>0]);
        dump($res);

    }

    //测试添加管理校区及管理权限
    public function testSaveManageSchool(){
        $res=$this->manager->saveManageSchool(2,5,3);
        dump($res);
    }

    //测试获取指定人员 指定校区权限
    public function testGetManagerAuth(){
        $res=$this->manager->getManagerAuth(1,5);
        dump($res);
    }


    /*********************** test Student *****************************/

    public function testPendingCheckInTimeError(){
        $res=$this->student->pendingCheckInTimeError(1,1,3,9);
        dump($res);
    }

    public function testGetCurrentCourse(){
        $res=$this->student->getCurrentCourse([1],1);
        dump($res);
    }

    public function testExportStudent(){
        $this->student->exportStudents(['scids'=>[1,2]]);
    }

    public function testGetStudentList(){
        $res=$this->student->getStudentList([
            'student_info.belong'=>'1',
            //'id_type'=>'身份证',
            'student_info.id_number'=>'510111199904042929'
        ]);
        dump($res);
    }

    public function testListWaitReceiveStudents(){
        $res=$this->student->listWaitReceiveStudents(['to_school'=>2]);
        //$res=$this->student->listWaitReceiveStudents(['from_school'=>5]);
        dump($res);
    }

    public function testRecord(){
        $recordCheckIn=M("record_check_in as rc");
        $r=$recordCheckIn->join("instance_active as ia on rc.instance_aid=ia.instance_aid")
            ->join("instance_class as ic on ic.instance_cid=rc.instance_cid")
            ->join("class_info as ci on ic.class_id=ci.id")
            ->join("active_info as ai on ia.active_id=ai.aid")
            ->where(['rc.ckid'=>3])
            ->field("rc.*,ai.active_name,ai.aid,ia.extend_name,ia.start_date,ia.belong,ic.active_time,ci.class_name,ci.duration")
            ->find();
        dump($r);
    }

    /************************test *************************************/

    public function testWFW(){
        $studentCode='WH427603';
        if($studentCode){
            $studentStr="&studentcode=".$studentCode;
        }
        $classCodes=[
            '1LJHC8018','J1HC7037','1LJHC8014'
        ];
        $classCodesStr=implode(',',$classCodes);

        $url="http://wxpay.xdf.cn/silenceauthorize/view.do?schoolid=4&callid=2".$studentStr."&classcodes=".$classCodesStr."&qrcode_id=7247E03B-0C5A-4075-A069-04ABEDEEC962&marketingSources=wechat_zrdb&marketingSourcesExt=WHCOURSES";
        dump($url);
    }

    public function testCookieInfo(){
        dump($_COOKIE);
    }

    public function testqr(){
        $url="https://weixin.qq.com/g/AS0X3jUilfyTxSac";
        $enurl=urlencode($url);
        //$url=urldecode($url);
        dump($enurl);
        $durl="http://127.0.0.1/tjc.php/test/testqr?durl=".$enurl;
        dump($durl);
        dump($url);
        //header("Location: ".$url);
    }

}