<?php
namespace LizusVitara\Comment;

/**
* QueryComment
* WP_Comment_Query查询类，子类在继承的时候只需实现get_item即可
*/
abstract class QueryComment extends \LizusVitara\Model\QueryData 
{    
    /**
     * default_args
     * 默认的查询条件
     * https://developer.wordpress.org/reference/classes/WP_Comment_Query/__construct/
     * @return void
     */
    protected function default_args(){
        return [
            'number'=>20,
            'no_found_rows'=>false,//用于获取总条数
            'type'=>'comment',
            'status'=>'approve',
            'hierarchical'=>'threaded',
        ];
    }
        
    /**
     * query
     * 查询方法，一般不需要重构
     * @return void
     */
    protected function query(){
        $data=[];
        $total=0;
        $count=0;
        $exclude=[];
        $sticky_num=0;
        $original_num=$this->args['number'] ?? -1;
        $num=0;

        //先获取置顶评论及其子评论
        $args=array_merge($this->args,[
            'meta_key'=>\LizusFunction\v_key('sticky','comment'),
            'meta_value'=>'yes',
        ]);
        $uq=new \WP_Comment_Query($args);
        $total+=$uq->found_comments;
        $rs=$uq->comments;
        $count+=count($rs);
        foreach ($rs as $item) {
            $sticky_num++;
            $data[]=$this->get_item($item->comment_ID);

            $args=array_merge($this->args,[
                'parent'=>$item->comment_ID,
            ]);
            $uq=new \WP_Comment_Query($args);
            $rs=$uq->comments;
            foreach ($rs as $it) {
                $data[]=$this->get_item($it->comment_ID);

                $exclude[]=$it->comment_ID;

                $children=$it->get_children(['format'=>'flat']);
                foreach ($children as $itt) {
                    $data[]=$this->get_item($itt->comment_ID);
                    $exclude[]=$itt->comment_ID;
                }
            }
            $exclude[]=$item->comment_ID;
        }

        if($original_num>0) {
            $num=$original_num-$sticky_num;
        }

        if($original_num<0 || $num>0) {
            //置顶评论之后再获取其他评论
            $args=array_merge($this->args,[
                'comment__not_in'=>$exclude,
            ]);
            if($num>0) {
                $args['number']=$num;
            }
            $uq=new \WP_Comment_Query($args);
            $total+=$uq->found_comments;
            $rs=$uq->comments;
            $count+=count($rs);
            foreach ($rs as $item) {
                $data[]=$this->get_item($item->comment_ID);
                $children=$item->get_children(['format'=>'flat']);
                foreach ($children as $it) {
                    $data[]=$this->get_item($it->comment_ID);
                }
            }
        }

        return [
            'data'=>$data,
            'total'=>$total,
            'count'=>$count,
        ];
    }
    
    /**
    * get_item
    * 在WP_Comment_Query的loop中对每一个comment进行数据清洗，获得需要的信息
    * @param  int $id
    * @return array
    */
    abstract protected function get_item($id);
}