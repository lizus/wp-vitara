<?php
namespace LizusVitara\User;

/**
* QueryPost
* WP_User_Query查询类，子类在继承的时候只需实现get_item即可
*/
abstract class QueryUser extends \LizusVitara\Model\QueryData 
{
    protected function default_args(){
        return [
            'orderby'=>'ID',
            'order'=>'DESC',
            'number'=>1,
        ];
    }
    
    protected function query(){
        $data=[];
        $uq=new \WP_User_Query($this->args);
        $users=$uq->get_results();
        foreach ($users as $u) {
            $data[]=$this->get_item($u->ID);
        }
        return [
            'data'=>$data,
            'total'=>$uq->get_total(),
            'count'=>count($users),
        ];
    }
    
    /**
    * get_item
    * 在WP_User_Query的loop中对每一个post进行数据清洗，获得需要的信息
    * @param  int $id
    * @return array
    */
    abstract protected function get_item($id);
}