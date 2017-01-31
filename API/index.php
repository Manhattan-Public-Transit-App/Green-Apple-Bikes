<?php
header("Access-Control-Allow-Origin: *");
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$app = new \Slim\App;

$app->get('/api/setLocation', function (Request $request, Response $response) {
	$get = file_get_contents("http://map.foxtraxgps.com/service/v1.0/latest-points/list?format=json&api-key=3762ac00-71c2-ca48-4233-0000378d8de6");
	$json = json_decode($get,true);
	$arr = array();
	$conn = new mysqli("localhost","webadmin","uSKCK34rFYNBmzV7","GAB");
	
	
	$sql = "select b1.ID,b1.longitude,b1.latitude,b1.date,b1.status,b1.bikeid from BIKE_LOCATIONS b1 INNER JOIN( SELECT max(date) date, bikeID from BIKE_LOCATIONS where status='Taken' group by bikeID) as b2 on b1.bikeID=b2.bikeID and b1.date=b2.date group by b1.bikeid order by b1.bikeID ASC";
	$arr3 = array();
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
			$long = $row["longitude"];
			$lat = $row["latitude"];
			$status = $row["Status"];
			$bikeId = $row["bikeId"];
			$date = $row["date"];
			$arr4 = array(
				'longitude' => $long,
				'latitude' => $lat,
				'status' => $status,
				'bikeId' =>$bikeId,
				'date' => $date
			);
			array_push($arr3,$arr4);
			
		}
	}
	$date = date("Y-m-d H:i:00");
	for($i=0;$i<count($json['features']);$i++){
		$long = $json['features'][$i]['geometry']['coordinates'][0];
		$lat = $json['features'][$i]['geometry']['coordinates'][1];
		$bikeId = $i;
		if($arr3[$i]['longitude']==$long&&$arr3[$i]['latitude']==$lat)$status = 'Available';
		else $status = 'Taken';
		$arr2 = array(
			'longitude' => $long,
			'latitude' => $lat,
			'status' => $status,
			'bikeId' => $bikeId,
			'date' => $date
		);
		array_push($arr,$arr2);

		$sql = "INSERT INTO BIKE_LOCATIONS (longitude,latitude,date,Status,bikeId) VALUES (".$long.",".$lat.",'".$date."','".$status."',". $bikeId .")";
		$conn->query($sql);
	}	
	return json_encode($arr);
});

$app->get('/api/getLocation', function (Request $request, Response $response) {
	
	$conn = new mysqli("localhost","webadmin","uSKCK34rFYNBmzV7","GAB");
	$dateNow = date("Y-m-d H:i:00")-10;
	$sql = "select b1.ID,b1.longitude,b1.latitude,b1.date,b1.status,b1.bikeid from BIKE_LOCATIONS b1 INNER JOIN( SELECT max(date) date, bikeID from BIKE_LOCATIONS where status='Taken' group by bikeID) as b2 on b1.bikeID=b2.bikeID and b1.date=b2.date group by b1.bikeid order by b1.bikeID ASC";
	$arr = array();
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
			$long = $row["longitude"];
			$lat = $row["latitude"];
			$status = $row["Status"];
			$bikeId = $row["bikeId"];
			$date = $row["date"];
			$arr2 = array(
				'longitude' => $long,
				'latitude' => $lat,
				'status' => $status,
				'bikeId' =>$bikeId,
				'date' => $date
			);
			array_push($arr,$arr2);
			
		}
	} else {
		echo "<br> " . $result;
	}
	
	$get = file_get_contents("http://map.foxtraxgps.com/service/v1.0/latest-points/list?format=json&api-key=3762ac00-71c2-ca48-4233-0000378d8de6");
	$json = json_decode($get,true);
	$date = date("Y-m-d H:i:00");
	$numberOfBikes = count($json['features']);
	for($i=0;$i<$numberOfBikes;$i++){
		$long = $json['features'][$i]['geometry']['coordinates'][0];
		$lat = $json['features'][$i]['geometry']['coordinates'][1];
		$bikeId = $i;
		$arr3 = array(
			'longitude' => $long,
			'latitude' => $lat,
			'status' => $status,
			'bikeId' => $bikeId,
			'date' => $date
		);
		array_push($arr,$arr3);
	}
	$returnArr = array();
	
	for($x=0;$x<count($arr)-$numberOfBikes;$x++){//count($json)
		$long = $arr[$x+$numberOfBikes]["longitude"];
		$lat = $arr[$x+$numberOfBikes]["latitude"];
		$status = "";
		$bikeId = $arr[$x+$numberOfBikes]["bikeId"];
		$recentBikeTime = date($arr[$x]['date']);
		
		$time =abs(strtotime($date) - strtotime($recentBikeTime))/60;
		$date = $arr[$x+$numberOfBikes]["date"];
		$currentBikeLocation = $x+$numberOfBikes;
		// $date->diff($recentBikeTime);
		//if($arr[$x]['longitude']==$arr[$currentBikeLocation]['longitude'] && $arr[$x]['latitude']==$arr[$currentBikeLocation]['latitude']){
			if($time >=10) $status = "Available";
			else if($time >=5 && $time <10) $status = "Might Be Taken";
			else $status = "Taken";
		//}
		//else{
			
		//	$status = "Taken";
		//}
		$arr4 = array(
				'longitude' => $long,
				'latitude' => $lat,
				'status' => $status,
				'bikeId' =>$bikeId,
				'id' => $json['features'][$x]['properties']['label'],
				'date' => $date,
				'time' =>$time
		);
		array_push($returnArr,$arr4);
	}
    return json_encode($returnArr);
});
$app->run();
?>
