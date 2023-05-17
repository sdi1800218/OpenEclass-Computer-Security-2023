# TODO
- Intro more info
- Finish XSS and SQLi
- Finish RFI
- Tooling writeup and move to main README

# Intro

The application is an old version of open-eclass learning platform based on a LAMP stack with PHP5 as the main web language.

- What didn't happen:
    - Content Security Policy (CSP)
    - CORS
    - Modern password hashing (deprecate md5)
    - Unit testing, for when something gets fixed to continue being usable

# SQL Injections

`autoquote()` was used but is insufficient

## Mitigation

- `mysqli` Prepared Statements with parameter type binding
- `filter_var()`, especially for email input
- `mysql_real_escape_string()`
- `intval()`

## Example fixes in the codebase

- /modules/phpbb/
- /modules/admin/
- /modules/auth/
- /modules/work/
- `/include/lib/`
Special love has been shown to `main.lib.php`. Functions `course_code_to_title` and `email_seems_valid` have been refactored.



In general, the `include` folder would be the first to be refactored to update the entire codebase and resolve security issues, since it's code is pinata included in different parts of the codebase so it carries any security issues along with it everywhere. If time permitted more issues would have been resolved.



# XSS

- `header("X-XSS-Protection: 1; mode=block");`

## $_SERVER['PHP_SELF']

The site is tremendously vulnerable to XSS. By "trememndously" we mean that in every single location there is at least 1 XSS vector via `$_SERVER["PHP_SELF"]`. Because their is no sanitazation on this variable we can inject XSS payloads that get reflected in the site's source code onload. Payloads of the form `"><script> alert("DRAGONS")</script>` are a go. There are functions such as `lang_selections()`::baseTheme.php that carry this vuln with them, but also many other sites in the code that have the above mentioned functionality included.

To prevent this XSS vulnerability we use the `htmlspecialchars()` function **in every single line of code** that uses the `$_SERVER["PHP_SELF"]` variable.  
An exhaustive list will not be provided. But essentially via a sed rule every occurence of the above has been changed. I tried to include a single commit with all the locations, but mistakes were made.



# CSRF

The [csrf-magic](https://github.com/ezyang/csrf-magic) library has been used, which automatically introduces CSRF tokens in all the forms of the web application.

It's configuration can be found at [the application's base theme](./openeclass/include/baseTheme.php#L53) which is included in every single path/page of the application.

Some other additions have been made to the [csrf-magic.php file](./openeclass/include/csrf-magic/csrf-magic.php) such as the [handler function](./openeclass/include/csrf-magic/csrf-magic.php#L266).

This proposed approach was deemed best because of time restrictions, but also because it can guarantee (with proper configuration, which I am not sure I have done due to lack of meaningful documentation) that no manual input in every form of the application is needed; hence mistakes won't happen.

# RFI

## Directory listing

Added an `index.html` to every subdirectory that didn't have an `index.html` or `index.php`. Alternatively and more professionaly `.htaccess` rules should be used, but personally it was easier to write a very long one liner that does the file addition than read the documentation of `.htaccess`.

## `/modules/work`

## `/modules/dropbox`



# Extras

## Tooling
- skipfish (guest, user, admin)
- burpsuite
- wapiti3
- sqlmap, for weird wueries after initial 
- phpcs security

## Resources
1. [PHP the right way -- Security Chapter](https://phptherightway.com/#security)
2. [PHP Documentation -- Security Section](https://www.php.net/manual/en/security.php)
