<?php
header("Access-Control-Allow-Origin: *");
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$app = new \Slim\App;
define("GPSurl","http://map.foxtraxgps.com/service/v1.0/latest-points/list?format=json&api-key=3762ac00-71c2-ca48-4233-0000378d8de6");
define("statusAvailable",10);
define("statusMightBeTaken",5);
define("statusTaken",0);
//Gets the current location to all bikes and stores them into a SQL database
$app->get('/api/setLocation', function (Request $request, Response $response) {
	//Get current bike information from GPS data
	$get = file_get_contents(GPSurl);
	$json = json_decode($get,true);
	$arr = array();
	//Connect to database
	$conn = new mysqli("localhost","USERNAME","PASSWORD","GAB");
	
	//Get all unique bikes from Database where status was last 'Taken'
	$sql = "select b1.ID,b1.longitude,b1.latitude,b1.date,b1.status,b1.bikeid from " +
	"BIKE_LOCATIONS b1 INNER JOIN( SELECT max(date) date, bikeID from BIKE_LOCATIONS where " +	
	"status='Taken' group by bikeID) as b2 on b1.bikeID=b2.bikeID and b1.date=b2.date group by "+
	"b1.bikeid order by b1.bikeID ASC";
	$arr3 = array();
	$result = $conn->query($sql);
	//Store bike information into arr3
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
	//Get usefull information from GPS JSON object and store into arr
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
		//Insert into database
		$sql = "INSERT INTO BIKE_LOCATIONS (longitude,latitude,date,Status,bikeId) VALUES (".$long.",".$lat.",'".$date."','".$status."',". $bikeId .")";
		$conn->query($sql);
	}	
	return json_encode($arr);
});
//Gets the current Location to bikes that available(have not moved for 10 minutes)
$app->get('/api/getLocation', function (Request $request, Response $response) {	
	$conn = new mysqli("localhost","USERNAME","PASSWORD","GAB");
	$dateNow = date("Y-m-d H:i:00")-statusAvailable;
	//Get all bikes from Database from ~10 minutes ago
	$sql = "select b1.ID,b1.longitude,b1.latitude,b1.date,b1.status,b1.bikeid from BIKE_LOCATIONS b1 " +
	"INNER JOIN( SELECT max(date) date, bikeID from BIKE_LOCATIONS where status='Taken' group by bikeID) "+
	"as b2 on b1.bikeID=b2.bikeID and b1.date=b2.date group by b1.bikeid order by b1.bikeID ASC";
	$arr = array();
	$result = $conn->query($sql);
	//Store bike information into arr variable
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
	//Get current bike information from GPS data
	$get = file_get_contents(GPSurl);
	$json = json_decode($get,true);
	$date = date("Y-m-d H:i:00");
	$numberOfBikes = count($json['features']);
	//Get usefull information from GPS JSON object and store into arr
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
	$status = "";
	//Sort by availability(last time moved)
	for($x=0;$x<count($arr)-$numberOfBikes;$x++){//count($json)
		$long = $arr[$x+$numberOfBikes]["longitude"];
		$lat = $arr[$x+$numberOfBikes]["latitude"];
		
		$bikeId = $arr[$x+$numberOfBikes]["bikeId"];
		$recentBikeTime = date($arr[$x]['date']);
		
		$time =abs(strtotime($date) - strtotime($recentBikeTime))/60;
		$date = $arr[$x+$numberOfBikes]["date"];
		$currentBikeLocation = $x+$numberOfBikes;
		//$date->diff($recentBikeTime);
		//if($arr[$x]['longitude']==$arr[$currentBikeLocation]['longitude'] && $arr[$x]['latitude']==$arr[$currentBikeLocation]['latitude']){
			
			//Check last time moved is greater than significant time intervals
			if($time >= statusAvailable) $status = "Available";
			else if($time >=statusMightBeTaken && $time <statusAvailable) $status = "Might Be Taken";
			else $status = "Taken";
		//}
		
		
		//Store bike information into returnArr
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
	//Return array of all bikes with status updated
    return json_encode($returnArr);
});

//http://coenraets.org/blog/2011/12/restful-services-with-jquery-php-and-the-slim-framework/
$app->post('/api/changeBikeEnabled', function(){
	$request = Slim::getInstance()->request();
	$bikeInfo = json_decode($request->getBody());
	$sql = "INSERT into ENABLE_BIKES (bikeID, enabled) VALUES (:bikeID, :enabled)";
	try{
		$conn = new mysqli("localhost","USERNAME","PASSWORD","GAB");
		$stmt = $db->prepare($sql);
		$stmt->bindParam("bikeID",$bikeInfo->bikeID);
		$stmt->bindParam("enabled",$bikeInfo->enabled);
		$stmt->execute();
	}
	} catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
$app->run();
?>
