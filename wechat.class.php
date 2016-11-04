<?php 
header('content-type:text/html;charset=utf-8');
require './wechat.inc.php';
class WeChat{
	private $appid;
	private $appsecret;

	public function __construct(){
		$this->appid = APPID;
		$this->appsecret = APPSECRET;
	}

	public function request($url,$https=true,$method='get',$data=null){
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		if($https == true){
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		}
		if($method == 'post'){
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		}
		$str = curl_exec($ch);
		curl_close($ch);
		return $str;
	}

	 public function testRequset(){
	 	$url ='http://www.baidu.com';
	 	$content = $this->request($url);
	 	var_dump($content);
	 }

	 public function getAccessToken(){
	 	$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
	 	/*echo $url;die();*/
		$content = $this->request($url);
		/*var_dump($content);*/
		$content = json_decode($content);
		/*var_dump($content);*/
		$access_token = $content->access_token;
		return $access_token;

	 }

	 public function getTicket($scene_id=111,$tmp=true,$expire_seconds=604800){
	 	$url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
	 	/*var_dump($url);die;*/
	 	if($tmp === true){
	 		$data = '{"expire_seconds":'.$expire_seconds.', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id":'.$scene_id.'}}}';
	 	}else{
	 		$data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id":'.$scene_id.'}}}';
	 	}
      	$content = $this->request($url,true,'post',$data);
      	$content = json_decode($content);
      	$ticket =$content->ticket;
      	return $ticket;
	 }

	 public function getQRCode(){
	 	$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$this->getTicket();
	 	$content = $this->request($url);
	 	$rs = file_put_contents('qrcode.jpg',$content);
	 	var_dump($rs);
	 }
    public function createMenu(){
    	$url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccessToken();
    	$data = ' {
     "button":[
     	{	
          "type":"click",
          "name":"今日歌曲",
          "key":"V1001_TODAY_MUSIC"
      	},
      	{
           "name":"菜单",
           "sub_button":[
           	{	
               "type":"view",
               "name":"baidu",
               "url":"http://www.baidu.com/"
            },
            {
               "type":"view",
               "name":"腾讯视频",
               "url":"http://v.qq.com/"
            }]
       }]
     }';
     $content = $this->request($url,true,'post',$data);
     $content = json_decode($content);
     if($content->errmsg == 'ok'){
     	echo '创建菜单成功!';
     }else{
     	echo '创建菜单失败!'.'<br />';
     	echo '错误码为:'.$content->errcode;
     }
    }
    public function showMenu(){
    	$url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$this->getAccessToken();
    	$content = $this->request($url);
    	var_dump($content);
    }
    public function delMenu(){
    	$url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$this->getAccessToken();
    	$content = $this->request($url);
        $content = json_decode($content);
        if($content->errmsg=='ok'){
        	echo '删除菜单成功!';
        }else{
        	echo '删除菜单失败!'.'<br />';
        	echo '错误代码为:'.$content->errcode;
        }
    } 
    public function getUserList(){
    	$url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->getAccessToken();
    	$content = $this->request($url);
    	$content = json_decode($content);
    	echo '关注数为:'.$content->total.'<br/>';
    	echo '本次拉取人数为:'.$content->count.'<br />';
    	echo '用户列表为'.'<br/>';
    	$i = 1;
    	foreach($content->data->openid as $key => $value){
    		echo $i.'####<a href="http://localhost/wechat51/getuserinfo.php?openid='.$value.'">'.$value.'<br/>';
    		$i++;
    	}
    }
    public function getUserInfo(){
    	$openid = $_GET['openid'];
    	$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$openid.'&lang=zh_CN';
    	$content = $this->request($url);
    	$content = json_decode($content);
    	switch($content->sex){
    		case '0':
    		$sex = '未知';
    		break;
    		case '1':
    		$sex = '男';
    		break;
    		case '2':
    		$sex = '女';
    		break;
    		default:
    		break;
    	}
    	echo '昵称:'.$content->nickname.'<br />';
    	echo '性别:'.$sex.'<br />';
    	echo '省份:'.$content->province.'<br />';
    	echo '<img src = "'.$content->headimgurl.'" />';

    }
}


