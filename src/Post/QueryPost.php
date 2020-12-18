<?php
namespace LizusVitara\Post;

/**
 * QueryPost
 * WP_Query查询类，子类在继承的时候只需实现get_item即可
 * 主题包中必须有\App\QueryPost\QueryPost
 */
abstract class QueryPost extends \LizusVitara\Model\QueryData 
{
    protected function default_args(){
        return [
            'posts_per_page'=>1,
            'post_type'=>'post',
            'post_status'=>'publish',
            'orderby'=>'date',
            'order'=>'DESC',
        ];
    }
    
    protected function query(){
        $pq=new \WP_Query($this->args);
        $data=[];
        if ($pq->have_posts()){
            while ($pq->have_posts()){
                $pq->the_post();
                $data[]=$this->get_item(get_the_ID());
            }
        }
        wp_reset_postdata();
        return [
            'data'=>$data,
            'total'=>$pq->found_posts,
            'count'=>$pq->post_count,
        ];
    }
    
    /**
    * get_item
    * 在WP_Query的loop中对每一个post进行数据清洗，获得需要的信息
    * @param  int $id
    * @return array
    */
    abstract protected function get_item($id);
}