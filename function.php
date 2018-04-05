<?php
//判断是否是手机
function isMobile(){
    $user_agent = $_SERVER['HTTP_USER_AGENT']; 
 
    $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte"); 
    $is_mobile = "0"; 
    foreach ($mobile_agents as $device) {//这里把值遍历一遍，用于查找是否有上述字符串出现过 
       if (stristr($user_agent, $device)) { //stristr 查找访客端信息是否在上述数组中，不存在即为PC端。 
			$is_mobile ="1"; 
            break; 
        } 
    }
    
    return $is_mobile;   
}
//判断是否是机器人
function isRobot(){
      if (empty($_SERVER['HTTP_USER_AGENT'])){
      return false;
      }
	  $searchEngineBot = array(
		  'googlebot'=>'google',
		  'mediapartners-google'=>'google',
		  'bingbot' =>'microsoft',
		  'baiduspider'=>'baidu',
		  'msnbot'=>'msn',
		  'yodaobot'=>'yodao',
		  'youdaobot'=>'yodao',
		  'yahoo! slurp'=>'yahoo',
		  'yahoo! slurp china'=>'yahoo',
		  'iaskspider'=>'iask',
		  'sogou web spider'=>'sogou',
		  'sogou push spider'=>'sogou',
		  'sogou inst spider'=>'sogou',
		  'sosospider'=>'soso',
		  'dotbot'=>'dotbot',
		  'spider'=>'other',
		  'mj12bot'=>'mj12',
		  'crawler'=>'other',
	 );
   $spider = strtolower($_SERVER['HTTP_USER_AGENT']);
     foreach ($searchEngineBot as $key => $value){ 
       if (strpos($spider, $key)!== false){
         return $value; 
        }
    }
 }
//ip 来源判断
function ip_views(){
	    //判断是否是机器人
		$robot_value=isRobot();
		if($robot_value){
			$add_ip['is_robot']=$robot_value;
		}
		//ip和ip定位
		if($_COOKIE['ip']){
			$ip=$_COOKIE['ip'];
		}else{
			$cookie_time=3600*(24-date('H'))-60*(60-date('i'));//有效期 到00:00
		    $ip = get_client_ip(0,true);
		    cookie("ip",$ip,$cookie_time);
		}
		if($_COOKIE['city']){
		    $add_ip['city']=$_COOKIE['city'];
		    $add_ip['area']=$_COOKIE['area'];	
		}else{
			$Ip = new \Org\Net\IpLocation('data.dat'); // 实例化类 参数表示IP地址库文件	
		    $area = $Ip->getlocation($ip); // 获取某个IP地址所在的位置
		    $add_ip['city']=iconv('GBK','UTF-8',$area['country']);
		    $add_ip['area']=iconv('GBK','UTF-8',$area['area']);
            cookie("city",$add_ip['city'],$cookie_time);
            cookie("area",$add_ip['area'],$cookie_time);			
		}
        //访问页面
		$views_url=$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		$add_ip['url']=$views_url;
		
		//访问来源 from_url
		
		if($_SERVER['HTTP_REFERER']){
			$search_url=explode("/link",$_SERVER['HTTP_REFERER']);
			if($search_url[1]){
				$add_ip['from_url']=$search_url[0];//来自搜索引擎。
			}else{
				$soyouqu_url=explode("soyouqu.com",$_SERVER['HTTP_REFERER']);
				if($soyouqu_url[1]){
					$add_ip['from_url']=$_SERVER['HTTP_REFERER'];//站内 自身
				}else{
					$search_url_2=explode("/",$_SERVER['HTTP_REFERER']);
					if($search_url_2[2]){
					     $add_ip['from_url']="http://".$search_url_2[2];//来自其他网站	
						
					}
				}
				
			}
		}

		if($add_ip['from_url']=="" && $add_ip['is_robot']==""){
			$add_ip['from_url']=$_SERVER['HTTP_REFERER'];
			
		}
		if(!$add_ip['from_url']){
			$add_ip['from_url']=$_SERVER['HTTP_USER_AGENT'];
		}
		
		//uv判断 1代表老访客
		if($_COOKIE["is_user"]){
            $add_ip['is_user']=1;
		}else{
			cookie("is_user","1");
			$add_ip['is_user']=0;
		}
        
		
		//访问设备
        if(!$_COOKIE['client_val']){//客服端
			$slient=isMobile();
			if($slient==1){
				$client_val="Mobile";
				cookie("client_val","Mobile");
			}else{
				$client_val="Pc";
				cookie("client_val","Pc"); 
			}
			
		}else{
			$client_val=$_COOKIE['client_val'];
		}
			
		$add_ip['ip']=$ip;
		$add_ip['client']=$client_val;  // 客服端  index.php
		$add_ip['view_time']=date('Y-m-d H:i:s');
		
		return $add_ip; 
		
    }
	
function object_to_array($obj) {
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }
 
    return $obj;
}
/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $day1
 * @param string $day2
 * @return number
 */
function betweenTwoDays($day1, $day2)
{
  $second1 = strtotime($day1);
  $second2 = strtotime($day2);
  if ($second1 < $second2) {
    $tmp = $second2;
    $second2 = $second1;
    $second1 = $tmp;
  }
  return ($second1 - $second2) / 86400;
}

function TwoDays_date($day1, $day2)
{
  $date_date1=explode(" ",$day1);
  $date_date2=explode(" ",$day2);
  $date_date1=explode("-",$date_date1[0]);
  $date_date2=explode("-",$date_date2[0]);
  $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));//这个月第一天
  $EndDate=date('Y-m-d', strtotime($BeginDate."+1 month -1 day"));//这个月最后一天
  $date=($date_date2[0]-$date_date1[0])*365+($date_date2[1]-$date_date1[2])*30;
  return ($second1 - $second2) / 86400;
}
  //获取星期方法
function get_week($date){
    //强制转换日期格式
    $date_str=date('Y-m-d',strtotime($date));
    //封装成数组
    $arr=explode("-", $date_str);
    //参数赋值
    //年
    $year=$arr[0];
    //月，输出2位整型，不够2位右对齐
    $month=sprintf('%02d',$arr[1]);
    //日，输出2位整型，不够2位右对齐
    $day=sprintf('%02d',$arr[2]);
    //时分秒默认赋值为0；
    $hour = $minute = $second = 0;
    //转换成时间戳
    $strap = mktime($hour,$minute,$second,$month,$day,$year);
    //获取数字型星期几
    $number_wk=date("w",$strap);
    //自定义星期数组
    $weekArr=array("7","1","2","3","4","5","6");
    //获取数字对应的星期
    return $weekArr[$number_wk];
  }
?>