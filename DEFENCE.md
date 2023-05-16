# Intro

The application is an old version of open-eclass learning platform based on a LAMP stack with PHP5 as the main web language.

- What didn't happen:
    - Content Security Policy (CSP)
    - CORS
    - Modern password hashing (deprecate md5)
    - Unit testing, for when something gets fixed to continue being usable

## Tooling
- skipfish
- burpsuite
- wapiti3
- sqlmap
- 

# SQL Injections

- `sqlmap` in batch mode has been used 


## Mitigation

## Example fixes in the codebase

# XSS

## $_SERVER['PHP_SELF']

The site is tremendously vulnerable to XSS. By "trememndously" we mean that in every single location there is at least 1 XSS vector via `$_SERVER["PHP_SELF"]`. Because their is no sanitazation on this variable we can inject XSS payloads that get reflected in the site's source code onload. Payloads of the form `"><script> alert("DRAGONS")</script>` are a go. There are functions such as `lang_selections()`::baseTheme.php that carry this vuln with them, but also many other sites in the code that have the above mentioned functionality included.

# CSRF

The [csrf-magic](https://github.com/ezyang/csrf-magic) library has been used, which automatically introduces CSRF tokens in all the forms of the web application.

It's configuration can be found at [the application's base theme](./openeclass/include/baseTheme.php#L53) which is included in every single path/page of the application.

Some other additions have been made to the [csrf-magic.php file](./openeclass/include/csrf-magic/csrf-magic.php) such as the [handler function](./openeclass/include/csrf-magic/csrf-magic.php#L266).

This proposed approach was deemed best because of time restrictions, but also because it can guarantee (with proper configuration, which I am not sure I have done due to lack of meaningful documentation) that no manual input in every form of the application is needed; hence mistakes won't happen.

# RFI


### References
1. [](https://phptherightway.com/#security)
2. https://www.php.net/manual/en/security.php
