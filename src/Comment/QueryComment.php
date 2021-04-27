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
            'paged'=>1,
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
        $offset=0;//偏移量，普通评论要把所有置顶的评论偏移掉
        $original_num=$this->args['number'] ?? -1;
        $num=$original_num;

        /**
         * 如果有设置post_id，则用于取文章评论，首先要取该文章评论的置顶评论
         */
        if(isset($this->args['post_id']) && $this->args['post_id']>0) {
            //先获取置顶评论及其子评论
            $args=array_merge($this->args,[
                'meta_key'=>\LizusFunction\v_key('sticky','comment'),
                'meta_value'=>'yes',
                'number'=>'',
                'paged'=>1,
            ]);
            $uq=new \WP_Comment_Query($args);
            $sticky_num=$uq->found_comments;
            $rs=$uq->comments;
            $count+=count($rs);
            $sticky_num=count($rs);
            foreach ($rs as $item) {
                if($this->args['paged']==1) {
                    $data[]=$this->get_item($item->comment_ID);
                }

                $args=array_merge($this->args,[
                    'parent'=>$item->comment_ID,
                    'number'=>'',
                    'paged'=>1,
                ]);
                $uq=new \WP_Comment_Query($args);
                $rs=$uq->comments;
                foreach ($rs as $it) {
                    if($this->args['paged']==1) {
                        $data[]=$this->get_item($it->comment_ID);
                    }

                    $exclude[]=$it->comment_ID;

                    $children=$it->get_children(['format'=>'flat']);
                    foreach ($children as $itt) {
                        if($this->args['paged']==1) {
                            $data[]=$this->get_item($itt->comment_ID);
                        }
                        $exclude[]=$itt->comment_ID;
                    }
                }
                $exclude[]=$item->comment_ID;
            }

            if($original_num>0) {
                if($this->args['paged']<=1) {
                    $num=$original_num-$sticky_num;
                }else {
                    $offset=$original_num-$sticky_num;
                    if($offset<0) $offset=0;
                    $offset+=($this->args['paged']-2)*$this->args['number'];
                }
            }
        }

        //置顶评论之后再获取其他评论
        $args=array_merge($this->args,[
            'comment__not_in'=>$exclude,
            'offset'=>$offset,
            'number'=>$num,
        ]);
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