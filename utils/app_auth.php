<?php

require_once 'wykop_api.php';
include_once 'confidential_vars.php';
require_once 'debug_log_file.php';

class app_auth
{
  private $cookie_login_key = 'autha';
  private $cookie_token_key = 'authb';
  private $sessionkey_auth_id = 'app_auth_id';
  private $login_redirect_page = 'login.php';
  private $login_name_cipher_key_b64;
  private $token_cipher_key_b64;
  private $cipher_method = 'aes-256-gcm';

  public function __construct()
  {
    $this->login_name_cipher_key_b64 = confidential_vars::$login_name_cipher_key_b64;
    $this->token_cipher_key_b64 = confidential_vars::$token_cipher_key_b64;
  } 

  public function redirect_to_login()
  {
    if($this->has_auth_in_session())
    {
      debug_log_file::append(__METHOD__, 'logged: '. $this->get_session_login_name());
      return;
    }

    if($this->has_auth_cookies())
    {
      $this->set_auth_to_session($this->get_cookie_login_name(), $this->get_cookie_token());
      if($this->has_auth_in_session())
      {
        debug_log_file::append(__METHOD__, 'logged: '. $this->get_session_login_name());
        return;
      }
    }

    $auth_id = urlencode($this->generate_auth_id());
    $cur_url = $this->get_current_base_url() . '/' . $this->login_redirect_page . '?id=' . $auth_id;
    $wapi = new wykop_api;
    $redir_url = $wapi->get_login_connect_url($cur_url);

    $this->redirect($redir_url);
  }

  public function redirect_to_index_if_logged()
  {
    if($this->has_auth_in_session())
      $this->redirect($this->get_current_base_url());

    if($this->has_auth_cookies())
    {
      $this->set_auth_to_session($this->get_cookie_login_name(), $this->get_cookie_token());
      if($this->has_auth_in_session())
        $this->redirect($this->get_current_base_url());
    }
  }

  public function remove_auth_data()
  {
    unset($_SESSION['login_name']);
    unset($_SESSION['login_token']);
    $this->remove_auth_id_from_session();

    setcookie($this->cookie_login_key, '', time() - 3600, '', '', true, true);
    setcookie($this->cookie_token_key, '', time() - 3600, '', '', true, true);
  }

  public function get_auth_id()
  {
    if(empty($_SESSION[$this->sessionkey_auth_id]) == false)
      return $_SESSION[$this->sessionkey_auth_id];

    return '';
  }

  public function remove_auth_id_from_session()
  {
    unset($_SESSION[$this->sessionkey_auth_id]);
  }

  private function generate_auth_id()
  {
    $len = random_int(33, 43);
    $auth_id = bin2hex(random_bytes($len));
    
    $_SESSION[$this->sessionkey_auth_id] = $auth_id;

    return $auth_id;
  }

  private function has_auth_cookies()
  {
    if(empty($_COOKIE[$this->cookie_login_key]) || empty($_COOKIE[$this->cookie_token_key]))
      return false;

    return true;
  }

  public function has_auth_in_session()
  {
    if(empty($_SESSION['login_name']) || empty($_SESSION['login_token']))
      return false;

    return true;
  }

  public function set_auth_to_session($login, $token)
  {
    $_SESSION['login_name'] = $login;
    $_SESSION['login_token'] = $token;
  }

  public function set_auth_cookies($login, $token)
  {
    $enc_login = $this->encrypt_data($login, $this->login_name_cipher_key_b64);
    $enc_token = $this->encrypt_data($token, $this->token_cipher_key_b64);

    $next_month = time() + (30 * 24 * 60 * 60);

    setcookie($this->cookie_login_key, $enc_login, $next_month, '', '', true, true);
    setcookie($this->cookie_token_key, $enc_token, $next_month, '', '', true, true);
  }

  public function get_session_login_name()
  {
    if(empty($_SESSION['login_name']) == false)
      return $_SESSION['login_name'];

    return '';
  }

  public function get_session_token()
  {
    if(empty($_SESSION['login_token']) == false)
      return $_SESSION['login_token'];

    return '';
  }
  
  private function get_cookie_login_name()
  {
    if(empty($_COOKIE[$this->cookie_login_key]) == false)
    {
      $enc = $_COOKIE[$this->cookie_login_key];
      $dec = $this->decrypt_data($enc, $this->login_name_cipher_key_b64);
      return $dec;
    }

    return '';
  }

  private function get_cookie_token()
  {
    if(empty($_COOKIE[$this->cookie_token_key]) == false)
    {
      $enc = $_COOKIE[$this->cookie_token_key];
      $dec = $this->decrypt_data($enc, $this->token_cipher_key_b64);
      return $dec;
    }

    return '';
  }

  public function redirect($url, $permanent = false)
  {
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit;
  }

  public function get_current_base_url($host_only = false)
  {
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
    $reqarr = explode('/', $_SERVER['REQUEST_URI']);
    $req = '';
    if($host_only == false && count($reqarr) >= 3)
      $req = '/' . $reqarr[1];
    
    return $scheme . '://' . $_SERVER['SERVER_NAME'] . $req;
  }

  private function encrypt_data($data, $key)
  {
    $ivlen = openssl_cipher_iv_length($this->cipher_method);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext = openssl_encrypt($data, $this->cipher_method, $key, $options=0, $iv, $tag);

    $res = base64_encode($tag) . ' ' . $ciphertext . ' ' . base64_encode($iv);
    return $res;
  }

  private function decrypt_data($data, $key)
  {
    $arr = explode(' ', $data);
    if(count($arr) == 3)
    {
      $tag = base64_decode($arr[0]);
      $ciphertext = $arr[1];
      $iv = base64_decode($arr[2]);

      $decrypted = openssl_decrypt($ciphertext, $this->cipher_method, $key, $options=0, $iv, $tag);
      return $decrypted;
    }

    return '';
  }
}
