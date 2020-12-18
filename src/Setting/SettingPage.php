<?php
namespace LizusVitara\Setting;

/**
* SettingPage
* 后台设置页生成Class
*/
class SettingPage
{
  private $data;
  
  public function __construct($setting)
  {
    if (is_array($setting)) {
      $this->data=$setting;
      \add_action('admin_menu',array(&$this,'creatMenu'),$this->data['position'],999);
    }
  }
  public function creatMenu() {
    if (empty($this->data['parent'])) {
      $page=\add_menu_page($this->data['title'],$this->data['title'],$this->data['capability'],$this->data['id'],array(&$this,'adminPanel'));
    }else{
      $page=\add_submenu_page($this->data['parent'],$this->data['title'],$this->data['title'],$this->data['capability'],$this->data['id'],array(&$this,'adminPanel'));
    }
  }
  public function adminPanel() {
    $d=$this->data;
    $error='';
    if (!empty($_POST) && $_POST['submit']=='go') {
      foreach ($_POST as $key => $value) {
        if ($key == \LizusFunction\v_key($key)) {
          if (empty($value) || $value=='all_setting_empty' || (is_array($value) && in_array('all_setting_empty',$value))) {
            \delete_option($key);
          }else {
            \update_option($key,$value,false);
          }
        }
      }
      \do_action($d['id'].'_setting_submit');
      $error.=__('设置已保存','vitara');
    }
    ?>
    <div class='wrap vitara'>
    <div class="vitara_panel">
    <form class="vitara_form" action="<?php echo \LizusFunction\get_current_url(); ?>" method="post">
    <h1><?php echo $d['title']; ?></h1>
    <?php
    echo '<p>'.($d['description'] ?? '').'</p>';
    ?>
    <div class="submit_div">
    <button type="submit" name="submit" value="go" class="btn btn-lg btn-primary col-xs-12 col-sm-2 col-lg-1">提交</button>
    </div>
    <hr>
    <?php
    \settings_fields($d['id']);
    \do_action($d['id'].'_setting_before');
    if (!empty($error)) {
      echo '<div class="box box-success"><p>'.$error.'</p><span class="close" title="close"><i class="icon-close"></i></span></div>';
    }
    if (array_key_exists('items',$d)) {//优先处理items
      foreach ($d['items'] as $item) {
        $item['value']=\get_option(\LizusFunction\v_key($item['id']));
        
        $setItem=new \LizusVitara\Setting\SettingItem($item);
        $setItem->output();
      }
    }
    if (array_key_exists('settings',$d)) {//处理设置块
      foreach ($d['settings'] as $setting) {
        echo '<div class="setting">';
        echo '<h3>'.$setting['title'].'</h3>';
        echo '<p>'.($setting['desc'] ?? '').'</p>';
        foreach ($setting['items'] as $item) {
          $item['value']=\get_option(\LizusFunction\v_key($item['id']));
          
          $setItem=new \LizusVitara\Setting\SettingItem($item);
          $setItem->output();
        }
        echo '</div>';
      }
    }
    \do_action($d['id'].'_setting_after');
    ?>
    <hr>
    <div class="submit_div">
    <button type="submit" name="submit" value="go" class="btn btn-lg btn-primary col-xs-12 col-sm-2 col-lg-1">提交</button>
    </div>
    </form>
    </div>
    </div>
    <?php
  }
}
