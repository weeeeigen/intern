<?php

	/********************************************************

		0. Verification codeの設定


		if($_GET["verify"] == "39712f94cfe4895ef536f100f3675b9f1c62d682cd5d048c8803fb687005efff"){
			http_response_code(204);
		}else{
			http_response_code(404);
		}

	********************************************************/


	ini_set('default_charset', 'UTF-8');
	ini_set('display_errors', 1);

	define('CLIENT_ID', '');
	define('CALLBACKURL', '');
	define('CLIENT_SECRET', '');



	/********************************************************

		1. サブスクライバの更新情報を取得

	********************************************************/



	//subscriberからの情報を受け取る
	$subscriber = file_get_contents("php://input");
	// $a = ["collectionType"=>"sleep", "date"=>"2016-06-14", "ownerId"=>"3SLS5J", "ownerType"=>"user", "subscriptionId"=>"weeei"];
	// $b = ["collectionType"=>"sleep", "date"=>"2016-06-15", "ownerId"=>"3SLS5J", "ownerType"=>"user", "subscriptionId"=>"weeei"];
	// $sjson = [$a, $b];
	$sjson = json_decode($subscriber, true);



	/********************************************************

		2. 更新日時ごとに処理を行う

	********************************************************/


	function sgetData($url){

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

				global $newActiveMinutes;
				global $newSteps;

				$newActiveMinutes = $fairlyAM + $veryAM;
				$newSteps = $json2['summary']['steps'];
			}else if ($url == $url4) {
				global $newSleepMinutes;

				$newSleepMinutes = $json2['summary']['totalTimeInBed'];
			}
		}



	$count = count($sjson);
	$i = 0;
	while($i < $count){



		/********************************************************

			3. リフレッシュトークンからアクセストークン取得

		********************************************************/



		//ユーザーデータを取得
		//$type = $sjosn[0]['collectionType'];
		$getid = $sjson[$i]['ownerId'];
		$subscriberdate = $sjson[$i]['date'];


		//それぞれのデータベースに接続
		$mysqli = new mysqli('', '', '', '');

		$userresult = $mysqli -> query("SELECT * FROM User WHERE FitbitID = '$getid'");
		$userrow = $userresult -> fetch_assoc();


		//リフレッシュトークンを取得
		$refreshtoken = $userrow['RefreshToken'];


		//アクセストークン再発行
		$param = [
   	     	'grant_type' => 'refresh_token',
        	'refresh_token' => $refreshtoken
		];

		$clientid = CLIENT_ID;
		$clientsecret = CLIENT_SECRET;
		$str = "Authorization: Basic " . base64_encode("$clientid:$clientsecret");
		$headers = [
        	$str,
        	'Content-Type: application/x-www-form-urlencoded'
		];

		$url = "https://api.fitbit.com/oauth2/token";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($param));

		$response = curl_exec($curl);
		$json = json_decode($response, true);

		curl_close($curl);


		//リフレッシュトークンを保存する
		$refreshtoken = $json['refresh_token'];
		$mysqli -> query("UPDATE User SET RefreshToken = '$refreshtoken' WHERE FitbitID = '$getid'");



		/********************************************************

			4. アクセストークンからユーザ情報取得

		********************************************************/



		//ヘッダ情報
		$headers2 = [
        	'Authorization: Bearer ' . $json['access_token']
		];



		$url2 = "https://api.fitbit.com/1/user/" . $getid . "/activities/date/" . $subscriberdate . ".json";
		$url4 = "https://api.fitbit.com/1/user/" . $getid . "/sleep/date/" . $subscriberdate . ".json";


		sgetData($url2);
		sgetData($url4);


		/********************************************************

			5. 日付ごとにユーザ情報を保存

		********************************************************/



		//前回の日付を取得
		$message = 'No Update';
		$time = date('H:i:s');

		$sresult = $mysqli -> query("SELECT * FROM subscriber WHERE FitbitID = '$getid' AND Date = '$subscriberdate'");
		$srow = $sresult -> fetch_assoc();

		//日付が同じ場合
		if($srow){

			$activeMinutes = $srow['ActiveTime'];
			$steps = $srow['Steps'];
			$sleepMinutes = $srow['SleepTime'];

        	if ($activeMinutes != $newActiveMinutes){  //運動時間が多い場合
        		$activeMinutes = $newActiveMinutes;
        		$message = "New Active";
        	}
        	if ($steps != $newSteps) {
        		$steps = $newSteps;
        		$message = "New Steps";
        	}
        	if ($sleepMinutes != $newSleepMinutes) {
        		$sleepMinutes = $newSleepMinutes;
        		$message = "New Sleep";
        	}

         	$mysqli -> query("UPDATE subscriber SET ActiveTime = '$activeMinutes', Steps = '$steps', SleepTime = '$sleepMinutes', Time = '$time' WHERE FitbitID = '$getid' AND Date = '$subscriberdate'");

			//日付が違う場合
		}else{
        	$mysqli -> query("INSERT INTO subscriber(Date, Time, FitbitID, ActiveTime, SleepTime, Steps) VALUES('$subscriberdate', '$time', '$getid', '$newActiveMinutes', '$newSleepMinutes', '$newSteps')");
        	$message = 'New Date';
		}




		/********************************************************

			6. ログに出力

		********************************************************/



		$fp = fopen('log.txt', 'a');
        fwrite($fp, $subscriberdate . ", " . $time . ", " . $getid . ", " . $refreshtoken . ", " . $activeMinutes . ", ". $sleepMinutes . ", " . $steps . ", " . $message . "\r\n");
        fclose($fp);

		$mysqli -> close();

		$i++;
	}


?>