<?php

require_once 'confidential_vars.php';

class wykop_api
{
  private $appkey;
  private $readonly_appkey = 'aNd401dAPp';
  private $appsecret;
  private $base_api_url = 'https://a2.wykop.pl';

  public function __construct()
  {
    $this->appkey = confidential_vars::$wykop_api_appkey;
    $this->appsecret = confidential_vars::$wykop_api_appsecret;
  }

  public function get_appkey()
  {
    return $this->appkey;
  }

  public function get_appsecret()
  {
    return $this->appsecret;
  }

  public function tag_entries($tag_name, $tag_page = 1)
  {
    $res = $this->curl($this->base_api_url . '/Tags/Entries/' . $tag_name . '/page/' . $tag_page . '/output/clear/appkey/' . $this->readonly_appkey);

    $content = $res['content'] ?? '';
    return json_decode($content, true);
  }

  public function tag_suggest($tag_name)
  {
    $res = $this->curl($this->base_api_url . '/Suggest/Tags/' . $tag_name . '/appkey/' . $this->readonly_appkey, null, 9);

    $content = $res['content'] ?? '';
    return json_decode($content, true);
  }

  public function login_index($login, $password_or_token)
  {
    $post_data = array(
      'login' => $login,
      'accountkey' => $password_or_token
    );

    $res = $this->curl($this->base_api_url . '/Login/Index/appkey/' . $this->appkey, $post_data);
    
    $res_obj = new stdClass;
    $content = $res['content'] ?? '';
    $res_obj->content = json_decode($content, true);
    $res_obj->errmsg_curl = $res['errmsg'] ?? '';

    return $res_obj;
  }

  public function add_entry($userkey, $body, $embed = null)
  {
    $post_data = array(
      'body' => $body,
    );

    if($embed != null)
      $post_data['embed'] = $embed;

    $res = $this->curl($this->base_api_url . '/Entries/Add/appkey/' . $this->appkey . '/userkey/' . $userkey, $post_data, 120);

    $res_obj = new stdClass;
    $content = $res['content'] ?? '';
    $res_obj->content = json_decode($content, true);
    $res_obj->errmsg_curl = $res['errmsg'] ?? '';

    return $res_obj;
  }

  public function get_login_connect_url($redir_url)
  {
    $redir = urlencode(base64_encode($redir_url));
    $secure = md5($this->appsecret . $redir_url);

    return $this->base_api_url . '/login/connect/appkey/' . $this->appkey . '/redirect/' . $redir . '/secure/' . $secure;
  }

  private function curl($url, $post_array = null, $timeout = 30)
  {
    $options = array(
      CURLOPT_RETURNTRANSFER     => true,
      CURLOPT_HEADER             => false,
      CURLOPT_USERAGENT          => 'Mozilla/5.0 (Windows NT 10.0; rv:68.0) Gecko/20100101 Firefox/68.0',
      CURLOPT_AUTOREFERER        => true,
      CURLOPT_CONNECTTIMEOUT     => 15,
      CURLOPT_TIMEOUT            => $timeout,
      CURLOPT_MAXREDIRS          => 3
    );

    if($post_array !== null)
		{
			$options[CURLOPT_POST] = true;
      $options[CURLOPT_POSTFIELDS] = $post_array;
      
      if($this->appsecret != '')
      {
        $pparams = '';
        foreach ($post_array as $key => $value)
        {
          if($key == 'embed')
          {
            if(is_a($value, 'CURLFile') == false)
              $pparams .= $value . ',';
          }
          else
          {
            $pparams .= $value . ',';
          }
        }
        if(empty($pparams) == false)
          $pparams = substr($pparams, 0, -1);
  
        $apisign = md5($this->appsecret . $url . $pparams);

        $options[CURLOPT_HTTPHEADER] = array('apisign: ' . $apisign);
      }
		}

    $cu  = curl_init($url);
    curl_setopt_array($cu, $options);
    $content = curl_exec($cu);
    $err = curl_errno($cu);
    $errmsg = curl_error($cu);
    $result = curl_getinfo($cu);
    curl_close($cu);

    $result['errno'] = $err;
    $result['errmsg'] = $errmsg;
    $result['content'] = $content;

    return $result;
  }
}
