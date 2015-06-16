Install:

	cd /var/www

	tar -xzvf asterisk-cdr-viewer.tgz OR svn checkout http://asterisk-cdr-viewer.googlecode.com/svn/trunk/ asterisk-cdr-viewer

	cp asterisk-cdr-viewer/contrib/httpd/asterisk-cdr-viewer.conf /etc/httpd/conf.d/

	service httpd restart

	change settings in /var/www/asterisk-cdr-viewer/include/config.inc.php

	open browser with url  http://your_ip/acdr/

Recording interface

	To display the links to the recorded files:

	* change the following settings in configuration file ( config.inc.php ):
		1. $system_monitor_dir = '/var/spool/asterisk/monitor'; - is the directory where call recordings are stored
		2. $system_audio_format = 'wav'; - audio file format 
	* Use like this command to start recording ( in asterisk dialplan ):

		[macro-monitor]
		exten => s,1,Set(MONITOR_FILE=/var/spool/asterisk/monitor/${UNIQUEID})
		exten => s,n,MixMonitor(${MONITOR_FILE}.wav,b)

		!!! if you use a different file format, see more examples in functions.inc.php ( function formatFiles) !!!


Regular expressions

	If an Source / Destination number is prefixed by a '_'
	character, it is interpreted as a pattern rather than a
	literal. In patterns, some characters have special meanings:
	
	   X - any digit from 0-9
	   Z - any digit from 1-9
	   N - any digit from 2-9
	   [1235-9] - any digit in the brackets (in this example, 1,2,3,5,6,7,8,9)
	   . - wildcard, matches anything remaining (e.g. _9011. matches
	       anything starting with 9011 excluding 9011 itself)
	
	For example, the Source/Destination _NXXXXXX would match 7 digit numbers,
	while _1NXXNXXXXXX would represent an area code plus phone number
	preceded by a one.


	This cdr-viewer supports the processing of multiple regular expressions in the 
	Source/Destination name separated by a comma.

    For example: 

	    * Source = '_2XXN, _562., _.0075'  - src is any of these numbers


Multiple user access

	uncomment following section in http.conf file:

	<Location "/acdr/">
		AuthName "Asterisk-CDR-Stat"
		AuthType Basic
		AuthUserFile /var/www/asterisk-cdr-viewer/.htpasswd
		AuthGroupFile /dev/null
		require valid-user
	</Location>


	* add admin user, for example (iokunev) :
	  htpasswd -c /var/www/asterisk-cdr-viewer/.htpasswd iokunev
	
	* added admin name to include/config.inc.php: $admin_user_names = "admin1,admin2,iokunev"

		!!! $admin_user_names = '*' - all registered users are administrators. !!!

	access for all CDRs: http://your_ip/acdr/ - with login iokunev

	* add user 2280:
	 htpasswd  /var/www/asterisk-cdr-viewer/.htpasswd 2280 test

	access for incomming/outgoing (cid or did=2280) calls for this user: http://your_ip/acdr/ - with login 2280


Optional CDR columns

	`clid` and `accountcode` optional column. to enable/disable it please change following variables in config file:
	/* enable / disabe column */
	$display_column = array();
	$display_column['clid'] = 0; /* disabled */
	$display_column['accountcode'] = 1; /* enabled */
	

CallRates Support

	* upload csv file to server and add path to the '$callrate_csv_file' variable in config file.

	callrates file format:
		areacode,callrate_per_minute[,Destination[,rate_type]]
	
	for example:
		011,0.12,Intr,m

	supported rate_type:
	    * s      - per second tarification
		* c      - per call tarification
		* m      - per minute tarification ( default )
		* 1m+s   - combi
		* 30s+s  - combi 2
		* 30s+6s - combi 3

Plugins support

   * Plug-in file name should be in the format: plugin_name.inc.php

   * Plug-in must contain function `plugin_name`.

   * Copy your plugin to the directory include/plugins/

   * Add plugin name to include/config.inc.php, to $plugins() variable

   * Please see au_callrates.inc.php plugin for more details.

Postgres

to use this software with postgres DB, add follow function to DB:

CREATE FUNCTION unix_timestamp(TIMESTAMP) RETURNS INTEGER AS '
SELECT date_part(''epoch'', $1)::INTEGER AS RESULT
' LANGUAGE sql;
CREATE FUNCTION
