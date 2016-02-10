<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: text/html');
include($_SERVER['DOCUMENT_ROOT']."/releasev1/data/datafilter.php");
include($_SERVER['DOCUMENT_ROOT']."/releasev1/data/datarepository.php");
//get the query
$query = "ing";
if(isset($_GET['query'])) {
    $query = htmlspecialchars($_GET["query"]);
} elseif(isset($_POST['query'])) {
    $query = htmlspecialchars($_POST["query"]);
}
//determine whether you want a masterkey. 
//NOTE: masterkeys cause devautocomplete to resort by masterkey and pop an error due to keyword "value" being deeper in the JSON.
$getmstrkey = false;
if(htmlspecialchars($_GET['mstrkey'])===true || htmlspecialchars($_GET['mstrkey'])==="1" || strtolower($_GET['mstrkey'])==='true') {
    $getmstrkey = true;
} elseif(htmlspecialchars($_POST['mstrkey'])===true || htmlspecialchars($_POST['mstrkey'])==="1" || strtolower($_POST['mstrkey'])==='true') {
    $getmstrkey = true;
}
//select your sort order.
$sortdscnd = false;
if(htmlspecialchars($_GET['sortdscnd'])===true || htmlspecialchars($_GET['sortdscnd'])==="1" || strtolower($_GET['sortdscnd'])==='true') {
    $sortdscnd = true;
} elseif(htmlspecialchars($_POST['sortdscnd'])===true || htmlspecialchars($_POST['sortdscnd'])==="1" || strtolower($_POST['sortdscnd'])==='true') {
    $sortdscnd = true;
}
//get the sortkey.
$sortkey = "value";
if(isset($_GET['sortkey'])) {
    $sortkey = htmlspecialchars($_GET["sortkey"]);
} elseif(isset($_POST['sortkey'])) {
    $sortkey = htmlspecialchars($_POST["sortkey"]);
}
//get the depth.  Depth is numeric, default 0.  
$depth = 0;
if(isset($_GET['depth'])) {
    $depth = (int)htmlspecialchars($_GET["depth"]);
} elseif(isset($_POST['depth'])) {
    $depth = (int)htmlspecialchars($_POST["depth"]);
}

//determine the category of json we want.  
//if the json repository has the specified type, it will return all files of that type to the webpage.
$jsoncategory = 'all';
if(isset($_GET['jsoncategory'])) {
    $jsoncategory = htmlspecialchars($_GET["jsoncategory"]);      
} elseif(isset($_POST['jsoncategory'])) {
    $jsoncategory = htmlspecialchars($_POST["jsoncategory"]);
}

//get the files from the data repository.
$jsonfromfile = getJsonFromFiles($jsoncategory);

//Build Data Return.
$data = '';
$data .= '{';
$data .= '"suggestions": ';
//create constructor and add the results to the output.  
//optional parameter $getmstrkey defaults to false because it modifies JSON to incorporate where in original JSON the record exists.
//optional parameter $depth when records are defined lower than 0 indices in the json array, 
//optional paramater $sortkey to choose the key to sort by, 
//optional $sortdscnd defaults to 'false' (ascending); any other value results in descending.  
$data .= filterJsonForQuery($query, $jsonfromfile, $getmstrkey, $depth, $sortkey, $sortdscnd);  
$data .= '}';
echo $data;

?>