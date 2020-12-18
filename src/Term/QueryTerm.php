<?php
namespace LizusVitara\Term;

/**
* QueryTerm
* WP_Term_Query查询类，子类在继承的时候只需实现get_item即可
*/
abstract class QueryTerm extends \LizusVitara\Model\QueryData 
{    
    /**
     * default_args
     * 默认的查询条件
     * https://developer.wordpress.org/reference/classes/WP_Term_Query/__construct/
     * @return void
     */
    protected function default_args(){
        return [
        ];
    }
        
    /**
     * query
     * 查询方法，一般不需要重构
     * @return void
     */
    protected function query(){
        $data=[];
        $uq=new \WP_Term_Query($this->args);
        $rs=$uq->get_terms();
        foreach ($rs as $item) {
            $data[]=$this->get_item($item->term_id);
        }
        return [
            'data'=>$data,
            'total'=>count($rs),
            'count'=>count($rs),
        ];
    }
    
    /**
    * get_item
    * 在WP_Term_Query的loop中对每一个post进行数据清洗，获得需要的信息
    * @param  int $id
    * @return array
    */
    abstract protected function get_item($id);
}