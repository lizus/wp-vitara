<?php

namespace LizusVitara\User;

/**
 *  主题中使用的时候请确保使用App\User\User来继承
 */
class User extends \LizusVitara\Model\SingleData
{
  protected $default_avatar = ''; //默认头像
  protected $type = 'user'; //数据类型:post,user,term,comment
  protected $method = [ //获取修改删除数据用的方法，根据不同类型不同
    'data' => '\get_userdata',
    'get' => '\get_user_meta',
    'set' => '\update_user_meta',
    'delete' => '\delete_user_meta',
  ];

  private $basic_keys = [ //自定义的user_meta，key为meta_key，存储时需经过$this->key()，值为处理函数，可用数组形如[self,'parse']
    'lastlogin' => '\strval', //最后登录时间，YY-mm-dd HH:ii:ss
    'loginLog' => '', //记录最近的50条登录记录
    'post_views' => '\intval', //用户人气值 （作者文章阅读量总计）
    'post_updated' => '\intval', //最近发布文章的时间，time()值
    'avatar' => '\strval', //头像地址
    'headimgurl' => '\strval', //微信头像地址
    'unionid' => '\strval', //微信公共平台unionid,
    'web_openid' => '\strval', //微信号在pc web登录的标识
    'wx_openid' => '\strval', //微信端浏览器登录用户标识
    'xcx_openid' => '\strval', //微信小程序用户标识
    'offi_openid' => '\strval', //微信公众号用户标识
    'nickname' => '\strval', //微信用户昵称
    'sex' => '\intval', //微信用户性别 0:未知，1:男,2:女
    'language' => '\strval', //微信用户使用语言
    'city' => '\strval', //微信用户城市
    'province' => '\strval', //微信用户省份
    'country' => '\strval', //微信用户国家
    'from' => '\strval', //从哪里登录上来的
  ];

  //不允许使用set来进行设置的key，这些key必须使用特定的方法进行更新
  protected $not_set = [
    'loginLog',
    'post_views',
  ];

  /**
   * metaKeysInit
   * 方便子类扩展: 
   * return array_merge(parent::metaKeysInit(),['testKey'=>'\strval',]);
   * @return array
   */
  protected function metaKeysInit()
  {
    return $this->basic_keys;
  }

  /**
   * updateLoginData
   * ANCHOR 用户从微信等登录时更新用户数据
   * @param  mixed $loginData
   * @return Object
   */
  public function updateLoginData(array $loginData)
  {
    //更新微信信息的时候不允许直接更新头像值，防止头像覆盖
    unset($loginData['avatar']);
    foreach ($loginData as $key => $value) {
      if (!empty($value)) $this->set($key, $value);
    }
    return $this;
  }
  /**
   * login
   * 用户登录操作
   * @return Object
   */
  public function login()
  {
    if (!$this->exist()) return false;
    $this->check_avatar(); //用户登录的时候先检查一下头像是否可以打开，如果打不开，则清掉avatar，使用微信的headimgurl
    \wp_set_current_user($this->sid, $this->user_login);
    \wp_set_auth_cookie($this->sid, true); //记住登录cookie，可用2周
    \do_action('wp_login', $this->user_login, \get_user_by('id', $this->sid));
    return $this;
  }
  /**
   * logout
   * @return void
   */
  public function logout()
  {
    \wp_logout();
  }

  /**
   * 用户权限判断，见user_can
   * @return bool
   */
  public function can($capability, ...$args)
  {
    if (!$this->exist()) return false;
    return \user_can($this->sid, $capability, ...$args);
  }

  /**
   * createBindCode
   * 用户的注册时间是不会改变的，可以用来验证确定是否为绑定用户登录需求
   * @return String
   */
  public function createBindCode()
  {
    return md5($this->user_registered);
  }

  /**
   * @return bool
   */
  public function verifyBindCode($code)
  {
    return $code === $this->createBindCode();
  }

  /**
   * current
   * 静态方法用于获取当前登录用户
   * @return Object
   */
  public static function current()
  {
    $user = \wp_get_current_user();
    return new static($user->ID);
  }

  /**
   * setUserEmail
   * 设置用户email，该操作应该小心处理，因为邮箱可以用于登录，及找回用户密码操作
   * @param  mixed $email
   * @return Object
   */
  public function setUserEmail($email)
  {
    if (!\is_email($email)) return $this;
    if (!$this->exist()) return $this;
    $args = [
      'ID' => $this->sid,
      'user_email' => $email
    ];
    $sid = \wp_update_user($args);
    if (!\is_wp_error($sid)) {
      $this->__construct($sid);
    }
    return $this;
  }

  /**
   * setDisplayName
   * 设置用户显示的名称
   * @param  mixed $name
   * @return Object
   */
  public function setDisplayName($name)
  {
    if (!$this->exist()) return $this;
    $args = [
      'ID' => $this->sid,
      'display_name' => strval($name),
    ];
    $sid = \wp_update_user($args);
    if (!\is_wp_error($sid)) {
      $this->__construct($sid);
    }
    return $this;
  }

  /**
   * addLoginLog
   * 添加登录日志，通常该函数挂载在wp_login中
   * @return Object
   */
  public function addLoginLog()
  {
    $loginLog = $this->loginLog;
    if (!is_array($loginLog)) $loginLog = [];
    $log = date('Y-m-d H:i:s', time() + 8 * 3600) . ' =&&= ' . \LizusFunction\get_ip_address() . ' =&&= ' . $_SERVER['HTTP_USER_AGENT'];
    array_unshift($loginLog, $log);
    $loginLog = array_slice($loginLog, 0, 50);
    $this->_set('loginLog', $loginLog);
    return $this;
  }


  /**
   * getLoginLog
   * 获取用户登录日志
   * @param  int $n  要获取的条数
   * @return array
   */
  public function getLoginLog($n = 10)
  {
    $loginLog = $this->loginLog;
    if (!is_array($loginLog)) $loginLog = [];
    return  array_slice($loginLog, 0, $n);
  }

  /**
   * get_avatar
   * 头像获取过滤函数
   * @return string
   */
  protected function get_avatar($avatar)
  {
    if (empty($avatar)) $avatar = $this->headimgurl;
    if (empty($avatar)) $avatar = $this->default_avatar;
    return $avatar;
  }

  /**
   * check_avatar
   * 当用户登录的时候检查一下他的头像，如果头像打不开，则删除
   * @return Object
   */
  protected function check_avatar()
  {
    $avatar = $this->avatar;
    if (!empty($avatar)) {
      try {
        $headers = get_headers($avatar);
        $data = $headers[0];
        if (preg_match('/\s200\s/', $data)) {

          //微信图片过期判断,在$headers中包含X-ErrNo: -6101表示图片已过期
          if (in_array('X-ErrNo: -6101', $headers)) {
            $this->set('avatar', '');
          }
        } else {
          $this->set('avatar', '');
        }
      } catch (\Throwable $th) {
        //do nothing
      }
    }
    return $this;
  }

  /**
   * addPostViews
   * 增加用户的人气值，该值一般使用在增加文章阅读数的时候同步增加
   * @param  mixed $num
   * @return Object
   */
  public function addPostViews($num)
  {
    if (!$this->exist()) return $this;
    $views = $this->post_views;
    $views += $num;
    $this->_set('post_views', $views);
    return $this;
  }

  /**
   * getSex
   * 返回性别
   * @return String
   */
  public function getSex()
  {
    if (!$this->exist()) return '';
    switch ($this->sex) {
      case '1':
        return 'male';
        break;
      case '2':
        return 'female';
        break;
      default:
        return 'unknown';
        break;
    }
  }
}
