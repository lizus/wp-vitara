<?php
namespace LizusVitara\Model;

/**
* QueryData
* 数据列表查询，使用例如post,user,comment,term等各种查询来进行扩展，需要实现的2个函数是query和default_args
* 传值 $args 中可使用cache_var来根据条件更新缓存，比如需要每天整点更新: $args['cache_var']=date('Y-m-d H:00:00',current_time('timestamp'));，记得设置的expire过期时间应该超过需要的更新时间一些，但不要太多，让它自动到期作废就好
*/
abstract class QueryData 
{    
    /**
    * 
    * 存储查询结果的数据，供调用
    */
    protected $_data=null;
    protected $_data_default=[
        'count'=>0,//当前列表计数
        'total'=>0,//该查询总计有的数据
        'data'=>[],//当前查询出来的列表数据
    ];
    protected $args=[];//查询条件
    protected $key='';//保存缓存用的key，如果设置该项，则$this->key()直接返回该值
    protected $expire=86400;//缓存过期时间，默认一天
    protected $cache='yes';//如果$cache为no则不使用cache
    
    public function __construct($args=[]){
        //查询条件和默认条件合并
        $this->args=\wp_parse_args($args,$this->default_args());

        //先检查有没有缓存
        $this->_data=$this->get_cache();
        
        //如果缓存未通过检查则启动数据库查询
        if(!$this->check_data($this->_data)) {

            //查询数据库
            $data=$this->query();

            //如果数据库查询执行出来的数据符合格式要求则存入_data并写入缓存
            if($this->check_data($data)) {
                $this->_data=$data;
                $this->set_cache();
            }else {
                //如果不合要求，则将_data设置成默认都是空的数组
                $this->_data=$this->_data_default;
            }
        }
    }
    
    /**
    * key
    * 生成用于缓存的key
    * @return string
    */
    protected function key(){
        if(empty($this->key)) $this->key=\LizusFunction\v_key(str_replace('\\','-',get_class($this)).':'.md5(json_encode($this->args)));
        //生成key之后将cache_var删掉，避免影响查询
        if(isset($this->args['cache_var'])) unset($this->args['cache_var']);
        return $this->key;
    }
    
    /**
    * check_data
    * 检查数据是否符合要求
    * @param  mixed $data
    * @return void
    */
    protected function check_data($data){
        if(empty($data) || !is_array($data) || !isset($data['data']) || !isset($data['total']) || !isset($data['count'])) return false;
        return true;
    }
    
    /**
    * get_cache
    * 获取缓存数据
    * @return array
    */
    protected function get_cache(){
        if($this->cache=='no') return null;
        return \LizusFunction\get_transient($this->key());
    }
        
    /**
     * set_cache
     * 将_data值存入缓存
     * @return void
     */
    protected function set_cache(){
        if($this->cache=='no') return null;
        \set_transient($this->key(),$this->_data,$this->expire);
    }
    
    /**
    * default_args
    * 设置默认的查询条件
    * @return array
    */
    abstract protected function default_args();
    
    /**
    * query
    * 通过查询条件来调用各自的查询方法，注意最终获取的是数组数据，格式参见顶部$_data说明
    * @return array
    */
    abstract protected function query();
    
    /**
    * get
    * 数据最终输出
    * @return array
    */
    public function get(){
        return $this->_data['data'];
    }    
    /**
    * total
    * 查询总计数据
    * @return int
    */
    public function total(){
        return $this->_data['total'];
    }    
    /**
    * count
    * 当前查询结果计数
    * @return int
    */
    public function count(){
        return $this->_data['count'];
    }    
    /**
    * do
    * 对查询出来的列表进行操作，传递函数，返回函数执行结果
    * @param  callable $fn
    * @return mixed
    */
    public function do($fn){
        return \call_user_func($fn,$this->get());
    }
}