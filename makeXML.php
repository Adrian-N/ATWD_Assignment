<?php 

/*
    # Author: Adrian Nowak
    # Notes: PHP script to make XML from database
	# Resources:
	# http://php.net/manual/en/class.simplexmlelement.php
	# http://stackoverflow.com/questions/19086344/how-to-display-simplexmlelement-with-php
	# http://php.net/manual/en/mysqli-result.fetch-array.php
*/


//Database connection:
    
    //Connection data:
    require("connectionData.php");  

    //Create a connection:
    $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_USERNAME); 

    //Check if connection is working
    if(mysqli_connect_errno()){  
        echo "connection error! " . mysqli_connect_error();  
    }  

//Files:
	$xmlFile = 'players.xml'; //Final XML file
	
//Additional:

    //Eliminate connection errors due to characters:
    mysqli_set_charset($conn, "utf8"); 

//Empty XML file on the beginning
	
	//open XML file
	echo ">Attempting to open " . $xmlFile . " file...<br />";
	$file = @fopen("$xmlFile", "r+");
	
	//Empty XML file
	if ($file !== false) {
		ftruncate($file, 0);
		fclose($file);
		echo ">Deleting old XML data from " . $xmlFile . "...<br />";
	}else{
		echo ">Error while trying to open " . $xmlFile . " file!<br /> >Exiting...";
		exit;
	}


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


	//Create function to generate XML:
	function makeXMLfile($conn){ 
		
		$id = ''; //clean id if this script is run more than once
        $DBdata = runSQL($conn); //run SQL
        $xml = new SimpleXMLElement('<players/>'); //new SimpleXMLElement
		
		//engine - go through the database
        while($row = mysqli_fetch_array($DBdata)){
			
			//time to create new player
            if($id != $row["idChampions"] ){ 
               
			   //add child "player"
                $playerChild = $xml->addChild('player'); 
					$name = $playerChild->addChild(
						'playerName',$row["playerName"]									//player name
					);	
					$name->addAttribute(
						'wikiLink',$row["playerLink"]									//player link
					); 	   			
					
					//add child "dates"
					$dates = $playerChild->addChild('championYears'); 
						$dates->addChild(
							'firstYears',$row["firstYear"]								// there is always first year
						); 				
						if(!empty($secondYear)){ 										//check for a second date
						$dates->addChild(
							'secondYears',$row["secondYear"]								//add second year if not empty
						); 			
						}  
						if(!empty($thirdYear)){ 										//check for a third year
							$dates->addChild(
								'thirdYears',$row["thirdYear"]							//add third year if not empty
							); 				
						}

					//add child "countries"
					$country = $playerChild->addChild('countries'); 						//there is always at least one country
						$countryName = $country->addChild(
							'country',$row["countryName"]									//create country name $row["countryName"]
						);	
						$countryName->addAttribute(
							'wikiLink',$row["countryLink"]									//add country URL attribute
						);			 
						$countryName->addAttribute(
							'flag',$row["countryFlag"]										//add country flag attribute
						);				
            }
			else //if there is more than one country - stay within current element and add more countries
				{ 
				
				if (empty($countryFlag)) {												//check if there is a flag for the country 
					$countryName = $country->addChild(
						'country',$row["countryName"]									//create country name
					);	 
                    $countryName->addAttribute(
						'wikiLink',$row["countryLink"]									//add country URL attribute
					); 		
                }
				else{ 																	//if there is a flag for country
                    $countryName = $country->addChild(
						'country',$row["countryName"]									//create country name
					); 	
                    $countryName->addAttribute(
						'wikiLink',$row["countryLink"]									//add country URL attribute
					); 		
                    $countryName->addAttribute(
						'flag',$row["countryFlag"]										//add country flag attribute
					); 			
                }
            }
            $id = $row["idChampions"] ; //change player id
        } 
        $xml = $xml->asXML(); 
        return $xml; // end the function
    }
			
echo ">Creating XML data...<br />";  
$finalXML = makeXMLfile($conn);										//call the function to create XML
if (empty($finalXML)) {												//Check if XML has been returned
	echo "There was an error while creating XML data! <br />";		//display message if there is no XML
	exit;
}else{	
	echo '>Sending XML data to "' . $xmlFile . '"...<br/>';
	file_put_contents($xmlFile, $finalXML); 													//replace xml in players.xml file
	if (filesize($xmlFile) == 0 ) { 															//check if player.xml file is not empty
		echo ">There was an error while creating XML file! <br /> " . $xmlFile . " is empty";	//display message if player.xml is empty and exit
		exit; 
	}else{
		echo ">XML file successfully created!<br />";											//All Good!
		echo "<a href='" . $xmlFile . "'>Click here for XML file.</a>";							//Create link to XML file
	}
}
		

?>