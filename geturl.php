<?php

	ini_set('diplay_errors', 1);

	$name = $_POST["UserName"];
	$date = $_POST["Date"];

	$urls = array();
	$types = array();
	$times = array();
	$sendData = array();

	$mysqli = new mysqli('', '', '', '');
    $result = $mysqli -> query("SELECT URL FROM Eat WHERE UserName = '$name' AND Date = '$date'");
    while($row = $result -> fetch_assoc()){
    	array_push($urls, $row["URL"]);
    }
    $result2 = $mysqli -> query("SELECT Type FROM Eat WHERE UserName = '$name' AND Date = '$date'");
    while ($row2 = $result2 -> fetch_assoc()) {
    	array_push($types, $row2["Type"]);
    }
    $result3 = $mysqli -> query("SELECT Time FROM Eat WHERE UserName = '$name' AND Date = '$date'");
    while ($row3 = $result3 -> fetch_assoc()) {
    	array_push($times, $row3["Time"]);
    }

    array_push($sendData, $urls);
    array_push($sendData, $types);
    array_push($sendData, $times);

    echo json_encode($sendData);

?>