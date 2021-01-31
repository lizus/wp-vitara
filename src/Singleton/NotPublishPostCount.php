<?php
namespace LizusVitara\Singleton;

/**
* NotPublishPostCount
* 用于获取未发布的各类文章统计，后台方便显示数字提醒
* 通过ajax来获取数据，使用action=get_new_custom_posts
*/
class NotPublishPostCount {
    private static $_instance=null;
    private function __construct(){
        add_action('transition_post_status', [&$this,'transition_post_status'],10,3);
        add_action('save_post', [&$this,'save_post'],10,3);
        add_action('wp_ajax_get_new_custom_posts', [&$this,'get_new_custom_posts']);
        add_action('wp_ajax_nopriv_get_new_custom_posts', [&$this,'get_new_custom_posts']);
    }
    public static function getInstance(){
        if(!self::$_instance instanceof self) {
            self::$_instance= new self;
        }
        return self::$_instance;
    }
    private function __clone() {
        trigger_error('Clone is not allow!',E_USER_ERROR);
    }
    
    public function transition_post_status($new,$old,$post){
        if ($new != $old) {
            $key=$this->key($post->post_type.'_count');
            \delete_transient($key);
        }
    }
    
    public function save_post($pid,$post,$update){
        if($post->post_type == 'nav_menu_item') return;
        if(!$update && $post->post_status != 'publish') {
            $key=$this->key($post->post_type.'_count');
            \delete_transient($key);
        }
    }
    
    public function get_new_custom_posts(){
        $opt=array();
        $error=array();
        $data=array();
        if (\is_user_logged_in()) {
            $post_types=\get_post_types(array(
                'show_ui'=>true,
                'public'=>true,
            ));
            foreach ($post_types as $key => $pt) {
                $data[$pt]=$this->get_count($pt);
            }
        }else{
            $error[]='你还没有登录喔';
        }
        
        $opt['error']=$error;
        $opt['data']=$data;
        echo json_encode($opt);
        die();
    }

    private function key($key){
        return \LizusFunction\v_key($key,'post').'_unpublish_';
    }

    private function get_count($post_type){
        $key=$this->key($post_type.'_count');
        if (false === ($count = \LizusFunction\get_transient($key))) {
            $count=0;//文章
            $args=array(
                'posts_per_page'=>1,
                'post_type'=>$post_type,
                'post_status'=>array('draft','pending'),
            );
            $pq=new \WP_Query($args);
            if ($pq->have_posts()) $count=$pq->found_posts;
            \wp_reset_postdata();
            \set_transient($key,$count);
        }
        return $count;
    }
}