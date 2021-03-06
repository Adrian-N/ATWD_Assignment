<!DOCTYPE html>
<html>
<!--
    # Author: Adrian Nowak
    # Notes: PHP script to make html table from database
	# Resources:
	# http://php.net/manual/en/mysqli-result.fetch-array.php
	# http://php.net/manual/en/control-structures.while.php
	# http://php.net/manual/en/mysqli-result.fetch-array.php
-->
<head>
	<title>.</title>
	<meta charset="UTF-8">
	<style>
		*{
			font-family: arial;
		}
		table{
			border-collapse: collapse;
		}
		td{
			width:200px;
			border:solid 1px grey;
			text-align: center;
			
		}
		
		img{
			width: 25px;
		}

	</style>
</head>
<body>
<?php
//Additional variables:
	//To start <tr> tag:
	$a = 0;

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

    //Eliminate connection errors due to characters:
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

//Create Table:
	
	//Start with <table> tag
	echo "<table>";
	echo "<tr><th>Player</th><th>Years</th><th>Countries</th></tr>";
	
	//Loop
	$DBdata = runSQL($conn); //run SQL
	$id = ''; //clean id if this script is run more than once
	while($row = mysqli_fetch_array($DBdata)){
		if($id != $row["idChampions"] ){ //if it's new player
			
			//close country cell, start after first player
				if ($a != 0){
					echo "</td>";
				}
				
			//establish correct <tr> tag
				if ($a == 0){
					echo "<tr>"; 
					$a++;
				}else{
					if ($a == 16){
						//do nothing...
					}else{
						$a++;
						echo "</tr><tr>";
					}
				}
				
			//Player Name and Wiki Link
				echo "<td><a href='" . $row["playerLink"] . "'>" . $row["playerName"] . "</a></td>";
				
			//Years
				echo "<td>"; 
				echo $row["firstYear"]; //there always will be a first year
				
				//Check for second year:
					$yearTwo = $row["secondYear"];
					if ($yearTwo != ''){ echo "<br />" .$yearTwo;}
				
				//Check for third year:
					$yearThree = $row["thirdYear"];
					if ($yearThree != ''){ echo "<br />" .$yearThree;}
					
				echo "</td>";
				
			
			//Countries:
				//add first country
				echo "<td><img src='" . $row["countryFlag"] . "' alt='Country Image' /><a target='_blank' href='" . $row["countryLink"] . "'>" . $row["countryName"] . "</a><br />";
				
			//Close last cell
				if ($a == 16){
					echo"</td></tr>";
				}
				
			
		}else{ //add more countries
			
			//determine if there is a flag for country
			if ($row["countryFlag"] == ''){
			echo "(<a target='_blank' href='" . $row["countryLink"] . "'>" . $row["countryName"] . "</a>)<br />";
			}else{
			echo "<img src='" . $row["countryFlag"] . "' alt='Country Image' /><a target='_blank' href='" . $row["countryLink"] . "'>" . $row["countryName"] . "</a><br />";
			}
		}
		
		$id = $row["idChampions"]; //change player id
	}
	
	//Close table:
	echo "</table>";
	

?>

</body>
</html>