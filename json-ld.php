
<!DOCTYPE html>
<html>
<!-- 

	# Author: Adrian Nowak
	# Notes: PHP script to make JSON-LD from database
	# Resources:
	# http://schema.org/
	# http://json-ld.org/playground/
	# http://webmaster.yandex.com/microtest.xml

 -->
	<head>
		<meta charset="UTF-8">
		<title>JSON-LD</title>
	</head>
	<body>
	<pre>
<?php

//Additional variables:
	
	//Main Counter:
	$counter = 0;

	//Additional Counter:
	$counterTwo = 0;

	//Compare player id:
	$id = '';
//Database connection:
    
    //Connection data:
    require("connectionData.php");  

    //Create a connection:
    $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_USERNAME); 

    //Check if connection is working
    if(mysqli_connect_errno()){  
        echo "connection error! " . mysqli_connect_error();  
    }  

//Additional:

    //Eliminate connection errors due to bad characters:
    mysqli_set_charset($conn, "utf8"); 
	
//Create function to get the correct data from MySQL database:
function runSQL($conn){ 

	//select all from players and countries tables. Also match players and countries using the "player_has_countries" table.
	$sql = "SELECT * FROM players LEFT JOIN players_has_countries ON players.idChampions = players_has_countries.players_idChampions LEFT JOIN countries ON players_has_countries.countries_idCountry = countries.idCountry";
	
	//Perform a query on the database
    $DBdata = mysqli_query($conn,$sql); 

    //Check sql command:
    if (!mysqli_query($conn, $sql)) { 
        printf(mysqli_error($conn)); 
        echo "<br />NO RESULTS FROM SQL<br />";
		exit;
    }else{ 
        return $DBdata; 
    } 
    $DBdata->close(); 
} //end of function

	//run SQL
	$DBdata = runSQL($conn);
//Start JSON-LD


	//context
	echo "{";
  	echo '"@context":{' . "\n";
    echo '"person":{' . "\n";
    echo '"@id":"http://schema.org/Person"' . "\n";
    echo "}\n";
   	echo "},\n";
   
	//person
	echo"\t" .'"person":[' . "\n";
	
	//Main Loop
	while($row = mysqli_fetch_array($DBdata)){
		if($id != $row["idChampions"] ){ //if it's new player
				
			//Count Rows
			$counter++;

			//Count Countries
			$counterTwo++;

			//Close last entry
			if ($counter== 1) {
    				//do nothing
    			}else{
   					echo "},\n";
   				}

			//Player Name and Wiki Link
				echo "{\n";
				echo '"@context": "http://schema.org/",' . "\n";
				echo  '"@type": "Person",' . "\n";
				echo  '"name": "' . $row["playerName"] . '",' . "\n";
				echo  '"url": "' . $row["playerLink"] . '",' . "\n";

			//Years
				echo '"event":[' . "\n";
    			echo "{\n";
    			echo '"@context": "http://schema.org",' . "\n";
    			echo '"@type": "Event",' . "\n";

				//Set variables
				$yearThree = $row["thirdYear"];
				$yearTwo = $row["secondYear"];

				//How many years thre are?
				if ($yearTwo != ''){ 
					echo '"period": "' . $row["firstYear"] . '",' . "\n"; 
					echo '"period": "' . $row["secondYear"] . '"' . "\n"; 
				}elseif ($yearThree != '') { 
					echo '"period": "' . $row["firstYear"] . '",' . "\n"; 
					echo '"period": "' . $row["secondYear"] . '",' . "\n";
					echo '"period": "' . $row["thirdYear"] . '"' . "\n"; 
				}else{
					echo '"period": "' . $row["firstYear"] . '"' . "\n"; 
				}

				//Close Years
				echo "}\n";
  				echo "],\n";


			//Countries
				echo '"country":[' . "\n";
    			echo "{\n";
    			echo '"@context": "http://schema.org",' . "\n";
    			echo '"@type": "Country",' . "\n";
				echo '"name": "' . $row["countryName"] . '",' . "\n";	//Name
				echo '"logo": "' . $row["countryFlag"] . '",' . "\n";	//Flag
				echo '"url": "' . $row["countryLink"] . '"' . "\n"; 	//Wiki Link

				//Set comma after country
				//I am aware that this is not an efficient way but I could not wind another way of putting the comma in the correct places
    			//This way allows me to avoid validator errors and correctly mark-up JSON-LD
    			echo "}\n";
    			if ($counterTwo == 4) {
    				echo "]\n";
    			}elseif ($counterTwo == 5) {
    				echo "]\n";
    			}elseif ($counterTwo == 8) {
    				echo "]\n";
    			}elseif ($counterTwo == 19) {
    				echo "]\n";
    			}elseif ($counterTwo == 25) {
    				echo "]\n";
    			}elseif ($counterTwo == 26) {
    				echo "]\n";
    			}elseif ($counterTwo == 27) {
    				echo "]\n";
    			}else{
  				echo "],\n";
  				}

  				//Close last entry
  				if ($counter == 16) {
     				echo "}\n";
  				}
			
		}else{ //add more countries

			//Country +1
			$counterTwo++;

			//determine if there is a flag for country
			if ($row["countryFlag"] == ''){
				echo '"country":[' . "\n";
	    		echo "{\n";
	    		echo '"@context": "http://schema.org",' . "\n";
	    		echo '"@type": "Country",' . "\n";
				echo '"name": "' . $row["countryName"] . '",' . "\n"; 
				echo '"url": "' . $row["countryLink"] . '"' . "\n"; 
	    		echo "}\n";
	  			if ($counterTwo == 2) {
    				echo "],\n";
    			}elseif ($counterTwo == 23) {
    				echo "],\n";
    			}else{
  					echo "]\n";
  				}
			}else{
				echo '"country":[' . "\n";
    			echo "{\n";
    			echo '"@context": "http://schema.org",' . "\n";
    			echo '"@type": "Country",' . "\n";
				echo '"name": "' . $row["countryName"] . '",' . "\n";
				echo '"logo": "' . $row["countryFlag"] . '",' . "\n"; 
				echo '"url": "' . $row["countryLink"] . '"' . "\n"; 
    			echo "}\n";
  				if ($counterTwo == 2) {
    				echo "],\n";
    			}elseif ($counterTwo == 23) {
    				echo "],\n";
    			}else{
  					echo "]\n";
  				}
			}
		}
		$id = $row["idChampions"]; //change player id
	}
	
	//Close JSON-LD:
	echo "]\n";
	echo "}\n";

?>
		</pre>
	</body>
</html>