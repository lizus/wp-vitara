<?php
namespace LizusVitara\Model;

/**
* SingleData
* 用于获取单项数据，如user,post,term,comment
*/
abstract class SingleData 
{
    protected $type='post';//数据类型:post,user,term,comment
    protected $method=[//获取修改删除数据用的方法，根据不同类型不同
        'data'=>'\get_post',
        'get'=>'\get_post_meta',
        'set'=>'\update_post_meta',
        'delete'=>'\delete_post_meta',
    ];
    
    protected $sid=0;//id号
    protected $data=[];//数据对象获取到的数据数组
    protected $meta_keys=[];//必须存于此数组中的key才能被使用到，该值在初始化时会被metaKeysInit生成
    
    protected $not_set=[];//用于规定一些不允许通过set方法来设置数据的key
        
    /**
     * __construct
     * 传入id获取实例
     * @param  int $sid
     * @return void
     */
    public function __construct($sid=0){
        $sid=intval($sid);
        if($sid<1) return;
        /**
         * 使用call_user_func时在查询评论时，get_comment在php中返回错误 PHP Warning:  Parameter 1 to get_comment() expected to be a reference, value given 
         * 所以改用直接函数调用的方式
         */
        //$s=\call_user_func($this->method['data'],$sid);
        if(!\is_callable($this->method['data'])) return;
        $s=$this->method['data']($sid);
        if($s !== false && \is_object($s) ) {
            $this->sid=$sid;
            $data=[];
            if(isset($s->data)){
                foreach ($s->data as $key => $value) {
                    $data[$key]=$value;
                }
            }else {
                foreach ($s as $key => $value) {
                    $data[$key]=$value;
                }
            }
            $this->data=$data;
            $this->meta_keys=$this->metaKeysInit();
        }
        unset($s);
    }
    
    /**
    * metaKeysInit
    * 生成返回给meta_keys的数组，使用key=>value的形式
    * @return array
    */
    abstract protected function metaKeysInit();
    
    /**
    * ANCHOR 判断用户是否存在
    * @return boolean
    */
    public function exist(){
        return $this->sid > 0;
    }
    
    /**
    * *网站定制的meta的meta_key名称 
    * @param string $name 
    * @return string
    */
    protected function key($name){
        return \LizusFunction\v_key($name,$this->type);
    }
    
    /**
    * __get
    * 获取数据
    * @param  string $key
    * @return mixed
    */
    public function __get(string $key){
        if(!$this->exist()) return null;
        if($key === 'id' || $key === 'ID') return $this->data['term_id'] ?? $this->data['ID'] ?? 0;
        $data=null;
        
        if(isset($this->data[$key])) $data=$this->data[$key];
        if(array_key_exists($key,$this->meta_keys)) {
            $data=\call_user_func_array($this->method['get'],[$this->sid,$this->key($key),true]);
        }
        
        //优先使用 get_$key的方法
        if(\method_exists($this,'get_'.$key)) return \call_user_func_array([$this,'get_'.$key],[$data]);
        
        if (array_key_exists($key,$this->meta_keys) && \is_callable($this->meta_keys[$key])) {
            return \call_user_func_array($this->meta_keys[$key],[$data]);
        }
        return $data;
    }
    
    /**
    * _set
    * * 设置数据，根据meta_keys中的key来设置，该方法仅用于内部调用。通常使用set方法返回$this可以用于链式调用
    * @param  string $key
    * @param  mixed $value
    * @return boolean
    */
    protected function _set(string $key,$value){
        
        if(!$this->exist() || \is_null($value)) return false;
        
        //如果有set_$key的方法存在，则对值先进行filter处理
        if (\method_exists($this,'set_'.$key)) $value=\call_user_func_array([$this,'set_'.$key],[$value]);
        
        if(array_key_exists($key,$this->meta_keys)) {
            \call_user_func_array($this->method['set'],[$this->sid,$this->key($key),$value]);
            return true;
        }
        return false;
    }

    /**
    * set
    * ANCHOR 设置数据
    * @param  mixed $key
    * @param  mixed $value
    * @return $this
    */
    public function set($key,$value){
        if(in_array($key,$this->not_set)) return $this;
        $this->_set($key,$value);
        return $this;
    }

    /**
    * del
    * ANCHOR 删除某键值对应的meta，该键必须在meta_keys数组中
    * @param  string $key
    * @return $this
    */
    public function del($key){
        if(!$this->exist()) return $this;
        if(array_key_exists($key,$this->meta_keys)) {
            \call_user_func_array($this->method['delete'],[$this->sid,$this->key($key)]);
        }
        return $this;
    }
        
    /**
     * allMetas
     * 获取所有meta集合
     * @return array
     */
    public function allMetas(){
        return \call_user_func_array($this->method['get'],[$this->sid]);
    }
        
    /**
     * metas
     * ANCHOR 获取网站自定义的meta集合
     * @return array
     */
    public function metas(){
        return array_filter($this->allMetas(),function ($v,$k){
            return $k == $this->key($k);
        },ARRAY_FILTER_USE_BOTH);
    }
}