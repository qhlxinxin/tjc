<?php
/**
 * Created by PhpStorm.
 * User: lizheng
 * Date: 2019/2/24
 * Time: 15:03
 */

/**
 * 检测是否含有变量
 */
function checkParam($param=[],$needs=[]){
    $res=[];
    foreach($param as $k=>$va ){
        $exist=in_array($k,$needs);
        if($exist){
            $res[$k]=$param[$k];
        }
    }
    return $res;
}

//获取page
function getCurrentPage($dat=''){
    $temp=file_get_contents("php://input");
    if(is_json($temp)==true){
        $temp=json_decode($temp,true);
        $page=$temp['page'];
    }
    else{
        $page=I('param.page');
    }
    //$page=I('param.page');
    if(!$page){
        if(!$dat['page']){
            $page=1;
        }
        else{
            $page=$dat['page'];
        }
    }
    return $page;
}
//获取page num
function getPageSize($dat=''){
    $temp=file_get_contents("php://input");
    if(is_json($temp)==true){
        $temp=json_decode($temp,true);
        $pageSize=$temp['page_num'];
    }
    else{
        $pageSize=I('param.page_num');
    }
    // $pageSize=I('param.page_size');
    if(!$pageSize){
        if($dat['page_num']){
            return $dat['page_num'];
        }
        else{
            return 30;
        }
    }
    return $pageSize;
}


function getParam(){
    $dat=file_get_contents("php://input");
    if(is_json($dat)==true){
        $dat=json_decode($dat,true);
    }
    else{
        $dat=I('param.');
    }
    return $dat;
}

function is_json($data = '', $assoc = false) {
    $data = json_decode($data, $assoc);
    if ($data && (is_object($data)) || (is_array($data) && !empty(current($data)))) {
        return $data;
    }
    return false;
}