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
    ];
    
    //不允许使用set来进行设置的key
    protected $not_set=[];
    
    /**
    * metaKeysInit
    * 方便子孙类扩展: 
    * return array_merge(parent::metaKeysInit(),['testKey'=>'\strval',]);
    * @return void
    */
    protected function metaKeysInit(){
        return $this->basic_keys;
    }
}