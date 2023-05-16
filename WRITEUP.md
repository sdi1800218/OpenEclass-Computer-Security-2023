# Security Rapport

## XSS

### $_SERVER['PHP_SELF']

The site is tremendously vulnerable to XSS. By "trememndously" we mean that in every single location there is at least 1 XSS vector via `$_SERVER["PHP_SELF"]`. Because their is no sanitazation on this variable we can inject XSS payloads that get reflected in the site's source code onload. Payloads of the form `"><script> alert("DRAGONS")</script>` are a go. There are functions such as `lang_selections()`::baseTheme.php that carry this vuln with them, but also many other sites in the code that have the above mentioned functionality included.

### The rest

- [v] adminannouncements.php
- [v] edituser.php
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