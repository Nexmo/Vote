VOTE BY SMS
===========

Simple vote by SMS application powered by [Nexmo][1], [CloudMine][2], and 
[PubNub][3]. Text a hashtagged name to a Nexmo number for realtime voting.

Example SMS: #github

[1]: http://www.nexmo.com/
[2]: http://cloudmine.me/
[3]: http://www.pubnub.com/

Install
-------

* `git clone git://github.com/Nexmo/SMS-Vote.git`
* `git submodule init`
* `git submodule update`
* Update `config.php` with your credentials and number.
* Point your Nexmo number to `vote.php`.

Requirements
------------

* Zend Framework
* Nexmo Account & Incomming Number
* CloudMine Account
* PubNub Account

Notes
-----

This was a quick and simple example, written for [Ann Arbor Startup Weekend][4].
Certianly plenty of room for imporvment/features, pull requests welcome.

[4]: http://annarbor.startupweekend.org/
