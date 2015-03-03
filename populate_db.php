<html>
<head>
	<meta charset="UTF-8">
</head>
<body>
<?php
/*
    # Author: Adrian Nowak
    # Notes: PHP script to populate MySQL database 
    # Resources:
    # https://www.daniweb.com/web-development/php/threads/83076/if-url-exists
    # http://stackoverflow.com/questions/8099171/php-code-to-delete-all-records-should-it-be-working
    # http://stackoverflow.com/questions/3379410/getting-error-cannot-use-object-of-type-mysqli-result-as-array
    # http://php.net/manual/en/index.php
	#
	# Extra notes: This code could be improved by implementing more functions instead of if statements.
*/

//php works?
echo ">PHP is working...<br />";

//Variables
	
	//row counters
	$counter = 0;

	

//Taking the data from HTML is based on simple html dom, source:
//http://simplehtmldom.sourceforge.net/
$simpleHTMLDom = "simple_html_dom.php";
if (file_exists($simpleHTMLDom)) {
    include 'simple_html_dom.php';
    echo ">Including " . $simpleHTMLDom . "...<br />";
}else{
    echo "There is no" . $simpleHTMLDom . "file!<br />";
    echo "Exiting...<br />";
    exit;
} 

//Connection Data:
$connData = "connectionData.php";
if (file_exists($simpleHTMLDom)) {
    require ($connData);
    echo ">Including " . $connData . "...<br />";
}else{
    echo "There is no" . $connData . "file!<br />";
    echo "Exiting...<br />";
    exit;
}


//Create a connection:
$conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_USERNAME);

//Check if connection is working
    if(mysqli_connect_errno()){  
        echo "connection error! " . mysqli_connect_error();  
    }

//set encoding
header('Content-type:text/html; charset=utf-8');

//check if URL exist

    $URL = "sample_data.html";
    $table = $URL;
    $handle = @fopen($table,'r');
    if($handle !== false){

    	//All good
    	echo '>Getting HTML from URL..<br />';

    	//get html table
    	$htmlTable = file_get_html($URL);
    }
    else{

    	//Exit
    	echo "Website doesn't exist!";
   		exit;
    }

    //Clear tables
    echo ">Attempting to clear MySQL tables...<br />";
    $emptyTable = "TRUNCATE TABLE players_has_countries";
    $emptyTableTwo ="DELETE FROM players";
    $emptyTableThree ="DELETE FROM countries";

    mysqli_query($conn, $emptyTable);
    mysqli_query($conn, $emptyTableTwo);
    mysqli_query($conn, $emptyTableThree);
    
    

    //Check if table is empty
    $checkTable = "SELECT COUNT(*) as num FROM players";
    $checkTablee = $conn->query($checkTable);
    $checkRow = $checkTablee->fetch_assoc();
    $checkTable = $checkRow['num'];
    if ($checkTable == 0) {
        echo '>Table "Players" is empty... <br />';
    }else{
        echo ">Players table is not empty! <br />";
        echo "Exiting...<br />";
        exit;
    }

//Engine

    //Loop
    echo ">Attempting to populate MySQL Tables...<br />";
    foreach($htmlTable->find('tr') as $row) {
    	//echo ">Loop: Start Loop...<br />";

    	//add to counter
    	$counter++;

    	//skip first row
    	if($counter!=1){

    	//Populating Champions Table

    		//Getting Champion Name
    		$championName = strip_tags($row->find('a',0));
            $championName = trim($championName);
            $championName = str_replace('é', '&#233;', $championName);
            $championName = str_replace('ú', '&#250;', $championName);
    		//echo $championName . "<br />";

    		//Getting Champion Wiki Link
    		$championLink = $row->find('a',0)->href;
    		//echo $championLink . "<br />";

    		//Years
    			//get cell from second column
    			$allYears = $row->find('td',1);
                //$allYears = str_replace('-', '-', $allYears->innertext);-
    			//replace <br> tags
    			$allYears = str_replace(array('<br>','<br/>'), '|', $allYears);
                //print_r($allYears);
                //echo "<br />";
                $allYears = str_replace(array('-', '–'), '-', $allYears);
                $allYears = str_replace(array('â€“'), '-', $allYears);





    			//converting years to arrays
    			$allYears = explode('|', $allYears);
    			//Getting First Year
    			$firstYear = $allYears[0];
    			$firstYear = strip_tags($firstYear);
                //echo $firstYear . "<br />";

    			//Getting Second Year
    			$secondYear = '';
    			if(isset($allYears[1])){
    				$secondYear = $allYears[1];
    				$secondYear = strip_tags($secondYear);
    				//echo $secondYear . "<br /	>";
    			}//isset

    			//Getting Third Year
    			$thirdYear = '';
    			if (isset($allYears[2])){
    				$thirdYear = $allYears[2];
    				$thirdYear = strip_tags($thirdYear);
    			}//isset

            //Sending data to players table
                $playerQuery = "INSERT INTO players (playerName, playerLink, firstYear, secondYear, thirdYear) VALUES ('$championName', '$championLink', '$firstYear', '$secondYear', '$thirdYear')";
                mysqli_query($conn, $playerQuery);

            //take player ID
            $takePlayerID = "SELECT playerName,idChampions FROM players WHERE playerName='$championName'";
            $result = mysqli_query($conn, $takePlayerID );
            $takePlayerIDRow = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $playerID = $takePlayerIDRow['idChampions'];
            //echo $playerID . "<br />";

            //Countries

                //get cell from third column
                $allCountries = $row->find('td',2);
                //$allCountries = $allCountries->innertext;


                //replace <br> tags
                $allCountries = str_replace(array('<br>','<br/>'), '|', $allCountries);

                //replace (
                $allCountries = str_replace(array('('), '|', $allCountries);

                //delete )
                $allCountries = str_replace(array(')'), '', $allCountries);

                //delete td tag
                $allCountries = str_replace(array('<td>'), '', $allCountries);

                $allCountries = str_replace(array('</td>'), '', $allCountries);

                //set countries to arrays
                $arrayCountries = explode('|', $allCountries);

                //count countries
                $numberOfCountries = count($arrayCountries);
                
                //if there is only one country in array
                if ($numberOfCountries == 1) {

                    //take from array
                    $oneCountry = $arrayCountries[0];

                    

                    //take name of country
                    $oneCountryName = strip_tags($oneCountry); //delete html tags
                    $oneCountryName = str_replace('&nbsp;', '', $oneCountryName);//delete non breaking space
                    $oneCountryName = trim($oneCountryName);
                    //echo $oneCountryName . "<br />";

                    //take link of the country
                    $dom = new DomDocument();
                    $dom->loadHTML($arrayCountries[0]);
                    $oneCountryLink = $dom->getElementsByTagName('a')->item(0)->getAttribute('href');
                    //echo $oneCountryLink . "<br />";

                    //take the flag
                    $dom = new DomDocument();
                    $dom->loadHTML($arrayCountries[0]);
                    $oneCountryFlag = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
                    //echo $oneCountryFlag . "<br />";

                    //check if country already exist
                    $oneCountryExist = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                    $result = mysqli_query($conn, $oneCountryExist);
                    $firstLoopRow = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    if ($firstLoopRow['countryName'] == $oneCountryName) {
                        $countryID = $firstLoopRow['idCountry']; //take country ID
                        //echo "$countryID";
                        //echo "country already exist!<br />";
                    }else{ //if the country doesnt exist

                        //send data to DB
                        $playerQueryTwo = "INSERT INTO countries (countryName, countryLink, countryFlag) VALUES ('$oneCountryName', '$oneCountryLink', '$oneCountryFlag')";
                        mysqli_query($conn, $playerQueryTwo);

                        //take country ID - find it first
                        $takeCountryID = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                        $result = mysqli_query($conn, $takeCountryID );
                        $takeCountryIDRow = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        if ($takeCountryIDRow['countryName'] == $oneCountryName) {
                            $countryID = $takeCountryIDRow['idCountry']; //take country ID
                            //echo $countryID;
                        } //if -take county ID
                    } //if- if the country doesnt exist

                    //populate player_has_country table for player with one country

                    //echo "player ID" . $playerID . " has country ID" . $countryID . "<br />";
                    $joinedTable = "INSERT INTO players_has_countries (players_idChampions, countries_idCountry) VALUES ('$playerID', '$countryID')";
                    mysqli_query($conn, $joinedTable);

                } //if - if there is one country in array
                 



                //if there are two countries in array
                if ($numberOfCountries == 2) {

                    //first country in array
                    if (isset($arrayCountries[0])) {


                        //take from array
                    $oneCountry = $arrayCountries[0];

                    //take name of country
                    $oneCountryName = strip_tags($oneCountry); //delete html tags
                    $oneCountryName = str_replace('&nbsp;', '', $oneCountryName);//delete non breaking space
                    $oneCountryName = trim($oneCountryName);
                    //echo $oneCountryName . "<br />";

                    //take link of the country
                    $dom = new DomDocument();
                    $dom->loadHTML($arrayCountries[0]);
                    $oneCountryLink = $dom->getElementsByTagName('a')->item(0)->getAttribute('href');
                    //echo $oneCountryLink . "<br />";

                    //take the flag
                    $dom = new DomDocument();
                    $dom->loadHTML($arrayCountries[0]);

                    $error = $arrayCountries[0];
                    if (strpos($error,'img') !== false) {
                        $oneCountryFlag = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
                    }else{
                        $oneCountryFlag = "";
                    }

                    //echo $oneCountryFlag . "<br />";

                    //check if country already exist
                    $oneCountryExist = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                    $result = mysqli_query($conn, $oneCountryExist);
                    $firstLoopRow = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    if ($firstLoopRow['countryName'] == $oneCountryName) {
                        $countryID = $firstLoopRow['idCountry']; //take country ID
                        //echo "$countryID";
                        //echo "country already exist!<br />";
                    }else{ //if the country doesnt exist

                        //send data to DB
                        $playerQueryTwo = "INSERT INTO countries (countryName, countryLink, countryFlag) VALUES ('$oneCountryName', '$oneCountryLink', '$oneCountryFlag')";
                        mysqli_query($conn, $playerQueryTwo);

                        //take country ID - find it first
                        $takeCountryID = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                        $result = mysqli_query($conn, $takeCountryID );
                        $takeCountryIDRow = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        if ($takeCountryIDRow['countryName'] == $oneCountryName) {
                            $countryID = $takeCountryIDRow['idCountry']; //take country ID
                            //echo $countryID;
                        } //if -take county ID
                    } //if- if the country doesnt exist

                    //populate player_has_country table for player with one country

                    //echo "player ID" . $playerID . " has country ID" . $countryID . "<br />";
                    $joinedTable = "INSERT INTO players_has_countries (players_idChampions, countries_idCountry) VALUES ('$playerID', '$countryID')";
                    mysqli_query($conn, $joinedTable);



                        
                    }else{
                        echo ">Error during taking country from array...";
                        exit;
                    }

                    //second country in array
                    if (isset($arrayCountries[1])) {

                            //take from array
                        $oneCountry = $arrayCountries[1];

                        

                        //take name of country
                        $oneCountryName = strip_tags($oneCountry); //delete html tags
                        $oneCountryName = str_replace('&nbsp;', '', $oneCountryName);//delete non breaking space
                        $oneCountryName = trim($oneCountryName);
                        $oneCountryName = str_replace('é', '&#233;', $oneCountryName);
                        //echo $oneCountryName . "<br />";

                        //take link of the country
                        //$dom = new DomDocument();
                        $domTwo = new DomDocument();
                        //$dom->loadHTML($arrayCountries[0]);

                        $domTwo->loadHTML($arrayCountries[1]);
                        $oneCountryLink = $domTwo->getElementsByTagName('a')->item(0)->getAttribute('href');
                        //echo $oneCountryLink . "<br />";

                        //take the flag
                        $dom = new DomDocument();
                        $dom->loadHTML($arrayCountries[1]);

                        $error = $arrayCountries[1];
                        if (strpos($error,'img') !== false) {
                            $oneCountryFlag = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
                        }else{
                            $oneCountryFlag = "";
                        }

                        //echo $oneCountryFlag . "<br />";

                        //check if country already exist
                        $oneCountryExist = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                        $result = mysqli_query($conn, $oneCountryExist);
                        $firstLoopRow = mysqli_fetch_array($result, MYSQLI_ASSOC);

                        if ($firstLoopRow['countryName'] == $oneCountryName) {
                            $countryID = $firstLoopRow['idCountry']; //take country ID
                            //echo "$countryID";
                            //echo "country already exist!<br />";
                        }else{ //if the country doesnt exist

                            //send data to DB
                            $playerQueryTwo = "INSERT INTO countries (countryName, countryLink, countryFlag) VALUES ('$oneCountryName', '$oneCountryLink', '$oneCountryFlag')";
                            mysqli_query($conn, $playerQueryTwo);

                            //take country ID - find it first
                            $takeCountryID = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                            $result = mysqli_query($conn, $takeCountryID );
                            $takeCountryIDRow = mysqli_fetch_array($result, MYSQLI_ASSOC);
                            if ($takeCountryIDRow['countryName'] == $oneCountryName) {
                                $countryID = $takeCountryIDRow['idCountry']; //take country ID
                                //echo $countryID;
                            } //if -take county ID
                        } //if- if the country doesnt exist

                        //populate player_has_country table for player with one country

                        //echo "player ID" . $playerID . " has country ID" . $countryID . "<br />";
                        $joinedTable = "INSERT INTO players_has_countries (players_idChampions, countries_idCountry) VALUES ('$playerID', '$countryID')";
                    mysqli_query($conn, $joinedTable);



                        
                    }else{
                        echo ">Error during taking country from array...";
                        exit;
                    }
                } //if there are two countries in array



                //if there are three countries in array
                if ($numberOfCountries == 3) {

                    //first country in array
                    if (isset($arrayCountries[0])) {


                        //take from array
                    $oneCountry = $arrayCountries[0];

                    //take name of country
                    $oneCountryName = strip_tags($oneCountry); //delete html tags
                    $oneCountryName = str_replace('&nbsp;', '', $oneCountryName);//delete non breaking space
                    $oneCountryName = trim($oneCountryName);
                    //echo $oneCountryName . "<br />";

                    //take link of the country
                    $dom = new DomDocument();
                    $dom->loadHTML($arrayCountries[0]);
                    $oneCountryLink = $dom->getElementsByTagName('a')->item(0)->getAttribute('href');
                    //echo $oneCountryLink . "<br />";

                    //take the flag
                    $dom = new DomDocument();
                    $dom->loadHTML($arrayCountries[0]);

                    $error = $arrayCountries[0];
                    if (strpos($error,'img') !== false) {
                        $oneCountryFlag = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
                    }else{
                        $oneCountryFlag = "";
                    }
                    //$oneCountryFlag = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
                    //echo $oneCountryFlag . "<br />";

                    //check if country already exist
                    $oneCountryExist = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                    $result = mysqli_query($conn, $oneCountryExist);
                    $firstLoopRow = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    if ($firstLoopRow['countryName'] == $oneCountryName) {
                        $countryID = $firstLoopRow['idCountry']; //take country ID
                        //echo "$countryID";
                        //echo "country already exist!<br />";
                    }else{ //if the country doesnt exist

                        //send data to DB
                        $playerQueryTwo = "INSERT INTO countries (countryName, countryLink, countryFlag) VALUES ('$oneCountryName', '$oneCountryLink', '$oneCountryFlag')";
                        mysqli_query($conn, $playerQueryTwo);

                        //take country ID - find it first
                        $takeCountryID = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                        $result = mysqli_query($conn, $takeCountryID );
                        $takeCountryIDRow = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        if ($takeCountryIDRow['countryName'] == $oneCountryName) {
                            $countryID = $takeCountryIDRow['idCountry']; //take country ID
                            //echo $countryID;
                        } //if -take county ID
                    } //if- if the country doesnt exist

                    //populate player_has_country table for player with one country

                    //echo "player ID" . $playerID . " has country ID" . $countryID . "<br />";
                    $joinedTable = "INSERT INTO players_has_countries (players_idChampions, countries_idCountry) VALUES ('$playerID', '$countryID')";
                    mysqli_query($conn, $joinedTable);



                        
                    }else{
                        echo ">Error during taking country from array...";
                        exit;
                    }

                    //second country in array
                    if (isset($arrayCountries[1])) {

                            //take from array
                        $oneCountry = $arrayCountries[1];

                        

                        //take name of country
                        $oneCountryName = strip_tags($oneCountry); //delete html tags
                        $oneCountryName = str_replace('&nbsp;', '', $oneCountryName);//delete non breaking space
                        $oneCountryName = trim($oneCountryName);
                        //echo $oneCountryName . "<br />";

                        //take link of the country
                        $dom = new DomDocument();
                        $dom->loadHTML($arrayCountries[1]);
                        $oneCountryLink = $dom->getElementsByTagName('a')->item(0)->getAttribute('href');
                        //echo $oneCountryLink . "<br />";

                        //take the flag
                        $dom = new DomDocument();
                        $dom->loadHTML($arrayCountries[1]);
                        $error = $arrayCountries[1];
                        if (strpos($error,'img') !== false) {
                            $oneCountryFlag = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
                        }else{
                            $oneCountryFlag = "";
                        }


                        
                        //echo $oneCountryFlag . "<br />";

                        //check if country already exist
                        $oneCountryExist = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                        $result = mysqli_query($conn, $oneCountryExist);
                        $firstLoopRow = mysqli_fetch_array($result, MYSQLI_ASSOC);

                        if ($firstLoopRow['countryName'] == $oneCountryName) {
                            $countryID = $firstLoopRow['idCountry']; //take country ID
                            //echo "$countryID";
                            //echo "country already exist!<br />";
                        }else{ //if the country doesnt exist

                            //send data to DB
                            $playerQueryTwo = "INSERT INTO countries (countryName, countryLink, countryFlag) VALUES ('$oneCountryName', '$oneCountryLink', '$oneCountryFlag')";
                            mysqli_query($conn, $playerQueryTwo);

                            //take country ID - find it first
                            $takeCountryID = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                            $result = mysqli_query($conn, $takeCountryID );
                            $takeCountryIDRow = mysqli_fetch_array($result, MYSQLI_ASSOC);
                            if ($takeCountryIDRow['countryName'] == $oneCountryName) {
                                $countryID = $takeCountryIDRow['idCountry']; //take country ID
                                //echo $countryID;
                            } //if -take county ID
                        } //if- if the country doesnt exist

                        //populate player_has_country table for player with one country

                        //echo "player ID" . $playerID . " has country ID" . $countryID . "<br />";
                        $joinedTable = "INSERT INTO players_has_countries (players_idChampions, countries_idCountry) VALUES ('$playerID', '$countryID')";
                        mysqli_query($conn, $joinedTable);



                        
                    }else{
                        echo ">Error during taking country from array...";
                        exit;
                    }




                    //third country in array
                    if (isset($arrayCountries[2])) {

                            //take from array
                        $oneCountry = $arrayCountries[2];

                        

                        //take name of country
                        $oneCountryName = strip_tags($oneCountry); //delete html tags
                        $oneCountryName = str_replace('&nbsp;', '', $oneCountryName);//delete non breaking space
                        $oneCountryName = trim($oneCountryName);
                        //echo $oneCountryName . "<br />";

                        //take link of the country
                        $dom = new DomDocument();
                        $dom->loadHTML($arrayCountries[2]);
                        $oneCountryLink = $dom->getElementsByTagName('a')->item(0)->getAttribute('href');
                        //echo $oneCountryLink . "<br />";

                        //take the flag
                        $dom = new DomDocument();
                        $dom->loadHTML($arrayCountries[2]);

                        $error = $arrayCountries[2];
                        if (strpos($error,'img') !== false) {
                            $oneCountryFlag = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
                        }else{
                            $oneCountryFlag = "";
                        }

                        //echo $oneCountryFlag . "<br />";

                        //check if country already exist
                        $oneCountryExist = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                        $result = mysqli_query($conn, $oneCountryExist);
                        $firstLoopRow = mysqli_fetch_array($result, MYSQLI_ASSOC);

                        if ($firstLoopRow['countryName'] == $oneCountryName) {
                            $countryID = $firstLoopRow['idCountry']; //take country ID
                            //echo "$countryID";
                            //echo "country already exist!<br />";
                        }else{ //if the country doesnt exist

                            //send data to DB
                            $playerQueryTwo = "INSERT INTO countries (countryName, countryLink, countryFlag) VALUES ('$oneCountryName', '$oneCountryLink', '$oneCountryFlag')";
                            mysqli_query($conn, $playerQueryTwo);

                            //take country ID - find it first
                            $takeCountryID = "SELECT countryName,idCountry FROM countries WHERE countryName='$oneCountryName'";
                            $result = mysqli_query($conn, $takeCountryID );
                            $takeCountryIDRow = mysqli_fetch_array($result, MYSQLI_ASSOC);
                            if ($takeCountryIDRow['countryName'] == $oneCountryName) {
                                $countryID = $takeCountryIDRow['idCountry']; //take country ID
                                //echo $countryID;
                            } //if -take county ID
                        } //if- if the country doesnt exist

                        //populate player_has_country table for player with one country

                        //echo "player ID" . $playerID . " has country ID" . $countryID . "<br />";
                        $joinedTable = "INSERT INTO players_has_countries (players_idChampions, countries_idCountry) VALUES ('$playerID', '$countryID')";
                    mysqli_query($conn, $joinedTable);

                        
                    }else{
                        echo ">Error during taking country from array...";
                        exit;
                    }
                } //if there are two countries in array


    		//in case of bad loop
    		if($counter==100){
    			exit;
    		}


    	}//if statement to skip first row


        echo(">Populating tables, " . $counter . "/17...<br />");
    }// main loop
    echo ">All done!<br />";
    echo ">Exiting...";
    exit;


?>
</body>
</html>