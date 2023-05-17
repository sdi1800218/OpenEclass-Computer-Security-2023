<!-- TOC -->

- [Intro](#intro)
    - [Scope](#scope)
    - [What didn't happen:](#what-didnt-happen)
    - [Modules Disabled](#modules-disabled)
- [SQL Injections](#sql-injections)
    - [Mitigation](#mitigation)
    - [Changes in the codebase](#changes-in-the-codebase)
- [Cross-Site Scripting XSS](#cross-site-scripting-xss)
    - [X-XSS-Protection Header](#x-xss-protection-header)
    - [$_SERVER['PHP_SELF']](#_serverphp_self)
    - [The Rest](#the-rest)
- [Cross-site Request Forgery CSRF](#cross-site-request-forgery-csrf)
- [Remote File Inclusion RFI](#remote-file-inclusion-rfi)
    - [Directory listing](#directory-listing)
    - [Assignments aka work](#assignments-aka-work)
    - [File Exchange aka dropbox](#file-exchange-aka-dropbox)
    - [Other Locations](#other-locations)

<!-- /TOC -->
# Intro

The application is an old version of open-eclass learning platform based on a LAMP stack with PHP5 as the main web language.

There are here and there protections that help secure SQL queries and avoid Cross-Site Scripting (XSS) but they are not satisfied across the entirety of the codebase. A despicable `db_query` wrapper function is used all over the place that performs "raw" sql queries which are subject to injection. Additionaly, sanitization is often there but is faulty, using functions such as `autoquote()`.

## Scope
- Basic `auth` functionality
- Basic `admin` workflows
- `work` module
- `dropbox` module
- `conference` module
- `phpbb` module

## What didn't happen:

Below are protections that could be implemented to better modernize the security of the application, but didn't happen because of the short auditing grind.

1. **Content Security Policy (CSP)**  
To satisfy the origin of JavaScript code being executed and prevent XSS attack surface.  
[Reference](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy)

2. **Cross-Origin Resource Sharing (CORS)**  
CORS is an HTTP-header based mechanism that allows a server to indicate any origins (domain, scheme, or port) other than its own from which a browser should permit loading resources. It is a security mechanism implemented by modern web browsers to maintain the integrity of a website and protect it from unauthorized access.  
[Reference](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)

3. **Modern Password Hashing**  
Usage of the `MD5` hashing algorithm has been prevalent throughout the application. `MD5` is not to be considered either modern or secure; not does `SHA1` for that matter. By default, PHP5's default hashing option is the `Bcrypt` hashing algorithm. This should be used for passwords and anything else.

4. **Unit testing**  
To satisfy that code that gets fixed is indeed fixed and functional.  
This should be done in 2 parts: 1. `phpcs` testing as a pre-commit hook and 2. functional testing of webpages that get changed. The first is doable, while the second is a bit of a stretch goal.

## Modules Disabled

- `/modules/import/import.php`
- `/modules/group/*`

All other stuff the went unused should have also been disabled. Some contain fixes though, because there wasn't the understanding of them being out of scope at the beginning of the auditing period. Because of this the site was exploited.

# SQL Injections

**All user input must be sanitized**.  
Having established that, the application allows attack surface for all types of SQL Injections (Blind, UNION-based, Authentication Bypass, Time-based) to take place.

We achieve protection of most injection points either via filtering parameters or when that's not sufficient by using Prepared Statements.

For correctnesss sake, Prepared Statements and the `mysqli` family of functions should be used everywhere, but changing whole SQL queries only to sanitize an `id` parameter wasn't efficient in this audit.

## Mitigation

`autoquote()` was used but is insufficient in most cases to mitigate an SQL Injection.

Instead, the below (especially when combined) can properly sanitize user input:

- `mysqli` library functions, Prepared Statements with parameter type binding.
- `filter_var()`, especially for email input, but in general for filtering; depends on the 3 argument directive.
- `mysql_real_escape_string()`, to filter special characters.
- `intval()`, to sanitize integer parameters and ids.

## Changes in the codebase

### `/upgrade/`  
 - `is_admin()` function has been rewritten in [upgrade_functions file](./openeclass/upgrade/upgrade_functions.php).

### `/modules/create_course/`
- `create_course.php`, added prepared statement.

### `/modules/profile`
- `profile.php`, prepared statement and variable sanitization.

### `/modules/unreguser`
- `unregcours.php`, prepared statement.

### `/modules/course_info`
- `infocours.php`, prepared statement and some weird user supplied value sanitization (what was I thinking?). 

### `/modules/phpbb/`
- `index.php`, variable sanitization
- `newtopic.php`, prepared statement queries **everywhere**.
- `reply.php`, filtering of user supplied values and prepared statement queries where needed.
- `viewforum.php`, variable sanitization.
- `viewtopic.php`, variable sanitization.

### `/modules/admin/`
- [adminannouncements.php], filtering of user supplied values and prepared statement queries where needed.

- [edituser.php](./openeclass/modules/admin/edituser.php), filtering of user supplied values and prepared statement queries where needed.

- [multireguser](./openeclass/modules/admin/multireguser.php), changed `autoquote` to `mysql_real_escape_string`.

- `newuseradmin`, filtering of user supplied values and prepared statement queries where needed.

- `password.php`, prepared statement on query.

### `/modules/auth/`
- [newuser.php](./openeclass/modules/auth/newuser.php), filtering of user supplied values and prepared statement queries where needed.

- [newusereq.php](./openeclass/modules/auth/newuserreq.php), filtering of user supplied values and prepared statement queries where needed.

### `/modules/work/`
- [work.php](./openeclass/modules/work/work.php), sanitize `id` variable and use prepared statement in `submit_work` function sql query.

### `/include/lib/`  
- Special love has been shown to `main.lib.php`. Functions `course_code_to_title` and `email_seems_valid` have been refactored.

- In general, the `include` folder would be the first to be refactored to update its entire codebase and resolve security issues, since it's code is "pinata" included in different parts of the codebase so it carries any security issues along with it everywhere. If time permitted more issues would have been resolved.

# Cross-Site Scripting (XSS)

## X-XSS-Protection Header
 The `header("X-XSS-Protection: 1; mode=block");` line has been enabled in the `baseTheme.php` in order to tell to modern browsers to enable their XSS protection ([Reference](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-XSS-Protection)). This measure though should not be taken as an absolute, because security has no panacea solutions. It's just a good practice.

## $_SERVER['PHP_SELF']  

The site is tremendously vulnerable to XSS. By "trememndously" we mean that in every single location there is at least 1 XSS vector via `$_SERVER["PHP_SELF"]`. Because there is no sanitization on this variable we can inject XSS payloads that get reflected in the site's source code onload. Payloads of the form `<any website path>"><script> alert("DRAGONS")</script>` are a go.

To prevent this XSS vulnerability we use the `htmlspecialchars()` function **in every single line of code** that uses the `$_SERVER["PHP_SELF"]` variable.  

An exhaustive list will not be provided. But essentially via a sed rule every occurence of the above has been changed. I tried to include a single commit with all the locations, but mistakes were made.

## The Rest

While some sanitization of user input did exist in different parts of the source code, such as when an `$id` variable gets set by user input and then is printed in an html element, some sanitization takes place. Where it did not exist, the `htmlspecialchars` has been used again.

Locations are too scattered to write them up.

# Cross-site Request Forgery (CSRF)

The [csrf-magic](https://github.com/ezyang/csrf-magic) library has been used, which automatically introduces CSRF tokens in all the forms of the web application.

It's configuration can be found at [the application's base theme](./openeclass/include/baseTheme.php#L53) which is included in every single path/page of the application.

Some other additions have been made to the [csrf-magic.php file](./openeclass/include/csrf-magic/csrf-magic.php) such as the [handler function](./openeclass/include/csrf-magic/csrf-magic.php#L266).

This proposed approach was deemed best because of time restrictions, but also because it can **guarantee** (with proper configuration, which I am not sure I have done due to lack of meaningful documentation) that no manual input in every form of the application is needed; hence mistakes won't happen.

# Remote File Inclusion (RFI)

This is probably the most important category of vulnerabilities, since a clever attacker can easily achieve code execution on the server and wreak all kinds of havoc.

The main 2 targets are `/modules/dropbox` and `/modules/work`.

**Defences against RFI**
- Change filename randomizer.
- Change file permissions to a 0222 mask.
- Save every file as a txt file.

Another prevalent exploitation technique for uploads in PHP5 is the infamous NULL byte vulnerability. If we sanitized extensions alone, it is probable that an attacker could upload a file of the form `hello.php\0.txt`, we would sanitize the extension `.txt` but the saved file would be a danger. (It's possible, not somehow found in the context of this codebase, but a good precaution).

Additionaly, directory listing has been disabled everywhere to protec from information disclosure on the saved files and their locations.

At last, other locations where Remote File Inclusion via upload are possible and they have been dealt accordingly (killed with kindness, no nines and stuff).

## Directory listing

Added an `index.html` to every subdirectory that didn't have an `index.html` or `index.php`. Alternatively and more professionaly `.htaccess` rules should be used, but personally it was easier to write a very long one liner that does the file addition than read the documentation of `.htaccess`.

## Assignments (aka work)

Changes to the application logic have been applied to `add_assignment` and `submit_work` functions.

In `add_assignment`:
1. Use `openssl_random_pseudo_bytes()` to get a random key which will be the uploaded file's directory name.

In `submit_work`:
1. Sanitized against NULL byte value.

2. Sanitize filename for SQLi.

3. Force `.txt` extension.

4. Enforce 0222 permission mask.

## File Exchange (aka dropbox)

In `dropbox_download.php`, change permissions to 0222.

In `dropbox_submit.php`:

- Sanitize NULL byte,
- Sanitize all user inputed values,
- Provide a .txt random key filename via the `safe_filename()` function which was rewritten to use `openssl_random_pseudo_bytes()`, and can be seen [here](./openeclass/include/lib/main.lib.php#L993).

## Other Locations
THe following modules do also provide leeway for RFI and need handling.

- document - **FORGOT IT AND GOT PWNED**
- import - **DISABLED**
- course_tools - **DISABLED** the website upload, not the link inclusion. 