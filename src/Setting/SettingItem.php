<?php
namespace LizusVitara\Setting;

/**
 * SettingItem
 * 作为设置项的代理类，根据传值数据中的type值来选择使用对应的设置类输出
 * 在应用中，\App\Setting\Item\IteM开头的设置项会优先使用
 */
class SettingItem {
    protected $class='\LizusVitara\Setting\Item\IteMnothing';
    protected $hasClass=true;
    protected $item=null;
    public function __construct($args=[]){
        if(isset($args['type']) && isset($args['id'])) {
            $type=preg_replace('/[_\W]/','',$args['type']);
            $this->checkClass($type);
        }
        $this->item=new $this->class($args);
    }
    public function output(){
        return $this->item->output();
    }
    protected function checkClass($type){
        $cls='\App\Setting\Item\IteM'.$type;
        if (!class_exists($cls)) $cls='\LizusVitara\Setting\Item\IteM'.$type;
        if (class_exists($cls)) {
            $this->class=$cls;
            $this->hasClass=true;
        }else {
            $this->hasClass=false;
        }
        return $this->hasClass;
    }
}