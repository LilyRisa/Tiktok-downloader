<?php 

function CurlData($url,$string_data='',$cookie='cookie.txt')
{
	$ch = curl_init();
	curl_setopt_array($ch,array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT => RandomUserAgent(),
		CURLOPT_URL => $url,
		CURLOPT_ENCODING => 'gzip',
		CURLOPT_TIMEOUT => 1000,
		CURLOPT_POST => true,
		CURLOPT_COOKIEJAR => realpath($cookie),
		CURLOPT_COOKIEFILE => realpath($cookie),
		CURLOPT_COOKIESESSION => true,
			CURLOPT_POSTFIELDS => http_build_query($string_data),
			CURLOPT_HTTPHEADER => array(
				'Content-Type'=>'application/x-www-form-urlencoded',
				'content-length:'=> count($string_data),
		),
		
	));
	return curl_exec($ch);
}

function parse_server($str){
	$str = explode('/',$str);
	$str = $str[2].$str[0];
	$str = base64_decode($str);
	return $str;
}

function RandomUserAgent()

{

    $userAgents=array(

        "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6)    Gecko/20070725 Firefox/2.0.0.6",

        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",

        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",

        "Opera/9.20 (Windows NT 6.0; U; en)",

        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 8.50",

        "Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.1) Opera 7.02 [en]",

        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; fr; rv:1.7) Gecko/20040624 Firefox/0.9",

        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48"       

    );

    $random = rand(0,count($userAgents)-1);

 

    return $userAgents[$random];

}



$cookie=realpath('cookie.txt');
$server = 'https://mangxahoi.club/api/tiktok/server.php?utc=non-request';
$getserver = file_get_contents($server);
$getserver = json_decode($getserver);
$base_var = parse_server($getserver->base);
$data_var = parse_server($getserver->data);
$json_var = parse_server($getserver->json);
$m3_var = parse_server($getserver->m3);
$ch = curl_init();
	curl_setopt_array($ch,array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT => RandomUserAgent(),
		CURLOPT_URL => $base_var,
		CURLOPT_ENCODING => 'gzip',
		CURLOPT_TIMEOUT => 1000,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_COOKIEJAR => $cookie,
		CURLOPT_COOKIEFILE => $cookie,
	));
	$data = curl_exec($ch);
	curl_close($ch);

$check_status_node_1 = 0;
$check_status_node_2 = 0;


$dom = new DomDocument();
@$dom->loadHTML($data);
$tokens = $dom->getElementsByTagName("input");	
for ($i = 0; $i < $tokens->length; $i++)
{
    $meta = $tokens->item($i);
    if($meta->getAttribute('name') == 'vtoken')
    $token = $meta->getAttribute('value');
}
 echo $data;
// var_dump($tokens);
// echo $token;	
if(isset($_GET['video']) && $_GET['video'] != null){
	$form = array(
		'url' => $_GET['video'],
		'vtoken' => $token
	);
}else{
	echo json_encode(array('status' => false));
	die();

}

if(isset($token)){
	$check_status_node_1 = 1;
	
	

	$after_data = CurlData($data_var,$form);
	// echo $after_data;
	@$dom->loadHTML($after_data);
	$after_token = $dom->getElementsByTagName('input');
	for ($i = 0; $i < $after_token->length; $i++)
	{
	    $meta = $after_token->item($i);
	    if($meta->getAttribute('name') == 'q')
	    $af_token = $meta->getAttribute('value');
	}
}

if(isset($af_token)){
	$check_status_node_2 = 1;
	$after_form = array(
		'q' => $af_token,
	);
	$finish_vid = CurlData($json_var,$after_form);
	$finish_mp3 = CurlData($m3_var,$after_form);
	@$dom->loadHTML($finish_mp3);
	$af = $dom->getElementsByTagName('source');
	// var_dump($af);
	$af = $af->item(0);
	$mp3 = $af->getAttribute('src');

}

if($check_status_node_1 == 1 && $check_status_node_2 == 1){
	$video = json_decode($finish_vid,true);
	$video = $video['url']['d_url'];
	$response = array(
		'status' => true,
		'video' => $video,
		'mp3' => $mp3,
	);
	echo json_encode($response);
}else{
	$response = array(
		'status' => false,
		'debug' => 'node_1:'.$check_status_node_1.' | node_2:'.$check_status_node_2,
	);
	echo json_encode($response);
}




 ?>