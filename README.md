Welcome to multi.Postman!
=========================

**multi.Postman** is a very powerful bulk mailing system developed for `FreeBSD/Linux/Unix` servers running `Postfix` and `PHP`.
The software is divided into 2 sections: the first one is the system installation software, located inside the folder `multi.Postman_install`, and the latter is the sendmail software, located inside the folder `multi.Postman_sendmail`.


---------------------
multi.Postman_install
---------------------

This is your very first step. Running the `mPostman_install.php`, will turn your Postfix SMTP server into a Multi-Instance server, which will handle the incomming and outgoing SMTP traffic for each of your IP interfaces completely independent, giving you total control of each interface, including separated mailqueue management, start and stop of each desired interface, and everything else.

Before running the `mPostman_install.php`, you must make sure that:

- You have full 'root' access to your server. The installation process must be done with 'root' previleges.
- Your server has Postfix and PHP properly installed and running.
- Your server has more than 1 IP interface properly installed and running.
- Your DNS is properly configured and responding.

	**Tip:** *You don't need to have a DNS server running on the same machine, although I'd extremely recommend that. Setting up your own DNS server on the same machine running your SMTP server, will not only improve the delivery speed of your SMTP server, but also will ensure that your mail do not fail to be delivered due to failed DNS querries.*
	
Once you have all the above checked out, it is time to move to the installation process.


###netmap.csv

The next step is to create your netmap file and save it as `netmap.csv`. There is a sample file in place, you can just edit that one, or replace it with your own, it is entirely up to you.

The file must have one IP interface per line, in the following format:

		<ip number>,<host name>

Example:

		192.168.0.1,www.example.com
		192.168.0.2,ms1.example.com
		192.168.0.3,mm.another-example.com
		192.168.0.4,www2.another-example.com
		192.168.0.5,smsv0.another-example.com
		192.168.0.6,www.the-example.com
		192.168.0.6,smtp45.the-example.com

Once you are done with the file `netmap.csv`, it is time to run the `mPostman_install.php`.


###mPostman_install.php

Before running the `mPostman_install.php`, you must understand its configuration parameters. They are as follows:
	
####--sys-install=[value]

This parameter tells the installation program what type of system you are going to be installing multi.Postman on. The `[value]` options for this parameter are: `freebsd`, `linux`, `custom` and `test`.

- `freebsd` = This is the default value. if you supress this option from your install command, this is going to be your default choice. Use this option if you're installing on a FreeBSD server running Postfix installed with its default file structure.

	Example:

		php mPostman_install.php --sys-install=freebsd
				
	or, simply:
				
		php mPostman_install.php
			
			
- `linux` = Use this option if you're installing on a Linux server running Postfix installed with its default file structure.

	Example:

		php mPostman_install.php --sys-install=linux


- `custom` = Use this option if you're installing on any system that does not use the default Postfix file structure, or even a system that you have modified the Postfix default file structure yourself, during its installation. If you choose this option, you need also to provide the Postfix paths manually, using the next set of parameters to be described below.  
Please, use this option only if you know exactly what you are doing. Setting the configuration paths to a wrong location will result in a faulty system.

	**Tip:** *Anyway, even if you do mess up with the installation config parameters, there is an easy way to go back and try again. All you need to do is to run the shell script `uninstall.sh` right away, and your system will be back to its original configuration. Please, refer to the end of the install section for a description on how to use the `uninstall`sh`.*


- `test` = Use this option for testing and reviewing purposes only. This option does not intall anything on your server, instead, it creates a mirror of the Postfix server directories inside your local installation folder, and create all the installation files within it, for the sole purpose of analysis.

	Example:

		php mPostman_install.php --sys-install=test


The following set of parameters below should only be used if you have choosen to do a `custom` installation type. These parameters are completely useless, if you have choosen any of the other installation types.


####--conf-dir=[value]

- [value] is the path for Postfix configuration directory. The default is `/usr/local/etc`

####--sbin-dir=[value]

- [value] is the path for Postfix binaries directory. The default is `/usr/local/sbin`

####--bin-dir=[value]

- [value] is the path for Postfix mailq commands directory. The default is `/usr/local/bin`

####--spool-dir=[value]

- [value] is the path for Postfix spool directory. The default is `/var/spool`

####--data-dir=[value]

- [value] is the path for Postfix data directory. The default is `/var/db`

####--start-dir=[value]

- [value] is the path for System start up scripts directory. The default is `/usr/local/etc/rc.d`

####--libexec-dir=[value]

- [value] is the path for Postfix daemon directory. The default is `/usr/local/libexec`

	Example:  
	
	a typical example of a command line for a `custom` installation type, is: 

		php mPostman_install.php --sys-install=custom --conf-dir=/etc --sbin-dir=/usr/sbin --bin-dir=/usr/bin --spool-dir=/var/spool --data-dir=/var/db --start-dir=/etc/rc.d --libexec-dir=/usr/libexec

	**Tip:** *if you supress any of the above options from your command line, the system will assume its default value. In other words, if you type a command line like:*

		php mPostman_install.php  --sys-install=custom

	*this is the same as typing:*

		php mPostman_install.php  --sys-install=custom --conf-dir=/usr/local/etc --sbin-dir=/usr/local/sbin --bin-dir=/usr/local/bin --spool-dir=/var/spool --data-dir=/var/db --start-dir=/usr/local/etc/rc.d --libexec-dir=/usr/local/libexec


Remember, the parameters above should only be used if you have choosen a `custom` installation type. The next, and also, the last parameter for the `mPostman_install.php`, can be used at anytime.


####--queue-life=[value]

This parameter defines the lifetime of your messages in the mailqueue. The default value is 12h (12 hours).  
Examples:

- performing a default `freebsd` installation, with a queue lifetime of 18 hours:

		php mPostman_install.php  --queue-life=18h

- performing a default `linux` installation, with a queue lifetime of 1 day:

		php mPostman_install.php --sys-install=linux --queue-life=1d

- performing a default `custom` installation, with a queue lifetime of 3 days:

		php mPostman_install.php --sys-install=custom --conf-dir=/etc --sbin-dir=/usr/sbin --bin-dir=/usr/bin --spool-dir=/var/spool --data-dir=/var/db --start-dir=/etc/rc.d --libexec-dir=/usr/libexec --queue-life=3d


####`mPostman_install.php` command line examples:


- Installing the multi.Postman system with all the default options:

		php mPostman_install.php

- This command line has the same effect as the one above:

		php mPostman_install.php --sys-install=freebsd --queue-life=12h

- Installing the multi.Postman system with all the default options for a `linux` system:

		php mPostman_install.php --sys-install=linux

- the `custom` installation command below has the same effect as the `linux` default installation command above:

		php mPostman_install.php --sys-install=custom --conf-dir=/etc --sbin-dir=/usr/sbin --bin-dir=/usr/bin --spool-dir=/var/spool --data-dir=/var/db --start-dir=/etc/rc.d --libexec-dir=/usr/libexec

- Installing the multi.Postman system with the `custom` option, and setting the **--queue-life** to 3 days:

		php mPostman_install.php --sys-install=custom --conf-dir=/my-system/etc --sbin-dir=/my-system/sbin --bin-dir=/my-system/bin --spool-dir=/my-system/var/spool --data-dir=/my-system/var/db --start-dir=/my-system/etc/rc.d --libexec-dir=/my-system/libexec --queue-life=3d


**`BEWARE:`** *Once you are done with the installation, you must **reboot** your machine in order to effect your installation.*


###Uninstalling multi.Postman

- When the installation process is finished, you will have a new, and very important, file automatically generated during the installation process named `uninstall.sh`. This file is your uninstallation script. In case you decide to uninstall **multi.Postman**, just run this shell script and reboot your system, and your Postfix will return to its orginal configuration.

	**Tip:** *This is also very useful in case you need to change your netmap. Changes to the IP numbers and domain names are very common for bulk mailers, so if you ever need to do that, just run the `uninstall.sh`, create a new 'netmap.csv' and run the `mPostman_install.php` again... done! You're ready to go!*


###Post Installation Tips

- Please, note that the **multi.Postman** installation will replace your Postfix configuration file `main.cf` with a new one. Your original `main.cf` file is saved as `main.cf.before.mPostman.install`. In case you have any custom modifications that you may have made to the `main.cf` file, prior to the **multi.Postman** installation, you can just copy them from the backup file `main.cf.before.mPostman.install` and add them manually to the new `main.cf` file. Please, be very careful with modifying any of the parameters which are already in place, any misconfiguration can lead to a system failure.


- After rebooting, you can check whether your installation was successful, by checking the Postfix processes running.

	Type:

		ps auxw | grep master

	You should get a response like:

		root     837   0.0  0.0  17188   2664  -  Is   12:09PM 0:00.01 /usr/local/libexec/postfix/master -w
		root     937   0.0  0.0  17188   2652  -  Is   12:09PM 0:00.00 /usr/local/libexec/postfix/master -w
		root    1037   0.0  0.0  17188   2652  -  Is   12:09PM 0:00.00 /usr/local/libexec/postfix/master -w
		root    1137   0.0  0.0  17188   2652  -  Is   12:09PM 0:00.00 /usr/local/libexec/postfix/master -w
		root    1237   0.0  0.0  17188   2652  -  Is   12:09PM 0:00.00 /usr/local/libexec/postfix/master -w
		root    1337   0.0  0.0  17188   2652  -  Is   12:09PM 0:00.00 /usr/local/libexec/postfix/master -w

	You should see one postfix/master daemon running for each ip of your netmap. Therefore, if you have made a netmap with 10 IP/Host adressess, it means that after your multi.Postman installation you should see 10 postfix/master daemons running.

	**Tip:** *If, after rebooting the system, you still see only one `postfix/master` deamon running, it means that something went wrong with your installation process. The best way to fix it, is to run the `uninstall.sh` script, and it will restore your Postfix to its original configuration. Then, figure out what is wrong and run the `mPostman_install.php` again. Usually the most comom problems are: path definitions not matching your Postfix file structure, a mistyped `netmap.csv` file, or you might not be logged in as `root` when running the installation software.*


- Checking the mail queue for every IP interface, independently.

	Type:

		mailq[value]

	[value] is the number of your IP interface. Let's say that you have intalled multi.Postman with a netmap of 10 IP interfaces, you will have the interface numbers from 0 to 9, respectively.
	
	Example:
	
	Checking the mail queue for the main, or, first interface:
	
		mailq0
		
	Checking the mail queue for the 3rd IP interface:
	
		mailq2
		
	Checking the mail queue for the IP interface number '7':
	
		mailq7
		
	... and so on...


- You can run any Postfix binary for any IP interface independently, by just adding the interface number to the command line path.

	Example:

	If you want to stop/start the main interface, you don't need to add any interface number:

		/usr/local/sbin/postfix start
		/usr/local/sbin/postfix stop

	Now, If want to start/stop interfaces 1 and 3:

		/usr/local/sbin/1/postfix start
		/usr/local/sbin/1/postfix stop

		/usr/local/sbin/3/postfix start
		/usr/local/sbin/3/postfix stop


- This same syntax works for every other Postfix binary, you just have to add the interface number to the path and issue the command in the same way you would for a main interface command.

	Example:
	
	These commands will affect the main interface, or, interface '0':

		/usr/local/sbin/postmaster
		/usr/local/sbin/postalias
		/usr/local/sbin/postsuper

	In case you need to issue any command to affect any other interface, just add the interface number to the end of your binaries path, as follows:

		/usr/local/sbin/2/postmaster
		/usr/local/sbin/2/postalias
		/usr/local/sbin/2/postsuper

		/usr/local/sbin/5/postmaster
		/usr/local/sbin/5/postalias
		/usr/local/sbin/5/postsuper


	Please, note that in those examples above, I'm taking into account that your Postfix binaries path is `/usr/local/sbin`. If you did your installation with a different path definition, like, `linux` or `custom`, you should replace the path on the commands listed above with the one you have used in your installation.


----------------------
multi.Postman_sendmail
----------------------

This folder contains the Bulk Mailer Software `mPostman_sendmail.php`, along with a set of files required by it. Below is a detailed description for each of those files:
	
###mPostman_sendmail.php

This is the multi.Postman sendmail program. It has many configuration options, as follows:
			
####-l
- This option will list your netmap configuration.

	Example:

		php mPostman_sendmail.php -l
					
	It will display the following output:
					
		INTERFACE: 0
			IP:		192.168.0.1
			HOST:	www.example.com
			DOMAIN:	example.com

		INTERFACE: 1
			IP:		192.168.0.2
			HOST:	ms1.example.com
			DOMAIN:	example.com

		INTERFACE: 2
		  IP:		192.168.0.3
		  HOST:	mm.another-example.com
		  DOMAIN:	another-example.com

		... and so on up to the last interface number...

	**Tip:** *This should be your very first `mPostman_sendmail.php` command. Get to know your IP interfaces, and, everytime you need to remember the details of a particular IP interface number, or even, find out the right IP interface number to be used for a certain host neme, just use the `mPostman_sendmail.php -l` command.*



####--i=[value]

- This option tells the program which IP interface is to be used for sending the mail.  
The [value] is the number of the interface you want to use. You can use only one interface number, or many numbers separated by comma (,). You can also use the value 'all', in case you want to use all interfaces. If you supress this parameter, the program will use its default value, which is '0', and your mail will be sent from the interface '0'.

	Example:

	if you want to send your mail from interface '1'. Type:

		php mPostman_sendmail.php --i=1

	if you want to send your mail from interfaces '0', '3' and '6'. Type:

		php mPostman_sendmail.php --i=0,3,6

	if you want to send your mail using all interfaces available. Type:

		php mPostman_sendmail.php --i=all


####--rotate-every=[value]

- This option tells the program when both the content of the email and the IP interface is going to rotate.  
The [value] is any integer number. Let's say you want to rotate your content and IP interface every 20 emails sent, then your [value] should '20'. The default value for this option is '1', so if you supress this parameter from your command, it will rotate every 1 email sent.

	Example:

	if you want to send your mail from interfaces '0', '3' and '6', and rotate every 10 emails. Type:

		php mPostman_sendmail.php --i=0,3,6 --rotate-every=10

	if you want to send your mail using all interfaces available, and rotate every 150 emails. Type:

		php mPostman_sendmail.php --i=all --rotate-every=150


####--type=[value]

- This option tells the program what kind of email body is your message.  
The [value] can be 'txt' for text messages, or, 'html' for html messages. The default value for this option is 'txt', therefore is you supress this parameter from your command, the program will send messages in the txt format.

	Example:

	if you want to send your mail using all interfaces available, and rotate every 150 emails, and in html format. Type:

		php mPostman_sendmail.php --i=all --rotate-every=150 --type=html

	The command below will send your mail using interface '0', rotating every 1 email, and in 'txt' format:

		php mPostman_sendmail.php


####--set-interval=[value]

- This parameter tells the program how long is the interval in-between the emails being sent.  
The [value] can be any integer number. This number represents the waiting period in 'seconds'. The default value for this parameter is '0', meaning that there will be no interval in-between the emails being sent.

	Example:

	if you want to send your mail using all interfaces available, rotating every 10 emails, in html format, and, with an interval in-between of 2 seconds. Type:

		php mPostman_sendmail.php --i=all --rotate-every=150 --type=html --set-interval=2



###interfaces.inc

- This file is your netmap configuration. This file is generated automatically during your installation process. You should not modify this file.


###list.destination.users.txt

- This is your users email recipent list. The list of emails you are going to be sending the message to.  

	This is a text file with one email per line in the following format:

		<email address>,<first name>,<last name>,<user id>

	Example:

		johnmayer@yahoo.com,John,Mayer,984530945
		elisa.mathews@gmail.com,Elisa,Mathews,ACC999I777
		maria34jones@hotmail.com,Maria,Jones,BRF0000456TT12

	Note that you don't need to add all the these fields to every line. Only the `<email address>` is needed. The other fields are only needed if you want to personalize your message by adding the value of those fields to your email body. See below the description for the files `list.message.html` and `list.message.txt` in order to understand how to place the content of these fields in your message body.


###list.local.users.txt

- This is your list of local users that will be in your `FROM` email address. This is a text file containing one username per line.

	Example:

		postfix
		guest
		root
		news

	If you want your whole mail to be sent from one only user, just make sure your file has only one line.  
	If you create a list of 2 or more users, they will rotate following the criteria defined by the option **--rotate-every**, as described above.

	Note that it doesn't matter whether these users are real username accounts in your system, or just email aliases., either way, they must be valid email addresses in your system, as every email boucing back will be delivered to their local inbox.

	**Tip:** *You must be asking yourself why can't you put a full email address in every line, instead of only usernames, right? Well, there is a purpose behind this practice. This will ensure that your email `FROM` will always have the `@domain.com` matching the domain of your hostname assigned to the IP interface you're using. This pratctice will improve your delivery rate, as one of the most basic rules for filtering emails in the biggest ESP's is, not accepting emails from addresses not matching the sender hostname.*


###list.subjects.txt

- This is your message subject line. This is a text file with one subject per line. In the same way, if you add only one line, you will have your whole mail sent using this same subject line, if you add 2 or more subject lines, they will rotate following the criteria defined by the option **--rotate-every=**, as described above.


###list.message.html

- This is the content of your email body in HTML format. It can be any HTML page. If you want to customize every email sent, you can add the following variables to your HTML page, respectively:

	[EMAIL] = this variable will be replaced by the field `<email address>` in your file `list.destination.users.txt`

	[FIRST_NAME] = this variable will be replaced by the field `<first name>` in your file `list.destination.users.txt`

	[LAST_NAME] = this variable will be replaced by the field `<last name>` in your file `list.destination.users.txt`

	[ID] = this variable will be replaced by the field `<user id>` in your file `list.destination.users.txt`
	
	Please, have a look at the file `list.message.html` provided, it is a good example of how to use the variables in you email body.


###list.message.txt

- This is the content of your email body in TEXT format. It can be any text. If you want to customize every email sent, you can add the following variables to your text, respectively:

	[EMAIL] = this variable will be replaced by the field `<email address>` in your file `list.destination.users.txt`

	[FIRST_NAME] = this variable will be replaced by the field `<first name>` in your file `list.destination.users.txt`

	[LAST_NAME] = this variable will be replaced by the field `<last name>` in your file `list.destination.users.txt`

	[ID] = this variable will be replaced by the field `<user id>` in your file `list.destination.users.txt`
	
	Please, have a look at the file `list.message.txt` provided, it is a good example of how to use the variables in you email body.


###log.txt

- This file is automatically generated everytime you run the `mPostman_sendmail.php` command. It is the program's output log for each email sent out.

	Each log line has the following format:

		message from <from_email> to <destination_email> ==> sent from Interface <interface_number>, IP: <interface_IP_number>, Host: <interface_hostname> @ <date>, <time>

	Example:

		message from news@example.com to johnmayer@yahoo.com ==> sent from Interface 0, IP: 192.168.0.1, Host: www.example.com @ 2015-03-25, 21:20:50


###Tips for Sending Out your Mail

- Always use the `&` symbol at the end of your `mPostman_sendmail.php` command. This will make the shell execute the command in the background, in a subshell. The shell does not wait for the command to finish, and  returns you to the command prompt. You can even log out from the system, and your `mPostman_sendmail.php` command will continue running up to the end. This is very handy, particularly if you have a very long email list that could take hours to be sent out.

	Example:
	
	if you want to send your mail using all interfaces available, rotating every 10 emails, in html format, and, with an interval in-between of 2 seconds. Type:

		php mPostman_sendmail.php --i=all --rotate-every=150 --type=html --set-interval=2 &

	if you want to send your mail from interface '0', rotating every 1 email, in text format, and with no interval in-between. Type:

		php mPostman_sendmail.php &

	which is the same as typing:

		php mPostman_sendmail.php --i=0 --rotate-every=1 --type=txt --set-interval=0 &


- Once you have launched the `mPostman_sendmail.php` command with the `&` symbol at the end, you're going to get an output like this:

		[1] 1461

	The number `1461`, in the example above, is the process number for your `mPostman_sendmail.php` running. In case anything goes wrong, and you need to stop your mail, just kill the process by issuing the following command:

		kill <process_number>
	
	Example:

		Kill 1461

	**Tip:** *In case you did not take note of the `<process_number>` for your `mPostman_sendmail.php` command running, and need stop it, you can always find out the right process number by typing:*

		ps auxw | grep php

	*you will get an output like:*

		root    1461   0.0  0.1  37796  10124  0  S     6:20PM  0:00.01 php mPostman_sendmail.php

	*the number right after the username 'root' is your `<process_number>`.*


- You can check if your mail is being sent out properly by checking your `log.txt` whilest your `mPostman_sendmail.php` is running. In order to do that, just type:

		tail -f log.txt

- Note that the output shown in the file `log.txt` doesn't mean that your email was sucessfully sent to the destination, it means that your email was sucessfully sent to right Postfix mail queue interface. In order to check if your emails are being sent out properly, check the Postfix maillog instead, by typing:

		tail -f /var/log/maillog



