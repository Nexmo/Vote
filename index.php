<?php
//setup autoloading
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
$config = include 'config.php';

//setup cloudmine
require_once 'lib/cloudmine/CloudMine.php';
$cloudmine = new CloudMine($config['cloudmine']['user'], $config['cloudmine']['pass']);

//get data
$data = $cloudmine->get('a2sw');

//render 'n stuff
?>
<html>
    <head>
        <link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.4.0/bootstrap.min.css">
        <script type="text/javascript" src=" https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script src="lib/highcharts/highcharts.js" type="text/javascript"></script>
        <script type="text/javascript">
            var app = {};
            $(document).ready(function() {
                votes = new Highcharts.Chart({
                   chart: {
                      renderTo: 'container',
                      type: 'column'
                   },
                   title: {
                      text: 'SWAnnArbor Popular Vote'
                   },
                   xAxis: {
                      categories: ['Votes']
                   },
                   yAxis: {
                      title: {
                         text: 'Votes'
                      }
                   },

                   series: [
                   <?php $teams = array(); $first = true; foreach($data['a2sw']['votes'] as $id => $votes):?>
                   <?php if(empty($id)) continue;?>
                   <?php $teams[] = "$id: " . count($votes);?>
                   <?php if(!$first)echo ','?>{
                       id: '<?php echo $id?>',
                       name: '<?php echo $id?>',
                       data: [<?php echo count($votes);?>]
                   }
                   <?php $first = false; endforeach;?>]
                });

                //simple app
                app = {
                        teams : {<?php echo implode(',', $teams)?>},
                        addVote : function(team){
                            if(!votes.get(team)){
                                votes.addSeries({animation: true, id: team, name: team});
                                app.teams[team] = 0;
                            }
                            app.teams[team]++;
                            votes.get(team).setData(app.teams[team]);
                        }
                };
            });            
        </script>
        <title>SWAnn Arbor Voting</title>
    </head>
    <body style="background-color: #333333;">
    <div>
        <div id="container" style="height:85%; margin: 10px;"></div>
    </div>
    <div sub-key="<?php echo $config['pubnub']['sub']?>" ssl="off" origin="pubsub.pubnub.com" id="pubnub"></div>

    <div style='padding: 20px;'>
        <span style='float: right;'>
            <img src="http://www.nexmo.com/img/logo_nexmo.png" height='30px' />
            <img src="http://pubnub.s3.amazonaws.com/2011/powered-by-pubnub/powered-by-pubnub-200.png" height='50px'/>
            <img src="https://cloudmine.me/assets/cloudmine_logo-c2f0670f4b46084cd993e61366a7ac63.png" height='50px'/>
        </span>
        <h1 style='color: #FFFFFF;'>Text Your Vote To: 1-818-937-4410</h1>
    </div>    

    <script src="http://cdn.pubnub.com/pubnub-3.1.min.js"></script>
    <script>(function(){
        PUBNUB.subscribe({
            channel  : "a2sw",      // CONNECT TO THIS CHANNEL.
            callback : function(message) { // RECEIVED A MESSAGE.
                app.addVote(message);
            }
        })
    
    })();</script>
    
    
    </body>
</html>
