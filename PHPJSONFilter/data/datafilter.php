<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: text/html');


$matchedrecordkeys = array();  //tracks the record keys from the original files.  
$returnedjsonarray = array();  //stores the records that match.  

//This main function filters a Json file for matches to a search query term.  The matches to the query are 
//stored in a json_encoded array which is returned.  If the user wants to have the returned indices
//of the records from the json, the user can set $getmstrkey to true; only the first JSON file will 
//be searched and an array in JSON format will be returned with the record key from the original json file. 
//The depth from which the records are called can be modified in the instance of deeper record indices, however
//masterkeys will not be returned at that depth as the key may not point to one and only one record.  Masterkeys
//cause sort functionality to not work with devbridgeautocomplete and also causes errors, although values will still
//appear in devbridgeautocomplete.
//NOTES:
//optional parameter $getmstrkey defaults to false because it modifies JSON to incorporate where in original JSON the record exists.
//optional parameter $depth when records are defined lower than 0 indices in the json array, 
//optional paramater $sortkey to choose the key to sort by, 
//optional $sortdscnd defaults to 'false' (ascending); any other value results in descending.  
function filterJsonForQuery($query, $jsonfromfile, $getmstrkey = false, $recordDepth = 0, $sortkey = 'value', $sortdscnd = false){
global $returnedjsonarray;
$data = ''; //declares the return data.  
if($query!=""){
    /* 
    Call the below function to add json directly to $returnedjsonarray.  This can add json from multiple
    files but does not store the masterkey.  
    */
    if($getmstrkey === false || $recordDepth !== 0){
        foreach($jsonfromfile as $file){
                buildQueriedArray($file, $recordDepth);
            }
        usort($returnedjsonarray, function($a, $b) use ($sortkey, $sortdscnd) {
            if($sortdscnd === false){
                if(isset($a[$sortkey]) && isset($b[$sortkey])){
                    return ($a[$sortkey] < $b[$sortkey]) ? -1 : 1; 
                } elseif(isset($a[$sortkey]) && !(isset($b[$sortkey]))){
                    return -1;
                } else {return 1;}
            } else {
                if(isset($a[$sortkey]) && isset($b[$sortkey])){
                    return ($a[$sortkey] > $b[$sortkey]) ? -1 : 1; 
                } elseif(isset($a[$sortkey]) && !(isset($b[$sortkey]))){
                    return -1;
                } else {return 1;}
            }
        });
    }   
    /*
    Call the below function to return json from just ONE file.  This can return the masterkey, but
    if it does it will get re-sorted by the masterkey in DevBridgeAutoComplete.    
    */
    if($getmstrkey === true && $recordDepth === 0){
        $returnedjsonarray = buildArrayWithMSTRKY($jsonfromfile[0]);
        //uasort to maintain mstrkey.  
        uasort($returnedjsonarray, function($a, $b) use ($sortkey, $sortdscnd) { 
            if($sortdscnd === false){
                if(isset($a[$sortkey]) && isset($b[$sortkey])){
                    return ($a[$sortkey] < $b[$sortkey]) ? -1 : 1; 
                } elseif(isset($a[$sortkey]) && !(isset($b[$sortkey]))){
                    return -1;
                } else {return 1;}
            } else {
                if(isset($a[$sortkey]) && isset($b[$sortkey])){
                    return ($a[$sortkey] > $b[$sortkey]) ? -1 : 1; 
                } elseif(isset($a[$sortkey]) && !(isset($b[$sortkey]))){
                    return -1;
                } else {return 1;}
            }
        });
    }
}
$data .=json_encode($returnedjsonarray);
return $data;
}
/***** SUB FUNCTIONS *****/
//this function loops through json from a file, finds masterkeys and outputs matched records to an array.  
//it saves the masterkeys from that file, but not the name of that file.  
function buildArrayWithMSTRKY($jsonrecords) {
    global $matchedrecordkeys;
    $matchedrecordsarray = array();  //stores the records that match. 
    $dummyarray = array();
    $doesitmatch = false;
    $masterkey = array();  //tracks the record's original key from its json.
    //form the recursive array:
    $jsonIterator = new RecursiveIteratorIterator(
    new RecursiveArrayIterator(json_decode($jsonrecords, true)),
    RecursiveIteratorIterator::SELF_FIRST);
    foreach ($jsonIterator as $key => $val) {
        //Determine Depth and determine whether to remove a key from the masterkey.
        $currentDepth = $jsonIterator->getDepth();
        if($currentDepth===0){ //tests to see if the current key is a complete record.  
                if($doesitmatch === true){
                    $matchedrecordsarray[$masterkey[0]] = array_pop($dummyarray);  
                    //this maintains the $masterkey but gets resorted by AutoComplete.  
                    $doesitmatch = false;
                }
                array_pop($masterkey);
                array_pop($dummyarray);
                array_push($dummyarray, $val); //adds temporary values to a new array.
                array_push($masterkey, $key); 
        }
        //matches values to query from the depth you specified
        if($currentDepth >= $recordDepth){
            if(!is_array($val)) {
                if(matchObjToQuery($val) === true) {
                    addToMatchedRecordKeys($masterkey[0]);
                    $doesitmatch = true;
                }
            }
        }
    }//end foreach
    if($doesitmatch === true){ //test to see if LAST record is a match.
        $matchedrecordsarray[$masterkey[0]] = array_pop($dummyarray);  
    }
    return $matchedrecordsarray;
}

//this function just adds the json from a file directly to $returnedjsonarray.  It cannot/does not
//save the original masterkey from that file.  This can be done multiple times. 
function buildQueriedArray($jsonrecords, $recordDepth) {
    global $returnedjsonarray;
    global $matchedrecordkeys;
    $dummyarray = array();
    $doesitmatch = false;
    //form the recursive array:
    $jsonIterator = new RecursiveIteratorIterator(
    new RecursiveArrayIterator(json_decode($jsonrecords, true)),
    RecursiveIteratorIterator::SELF_FIRST);
    foreach ($jsonIterator as $key => $val) {
        //Determine Depth and determine whether to remove a key from the masterkey.
        $currentDepth = $jsonIterator->getDepth();
        if($currentDepth===$recordDepth) {//tests to see if the current key is a complete record.  
                if($doesitmatch === true){
                    //if(end($dummyarray)!==null){
                        array_push($returnedjsonarray, array_pop($dummyarray));
                    //} else { array_pop($dummyarray); echo "null";}
                    $doesitmatch = false;
                }
                array_pop($dummyarray);
                array_push($dummyarray, $val); //adds temporary values to a new array.
        }
        //matches values to query from the depth you specified:
        if($currentDepth >= $recordDepth){
            if(!is_array($val)) {
                if(matchObjToQuery($val) === true) {
                    $doesitmatch = true;
                }
            }
        }

    }//end foreach
    if($doesitmatch === true){ //test to see if LAST record is a match.
        array_push($returnedjsonarray, array_pop($dummyarray));
    }
}

//this function matches the values to the query string.
function matchObjToQuery($stringtomatch) {
    global $query;
    if(strpos(strtolower($stringtomatch), strtolower($query)) !== false){
        return true;
    }
}

//this function adds a matched record to the matched records array:
function addToMatchedRecordKeys($mkey){
    global $matchedrecordkeys;
    $alreadyin = false;
    foreach ($matchedrecordkeys as $value) {
        if($mkey === $value){
            $alreadyin = true;
        }
    }//end foreach
    if($alreadyin === false){
        array_push($matchedrecordkeys, $mkey);
    }
}

//This function is mainly used for debugging.
//This function writes an array out for all of the values in an array.  
//If a key is an array, it prints out the key and then puts the objects in a list:
function decodeArray($arrayToDecode) {
    foreach($arrayToDecode as $key => $obj){
        if(is_array($obj)) {
            echo $key . "</br>";
            decodeArray($obj);
        } else {
            if(gettype($key) == "integer"){
                    echo $obj . "</br>"; //was "<li>" . $obj . "</li>";
            } else {
                echo $key . ": " . $obj . "</br>";
            }
        }
    }//end foreach
}
?>