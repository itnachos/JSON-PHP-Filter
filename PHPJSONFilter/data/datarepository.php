<?php

function getJsonFromFiles($jsoncategory){
$jsonfromfile = array();
switch (strtolower($jsoncategory)) {
    case 'sports':
        array_push($jsonfromfile, file_get_contents('https://webelements-patjknott.c9users.io/releasev1/data/json/nhl2.json'));
        break;
    case 'movies':
        array_push($jsonfromfile, file_get_contents('https://webelements-patjknott.c9users.io/releasev1/data/json/post_1960.json'));
        break;
    case 'companies':  //employee records at depth 2
        array_push($jsonfromfile, file_get_contents('https://webelements-patjknott.c9users.io/releasev1/data/json/compemployees.json'));
        break;
    default:  //all records that do not specify a depth.
        array_push($jsonfromfile, file_get_contents('https://webelements-patjknott.c9users.io/releasev1/data/json/post_1960.json'));
        array_push($jsonfromfile, file_get_contents('https://webelements-patjknott.c9users.io/releasev1/data/json/nhl2.json'));
}
return $jsonfromfile;
}
?>