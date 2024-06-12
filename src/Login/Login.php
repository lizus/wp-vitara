<?php

namespace LizusVitara\Login;

/**
 *
 */
abstract class Login
{
  protected $bind = false;
  protected $user = null;
  protected $sourceData = null; //用于接收登录方返回的数据
  protected $formatedData = []; //用于存储格式化后的用户数据数组
  protected static $userClass = '\LizusVitara\User\User'; //用于处理的User类
  /**
   * __construct
   * $data 登录渠道返回的用户登录信息，存入$sourceData
   * $bind 传入是否绑定登录渠道信息，true表示覆盖原该渠道的登录帐号信息，默认为false，这意味着假如用户已经登录，则不作处理
   * 
   * @param  mixed $data
   * @param  mixed $bind
   * @return void
   */
  public function __construct($data, $bind = false)
  {
    $this->bind = $bind;
    $this->user = static::$userClass::current();
    $this->sourceData = $data;
  }

  /**
   * sourceDataFormat
   * 对登录渠道服务器返回数据进行处理，并以数组形式存入$formatedData，返回$this方便链式调用
   * 格式化后的数据必须包含user_login,user_email,display_name
   * @return $this
   */
  abstract protected function sourceDataFormat();

  /**
   * existUserQuery
   * 设置已存在用户的查询语句，该语句决定了会登录哪个用户，一定要慎重
   * @return array
   */
  abstract protected function existUserQuery();

  /**
   * hasUser
   * 判断是否已有该登录渠道用户信息，如果有，则返回true，否则返回false
   * 同时，根据$bind值，如果$bind值为true，则清除原有帐号中的绑定，false，则将查询到的帐户user对象赋值给$this->user
   * @return boolean
   */
  protected function hasUser()
  {
    $args = $this->existUserQuery();
    if (empty($args)) return false;
    $args['orderby'] = 'user_registered';
    $args['order'] = 'DESC';
    $users = \get_users($args);
    if (!empty($users)) {
      if ($this->isBind()) { //如果使用绑定且当前有用户已登录，则清除所有绑定用户的相关信息
        foreach ($users as $item) {
          if ($item->ID != $this->user->ID) {
            $this->removeExistBind($item->ID);
          }
        }
      } else {
        $user = $users[0];
        $this->user = new static::$userClass($user->ID);
      }
      return true;
    }
    return false;
  }

  /**
   * isBind
   * 确认是否是绑定登录渠道的操作，绑定渠道需要用户已登录，且传入$bind=true
   * @return boolean
   */
  protected function isBind()
  {
    return $this->bind && $this->userHasLogin();
  }

  /**
   * removeExistBind
   * 清理原有绑定
   * @param  int $uid
   * @return void
   */
  abstract protected function removeExistBind($uid);

  /**
   * createUser
   * 如果没有查到对应登录渠道的帐号，同时也不是已登录用户的绑定渠道操作
   * @return object
   */
  private function createUser()
  {
    if (!$this->hasUser() && !$this->isBind()) {
      if (isset($this->formatedData['user_login']) && isset($this->formatedData['user_email'])) {
        $user_name = $this->formatedData['user_login'];
        $user_email = $this->formatedData['user_email'];
        $user_id = username_exists($user_name);
        if (false != $user_id) {
          $user_name = $user_name . '_' . ceil(microtime(true) * 1000);
        }
        if (false != email_exists($user_email)) {
          $user_email = $user_name . '_' . ceil(microtime(true) * 1000) . '@' . $_SERVER['SERVER_NAME'];
        }
        $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
        $user_id = wp_create_user($user_name, $random_password, $user_email);
        if (!is_wp_error($user_id)) {
          $this->user = new static::$userClass($user_id);
          if (isset($this->formatedData['display_name'])) $this->user->setDisplayName($this->formatedData['display_name']);
        }
      }
    }
    return $this->user;
  }

  /**
   * userHasLogin
   * 用于判断是否已经有用户登录了
   *
   * @return boolean
   */
  protected function userHasLogin()
  {
    return $this->user->exist();
  }

  /**
   * login
   * 主要公开方法
   * @return void
   */
  public function login()
  {
    //return $this->sourceDataFormat()->createUser();
    return $this->sourceDataFormat()->createUser()->updateLoginData($this->formatedData)->login();
  }

  /**
   * 处理错误
   */
  public static function handleError($err)
  {
    //默认不处理错误
  }
}
