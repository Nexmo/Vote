<?php
//setup autoloading
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
$config = include 'config.php';

//some nexmo includes
require_once 'lib/nexmo/NexmoMessage.php';

//validate it's from nexmo
$nexmo = new NexmoMessage($config['nexmo']['user'], $config['nexmo']['pass']);
if(!$nexmo->inboundText($_REQUEST)){
//    header('HTTP/1.0 404 Not Found');
//    exit;
}

//TODO: match to whitelist

//parse vote
//pull out all hash tags
if(preg_match_all('/#(\w*)/', $nexmo->text, $votes)){
    foreach($votes[1] as $vote){
        //TODO: accept multiple votes?
        break; //first vote is all that counts
    }
} else {
    exit; //no vote
}

//setup cloudmine
require_once 'lib/cloudmine/CloudMine.php';
$cloudmine = new CloudMine($config['cloudmine']['user'], $config['cloudmine']['pass']);

/**
 * Add to cloudmine
 */
//track numbers, to limit voting
$data = $cloudmine->get('a2sw'); //yeah, pulling back all the data here
if(isset($data['a2sw']['voters'][$nexmo->from])){
    //return; //only count the first 
}

//use message id so it's unique
$data = array();
$data['a2sw']['votes'][$vote][$_REQUEST['messageId']] = array('to' => $nexmo->to, 'text' => $nexmo->text, 'from' => $nexmo->from, 'time' => time());
$data['a2sw']['voters'][$nexmo->from] = $data['a2sw']['votes'][$vote];

$cloudmine->update($data);

//publish to pubnub
require_once 'lib/Pubnub.php';

$pubnub = new Pubnub(
    $config['pubnub']['pub'],  ## PUBLISH_KEY
    $config['pubnub']['sub'],  ## SUBSCRIBE_KEY
    "",      ## SECRET_KEY
    false    ## SSL_ON?
);

$info = $pubnub->publish(array(
    'channel' => 'a2sw', ## REQUIRED Channel to Send
    'message' => $vote   ## REQUIRED Message String/Array
));

error_log(var_export($info, true));