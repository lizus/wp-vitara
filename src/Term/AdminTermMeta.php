<?php
namespace LizusVitara\Term;


class AdminTermMeta
{
  private $data=array();

  function __construct($arr){
    $this->data=$arr;
    $taxs=\LizusFunction\get_taxs();
    foreach ($taxs as $term=>$obj) {
  		\add_action($term.'_edit_form_fields',array(&$this,'edit_form_fields'));
  		\add_action('create_'.$term,array(&$this,'save_meta'));
  		\add_action('edit_'.$term,array(&$this,'save_meta'));
    }
    foreach ($arr as $item) {
      if (isset($item['show_in_column']) && $item['show_in_column'] == true && isset($item['taxs']) && is_array($item['taxs'])) {
        foreach ($item['taxs'] as $tax) {
          \add_filter('manage_edit-'.$tax.'_columns', function ($cols) use ($item) {
            $cols[$item['id']] = $item['title'];
            return $cols;
          });
      		\add_filter('manage_'.$tax.'_custom_column',function ($out,$column,$term_id) use ($item) {
            if ($column==$item['id']) {
              $value=\get_term_meta($term_id,$this->key($item['id']),true);
              if (!empty($value)) {
                switch ($item['type']) {
                  case 'image':
                  $value='<a href="'.$value.'" class="colorbox" rel="group"><img src="'.$value.'" alt="thumb"></a>';
                  break;
                  default:
                  break;
                }
                $out='<div class="vitara_meta">'.$value.'</div>';
              }
            }
            return $out;
          }, 10, 3 );
        }
      }
    }
  }
  protected function key($key){
    return \LizusFunction\v_key($key,'term');
  }
  //设置项页面输出内容
  public function edit_form_fields($term) {
  	echo \wp_nonce_field(basename( __FILE__ ),'vitara_nonce');
    foreach ($this->data as $item) {
      if (!empty($item['taxs']) && !in_array($term->taxonomy,(array)$item['taxs'])) continue;
      $item['prefix']='term';
      $item['value']=\get_term_meta($term->term_id,$this->key($item['id']),true);
      $item['echo']='tr';
      
			$setItem=new \LizusVitara\Setting\SettingItem($item);
			$setItem->output();
    }
  }
  //设置项保存
  public function save_meta($term_id) {
    if (! isset($_POST['vitara_nonce']) || ! \wp_verify_nonce($_POST['vitara_nonce'],basename(__FILE__))) return;
    foreach ($this->data as $item) {
      $id=$this->key($item['id']);
      if (!isset($_POST[$id])) {
        \delete_term_meta($term_id,$id);
      }else{
        \update_term_meta($term_id,$id,$_POST[$id]);
      }
    }
  }
}
