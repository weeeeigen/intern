<?php


	ini_set('default_charset', 'UTF-8');
	ini_set('display_errors', 1);


	define('CLIENT_ID', '');
	define('CALLBACKURL', '');
	define('CLIENT_SECRET', '');



	/********************************************************

		1. アクセストークンを取得

	********************************************************/



	//パラメータ
	$param = [
        'code' => $_GET['code'],
        'grant_type' => 'authorization_code',
        'client_id' => CLIENT_ID,
        'redirect_uri' => CALLBACKURL,
	];


	//ヘッダ
	$clientid = CLIENT_ID;
	$clientsecret = CLIENT_SECRET;
	$str = "Authorization: Basic " . base64_encode("$clientid:$clientsecret");
	$headers = [
        $str,
        'Content-Type: application/x-www-form-urlencoded'
	];


	//アクセストークン取得
	$url = "https://api.fitbit.com/oauth2/token";

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($param));

	$response = curl_exec($curl);
	$json = json_decode($response, true);
	//var_dump($json);

	curl_close($curl);


	//リフレッシュトークンを取り出す
	$refreshtoken = $json['refresh_token'];



	/********************************************************

		2. 現在のユーザ情報（運動時間，睡眠時間）を取得

	********************************************************/



	//ヘッダー
	$headers2 = [
        'Authorization: Bearer ' . $json['access_token']
	];


	//ユーザー情報を取得
	$getid = $json['user_id'];
	$url2 = "https://api.fitbit.com/1/user/" . $getid . "/activities/date/today.json";
	$url4 = "https://api.fitbit.com/1/user/" . $getid . "/sleep/date/today.json";

	function getData($url){

		global $json;
		global $headers2;

		$curl2 = curl_init();
		curl_setopt($curl2, CURLOPT_HTTPHEADER, $headers2);
		curl_setopt($curl2, CURLOPT_URL, $url);
		curl_setopt($curl2, CURLOPT_POST, true);
		curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);

		$response2 = curl_exec($curl2);
		$json2 = json_decode($response2, true);
		$json2;
		//var_dump($json2);

		curl_close($curl2);

		global $url2;
		global $url4;

		if ($url == $url2) {
			$fairlyAM = $json2['summary']['fairlyActiveMinutes'];
			$veryAM = $json2['summary']['veryActiveMinutes'];

			global $activeMinutes;
			global $steps;

			$activeMinutes = $fairlyAM + $veryAM;
			$steps = $json2['summary']['steps'];
		}else if ($url == $url4) {
			global $sleetMinutes;

			$sleetMinutes = $json2['summary']['totalTimeInBed'];
		}
	}

	getData($url2);
	getData($url4);


	/********************************************************

		3. データベースに保存

	********************************************************/



	$mysqli = new mysqli('', '', '', '');
	$sid = 0;
	date_default_timezone_set('Asia/Tokyo');
	$date = date('Y-m-d');
	$time = date('H:m:s');
	$result = $mysqli -> query("SELECT * FROM User");
	$rowcount = $result -> num_rows;
	$sid = $rowcount + 1;
	$mysqli -> query("INSERT INTO subscriber(date, time, FitbitID, ActiveTime, SleepTime, Steps) VALUES('$date', '$time', '$getid', '$activeMinutes', '$sleetMinutes', '$steps')");
	$mysqli -> query("INSERT INTO User(FitbitID, date, time, RefreshToken) VALUES('$getid', '$date', '$time', '$refreshtoken')");
	$mysqli -> close();



	/********************************************************

		4. サブスクライバに登録

	********************************************************/



	$url3 = //"https://api.fitbit.com/1/user/-/activities/apiSubscriptions.json";
      		"https://api.fitbit.com/1/user/-/activities/apiSubscriptions/'$sid'.json";

	$curl3 = curl_init();
	curl_setopt($curl3, CURLOPT_HTTPHEADER, $headers2);
	curl_setopt($curl3, CURLOPT_URL, $url3);
	curl_setopt($curl3, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl3, CURLOPT_POST, true);
	curl_setopt($curl3, CURLOPT_POSTFIELDS, http_build_query($param));

	$response3 = curl_exec($curl3);
	$json3 = json_decode($response3, true);
	var_dump($json3);

	curl_close($curl3);


	echo ("あなたのFitbitのIDは" . $getid . "です<br>" );
	echo ("このIDをコピーしてアプリケーションに戻ってください");

?>
