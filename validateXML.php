<?php
/*
	# Student Name: Adrian Nowak
	# Student Number: 12018991
	# Notes: Validate XML based on XSD schema.
	# Based on example from:
	# http://stackoverflow.com/questions/11650377/validate-a-xml-file-against-a-xsd-using-php
	# Resources:
	# http://php.net/manual/en/domdocument.schemavalidate.php
*/
	
	
	// New DOM document:
	$xml= new DOMDocument();
	
	//Files
	$a = 'players.xml'; 	// XML File
	$b = 'xmlSchema.xsd';	// XSD File 
	
	//Load XML file:
	$xml->load($a, LIBXML_NOBLANKS);

	//Validate against schema:
	if (!$xml->schemaValidate($b)) 
	{
	   echo "XML file contains error(s)!";
	} else {
		echo"<strong>XML file is valid</strong>. <br />";
		echo "<a href='" . $a . "'>Go to XML file</a><br />";
		echo "<a href='" . $b . "'>Go to XSD file</a>";
	}

?>