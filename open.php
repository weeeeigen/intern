<?php

	$message = $_POST["message"];
	$user_name = $_POST["name"];

	$mysqli = new mysqli("", "","", "");

	if($mysqli->connect_errno) {
		echo 'データベースアクセスエラー';
   	 	exit;
	}

	$query = "INSERT INTO key_state (name, state) VALUES ('$user_name', '$message')";

	if( $mysqli->query( $query ) ) {
    	echo 'INSERT成功';
	}else {
    	echo 'INSERT失敗';
	}

	$mysqli->close();

	function SendRequest($url, $send_data){
		$query_string = http_build_query($send_data);

		$options = array(
			"http" => array(
				"method" => "POST",
				"content" => $query_string
			)
		);

		return file_get_contents($url, false, stream_context_create($options));

	}

	$url = "";

	$send_data["message"] = "open";

	echo SendRequest($url, $send_data);
	fwrite($fp, "post");

	$fp = fopen('test.txt', 'a');
		fwrite($fp, "ok");
		fclose($fp);


	echo $message;


?>
