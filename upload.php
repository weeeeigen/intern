<?php

    //エラー表示
    ini_set('display_errors', 1);


    // POSTデータを受け取る
    $name = $_POST["UserName"];
    $lat = $_POST["lat"];
    $lon = $_POST["lon"];
    $level = $_POST["Level"];
    $type = $_POST["Type"];
    $steps = $_POST["Steps"];
    $image = $_FILES["file"]["name"];


    // 時間を設定
    date_default_timezone_set('Asia/Tokyo');
    $date = date('Y-m-d');
    $time = date('H:m:s');


    // LINEで通知
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
    $send_data["Name"] = $name;
    $send_data["Type"] = $type;
    
    SendRequest($url, $send_data);


    // 保存先とURLを設定
    $target_dir = "Image";
    $target_dir = $target_dir . "/" . basename($name) . "_" . $date . "_" . $time . ".png";
    $url = "" . $target_dir;


    // データベースに保存
    $mysqli = new mysqli('', '', '', '');
    if ( $mysqli -> query("INSERT INTO Eat(Date, Time, UserName, Level, Type, Latitude, Longitude, Steps, URL) VALUES('$date', '$time', '$name', '$level' ,'$type', '$lat', '$lon',  '$steps', '$url')") ){
        //ファイル保存
        if ( move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir) ){
            echo "true";
        }else{
            echo "false";
        }
    }
    //$mysqli -> close();

?>