<?
ignore_user_abort(true);
set_time_limit(0);
/*
Url for token (for example): https://oauth.vk.com/authorize?client_id=3990926&scope=messages,offline,friends,photo&redirect_uri=https://oauth.vk.com/blank.html&display=popup&v=5.2&response_type=token
*/
$token="";//bot token
$bot=234567890;//bot id
$chat1=2000000008; $chat1id=8;//main chat
$chat2=2000000006; $chat2id=6;//primary chat
$chat3=2000000009; $chat3id=9;//primary chat

$get=api("messages.getLongPollServer","",$token);
$myMsg=Array();
if(isset($get->response)){
	$key=$get->response->key;
	$server=$get->response->server;
	$ts=$get->response->ts;
	$url="http://{$server}?act=a_check&key={$key}&ts={$ts}&wait=25&mode=2";
	echo "First URL: ".$url."\r\n";
	while(true){
		$go=json_decode(file_get_contents($url));
		if(isset($go->ts)){$ts=$go->ts;}
		$url="http://{$server}?act=a_check&key={$key}&ts={$ts}&wait=25&mode=2";
		if(isset($go->failed)){
			$get=api("messages.getLongPollServer","",$token);
			$key=$get->response->key;
			$server=$get->response->server;
			$ts=$get->response->ts;
			$url="http://{$server}?act=a_check&key={$key}&ts={$ts}&wait=25&mode=2";
			echo "UPDATE URL RIGHT NOW: ".$url."\r\n";
		}
		if(count($go->updates)!==0){
			foreach($go->updates as $v){
				if($v[0]==4 AND ($v[3]==$chat1 OR $v[3]==$chat2 OR $v[3]==$chat3)){
					$who=whos($v[7]->from);
					$whoz=$v[7]->from ;
					$att=Array();
					for($i=1;$i<=12;$i++){
						if(isset($v[7]->{"attach".$i})){
							$att[]=$v[7]->{"attach".$i."_type"}.$v[7]->{"attach".$i};
						}
					}
					if($who!==null AND intval($v[7]->from)!==$bot AND trim($v[6])!==""){
						//var_dump($v);
						if(isset($v[7]->fwd)){$fwd=$v[1];}else{$fwd="";}
						if($v[3]==$chat1){$to=$chat2id;$to2=$chat3id;}elseif($v[3]==$chat2){$to=$chat1id;$to2=$chat3id;}elseif($v[3]==$chat3){$to=$chat1id;$to2=$chat2id;}
						sendMSG("<<".$who.">>:\r\n".$v[6],$to,$att,$fwd);
						if($to2!==0){sendMSG("<<".$who.">>:\r\n".$v[6],$to2,$att,$fwd);}
						echo "Sending $who to $to AND $to2: $v[6].\r\n";
					}
					
				} //need chat
			} //End foreach updates
		} // End updates
	} // End while
}else{
	die("Bad Token");
}
$whos_arr=Array();


function sendMSG($text,$to,$att,$fwd){
	global $token,$myMsg;
	$str="";
	if($att!==Array()){
		foreach($att as $k=>$v){$str.=$v.",";}
	}
	if($fwd!==""){$fwd="&forward_messages=".$fwd;}
	$text=rawurlencode($text);
	$req=api("messages.send","chat_id=".$to."&message=".$text."&attachment=".$str.$fwd,$token);
	if(isset($req->response)){
		return true;
	}else{
		echo "Error: Maybe need captcha!\r\n";
		return false;
	}
}

function whos($id){
	global $whos_arr,$token;
	if(isset($whos_arr[$id])){
		return $whos_arr[$id];
	}else{
		$req=api("users.get","user_ids=".$id."&",$token);
		if(isset($req->response)){
			$whos_arr[$id]=$req->response[0]->first_name." ".$req->response[0]->last_name;
			return $whos_arr[$id];
		}else{
			return null;
		}
	}
}
function api($method,$req,$token){
	$get=json_decode(file_get_contents("https://api.vk.com/method/".$method."?".$req."&access_token=".$token));
	return $get;
}
?>