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
    public function preparePromoteCourseProgress($dat=''){
        if($dat==''){
            $dat=getParam();
        }
        $auc=$this->course->getAUCidByInstanceCid($dat['instance_cid']);
        $relative=$this->course->getActiveRelative($auc['active_id']);
        $courseArray=$this->course->getPreCourseAndAfterCourse($auc['class_id'],$relative);
        $checkIn=$this->student->pendingCheckInTimeError($dat['sid'],$auc['active_id'],$auc['class_id'],$dat['instance_cid']);

        //获取当前的进度记录
        $progress=$this->student->getCurrentCourse($dat['sid'],$auc['active_id']);
        if($progress==null){
            //如果当前课程进度为应先添加当前的课程进度
            //这里需要实地测试一下
            $this->student->promoteCourseProgress($dat['sid'],$courseArray['current']['aid'],$courseArray['current']['uid'],$courseArray['current']['cid']);
        }

        // dump($checkIn);
        $existClassHistory=$this->student->pendingClassHistory($dat['sid'],$auc['active_id'],$auc['class_id']);
        $this->student->saveClassHistory($dat['sid'],$auc['active_id'],$auc['class_id'],$dat['instance_cid'],$checkIn['data']);
        if(!$existClassHistory){
            //之前没上过这门课的记录
            if($checkIn['data']){
                //查询到打卡记录且没问题
                if($courseArray['after']!=null){
                    //下一堂课存在，不论是否是同一个单元
                    // dump($courseArray);
                    if($courseArray['after']['same_unit']=='yes'){
                        //相同的单元下直接升到下一堂课进度
                        $this->student->promoteCourseProgress($dat['sid'],$courseArray['after']['data']['aid'],$courseArray['current']['uid'],$courseArray['after']['data']['cid']);
                    }
                    elseif($courseArray['current']==null){
                        //当前课程为空，通常为某个活动的第一堂课，新增当前课堂进度
                        //感觉这个逻辑判断似乎有问题，当前课程应该一定存在，如果不存在，则说明活动模板，单元模板有改动
                        //$this->student->promoteCourseProgress($dat['sid'],$courseArray['current']['aid'],$courseArray['current']['uid'],$courseArray['current']['cid']);
                        //保存下一堂课的进度，因为已经判明打卡是没问题的
                        $this->student->promoteCourseProgress($dat['sid'],$courseArray['after']['data']['aid'],$courseArray['after']['data']['uid'],$courseArray['after']['data']['cid']);
                    }
                    else{
                        //当前课程存在并且下一堂课不是同一个单元，则进入升级的状态
                        $this->student->readyToLevelUp($dat['sid'],$courseArray['current']['aid'],$courseArray['current']['uid'],$courseArray['current']['cid']);
                    }
                }
                else{
                    //下一堂课不存在，基本上就是本活动的最后一次课的情况的，进入升级状态
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
     * 根据aid和sids 获取一群同学 的上课历史纪录
     * @param $sids
     * @param $aid
     */
    public function getStudentActiveCourseProgress(){
        $dat=getParam();
<<<<<<< HEAD
        $res=$this->student->getStudentActiveCourseProgress($dat['sids'],$dat['aid']);
=======
        $res=$this->student->getStudentActiveCourseProgress($dat);
>>>>>>> origin/master
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
        $current=$this->student->getCurrentCourse([$dat['sid']],$dat['aid']);
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
    public function listStudentExamScored(){
        $dat=getParam();
        $res=$this->student->listStudentExamScored($dat['sids'],$dat['instance_uid']);
        $this->ajaxReturn($res);
    }

    /**
     * 添加学生和教材的关系
     * @param $sid
     * @param $book_code
     */
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
        if($dat['id_number']){
            $extraInfo=$this->student->pendingStudentIsManager($dat['id_number']);
        }
        if(!$dat['sid']){
            $res=$this->student->getStudentByIdNumber($dat['id_number']);
            if(!$res['sid']){
                $this->ajaxReturn([
                    'success'=>false,
                    'info'=>'没有找到该学生，应该先录入该学生'
                ]);
            }
            // 关闭验证非正式学员
            //if($res['formal']!=1){
            //    $this->ajaxReturn([
            //        'success'=>false,
            //        'info'=>'该学生是非正式学员，不能进行打卡操作'
            //    ]);
            //}
            $nDat['sid']=$res['sid'];
        }
        unset($nDat['id_number']);
        if(is_array($nDat['instance_cid'])){
            foreach($nDat['instance_cid'] as $k => $va){
                $_nDat=$nDat;
                $_nDat['instance_cid']=$va;
                $res=$this->student->recordClock($_nDat);
                if($res['data']['check_type']=='下课'){
                    $this->preparePromoteCourseProgress($_nDat);
                }
            }
        }else{
            $res=$this->student->recordClock($nDat);
            if($res['data']['check_type']=='下课'){
                $this->preparePromoteCourseProgress($nDat);
            }
        }
<<<<<<< HEAD
        $res['extra_info']=$extraInfo;
=======
>>>>>>> origin/master
        $this->ajaxReturn($res);
    }



    /**
     * 记录一群打卡时间
     * 接受的是json数组
     * [
     * {check_type:'上课',check_time:'2019-03-28 14:10:12',instance_aid:'1','instance_cid:'3','id_number':'510113199903043435',descr:'测试'},
     * {check_type:'上课',check_time:'2019-03-28 14:10:12',instance_aid:'1','instance_cid:'3','id_number':'510113199903043435',descr:'测试'},
     * ]
     *
     * 内部参考
     * check_type   本地智能判断，打卡类型   上课   下课  这里需要根据时间和课程时长来计算是上课还是下课   补打的时候有手动选择
     * check_time       打卡时间
     * id_number        身份证号            注意 控制器里接受的是身份证号 然后去换取的 sid
     * descr            备注 非必填
     * instance_aid     哪一个具体的活动
     * instance_cid     哪一个具体的课程
     */
    public function recordAllClock(){
        $dat=getParam();
        $result=[];
        $fail=[];
        foreach($dat as $k => $va){
            $nDat=$va;
            if(!$va['sid']){
                $res=$this->student->getStudentByIdNumber($va['id_number']);
                if(!$res['sid']){
                    $fail[]=[
                        'success'=>false,
                        'info'=>'没有找到身份证号为'.$va['id_number'].'学生，应该先录入该学生'
                    ];
                }
                // 关闭验证非正式学员
                //elseif($res['formal']!=1){
                //    $fail[]=[
                //        'success'=>false,
                //        'info'=>$va['id_number'].'学生是非正式学员，不能进行打卡操作'
                //    ];
                //}
                else{
                    $nDat['sid']=$res['sid'];
                    unset($nDat['id_number']);

                    if(is_array($nDat['instance_cid'])){
                        foreach($nDat['instance_cid'] as $k => $va){
                            $_nDat=$nDat;
                            $_nDat['instance_cid']=$va;
                            $result[]=$r=$this->student->recordClock($_nDat);
                            if($r['data']['check_type']=='下课'){
                                $this->preparePromoteCourseProgress($_nDat);
                            }
                        }
                    }else{
                        $result[]=$r=$this->student->recordClock($nDat);
                        if($r['data']['check_type']=='下课'){
                            $this->preparePromoteCourseProgress($nDat);
                        }
                    }
                }

            }
            else{
                unset($nDat['id_number']);
                if(is_array($nDat['instance_cid'])){
                    foreach($nDat['instance_cid'] as $k => $va){
                        $_nDat=$nDat;
                        $_nDat['instance_cid']=$va;
                        $result[]=$r=$this->student->recordClock($_nDat);
                        if($r['data']['check_type']=='下课'){
                            $this->preparePromoteCourseProgress($_nDat);
                        }
                    }
                }else {
                    $result[] = $r = $this->student->recordClock($nDat);
                    if ($r['data']['check_type'] == '下课') {
                        $this->preparePromoteCourseProgress($nDat);
                    }
                }

            }
        }
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'批量处理完成',
            'data'=>$result,
            'fail'=>$fail,
        ]);
    }

    /**
     * 获取学生指定active-classid 的对应的打卡记录，
     * sid          学生id
     * aid          active模板的id
     * cid          class 模板的id
     */
    public function getStudentSpecifiedCourseCheckInRecord($sid,$aid,$cid,$instance_cid){

    }

    /**
     * 获取指定具体课程下 指定学生的打卡记录
     * 三个参数不能同时为空  否则就返回空数组
     * @param $instance_cid  非必填   需要具体到具体的课程时候 才填
     * @param $instance_cid  非必填   需要具体到具体的活动的时候才填
     * @param array $sids    非必填    需要指定学生的时候才填，单个学员也是传数组
     * @return mixed
     */
    public function getStudentCheckInRecord(){
        $dat=getParam();
        $res=$this->student->getStudentCheckInRecord($dat['instance_cid'],$dat['instance_aid'],$dat['sids']);
        $this->ajaxReturn([
            'success'=>true,
            'data'=>$res,
            'info'=>'查询指定学生群体/指定课程/指定活动的课程'
        ]);

    }

    /**
     * 通过身份证号获取学员信息
     * @param $id_number
     */
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

    /**
     * 添加 编辑 学员证书状态
     * @param $dat
     * scids        一群学生id
     * sctid        修改其他参数时必传
     * cert_id      证书ctid  来自 cert_info 表
     * passed       是否通过
     */
    public function saveStudentCert(){
        $dat=getParam();
        $res=$this->student->saveStudentCert($dat);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'操作成功',
            'data'=>$res
        ]);
    }

    /**
     * 获取一群学生的证书信息  若ctid为空 则获取这批学生的所有证书信息
     * @param $sids
     * @param string $ctid          证书id  来自 cert_info 表的ctid  非必传
     * @param boolen $passed        是否已经通过了考试取得证书  默认0
     *
     */
    public function listStudentCert(){
        $dat=getParam();
        $res=$this->student->listStudentCert($dat['sids'],$dat['ctid']);
        $this->ajaxReturn([
            'success'=>true,
            'info'=>'操作成功',
            'data'=>$res
        ]);
    }

    /**
     * 补打操作
     *  * * check_type   打卡类型   上课   下课  这里需要根据时间和课程时长来计算是上课还是下课   补打的时候有手动选择
     * check_time       打卡时间
     * id_number        身份证号            注意 控制器里接受的是身份证号 然后去换取的 sid
     * descr            备注 非必填
     * instance_aid     哪一个具体的活动
     * instance_cid     哪一个具体的课程
     */
    public function addCheckIn(){
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
            if($res['formal']!=1){
                $this->ajaxReturn([
                    'success'=>false,
                    'info'=>'该学生是非正式学员，不能进行打卡操作'
                ]);
            }
            $nDat['sid']=$res['sid'];
        }
        unset($nDat['id_number']);
        $res=$this->student->recordClock($nDat);
        if($res['success']==true){
            if($res['data']['check_type']=='下课'){
                $this->preparePromoteCourseProgress($nDat);
            }
            $ckid=$res['data']['ckid'];
            $this->student->addCheckIn($dat['mid'],$ckid,$dat['reason']);
        }
    }
}