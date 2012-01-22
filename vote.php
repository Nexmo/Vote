<?php
//setup autoloading
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
$config = include 'config.php';

//some nexmo includes
require_once 'lib/nexmo/NexmoMessage.php';

//validate it's from nexmo
$nexmo = new NexmoMessage($config['nexmo']['user'], $config['nexmo']['pass']);
//this just checks for the expects fields
if(!$nexmo->inboundText($_POST)){
    header('HTTP/1.0 404 Not Found');
    exit;
}

//TODO: would be nice to have an optional whitelist of choices 

//parse vote
//pull out all hash tags
if(preg_match_all('/#(\w*)/', $nexmo->text, $votes)){
    foreach($votes[1] as $vote){
        //TODO: could allow multiple votes here (but still limit to one SMS)
        break; //first vote is all that counts
    }
} else {
    exit; //no vote detected, nothing to do
}

//we're very insensitive to case
$vote = strtolower($vote);

//setup cloudmine
require_once 'lib/cloudmine/CloudMine.php';
$cloudmine = new CloudMine($config['cloudmine']['user'], 
                           $config['cloudmine']['pass']);

/**
 * Add to cloudmine
 */
//TODO: yeah, pulling back all the data here, this could be better modeled
//look at us, using a key that matches the event, it's like we're thinking of
//adding an admin UI layer allowing multiple events
$data = $cloudmine->get('a2sw'); 

//TODO: make this check optional
if(isset($data['a2sw']['voters'][$nexmo->from])){
    return; //only count the first vote
}

$data = array();
//add vote to the matched choice (using the message id as the key)
$data['a2sw']['votes'][$vote][$_REQUEST['messageId']] 
    = array('to' => $nexmo->to, 
    		'text' => $nexmo->text, 
    		'from' => $nexmo->from, 
    		'time' => time());
//track the voters, so we can limit to one vote per phone
$data['a2sw']['voters'][$nexmo->from] = $data['a2sw']['votes'][$vote];

//update the data (merging with the exsisting data)
$cloudmine->update($data);

//publish to pubnub, updating the chart in real time
require_once 'lib/Pubnub.php';

$pubnub = new Pubnub(
    $config['pubnub']['pub'],
    $config['pubnub']['sub'],
    "", //secret key, no need for that
    false //not using ssl
);

$pubnub->publish(array(
    'channel' => 'a2sw', //look at that, this should be a variable in the config
    'message' => $vote //the data is just the text of the vote
));

