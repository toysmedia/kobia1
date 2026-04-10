# FreeRADIUS SQL Configuration for iNettotik

This guide shows how to configure FreeRADIUS 3.x to use the Laravel MySQL database.

## Prerequisites
- Ubuntu 22.04 LTS
- FreeRADIUS 3.x installed (`apt install freeradius freeradius-mysql`)
- MySQL 8.x
- iNettotik Laravel app running with migrations applied (`php artisan migrate`)

## 1. Enable the SQL Module

```bash
cd /etc/freeradius/3.0
ln -s mods-available/sql mods-enabled/sql
```

## 2. Configure SQL Module

Edit `/etc/freeradius/3.0/mods-available/sql`:

```
sql {
    driver = "rlm_sql_mysql"
    dialect = "mysql"

    server = "127.0.0.1"
    port = 3306
    login = "your_db_user"
    password = "your_db_password"
    radius_db = "your_laravel_database"

    # The table names match the Laravel migrations exactly
    acct_table1 = "radacct"
    acct_table2 = "radacct"
    postauth_table = "radpostauth"
    authcheck_table = "radcheck"
    groupcheck_table = "radgroupcheck"
    authreply_table = "radreply"
    groupreply_table = "radgroupreply"
    usergroup_table = "radusergroup"

    read_groups = yes
    pool {
        start = 5
        min = 4
        max = 10
        spare = 3
        uses = 0
        lifetime = 0
        idle_timeout = 60
    }

    read_clients = yes
    client_table = "nas"
}
```

## 3. Configure Default Site

Edit `/etc/freeradius/3.0/sites-available/default`:

In the `authorize` section, add/uncomment:
```
authorize {
    preprocess
    chap
    mschap
    suffix
    sql           # <-- add this
    pap
}
```

In the `accounting` section:
```
accounting {
    sql           # <-- add this
}
```

In the `post-auth` section:
```
post-auth {
    sql           # <-- add this
    Post-Auth-Type REJECT {
        sql       # <-- add this
    }
}
```

## 4. Configure Inner Tunnel (for PEAP/TTLS)

Edit `/etc/freeradius/3.0/sites-available/inner-tunnel`:
```
authorize {
    sql
    pap
}
post-auth {
    sql
}
```

## 5. NAS/Client Configuration

FreeRADIUS will read clients from the `nas` table (configured with `read_clients = yes`).
Alternatively, you can add static clients in `/etc/freeradius/3.0/clients.conf`:

```
client mikrotik-router-1 {
    ipaddr = 1.2.3.4          # Your router WAN IP
    secret = testing123       # Must match router's RADIUS secret
    shortname = router1
    nas_type = other
}
```

## 6. Testing

```bash
# Test FreeRADIUS config syntax
freeradius -XC

# Start in debug mode
freeradius -X

# Test authentication
radtest testuser testpass localhost 0 testing123
```

## 7. FreeRADIUS as Service

```bash
systemctl enable freeradius
systemctl start freeradius
systemctl status freeradius
```

## 8. Verify RADIUS Tables

After `php artisan migrate`, verify tables exist:
```sql
SHOW TABLES LIKE 'rad%';
SHOW TABLES LIKE 'nas';
```

You should see: radcheck, radreply, radgroupcheck, radgroupreply, radusergroup, radacct, radpostauth, nas

## 9. MikroTik RADIUS Client Setup

In MikroTik Winbox:
1. Go to RADIUS → Add
2. Service: ppp + hotspot + login
3. Address: your_server_ip
4. Secret: same as in router record
5. Authentication Port: 1812
6. Accounting Port: 1813

Or use the generated MikroTik script from the admin panel (Admin → Routers → Generate Script).
