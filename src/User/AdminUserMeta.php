<?php
namespace LizusVitara\User;

class AdminUserMeta
{
  private $data;
  
  function __construct($user_meta)
  {
    $this->data=$user_meta;
    if (!empty($user_meta) && is_array($user_meta)) {
      \add_action( 'show_user_profile', array($this,'extra_user_profile_fields') );
      \add_action( 'edit_user_profile', array($this,'extra_user_profile_fields') );
      \add_action( 'personal_options_update', array($this,'save_extra_user_profile_fields') );
      \add_action( 'edit_user_profile_update', array($this,'save_extra_user_profile_fields') );
    }
  }
  //后台的用户信息编辑面板
  public function extra_user_profile_fields($user) {
    foreach ($this->data as $setting) {
      echo '<h3>'.$setting['title'].'</h3>';
      if (!empty($setting['desc'])) {
        echo '<p>'.$setting['desc'].'</p>';
      }
      echo '<table class="form-table">';
      foreach ($setting['items'] as $item) {
        if (!empty($item['cap']) && !\current_user_can($item['cap'])) continue;
        $item['prefix']='user';
        $item['value']=\get_user_meta($user->ID,\LizusFunction\v_key($item['id'],'user'),true);
        $item['echo']='tr';
        $setItem=new \LizusVitara\Setting\SettingItem($item);
        $setItem->output();
      }
      echo '</table>';
    }
    
  }
  //用户信息保存
  public function save_extra_user_profile_fields($user_id){
    if ( !\current_user_can( 'edit_user', $user_id ) ) { return false; }
    foreach ($this->data as $setting) {
      foreach ($setting['items'] as $item) {
        $id=\LizusFunction\v_key($item['id'],'user');
        \update_user_meta($user_id,$id,$_POST[$id]);
      }
    }
  }
}
