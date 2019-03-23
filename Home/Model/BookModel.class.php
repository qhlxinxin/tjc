<?php
/**
 * Created by PhpStorm.
 * User: qhlxi
 * Date: 2019/1/15
 * Time: 23:11
 */

namespace Home\Model;
use Think\Model;

class BookModel extends Model
{
    protected $tableName = 'book_info';
    protected $tablePrefix = '';

    /**
     * 添加/编辑 书本信息
     * bid   book id   修改其他参数时候必传
     */
    public function saveBookInfo($dat){
        if($dat['bid']){
            $needs=['book_name','book_code','status'];
            $save=checkParam($dat,$needs);
            return $this->where(['bid'=>$dat['bid']])->save($save);
        }
        else{
            return $this->add([
                'book_name'=>$dat['book_name'],
                'book_code'=>$dat['book_code']
            ]);
        }
    }

    /**
     * 机械生成book的编码
     * rule 规则
     * number  生成多少本
     */
    public function makeBookCode($rule,$number){

    }

    public function getRule($rule){
        switch ($rule){
            case 'TJC':
                break;
            case 'DL':
                break;
        }
    }
}