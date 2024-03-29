## Open eClass 2.3

Το repository αυτό περιέχει μια __παλιά και μη ασφαλή__ έκδοση του eclass.
Προορίζεται για χρήση στα πλαίσια του μαθήματος
[Προστασία & Ασφάλεια Υπολογιστικών Συστημάτων (ΥΣ13)](https://ys13.chatzi.org/), __μην τη
χρησιμοποιήσετε για κάνενα άλλο σκοπό__.


### Χρήση μέσω docker
```
# create and start (the first run takes time to build the image)
docker-compose up -d

# stop/restart
docker-compose stop
docker-compose start

# stop and remove
docker-compose down -v
```

To site είναι διαθέσιμο στο http://localhost:8001/. Την πρώτη φορά θα πρέπει να τρέξετε τον οδηγό εγκατάστασης.


### Ρυθμίσεις eclass

Στο οδηγό εγκατάστασης του eclass, χρησιμοποιήστε __οπωσδήποτε__ τις παρακάτω ρυθμίσεις:

- Ρυθμίσεις της MySQL
  - Εξυπηρέτης Βάσης Δεδομένων: `db`
  - Όνομα Χρήστη για τη Βάση Δεδομένων: `root`
  - Συνθηματικό για τη Βάση Δεδομένων: `1234`
- Ρυθμίσεις συστήματος
  - URL του Open eClass : `http://localhost:8001/` (προσοχή στο τελικό `/`)
  - Όνομα Χρήστη του Διαχειριστή : `drunkadmin`

Αν κάνετε κάποιο λάθος στις ρυθμίσεις, ή για οποιοδήποτε λόγο θέλετε να ρυθμίσετε
το openeclass από την αρχή, διαγράψτε το directory, `openeclass/config` και ο
οδηγός εγκατάστασης θα τρέξει ξανά.

## 2023 Project 1

Εκφώνηση: https://ys13.chatzi.org/assets/projects/project1.pdf


## Μέλη ομάδας

- 1115201800218, Pantazis Harry

## Shield

The report on vulnerabilities found in the application's code and fixes provided can be found at [the Defense writeup](./DEFENCE.md).

## Sword

The report on attacks tested and found on the opponent team's site can be found at [the Attack writeup](./ATTACK.md).


# Extras

## Tooling
- `skipfish` (guest, user, admin), for web security auditing.
- `burpsuite`, to intercept and play with HTTP requests.
- `wapiti3`, as a second web app security auditor.
- `sqlmap`, for weird query weaponization after initial identification.
- `phpcs`, for static analyzing the codebase with the [phpcs security audit ruleset](https://github.com/FloeDesignTechnologies/phpcs-security-audit).

## Resources
1. [PHP the right way -- Security Chapter](https://phptherightway.com/#security)
2. [PHP Documentation -- Security Section](https://www.php.net/manual/en/security.php)
