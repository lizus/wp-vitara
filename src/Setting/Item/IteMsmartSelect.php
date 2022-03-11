<?php
namespace LizusVitara\Setting\Item;

/**
 * 智能下拉选项框
 * 设置type='smartSelect'
 * 需要source和smartSelect项
 * 示例：
 array(
    'id'=>'change_tougao_author',
    'title'=>'投稿人转换作者',
    'type'=>'smartSelect',
    'desc'=>'选择投稿人',
    'source'=>[
        'type'=>'custom',
        'custom'=>get_smartselect_authors(),
    ],
    'smartSelect'=>[
        'total'=>1,//smartSelect用到的最大取值数量
        'ajax'=>'',
    ],
 ),
 * source的custom项为key=>value数组，value为显示的选项名称，key为值
 * ajax为获取key=>value数组的ajax网址
 * source和ajax必须有一项填写
 */


class IteMsmartSelect extends Item {
    
    protected function content($echo=false){
        $item=$this->data;
        $value=strval($item['value']);
        /**
         * 基于保存存数据库的格式有不同，有可能存在数据库里的字符串本身就是json_encode的字符串，这时候只要能json_decode就可以了，
         */
        $val=json_decode($value,true);
        if (is_null($val)) {
            /**
             * 假如json_decode失败了，则有可能是因为保存的数据并没有实现清除转义，这时候清除转义再试一次
             */
            $value=stripslashes($value);
            $val=json_decode($value,true);
            /**
             * 如果尝试之后仍旧是失败的，则说明原有的数据可能是旧数据，并未使用过smartselect，这时候做一次兼容，让原本的数据也可以直接进行选择使用
             */
            if (is_null($val)) {
                $val=json_decode('[{"key":"'.$value.'","value":"'.$value.'"}]',true);
            }
        }
        if (empty($value)) {
            $val='';
        }
        $value=json_encode($val);
        $html='<div class="row smartSelects">';
        $items=$this->get_source();
        $name=$item['id'];
        $total=10;
        $ajax='';
        if(isset($item['smartSelect']) && is_array($item['smartSelect'])) {
            $total=$item['smartSelect']['total'] ?? 10;
            $ajax=$item['smartSelect']['ajax'] ?? '';
        }
        $html.='<div class="smartSelect" data-component="smartSelect" data-total="'.$total.'" data-row="5">';
        $html.='<textarea class="smartSelect-value hidden" name="'.$name.'" data-type="jsonp" data-field="value" name="sample">'.$value.'</textarea>';
        if (!empty($items)) {
            $html.='<textarea class="smartSelect-source hidden" data-type="jsonp" data-field="source">';
            $src=[];
            foreach ($items as $k => $v) {
                $src[]=[
                    'key'=>$k,
                    'value'=>$v,
                ];
            }
            $html.=json_encode($src);
            $html.='</textarea>';
        }
        if (!empty($ajax)) {
            $html.='<input class="smartSelect-ajax hidden" type="hidden" data-type="string" data-field="ajax" value="'.$ajax.'" />';
        }
        $html.='<ul class="selected-ul li_has_'.$total.'" data-field="selected"></ul></div>';
        $html.='</div>';
        if ($echo) echo $html;
        return $html;
    }
}