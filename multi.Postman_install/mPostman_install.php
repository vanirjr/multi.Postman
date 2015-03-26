<?php
$myArgs = array();
if ($argc > 1) {
	foreach ($argv as $arg) {
		if (ereg('--([^=]+)=(.*)',$arg,$reg)) {
			$myArgs[$reg[1]] = $reg[2];
		} elseif (ereg('-([a-zA-Z0-9])',$arg,$reg)) {
			$myArgs[$reg[1]] = 'true';
		} 
	}
}
$myArgs['sys-install'] = isset($myArgs['sys-install']) ? $myArgs['sys-install'] : "freebsd";
$myArgs['queue-life'] = isset($myArgs['queue-life']) ? $myArgs['queue-life'] : "12h";
$queue_life = $myArgs['queue-life'];
$install_mode = $myArgs['sys-install'];
switch ($install_mode) {
	case "test":
		$dir_conf = "./etc";
		$dir_sbin = "./sbin";
		$dir_bin = "./bin";
		$dir_spool = "./spool";
		$dir_data = "./db";
		$dir_start = "./etc/rc.d";
		$dir_libexec = "./libexec";
		break;
	case "linux":
		$dir_conf = "/etc";
		$dir_sbin = "/usr/sbin";
		$dir_bin = "/usr/bin";
		$dir_spool = "/var/spool";
		$dir_data = "/var/db";
		$dir_start = "/etc/rc.d";
		$dir_libexec = "/usr/libexec";
		break;
	case "freebsd":
		$dir_conf = "/usr/local/etc";
		$dir_sbin = "/usr/local/sbin";
		$dir_bin = "/usr/local/bin";
		$dir_spool = "/var/spool";
		$dir_data = "/var/db";
		$dir_start = "/usr/local/etc/rc.d";
		$dir_libexec = "/usr/local/libexec";
		break;
	case "custom":
		$myArgs['conf-dir'] = isset($myArgs['conf-dir']) ? $myArgs['conf-dir'] : "/usr/local/etc";
		$myArgs['sbin-dir'] = isset($myArgs['sbin-dir']) ? $myArgs['sbin-dir'] : "/usr/local/sbin";
		$myArgs['bin-dir'] = isset($myArgs['bin-dir']) ? $myArgs['bin-dir'] : "/usr/local/bin";
		$myArgs['spool-dir'] = isset($myArgs['spool-dir']) ? $myArgs['spool-dir'] : "/var/spool";
		$myArgs['data-dir'] = isset($myArgs['data-dir']) ? $myArgs['data-dir'] : "/var/db";
		$myArgs['start-dir'] = isset($myArgs['start-dir']) ? $myArgs['start-dir'] : "/usr/local/etc/rc.d";
		$myArgs['libexec-dir'] = isset($myArgs['libexec-dir']) ? $myArgs['libexec-dir'] : "/usr/local/libexec";
		$dir_conf = $myArgs['conf-dir'];
		$dir_sbin = $myArgs['sbin-dir'];
		$dir_bin = $myArgs['bin-dir'];
		$dir_spool = $myArgs['spool-dir'];
		$dir_data = $myArgs['data-dir'];
		$dir_start = $myArgs['start-dir'];
		$dir_libexec = $myArgs['libexec-dir'];
		break;
}
$services = array("postalias", "postcat", "postconf", "postdrop", "postfix", "postkick", "postlock", "postlog", "postmap", "postqueue", "postsuper", "sendmail");
$mydest = "";
$altern = "alternate_config_directories = ";
$interfaces = "<?php\n\n\$interface = array(";
$dom_ip = array();
$dom_host = array();
$dom_name = array();
$dom_uniq = array();
if (!file_exists("netmap.csv")) {
	print "\n----------------------------------------------------------------------------------------\nWARNING:\nThe file 'netmap.csv' is missing.\nWe cannot proceed without it.\n----------------------------------------------------------------------------------------\n\n";
	exit();
}
$fh = fopen("netmap.csv", "r");
while (!feof($fh)) {
	$host = fgetcsv($fh, 4096, ",");
	if ($host) {
		$dom_ip[] = trim($host[0]);
		$dom_host[] = trim($host[1]);
		$this_dom_host = "http://".trim($host[1]);
		$this_dom_name = "";
		$pieces = parse_url($this_dom_host);
		$domain = isset($pieces['host']) ? $pieces['host'] : '';
		if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
			$this_dom_name = $regs['domain'];
		}
		$dom_name[] = $this_dom_name;
		$not_uniq = 0;
		for ($x=0;$x<count($dom_uniq);$x++) {
			if ($this_dom_name == $dom_uniq[$x]) {
				$not_uniq = 1;
				break;
			}
		}
		if ($not_uniq == 0) {
			$dom_uniq[] = $this_dom_name;
		}
	}
}
fclose($fh);
print "\n-------------------------------------------------------\nThis is your Installation Configuration:\n-------------------------------------------------------\n\nSystem type of installation = $install_mode\nPostfix configuration directory = $dir_conf\nPostfix binaries directory = $dir_sbin\nMail queue command directory = $dir_bin\nMail spool directory = $dir_spool\nPostfix data directory = $dir_data\nSystem startup directory = $dir_start\nPostfix daemon directory = $dir_libexec\nQueue lifetime = $queue_life\n";
print "\n-------------------------------------------------------\nThis is your Net Map information:\n-------------------------------------------------------\n\n";
for ($i=0;$i<count($dom_ip);$i++) {
	print "INTERFACE: i$i - IP: $dom_ip[$i] - HOST: $dom_host[$i] - DOMAIN: $dom_name[$i]\n";
}
print "\n-------------------------------------------------------\n\n";
print "You are in '$install_mode' mode... Shall we proceed with the installation? [y/n] - ";
$handle = fopen("php://stdin","r");
$input = fgets($handle);
if(trim($input) != "y"){
	print "\nABORTING... Bye!!!\n\n";
	exit;
}
print "\nProceeding with install...\n\n";
$my_uninstall = "#!/bin/sh\n";
if ($install_mode == "test") {
	print "\n-------------------------------------------------------\nCreating system directories for TEST mode:\n-------------------------------------------------------\n\n";
	if (!file_exists($dir_conf)) {
		mkdir($dir_conf);
		mkdir("$dir_conf/postfix");
		exec("cat /dev/null > $dir_conf/postfix/main.cf");
		print "$dir_conf ... sucessfully created.\n";
	} else {
		print "could not create $dir_conf ... file exists!\n";
	}
	if (!file_exists($dir_sbin)) {
		mkdir($dir_sbin);
		print "$dir_sbin ... sucessfully created.\n";
	} else {
		print "could not create $dir_sbin ... file exists!\n";
	}
	if (!file_exists($dir_bin)) {
		mkdir($dir_bin);
		print "$dir_bin ... sucessfully created.\n";
	} else {
		print "could not create $dir_bin ... file exists!\n";
	}
	if (!file_exists($dir_spool)) {
		mkdir($dir_spool);
		print "$dir_spool ... sucessfully created.\n";
	} else {
		print "could not create $dir_spool ... file exists!\n";
	}
	if (!file_exists($dir_data)) {
		mkdir($dir_data);
		mkdir("$dir_data/postfix");
		print "$dir_data ... sucessfully created.\n";
	} else {
		print "could not create $dir_data ... file exists!\n";
	}
	if (!file_exists($dir_start)) {
		mkdir($dir_start);
		$fr = fopen("$dir_start/rc.local", "w");
		$fr_w = fputs($fr, "This is a test rc.local file...\ngo ahead!!!\n");
		fclose($fr);
		print "$dir_start ... sucessfully created.\n";
	} else {
		print "could not create $dir_start ... file exists!\n";
	}
}
print "\n-------------------------------------------------------\n\n";
if ($install_mode != "freebsd") {
	print "Creating a backup of you rc.local file ... ";
	exec("cp -p $dir_start/rc.local $dir_start/rc.local.before.multi-post.install");
	$fr = fopen("$dir_start/rc.local", "a");
	$fr_w = fputs($fr, "\n\n");
	fclose($fr);
	$my_uninstall .= "mv -f $dir_start/rc.local.before.multi-post.install $dir_start/rc.local\n";
	print "done!\n\n";
}
print "Creating include files, alternative interfaces and mydestination maps ... ";
for ($i=0;$i<count($dom_ip);$i++) {
	$mydest .= $dom_host[$i].", ";
	if ($i == 0) {
		$interfaces .= "array('host' => '".$dom_host[$i]."', 'domain' => '".$dom_name[$i]."', 'ip' => '".$dom_ip[$i]."', 'sendmail' => '$dir_sbin/sendmail'), ";
	} elseif ($i > 0 && $i < (count($dom_ip)-1)) {
		$altern .= $dir_conf."/postfix_".$i.",";
		$interfaces .= "array('host' => '".$dom_host[$i]."', 'domain' => '".$dom_name[$i]."', 'ip' => '".$dom_ip[$i]."', 'sendmail' => '$dir_sbin/$i/sendmail'), ";
	} elseif ($i == count($dom_ip)-1) {
		$altern .= $dir_conf."/postfix_".$i;
		$interfaces .= "array('host' => '".$dom_host[$i]."', 'domain' => '".$dom_name[$i]."', 'ip' => '".$dom_ip[$i]."', 'sendmail' => '$dir_sbin/$i/sendmail')";
	}
}
for ($i=0;$i<count($dom_uniq);$i++) {
	$mydest .= $dom_uniq[$i].", ";

}
$mydest .= "localhost\n";
$interfaces .= ");\n\n?>\n";
$fi = fopen("../multi.Postman_sendmail/interfaces.inc", "w");
$fi_w = fputs($fi, $interfaces);
fclose($fi);
print "done!\n\n";
$dir_conf_src = $dir_conf."/postfix";
$dir_data_src = $dir_data."/postfix";
for ($i=0;$i<count($dom_ip);$i++) {
	if ($i == 0) {
		$dir_conf_this = $dir_conf_src;
		$dir_queue_this = $dir_spool."/postfix";
		$dir_progr_this = $dir_sbin;
		$dir_data_this = $dir_data_src;
		print "Creating a backup of your original configuration file main.cf as main.cf.before.mPostman.install ... ";
		exec("mv -f $dir_conf_src/main.cf $dir_conf_src/main.cf.before.mPostman.install");
		$my_uninstall .= "mv -f $dir_conf_src/main.cf.before.mPostman.install $dir_conf_src/main.cf\n";
		print "done.\n\n";
		print "Creating mydestination file ... ";
		$fm = fopen("$dir_conf_src/mydestination", "w");
		$fm_w = fputs($fm, $mydest);
		fclose($fm);
		$my_uninstall .= "rm -f $dir_conf_src/mydestination\n";
		print "done.\n\n";
		print "Creating main configuration file for default interface ... ";
		$my_main_cf = $altern."\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "smtp_bind_address = ".$dom_ip[$i]."\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "myhostname = ".$dom_host[$i]."\n";
		$my_main_cf .= "mydomain = ".$dom_name[$i]."\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "inet_interfaces = \$myhostname, localhost\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "myorigin = \$mydomain\n";
		$my_main_cf .= "mydestination = ".$dir_conf_src."/mydestination\n";
		$my_main_cf .= "relay_domains = \$mydestination\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "queue_directory = ".$dir_queue_this."\n";
		$my_main_cf .= "command_directory = ".$dir_progr_this."\n";
		$my_main_cf .= "data_directory = ".$dir_data_this."\n";
		$my_main_cf .= "daemon_directory = ".$dir_libexec."/postfix\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "mail_owner = postfix\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "unknown_local_recipient_reject_code = 450\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "debug_peer_level = 2\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "debugger_command =\n";
		$my_main_cf .= "\tPATH=/bin:/usr/bin:/usr/local/bin:/usr/X11R6/bin\n";
		$my_main_cf .= "\txxgdb \$daemon_directory/\$process_name \$process_id & sleep 5\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "sendmail_path = ".$dir_sbin."/sendmail\n";
		$my_main_cf .= "newaliases_path = ".$dir_bin."/newaliases\n";
		$my_main_cf .= "mailq_path = ".$dir_bin."/mailq\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "setgid_group = maildrop\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "manpage_directory = /usr/local/man\n";
		$my_main_cf .= "sample_directory = ".$dir_conf_src."\n";
		$my_main_cf .= "readme_directory = no\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "bounce_queue_lifetime = ".$queue_life."\n";
		$my_main_cf .= "maximal_queue_lifetime = ".$queue_life."\n";
		$my_main_cf .= "qmgr_message_active_limit = 100000\n";
		$my_main_cf .= "qmgr_message_recipient_limit = 100000\n";
		$my_main_cf .= "\n";
		$fm = fopen("$dir_conf_src/main.cf", "w");
		$fm_w = fputs($fm, $my_main_cf);
		fclose($fm);
		print "done.\n\n";
		print "Creating mailq wraper for default interface ... ";
		$my_mailq_wrpr = "#!/bin/sh\nexport MAIL_CONFIG=$dir_conf_this\n$dir_bin/mailq\n";
		$fm = fopen("$dir_bin/mailq$i", "w");
		$fm_w = fputs($fm, $my_mailq_wrpr);
		fclose($fm);
		exec("chmod ugo+rx $dir_bin/mailq$i");
		$my_uninstall .= "rm -f $dir_bin/mailq$i\n";
		print "done.\n\n";
	} else {
		$dir_conf_this = $dir_conf."/postfix_".$i;
		$dir_queue_this = $dir_spool."/postfix_".$i;
		$dir_progr_this = $dir_sbin."/".$i;
		$dir_data_this = $dir_data."/postfix_".$i;
		print "Creating configuration directory for interface i$i ... ";
		exec("cp -pR $dir_conf_src $dir_conf_this");
		$my_uninstall .= "rm -rf $dir_conf_this\n";
		print "done.\n\n";
		print "Creating main configuration file for interface i$i ... ";
		$my_main_cf = "\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "smtp_bind_address = ".$dom_ip[$i]."\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "myhostname = ".$dom_host[$i]."\n";
		$my_main_cf .= "mydomain = ".$dom_name[$i]."\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "inet_interfaces = \$myhostname\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "myorigin = \$mydomain\n";
		$my_main_cf .= "mydestination = ".$dir_conf_src."/mydestination\n";
		$my_main_cf .= "relay_domains = \$mydestination\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "queue_directory = ".$dir_queue_this."\n";
		$my_main_cf .= "command_directory = ".$dir_progr_this."\n";
		$my_main_cf .= "data_directory = ".$dir_data_this."\n";
		$my_main_cf .= "daemon_directory = ".$dir_libexec."/postfix\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "mail_owner = postfix\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "unknown_local_recipient_reject_code = 450\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "debug_peer_level = 2\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "debugger_command =\n";
		$my_main_cf .= "\tPATH=/bin:/usr/bin:/usr/local/bin:/usr/X11R6/bin\n";
		$my_main_cf .= "\txxgdb \$daemon_directory/\$process_name \$process_id & sleep 5\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "sendmail_path = ".$dir_sbin."/sendmail\n";
		$my_main_cf .= "newaliases_path = ".$dir_bin."/newaliases\n";
		$my_main_cf .= "mailq_path = ".$dir_bin."/mailq\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "setgid_group = maildrop\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "manpage_directory = /usr/local/man\n";
		$my_main_cf .= "sample_directory = ".$dir_conf_src."\n";
		$my_main_cf .= "readme_directory = no\n";
		$my_main_cf .= "\n";
		$my_main_cf .= "bounce_queue_lifetime = ".$queue_life."\n";
		$my_main_cf .= "maximal_queue_lifetime = ".$queue_life."\n";
		$my_main_cf .= "qmgr_message_active_limit = 100000\n";
		$my_main_cf .= "qmgr_message_recipient_limit = 100000\n";
		$my_main_cf .= "\n";
		$fm = fopen("$dir_conf_this/main.cf", "w");
		$fm_w = fputs($fm, $my_main_cf);
		fclose($fm);
		print "done.\n\n";
		print "Creating mailq wrapper for interface i$i ... ";
		$my_mailq_wrpr = "#!/bin/sh\nexport MAIL_CONFIG=$dir_conf_this\n$dir_bin/mailq\n";
		$fm = fopen("$dir_bin/mailq$i", "w");
		$fm_w = fputs($fm, $my_mailq_wrpr);
		fclose($fm);
		exec("chmod ugo+rx $dir_bin/mailq$i");
		$my_uninstall .= "rm -f $dir_bin/mailq$i\n";
		print "done.\n\n";
		print "Creating service binaries wrappers for interface i$i ... ";
		mkdir("$dir_sbin/$i");
		for ($s=0;$s<count($services);$s++) {
			$my_service = $services[$s];
			$my_bin_wrpr = "#!/bin/sh\nexport MAIL_CONFIG=$dir_conf_this\n$dir_sbin/$my_service $*\n";
			$fm = fopen("$dir_sbin/$i/$my_service", "w");
			$fm_w = fputs($fm, $my_bin_wrpr);
			fclose($fm);
			exec("chmod ugo+rx $dir_sbin/$i/$my_service");
			if ($my_service == "postdrop" || $my_service == "postqueue") {
				exec("chown root:maildrop $dir_sbin/$i/$my_service");
				exec("chmod g+s $dir_sbin/$i/$my_service");
			}
		}
		$my_uninstall .= "rm -rf $dir_sbin/$i\n";
		print "done.\n\n";
		print "Creating spool directory for interface i$i ... ";
		mkdir("$dir_spool/postfix_$i");
		$my_uninstall .= "rm -rf $dir_spool/postfix_$i\n";
		print "done.\n\n";
		print "Creating data directory for interface i$i ... ";
		exec("cp -pR $dir_data_src $dir_data_this");
		$my_uninstall .= "rm -rf $dir_data_this\n";
		print "done.\n\n";
		print "Adding postfix interface i$i to the system startup process ... ";
		switch ($install_mode) {
			case "freebsd":
				exec("ln -s $dir_sbin/$i/postfix $dir_start/postfix_$i.sh");
				$my_uninstall .= "rm -f $dir_start/postfix_$i.sh\n";
				break;
			case "linux":
				$my_rclocal_line = "$dir_sbin/$i/postfix start\n";
				$fr = fopen("$dir_start/rc.local", "a");
				$fr_w = fputs($fr, $my_rclocal_line);
				fclose($fr);
				break;
			default:
				exec("ln -s $dir_sbin/$i/postfix $dir_start/postfix_$i.sh");
				$my_uninstall .= "rm -f $dir_start/postfix_$i.sh\n";
				$my_rclocal_line = "$dir_sbin/$i/postfix start\n";
				$fr = fopen("$dir_start/rc.local", "a");
				$fr_w = fputs($fr, $my_rclocal_line);
				fclose($fr);
				break;
		}
		print "done.\n\n";
	}
}
if ($install_mode == "test") {
	$my_uninstall .= "rm -rf $dir_conf $dir_sbin $dir_bin $dir_spool $dir_data uninstall.sh\n\n";
	$my_uninstall .= "echo '\n-------------------------------------------------------\nUninstallation process completed successfully!\nThank you for trying *multi.Postman*!\n-------------------------------------------------------\n\n'\n\n";
} else {
	$my_uninstall .= "rm -rf uninstall.sh\n\n";
	$my_uninstall .= "echo '\n-------------------------------------------------------\nUninstallation process completed successfully!\nPlease, reboot your machine... Thank you for trying *multi.Postman*!\n-------------------------------------------------------\n\n'\n\n";
}
$fu = fopen("uninstall.sh", "w");
$fu_w = fputs($fu, $my_uninstall);
fclose($fu);
exec("chmod u+x uninstall.sh");
print "Running post installation commands ... \n";
exec("$dir_sbin/postfix stop");
exec("$dir_sbin/postfix start");
for ($i=1;$i<count($dom_ip);$i++) {
	exec("$dir_sbin/$i/postfix check");
}
print "done.\n\n";
print "\n-------------------------------------------------------\nInstallation process completed successfully!\nReboot your machine... and... Enjoy *multi.Postman*!\n-------------------------------------------------------\n\n";
?>
