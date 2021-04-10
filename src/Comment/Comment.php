<?php
namespace LizusVitara\Comment;

/**
* Comment
* 类目处理用根类
* 主题中使用的时候一定要使用App\Comment\Comment来继承
*/
class Comment extends \LizusVitara\Model\SingleData 
{
    protected $type='comment';
    protected $method=[
        'data'=>'\get_comment',
        'get'=>'\get_comment_meta',
        'set'=>'\update_comment_meta',
        'delete'=>'\delete_comment_meta',
    ];
    
    //所有类目中都有的key
    private $basic_keys=[
        'sticky'=>'\strval',//判断评论置顶，置顶则值为yes
    ];
    
    //不允许使用set来进行设置的key
    protected $not_set=[
        'sticky',
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
     * isSticky
     * 用于判断当前评论是否是置顶评论
     * @return void
     */
    public function isSticky(){
        return $this->sticky == 'yes';
    }
    /**
     * setSticky
     * 设置该评论为置顶
     * @return this
     */
    public function setSticky(){
        return $this->_set('sticky','yes');
    }    
    /**
     * delSticky
     * 删除该评论置顶状态
     * @return this
     */
    public function delSticky(){
        return $this->del('sticky');
    }
}