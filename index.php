<?php
 session_start();
 // if( session_id()){
 //   var_dump( session_id());exit;
 // }
  include './config.php';
  Class Utils_login
  {
    // 获取header头信息
    public function get_all_header(){
        // 忽略获取的header数据。这个函数后面会用到。主要是起过滤作用
        $ignore = array('host','accept','content-length','content-type');
        $headers = array();
        foreach($_SERVER as $key=>$value){
          if(substr($key, 0, 5)==='HTTP_'){
          //这里取到的都是'http_'开头的数据。
          //前去开头的前5位
            $key = substr($key, 5);
            //把$key中的'_'下划线都替换为空字符串
            $key = str_replace('_', ' ', $key);
            //再把$key中的空字符串替换成‘-’
            $key = str_replace(' ', '-', $key);
            //把$key中的所有字符转换为小写
            $key = strtolower($key);

            //这里主要是过滤上面写的$ignore数组中的数据
            if(!in_array($key, $ignore)){
              $headers[$key] = $value;
            }
          }
      }
      //输出获取到的header
        return $headers;
    }
    // 微信登录
    public  function weixin_login(){
        global $jscode2sessionUrl;
        $header = $this->get_all_header();
        $userInfo = array();
        if(isset($header['wx-code'])){
          $code = $header['wx-code'];
          $url = preg_replace('/({JSCODE})/i', $code, $jscode2sessionUrl);
          $weixin =  file_get_contents($url);//通过code换取网页授权access_token
          $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
          if($jsondecode->openid){
              $userInfo['openid'] = $jsondecode->openid;
              $thirrd_session = sha1(mt_rand() . $jsondecode->openid);
               $response = [
                   'error_code'=>0,
                   'result'=>['thirdSession'=>$thirrd_session]//生成第三方3rd_session
               ];
               $_SESSION[$thirrd_session] = $jsondecode->openid . $jsondecode->session_key;
               echo json_encode($response);
              // 将openid存入数据库
          }else{
            $error = [
                'error_code'=>4000,
                'error_message'=>"登录失效"
            ];
            // echo json_encode($error);
            // echo json_encode($error);
            var_dump(session_id());
          }
        }else{
            // $error = [
            //     'error_code'=>4000,
            //     'error_message'=>"登录失败，code不存在"
            // ];
            // echo json_encode($error);
            var_dump(session_id());
        }
    }
    // 解密用户信息
    public function decryData($encryptData,$sessionKey,$iv){
      $decryptData = openssl_decrypt(
           base64_decode($encryptData),
           'AES-128-CBC',
           base64_decode($sessionKey),
           OPENSSL_RAW_DATA,
           base64_decode($iv)
       );
        return json_decode($decryptData);
    }
  }

  $wx_login = new Utils_login();
  $wx_login->weixin_login()
 ?>
