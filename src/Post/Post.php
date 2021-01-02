<?php
namespace LizusVitara\Post;

/**
* Post
* 文章类的根类
* 主题中使用的时候一定要使用App\Post\Post来继承
*/
class Post extends \LizusVitara\Model\SingleData 
{
    protected $type='post';
    protected $method=[
        'data'=>'\get_post',
        'get'=>'\get_post_meta',
        'set'=>'\update_post_meta',
        'delete'=>'\delete_post_meta',
    ];
    
    //所有文章中都有的key
    private $basic_keys=[
        'views'=>'\intval',//文章阅读数
    ];
    
    //不允许使用set来进行设置的key
    protected $not_set=[
        'views',
    ];
    
    /**
    * metaKeysInit
    * 方便子孙类扩展: 
    * return array_merge(parent::metaKeysInit(),['testKey'=>'\strval',]);
    * @return void
    */
    protected function metaKeysInit(){
        return $this->basic_keys;
    }
    
    /**
    * addViews
    * 增加文章阅读数
    * @return void
    */
    public function addViews(){
        if(!$this->exist()) return $this;
        $views=$this->views; 
        $views++;
        $this->_set('views',$views);
        $uid=$this->post_author;
        $u=new \LizusVitara\User\User($uid);
        $u->addPostViews(1);
        return $this;
    }
    
    /**
    * setViews
    * 直接设置文章阅读数
    * @param  mixed $views
    * @return void
    */
    public function setViews($views){
        if(!$this->exist()) return $this;
        $this->_set('views',intval($views));
        return $this;
    }
    
    /**
    * getExcerpt
    * 获取文章摘要
    * @param  int $len
    * @return string
    */
    public function getExcerpt($len=255){
        $data=$this->post_excerpt;
        if(empty($data)) $data=$this->post_content;
        return \LizusVitara\cut_text($data,$len);
    }
    
    /**
     * getContent
     * 获取文章内容
     * @return string
     */
    public function getContent(){
        return \apply_filters('the_content',$this->post_content);
    }
    
    /**
     * getTitle
     * 获取文章标题
     * @return string
     */
    public function getTitle(){
        return \get_the_title($this->id);
    }
    
    /**
    * getTime
    * 根据需要的格式输出时间
    * @param  string $format
    * @return string
    */
    public function getTime($format){
        return \get_the_time($format,$this->id);
    }
    
    /**
     * getRealTime
     * 获取时间值
     * @return int
     */
    public function getRealTime(){
        return \strtotime($this->getTime('Y-m-d H:i:s'));
    }
    
    /**
    * getPermalink
    * 获取文章链接地址
    * @return string
    */
    public function getPermalink(){
        return \get_permalink($this->id);
    }
    
    /**
    * getTerms
    * 获取文章所有的归属类目
    * @return array
    */
    public function getTerms(){
        $taxs=get_object_taxonomies($this->post_type);
        $rs=[];
        if(!empty($taxs)) {
            foreach ($taxs as $tax) {
                $terms=get_the_terms($this->id,$tax);
                if(!empty($terms) && !\is_wp_error($terms)) $rs[$tax]=$terms;
            }
        }
        return $rs;
    }
    
    protected function has_post_thumbnail(){
        return \has_post_thumbnail($this->id);
    }

    /**
     * getContentImages
     * 获取文章内容中的所有图片地址
     * @return array
     */
    public function getContentImages(){
        $rs=[];
        $content = $this->post_content;
        $c_img='~https?://[^\s]+\.(jpg|jpeg|gif|png)~';
        if(\preg_match_all( $c_img, $content, $imgs )){
            $rs=$imgs[0];
        }
        return $rs;
    }
    
    /**
    * getPostAttachments
    * 获取文章的所有上传图片,返回为所有图片地址
    *
    * @return array
    */
    public function getPostAttachments(){
        $images_array=[];
        $args=array(
            'post_type'=>'attachment',
            'post_mime_type'=>'image',
            'post_parent'=>$this->id,
        );
        $images=\get_children($args);
        if(!empty($images)){
            foreach ($images as $attachment_id => $attachment){
                $image_src=\wp_get_attachment_image_src($attachment_id, 'full');
                $images_array[]=$image_src;
            }
        }
        return $images_array;
    }
    
    /**
    * getThumb
    * 获取文章的封面图
    * @return string
    */
    public function getThumb(){
        $src='';
        $pid=$this->id;
        if ($this->has_post_thumbnail()){ //如果有设置封面图
            $imageid=\get_post_thumbnail_id($pid);
            $image=\wp_get_attachment_image_src($imageid,'full');
            $src=$image[0];
        }
        if(empty($src)){ //如果内容中有图片
            $images=$this->getContentImages($pid);
            if(empty($images)){
                $images=$this->getPostAttachments($pid);//如果有在这个文章里上传图片
            }
            if(!empty($images)){
                $src=array_shift($images);
            }
        }
        return $src;
    }
}