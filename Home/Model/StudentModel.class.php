<?php
/**
 * Created by PhpStorm.
 * User: qhlxi
 * Date: 2019/1/15
 * Time: 22:15
 */

namespace Home\Model;
use Think\Model;
use Home\Model\CourseModel;

class StudentModel extends Model
{
    protected $tableName = 'student_info';
    protected $tablePrefix = '';
    private $course;
    /**添加 修改学员基本信息
     *
     * @param $dat  数组 包含以下参数
     * sid  学员ID  修改其他参数时候必填
     * name
     * id_type 这个件类型 比如学生证  身份证  护照  默认为身份证
     * id_number    证件号码
     * sex          性别
     * address      地址，取身份证地址
     * section      A区  还是 B 区   手动输入在A区  身份证刷卡输入在B区
     * direct_teacher   指导老师   非必填
     * grade            年级
     * belong           所属什么机构
     * status           是否有效    默认有效
     * creator_id       创建者的id  来自 manager_id   创建时必传
     * update_id        更新者的id  来自 manager_id   修改时必传
     *
     * 默认年级如果不传则为学前班
     */
    public function saveStudentInfo($dat){
        $_sid='';
        if($dat['sid']){
            $needs=['name','tel','id_type','id_number','sex','address','section','formal','direct_teacher','belong','status','creator_id','update_id','grade'];
            $save=checkParam($dat,$needs);
            $this->where(['sid'=>$dat['sid']])->save($save);
            $_sid=$dat['sid'];
        }
        else{
            if(!isset($dat['grade'])){
                $_grade='学前班';
            }else{
                $_grade=$dat['grade'];
            }
            $_sid=$this->add([
                'name'=>$dat['name'],
                'tel'=>$dat['tel'],
                'sex'=>$dat['sex'],
                'id_type'=>$dat['id_type'],
                'id_number'=>$dat['id_number'],
                'address'=>$dat['address'],
                'belong'=>$dat['belong'],
                'grade'=>$_grade,
                'formal'=>$dat['formal'],
                'direct_teacher'=>$dat['direct_teacher'],
                'section'=>$dat['section'],
                'creator_id'=>$dat['creator_id'],
            ]);
        }
        return $this->where(['sid'=>$_sid])->find();
    }


    /**
     * 获取带分页的学员信息
     * 带条件
     * $con 数组 可包含以下参数
     * page   页数 默认从1开始  page 推荐使用get方法传递
     * page_num  默认是20  可以用 page_num指定
     * 常规参数，参考 student_info 对象
     * sid
     * status
     * id_type
     * id_number
     * section
     * address
     * formal
     * name
     * tel
     * sex
     * direct_teacher
     * belong
     * creator_id
     * update_id
     *
     * 额外增加，级别
     * s.level
     */
    public function getStudentList($con=''){
        //总条数
        if(isset($con['name'])){
            $con['name']=['like',"%".$con['name']."%"];
        }
        if(isset($con['belong'])&&is_array($con['belong'])){
            $con['belong']=['IN',$con['belong']];
        }

        if(isset($con['status'])){
            $con['student_info.status']=['eq',$con['status']];
            unset($con['status']);
        }
        if(isset($con['level'])){
            $con['s.level']=$con['level'];
            unset($con['level']);
        }

        //$t=$this->where($con)->select();
        //dump($t);
        $page=getCurrentPage($con);
        $pageNum=getPageSize($con);
        unset($con['page'],$con['page_num']);
        $total=$this
            ->join('left join school_manager as csm on student_info.creator_id=csm.mid')
            ->join('left join school_manager as usm on student_info.update_id=usm.mid')
            ->join("left join school as s on student_info.belong=s.scid")
            ->where($con)->count();
        //总页数
        $totalPages=ceil($total/$pageNum);
        //$content=$this
        $content=M('student_info')
            ->join('left join school_manager as csm on student_info.creator_id=csm.mid')
            ->join('left join school_manager as usm on student_info.update_id=usm.mid')
            ->join("left join school as s on student_info.belong=s.scid")
            ->where($con)
            ->field("student_info.*,s.school_name,s.level,
            csm.username as creator_username,
            csm.manager_name as creator_manager_name,
            usm.username as update_username,
            usm.manager_name as update_manager_name")
            ->page($page,$pageNum)
            ->select();
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

    //private function getPagesData($table,$con);

    /**
     * 获取指定某些学生的某个活动进度课程
     * $sids        学生id的数组
     * $active_id   活动模板id
     */
    public function getCurrentCourse($sids,$active_id){

        $con=['scp.sid'=>['IN',$sids],'current_active'=>$active_id];
        $studentProgress=M('student_class_progress scp');
        $progress=$studentProgress->
            join('class_info as ci on scp.current_class=ci.id')
            ->join('student_info as si on scp.sid=si.sid')
            ->where($con)
            ->field("scp.*,ci.class_name,si.*")
            ->select();
        return $progress;

    }

    /**
     * 保存上课历史纪录
     */
    public function saveClassHistory($sid,$aid,$cid,$instance_cid,$descr=''){
        $classHistory=M('student_class_history');
        $con=[
            'sid'=>$sid,
            'aid'=>$aid,
            'cid'=>$cid,
            'instance_cid'=>$instance_cid
        ];
        $exist=$classHistory->where($con)->find();
        if($exist==null){
            $classHistory->add([
                'sid'=>$sid,
                'aid'=>$aid,
                'cid'=>$cid,
                'instance_cid'=>$instance_cid,
                'descr'=>$descr
            ]);
        }
        else{
            $classHistory->where($con)->save(['descr'=>$descr]);
        }

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
    public function getClassHistory($dat){
        $classHistory=M('student_class_history');
        $page=getCurrentPage($dat);
        $pageNum=getPageSize($dat);
        $con=$dat;
        unset($con['page'],$con['page_num']);
        $list=$classHistory->where($con)->select();
        $total=$classHistory->where($con)->count();
        $total_page=ceil($total/$pageNum);
        return [
            'content'=>$list,
            'total'=>$total,
            'total_page'=>$total_page
        ];
    }

    /**
     * 根据aid和sids 获取一群同学 的上课历史纪录
     * @param $sids
     * @param $aid
     */
    public function getStudentActiveCourseProgress($sids,$aid){
        $studentClassHistory=M('student_class_history');
        $con=[
            'sid'=>['IN',$sids],
            'active_id'=>$aid
        ];
        $res=$studentClassHistory->where($con)
            ->order('sid')
            ->select();
        return $res;
    }

    /**
     * 判断一个活动模板下，某个课程是否已经上过了。
     * @param $sid
     * @param $aid
     * @param $cid
     */
    public function pendingClassHistory($sid,$aid,$cid){
        $classHistory=M('student_class_history');
        $con=[
            'sid'=>$sid,
            'aid'=>$aid,
            'cid'=>$cid,
            'reset'=>1
        ];
        return $classHistory->where($con)->find();
    }

    public function resetClassHistory($sid,$aid,$cid){
        $classHistory=M('student_class_history');
        $con=[
            'sid'=>$sid,
            'aid'=>$aid,
            'cid'=>$cid,
            'reset'=>1
        ];
        $classHistory->where($con)->save(['reset'=>0]);
    }

    /**
     * 进入下一节课程
     * 如果是最后一节课
     * 则不进入下一节课
     * $sid     学生id
     * $aid     活动模板id
     * $uid     单元模板id
     * $next_course_id 下一节课的id   必填   该为课程模板id
     */
    public function promoteCourseProgress($sid,$aid,$uid,$next_course_id){
        $con=[
            'sid'=>$sid,
            'current_active'=>$aid,
            'current_unit'=>$uid
            //'current_class'=>$next_course_id
        ];
        //dump($con);
        $courseProcess=M('student_class_progress');
        $process=$courseProcess->where($con)->find();
        //dump($process);
        if($process!=null){
            $courseProcess->where($con)->save(['current_class'=>$next_course_id]);
        }
        else{
            $courseProcess->add([
                'sid'=>$sid,
                'current_active'=>$aid,
                'current_unit'=>$uid,
                'current_class'=>$next_course_id
            ]);
        }
        //判断 当前最后一次课 上课下课的打卡记录 是否都存在 且无异常
    }

    public function readyToLevelUp($sid,$aid,$uid,$cid){
        $con=[
            'sid'=>$sid,
            'current_active'=>$aid,
            'current_unit'=>$uid,
            'current_class'=>$cid
        ];
        //dump($con);
        $courseProcess=M('student_class_progress');
        $current=$courseProcess->where($con)->find();
        if($current!=null){
            $courseProcess->where($con)->save(['level_up'=>1]);
        }
        else{

            $courseProcess->where($con)->add([
                'sid'=>$sid,
                'current_active'=>$aid,
                'current_unit'=>$uid,
                'current_class'=>$cid,
                'level_up'=>1
            ]);
        }
    }
    /**
     * 判定某个学生 某个课程打卡是否异常
     * $sid         学生id
     * $active_id   指定的活动模板
     * $class_id    课程模板的id
     *
     * 返回 素质三联
     */
    public function pendingCheckInTimeError($sid,$active_id,$class_id,$instance_cid){

        $list=$this->getStudentSpecifiedCourseCheckInRecord($sid,$active_id,$class_id,$instance_cid);
        //dump($list);
        $hasStart=false;
        $hasEnd=false;
        $classFinished=false;
        // 这里算判断逻辑

        foreach($list as $k => $va){
            if($va['check_type']=='上课'){
                $hasStart=true;
            }
            if($va['check_type']=='下课'){
                $hasEnd=true;
            }
        }
        if($hasEnd&&$hasStart){
            $classFinished=true;
        }
        $result=[
            'success'=>true,
            'data'=>$classFinished
        ];
        return $result;
    }

    /**
     * 获取学生指定active-classid 的对应的打卡记录，
     * sid          学生id
     * aid          active模板的id
     * cid          class 模板的id
     */
    public function getStudentSpecifiedCourseCheckInRecord($sid,$aid,$cid,$instance_cid){
        $con=[
            'rci.sid'=>$sid,
            'rci.instance_cid'=>$instance_cid,
            'ai.aid'=>$aid,
            'ci.id'=>$cid,
            'rci.status'=>1,
        ];
        //dump($instance_cid);
        $recordCheckIn=M("record_check_in as rci");
        $list=$recordCheckIn
            ->join("instance_class as ic on rci.instance_cid=ic.instance_cid")
            ->join("instance_active as ia on rci.instance_aid=ia.instance_aid")
            ->join("active_info as ai on ai.aid=ia.active_id")
            ->join("class_info as ci on ic.class_id=ci.id")
            ->where($con)
            ->field("rci.*,ai.aid,ai.active_name,ia.extend_name,ia.start_date,ci.id as cid,ci.class_name,ic.active_time,ci.duration,ia.belong")
            ->select();
        return $list;
    }


    /**
     * 获取指定具体课程下 指定学生的打卡记录
     * 三个参数不能同时为空  否则就返回空数组
     * @param $instance_cid  非必填   需要具体到具体的课程时候 才填
     * @param $instance_cid  非必填   需要具体到具体的活动的时候才填
     * @param array $sids    非必填    需要指定学生的时候才填，单个学员也是传数组
     * @return mixed
     */
    public function getStudentCheckInRecord($instance_cid='',$instance_aid='',$sids=[]){
        if(!$instance_cid&&!$instance_aid&&!count($sids)){
            return [];
        }
         if($instance_cid){
            $con['rci.instance_cid']=$instance_cid;
        }
        if($instance_aid){
            $con['rci.instance_aid']=$instance_aid;
        }
        if(count($sids)){
            $con['rci.sid']=['IN',$sids];
        }
        $recordCheckIn=M("record_check_in as rci");
        $list=$recordCheckIn
            ->join("instance_class as ic on rci.instance_cid=ic.instance_cid")
            ->join("instance_active as ia on rci.instance_aid=ia.instance_aid")
            ->join("active_info as ai on ai.aid=ia.active_id")
            ->join("class_info as ci on ic.class_id=ci.id")
            ->join('student_info as si on rci.sid=si.sid')
            ->where($con)
            ->field("rci.*,ai.aid,ai.active_name,ia.extend_name,ia.start_date,ci.id as cid,ci.class_name,ic.active_time,ci.duration,ia.belong,si.name")
            ->select();
        return $list;
    }



    /**
     * 学生考试成绩通过，升级
     * 由管理员进行调取操作升级
     */
    public function promoteGrade($sid,$aid,$cid){
        $this->course=new CourseModel();
        $relative=$this->course->getActiveRelative($aid);
        $courseArray=$this->course->getPreCourseAndAfterCourse($cid,$relative);
        $studentProgress=M('student_class_progress');
        $con=[
            'sid'=>$sid,
            'current_active'=>$aid,
            'current_class'=>$cid,
            'level_up'=>1
        ];
        if($courseArray['after']==null){
            $courseArray['after']['data']['cid']=0;
            $courseArray['after']['data']['uid']=0;
        }
        $studentProgress->where($con)->save([
            'level_up'=>0,
            'current_class'=>$courseArray['after']['data']['cid'],
            'current_unit'=>$courseArray['after']['data']['uid'],
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
    public function saveStudentExamScored($dat){
        $exam=M('student_exam_scored');
        if($dat['exid']){
            $save=checkParam($dat,['instance_uid','instance_aid','sid','scored']);
            $exam->where(['exid'=>$dat['exid']])->save($save);
        }
        else{
            $add=[
                'sid'=>$dat['sid'],
                'instance_aid'=>$dat['instance_aid'],
                'instance_uid'=>$dat['instance_uid'],
                'scored'=>$dat['scored']
            ];
            $exam->add($add);
        }
        return [
            'success'=>true,
            'info'=>'保存成功',
            //'data'=>
        ];
    }

    /**
     * 获取一群学生 某个单元的考试成绩
     * @param $sids
     * @param $instance_uid
     */
    public function listStudentExamScored($sids,$instance_uid){
        $exam=M('student_exam_scored');
        $con=[
            'sid'=>['IN',$sids],
            'instance_uid'=>['eq',$instance_uid]
        ];
        $res=$exam->where($con)->select();
        return [
            'success'=>true,
            'info'=>'查询到学生的成绩',
            'data'=>$res
        ];
    }

    /**
     * 获取指定学生 指定具体单元的考试成绩
     * @param $sid
     * @param $instance_uids  数组
     */
    public function getStudentExamScoreds($sid,$instance_uids){
        $exam=M('student_exam_scored');
        $con=[
            'sid'=>['eq',$sid],
            'instance_uid'=>['IN',$instance_uids]
        ];
        $res=$exam->where($con)->select();
        return [
            'success'=>true,
            'info'=>'获取指定学生指定具体单元的考试成绩',
            'data'=>$res
        ];
    }

    /**
     * 添加学生和教材的关系
     * @param $sid
     * @param $book_code
     */
    public function addBook($sid,$book_code){
        $studentBook=M('student_book');
        $con=[
            'sid'=>$sid,
            'book_code'=>$book_code
        ];
        $used=$studentBook->where(['book_code'=>$book_code])->find();
        if($used!=null){
            return [
                'success'=>false,
                'info'=>'该教材已经已经使用过了'
            ];
        }
        $exist=$studentBook->where($con)->find();
        if($exist!=null){
            return [
                'success'=>false,
                'info'=>'该学生已经拥有这本书籍'
            ];
        }else{
            $bid=$studentBook->add([
                'sid'=>$sid,
                'book_code'=>$book_code
            ]);
            //将非正式的学员变为正式学员
            $studentInfo=M('student_info');
            $student=$studentInfo->where(['sid'=>$sid])->find();
            $studentInfo->where(['sid'=>$sid])->save(['formal'=>1]);
            if($student['grade']=='学前班'||$student['grade']==''){
                $studentInfo->where(['sid'=>$sid])->save(['grade'=>'一年级']);
            }
            return [
                'success'=>true,
                'info'=>'添加学生书籍关系成功',
                'data'=>$bid
            ];
        }
    }

    /**
     * 删除学生教材关系
     * @param $sid              学生sid
     * @param $book_code        电子教材编码/
     */
    public function removeBook($sid,$book_code){
        $studentBook=M('student_book');
        $con=[
            'sid'=>$sid,
            'book_code'=>$book_code
        ];
        $studentBook->where($con)->delete();
    }

    /**
     * 获取指定学生的电子教材编号
     * @param $sid
     * @return array
     */
    public function listStudentBook($sid){
        $studentBook=M('student_book');
        $con=[
            'sid'=>$sid,
        ];

        $res=$studentBook->where($con)->select();
        return $res;
    }

    /**
     * 补录打卡
     * @param $dat
     * $mid             记录者的id
     * $ckid            补打的记录
     * $reason          补打原因
     */
    public function addCheckIn($mid,$ckid,$reason){
        $addCheckIn=M("add_check_in");
        $addCheckIn->add([
            'manager_id'=>$mid,
            'ckid'=>$ckid,
            'reason'=>$reason
        ]);
    }

    /**
     * 通过身份证来判断是否是管理者
     *
     * @param $id_number
     * 返回 对象， 包含  is_manager   和  manager_info
     * @return array
     */
    public function pendingStudentIsManager($id_number){

        $res=M('school_manager')->where(['id_number'=>$id_number])->field("username,manager_name,mid,id_number,status")->find();
        if($res){
            $extra=[
                'is_manager'=>true,
                'manager_info'=>$res
            ];
        }else{
            $extra=[
                'is_manager'=>false,
            ];
        }
        return $extra;
    }

    /**
     * 记录打卡时间
     * check_type   打卡类型   上课   下课  这里需要根据时间和课程时长来计算是上课还是下课   补打的时候有手动选择
     * check_time   打卡时间
     * sid          学生id
     * descr        备注 非必填
     * instance_aid  哪一个具体的活动
     * instance_cid  哪一个具体的课程
     */
    public function recordClock($dat){
        $needs=['check_type','sid','descr','instance_aid','instance_cid'
        ,'check_time'];
        // 由于调整打卡逻辑为只打一次卡因此默认所有的check_type 全部为下课
        if(!$dat['check_type']){
            //$checkType=$this->autoPendingRecordType($dat['instance_cid'],$dat['check_time']);
            //$dat['check_type']=$checkType;
            $dat['check_type']='下课';
        }
        $dat=checkParam($dat,$needs);
        $recordCheckIn=M("record_check_in as rc");
        $ckid=M("record_check_in")->add($dat);
        $_record=$recordCheckIn->join("instance_active as ia on rc.instance_aid=ia.instance_aid")
            ->join("instance_class as ic on ic.instance_cid=rc.instance_cid")
            ->join("class_info as ci on ic.class_id=ci.id")
            ->join("active_info as ai on ia.active_id=ai.aid")
            ->join("student_info as si on rc.sid=si.sid")
            ->join("school as s on s.scid=si.belong")
            ->where(['rc.ckid'=>$ckid])
            ->field("rc.*,ai.active_name,ai.aid,ia.extend_name,ia.start_date,ia.belong,ic.active_time,ci.class_name,ci.duration,s.level,si.formal,s.school_name")
            ->find();
        $books=M("book_info")->where(['sid'=>$dat['sid']])->select();
        $_record['books']=$books;
        return [
            'success'=>true,
            'info'=>'签到成功，但不代表有效，请注意签到时间',
            'data'=>$_record
        ];
    }

    /**
     * 自动判断该次打卡是上课 还是下课
     * @param $instance_cid
     * @param $check_time
     */
    public function autoPendingRecordType($instance_cid,$check_time){
        $instanceClass=M("instance_class as ic");
        $instance_class=$instanceClass
            ->join("class_info as ci on ic.class_id=ci.id")
            ->field("ic.*,ci.class_name,ci.duration")
            ->where(['ic.instance_cid'=>$instance_cid])
            ->find();
            
        $startTime=strtotime($instance_class['active_time']);
        $endTime=strtotime($instance_class['active_time'])+$instance_class['duration']*60;
        $_checkTime=strtotime($check_time);
        // dump($_checkTime);
        $up=$startTime-60*60;
        $down=$startTime+45*60;
        if($_checkTime<$down&&$_checkTime>$up){
            return '上课';
        }
        elseif($_checkTime>=$up&&$_checkTime<strtotime('tomorrow')){
            return '下课';
        }
        else{
            return '无效打卡';
        }
    }

    /**
     * 判断学生类别，能否打卡成功
     * 判断status   和   formal
     */
    public function autoPendingStudentTypeInfo($sid){
        $student=$this->where(['sid'=>$sid])->find();
        if($student['status']!=1){
            return [
                'success'=>false,
                'info'=>'该学员信息无效，请核查是否已经退学',
            ];
        }
        if($student['formal']!=1){
            return [
                'success'=>false,
                'info'=>'该学员为非正式学员,不能参与课程打卡',
            ];
        }

        return [
            'success'=>true
        ];
    }

    /**
     * 通过身份证号获取学院
     * @param $id_number
     * @return mixed
     */
<<<<<<< HEAD
    public function getStudentByIdNumber($id_number){
=======
    public function getStudentByIdNumbegetStudentListr($id_number){
>>>>>>> origin/master
        $student=M('student_info as si')
            ->join("left join school as s on s.scid=si.belong")
            ->where(['si.id_number'=>$id_number])
            ->field("si.*,s.school_name,s.level")
            ->find();
        //dump($student);
        return $student;
    }

    /**
     * 判断学员是哪一堂课有没有上
     */
    public function pendingStudentCourseFinish(){

    }

    /**
     * 从读卡器获取身份证信息
     *
     * 貌似后台用不上了，应该从前台获取信息

    public function getInfoFromIdCardReader(){
        exit;
    }
     */

    /**
     * 根据条件查询到学生并导出
     * 默认条件是scids   数组查询某一群机构下所有的学生并导出
     *
     * @param $dat
     * belongs           查询学校的   scid 的数组
     * 以下条件为可选
     * section          选择A  B 区
     * id_type          证件类型
     * id_number
     *
     * 完成
     */
    public function exportStudents($dat){
        vendor("PHPExcel.Classes.PHPExcel");
        vendor("PHPExcel.Classes.PHPExcel.IOFactory");
        vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Lizheng")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("health")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        // $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
        // $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        // $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        // $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

        // Add some data


        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '姓名')
            ->setCellValue('B1', '证件类型')
            ->setCellValue('C1', '证件号')
            ->setCellValue('D1', '性别')
            ->setCellValue('E1', '地址')
            ->setCellValue('F1', 'AB区')
            ->setCellValue('G1', '指导老师')
            ->setCellValue('H1', '所属机构')
            ->setCellValue('I1', '机构级别')
            ->setCellValue('J1', '有效性')
            ->setCellValue('K1', '录入管理员ID')
            ->setCellValue('L1', '更新管理员ID')
            ->setCellValue('M1', '录入时间')
            ->setCellValue('N1', '更新时间');



        $model=M('student_info as si');
        $con=$dat;
        if(isset($dat['scids'])){
            unset($con['scids']);
            $con['scid']=['IN',$dat['scids']];
        }
        // dump($con);
        // exit;
        $res=$model
            ->join("school as s on s.scid=si.belong")
            ->where($con)
            ->select();


        $numrows=count($res);
        // exit;
        if ($numrows>0)
        {
            $count=1;
            foreach ($res as $key => $data)
            {
                $count+=1;
                $l1="A"."$count";
                $l2="B"."$count";
                $l3="C"."$count";
                $l4="D"."$count";
                $l5="E"."$count";
                $l6="F"."$count";
                $l7="G"."$count";
                $l8="H"."$count";
                $l9="I"."$count";
                $l10="J"."$count";
                $l11="K"."$count";
                $l12="L"."$count";
                $l13="M"."$count";
                $l14="N"."$count";

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($l1, $data['name'])
                    // ->setCellValue($l2, $data['usercode'])
                    ->setCellValue($l2, $data['id_type'])
                    ->setCellValueExplicit($l3,$data['id_number'],\PHPExcel_Cell_DataType::TYPE_STRING)
                    ->setCellValue($l4, $data['sex'])
                    ->setCellValue($l5, $data['address'])
                    ->setCellValue($l6, $data['section'])
                    ->setCellValue($l7, $data['direct_teacher'])
                    // ->setCellValueExplicit($l3,'武汉市N1381',PHPExcel_Cell_DataType::TYPE_STRING)
                    // ->setCellValueExplicit($l3,PHPExcel_Cell_DataType::TYPE_STRING)
                    ->setCellValue($l8, $data['school_name'])
                    ->setCellValue($l9, $data['level'])
                    ->setCellValue($l10, $data['status'])
                    ->setCellValue($l11, $data['creator_id'])
                    ->setCellValue($l12, $data['update_id'])
                    ->setCellValue($l13, $data['create_time'])
                    ->setCellValue($l14, $data['update_time']);
                // ->setCellValue($l14,'1')
                // ->setCellValue($l15,$data['name'])
                // ->setCellValue($l16,$data['sex'])
                // ->setCellValue($l17,$data['birthday']);
            }
        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle("学员列表");


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel5)
        header('pragma:public');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=学员列表.xlsx');
        header('Cache-Control: max-age=0');

        // header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        // header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印

        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        // $objWriter->save('myexchel.xlsx');

        exit;
    }

    /**
     * 学生转学，由一个机构发起，学生进入待转表里等待对方机构接受
     * @param $sid      学生的sid
     * @param $from_school     转出机构的scid
     * @param $to_school       接受机构的scid
     * @param $sender   转出操作者
     *
     * @return 素质三联
     */
    public function sendStudent($sid,$from_school,$to_school,$sender){
        $sendStudent=M('send_student');
        $con=[
            'sid'=>$sid,
            'from_school'=>$from_school,
            'to_school'=>$to_school,
            'finished'=>0
        ];
        $exist=$sendStudent->where($con)->find();
        if($exist!=null){
            return [
                'success'=>false,
                'info'=>'该学生已经处于转学状态了'
            ];
        }
        else{
            $sendId=$sendStudent->add([
                'sid'=>$sid,
                'from_school'=>$from_school,
                'to_school'=>$to_school,
                'sender'=>$sender
            ]);
            return [
                'success'=>true,
                'info'=>'已经发送转学申请',
                'data'=>$sendId
            ];
        }
    }

    /**
     * 接受学生转学，待转表里的finished 状态置1
     * @param $sid
     * @param $from_school
     * @param $to_school
     * @param $receiver   接受操作者
     * @return 素质三联
     */
    public function receiveStudent($sid,$from_school,$to_school,$receiver){
        $sendStudent=M('send_student');
        $con=[
            'sid'=>$sid,
            'from_school'=>$from_school,
            'to_school'=>$to_school,
            'finished'=>0
        ];
        $exist=$sendStudent->where($con)->find();
        if($exist!=null){
            $sendStudent->where($con)->save([
                'receiver'=>$receiver,
                'finished'=>1
            ]);
            //强制更新学生的所属校区 这里要不要更新updater？
            M("student_info")->where(['sid'=>$sid])->save(['belong'=>$to_school]);
            return [
                'success'=>true,
                'info'=>'接受学生成功'
            ];
        }
        else{
            return [
                'success'=>false,
                'info'=>'没有找到该学生的转学信息'
            ];

        }
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
     *
     * @return [] 查询到学生的数组
     */
    public function listWaitReceiveStudents($dat){
        $needs=['to_school','from_school','finished','sender','receiver','sid'];
        $con=checkParam($dat,$needs);
        if(!isset($con['finished'])){
            $con['finished']=0;
        }
        $res=M('send_student  as ss')
            ->join("student_info as si on ss.sid=si.sid")
            ->join('school as sf on ss.from_school=sf.scid')
            ->join('school as st on ss.to_school=st.scid')
            ->where($con)
            ->field("ss.*,si.*,sf.school_name as from_school_name,st.school_name as to_school_name")
            ->select();
        return $res;
    }


    /**
     * 关于重修的问题，一个学生可能本单元的课程学习的不好
     * 考试无法通过，所以可能需要重修，就会产生新的 instance_cid打卡记录
     * 对应的仍为同一个 active 模板
     * 因此需要指定学生的课程进度到指定的某一个课程 或者 某一个单元
     */
    public function setClassPrograss(){

    }


    /**
     * 添加 编辑 学员证书状态
     * @param $dat
     * sctid        修改其他参数时必传
     * sids         一群学生sid
     * cert_id      证书ctid  来自 cert_info 表
     * passed       是否通过
     */
    public function saveStudentCert($dat){
        $studentCert=M('student_cert');
        foreach ($dat['sids'] as $key => $va) {
            $ndat=$dat;
            unset($ndat['sids']);
            $ndat['sid']=$va;
            if($dat['sctid']){
                # code...
                $needs=['sid','cert_id','passed'];
                $save=checkParam($ndat,$needs);
                $studentCert->where(['sctid'=>$dat['sctid']])->save($save);
            }
            else{
                $add=$studentCert->add([
                    'sid'=>$ndat['sid'],
                    'cert_id'=>$ndat['cert_id'],
                    'passed'=>$ndat['passed']
                ]);
                $studentCert->join('cert_info on student_cert.cert_id=cert_info.ctid')
                    ->where(['student_info.sctid'=>$add])
                    ->find();
                // return $studentCert;
            }
        }

    }

    /**
     * 获取一群学生的证书信息  若ctid为空 则获取这批学生的所有证书信息
     * @param $sids
     * @param string $ctid          证书id  来自 cert_info 表的ctid
     * @param boolen $passed        是否已经通过了考试取得证书  默认0
     *
     */
    public function listStudentCert($sids,$ctid=''){
        $studentCert=M('student_cert as sc');
        $con=[
            'sc.sid'=>['IN',$sids]
        ];
        if($ctid){
            $con['sc.cert_id']=$ctid;
        }
        $list=$studentCert->join("cert_info cti on sc.cert_id=cti.ctid")
            ->where($con)
            ->select();
        return $list;
    }



}