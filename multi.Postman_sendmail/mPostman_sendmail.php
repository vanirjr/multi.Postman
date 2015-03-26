<?php
ignore_user_abort(1);
set_time_limit(0);
if (file_exists("interfaces.inc")) {
	include "interfaces.inc";
} else {
	print "\n----------------------------------------------------------------------------------------\nWARNING:\nYou have to run the 'Install Program' prior to start using this one.\nPlease, read the README files instructions.\n----------------------------------------------------------------------------------------\n\n";
	exit();
}
$myArgs = array();
if ($argc > 1) {
	foreach ($argv as $arg) {
		if (ereg('--([^=]+)=(.*)',$arg,$reg)) {
			$myArgs[$reg[1]] = $reg[2];
		} elseif (ereg('-([a-zA-Z0-9])',$arg,$reg)) {
			$myArgs[$reg[1]] = true;
		} 
	}
}
$myArgs['i'] = isset($myArgs['i']) ? $myArgs['i'] : 0;
$myArgs['type'] = isset($myArgs['type']) ? $myArgs['type'] : "txt";
$myArgs['rotate-every'] = isset($myArgs['rotate-every']) ? $myArgs['rotate-every'] : 1;
$myArgs['set-interval'] = isset($myArgs['set-interval']) ? $myArgs['set-interval'] : 0;
$myArgs['l'] = isset($myArgs['l']) ? $myArgs['l'] : false;
if ($myArgs['l']) {
	for ($x=0;$x<count($interface);$x++) {
		print "\n";
		print "INTERFACE: $x\n";
		print "\tIP:\t".$interface[$x]['ip']."\n";
		print "\tHOST:\t".$interface[$x]['host']."\n";
		print "\tDOMAIN:\t".$interface[$x]['domain']."\n";
		print "\n";
	}
	exit();
}
$my_type = $myArgs['type'];
$my_rotate = $myArgs['rotate-every'];
$my_interval = $myArgs['set-interval'];
if ($myArgs['i'] == "all") {
	$my_interface = array();
	for ($x=0;$x<count($interface);$x++) {
		$my_interface[] = $x;
	}
} else {
	$my_interface = explode(",", $myArgs['i']);
}

if (!file_exists("list.destination.users.txt")) {
	print "\n----------------------------------------------------------------------------------------\nWARNING:\nThe file 'list.destination.users.txt' is missing.\nWe cannot proceed without it.\n----------------------------------------------------------------------------------------\n\n";
	exit();
}
if (file_exists("list.local.users.txt")) {
	$my_user = file("list.local.users.txt");
} else {
	print "\n----------------------------------------------------------------------------------------\nWARNING:\nThe file 'list.local.users.txt' is missing.\nWe cannot proceed without it.\n----------------------------------------------------------------------------------------\n\n";
	exit();
}
if (file_exists("list.subjects.txt")) {
	$my_subj = file("list.subjects.txt");
} else {
	print "\n----------------------------------------------------------------------------------------\nWARNING:\nThe file 'list.subjects.txt' is missing.\nWe cannot proceed without it.\n----------------------------------------------------------------------------------------\n\n";
	exit();
}
switch ($my_type) {
	case "txt":
		if (file_exists("list.message.txt")) {
			$my_message = file_get_contents("list.message.txt");
		} else {
			$my_message = "\n\nThis is a test message.\nThanks for using *multi.Postman*\n\n\n";
		}
		break;
	case "html":
		if (file_exists("list.message.html")) {
			$my_message = file_get_contents("list.message.html");
		} else {
			$my_message = "\n\n<h1>This is a test message.</h1>\n<h3>Thanks for using *multi.Postman*</h3>\n\n\n";
		}
		break;
}
print "\nINTERFACE: ".$myArgs['i']."\nMessage TYPE: $my_type\nRotate every $my_rotate messages.\nInterval: $my_interval seconds.\n\n";
$l_log = "Sending process started @ ".@date("Y-m-d, H:i:s")."\n\n";
$flog = fopen("log.txt", "w");
$fr_log = fputs($flog, $l_log);
fclose($flog);
$cur_user = 0;
$cur_subj = 0;
$cur_interface = 0;
$cur_rotate = 0;
$fh = fopen("list.destination.users.txt", "r");
while (!feof($fh)) {
	$my_dest_user = fgetcsv($fh, 4096, ",");
	if ($my_dest_user) {
		$my_dest_user_email = $my_dest_user[0];
		$my_dest_user_fname = $my_dest_user[1];
		$my_dest_user_lname = $my_dest_user[2];
		$my_dest_user_id = $my_dest_user[3];
		$this_message = str_replace("[FIRST_NAME]", $my_dest_user_fname, $my_message);
		$this_message = str_replace("[LAST_NAME]", $my_dest_user_lname, $this_message);
		$this_message = str_replace("[ID]", $my_dest_user_id, $this_message);
		$this_message = str_replace("[EMAIL]", $my_dest_user_email, $this_message);
		$this_interface = $my_interface[$cur_interface];
		$my_from = trim($my_user[$cur_user])."@".$interface[$this_interface]['domain'];
		$my_subject = trim($my_subj[$cur_subj]);
		$my_sendmail = $interface[$this_interface]['sendmail']." -t -f $my_from";
		$fd = popen($my_sendmail, "w");
		fputs($fd, "To: $my_dest_user_email\n");
		fputs($fd, "From: $my_from\n");
		fputs($fd, "Subject: $my_subject\n");
		if ($my_type == "html") {
			fputs($fd, "Content-Type: text/html; charset=iso-8859-1\n");
		}
		fputs($fd, "MIME-Version: 1.0\n\n");
		fputs($fd, $this_message);
		pclose($fd);
		$l_log = "message from $my_from to $my_dest_user_email ==> sent from Interface $this_interface, IP: ".$interface[$this_interface]['ip'].", Host: ".$interface[$this_interface]['host']." @ ".@date("Y-m-d, H:i:s")."\n";
		$flog = fopen("log.txt", "a");
		$fr_log = fputs($flog, $l_log);
		fclose($flog);
		$cur_rotate++;
		if ($cur_rotate >= $my_rotate) {
			$cur_rotate = 0;
			$cur_user++;
			if ($cur_user >= count($my_user)) {
				$cur_user = 0;
			}
			$cur_subj++;
			if ($cur_subj >= count($my_subj)) {
				$cur_subj = 0;
			}
			$cur_interface++;
			if ($cur_interface >= count($my_interface)) {
				$cur_interface = 0;
			}
		}
		sleep($my_interval);
	}
}
fclose($fh);
$l_log = "\nSending process finished @ ".@date("Y-m-d, H:i:s")."\n\n";
$flog = fopen("log.txt", "a");
$fr_log = fputs($flog, $l_log);
fclose($flog);
?>
