# Server Installation Guide (Ubuntu 22.04 LTS)

This guide installs the exact required versions:
- OpenVPN `2.4.12`
- FreeRADIUS `3.2.8`
- Supervisor `4.1.0`

> Run commands as a sudo-capable user.

---

## 1) Install OpenVPN 2.4.12 (from source)

```bash
sudo apt update
sudo apt install -y build-essential libssl-dev liblzo2-dev libpam0g-dev libsystemd-dev pkg-config wget
cd /usr/local/src
sudo wget https://swupdate.openvpn.net/community/releases/openvpn-2.4.12.tar.gz
sudo tar -xzf openvpn-2.4.12.tar.gz
cd openvpn-2.4.12
sudo ./configure --enable-systemd --enable-async-push
sudo make
sudo make install
```

Create placeholder server config:

```bash
sudo mkdir -p /etc/openvpn
cat <<'EOF' | sudo tee /etc/openvpn/server.conf
port 1194
proto udp
dev tun
server 10.8.0.0 255.255.255.0
ifconfig-pool-persist ipp.txt
keepalive 10 120
EOF
```

Create systemd service:

```bash
cat <<'EOF' | sudo tee /etc/systemd/system/openvpn-server.service
[Unit]
Description=OpenVPN Server (custom build)
After=network.target

[Service]
Type=notify
ExecStart=/usr/local/sbin/openvpn --config /etc/openvpn/server.conf
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF
```

Enable/start + verify:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now openvpn-server
openvpn --version | head -1
```

Add Supervisor program config:

```bash
cat <<'EOF' | sudo tee /etc/supervisor/conf.d/openvpn.conf
[program:openvpn]
command=/usr/local/sbin/openvpn --config /etc/openvpn/server.conf
autostart=true
autorestart=true
stderr_logfile=/var/log/openvpn.err.log
stdout_logfile=/var/log/openvpn.out.log
EOF
```

---

## 2) Install FreeRADIUS 3.2.8 (from source)

```bash
sudo apt update
sudo apt install -y libtalloc-dev libkqueue-dev libssl-dev libpcre3-dev libcap-dev libmysqlclient-dev libsqlite3-dev libperl-dev libgdbm-dev libpam0g-dev libreadline-dev libjson-c-dev libcurl4-openssl-dev wget
cd /usr/local/src
sudo wget https://github.com/FreeRADIUS/freeradius-server/releases/download/release_3_2_8/freeradius-server-3.2.8.tar.gz
sudo tar -xzf freeradius-server-3.2.8.tar.gz
cd freeradius-server-3.2.8
sudo ./configure --with-modules="rlm_sql rlm_sql_mysql" --prefix=/usr/local
sudo make
sudo make install
```

Create runtime directory:

```bash
sudo mkdir -p /var/run/radiusd
```

Create systemd service:

```bash
cat <<'EOF' | sudo tee /etc/systemd/system/freeradius.service
[Unit]
Description=FreeRADIUS Server (custom build)
After=network.target

[Service]
Type=forking
PIDFile=/var/run/radiusd/radiusd.pid
ExecStartPre=/usr/local/sbin/radiusd -C -d /usr/local/etc/raddb
ExecStart=/usr/local/sbin/radiusd -d /usr/local/etc/raddb
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF
```

Enable/start + verify:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now freeradius
/usr/local/sbin/radiusd -v | head -3
```

Important config paths:
- Main config: `/usr/local/etc/raddb/radiusd.conf`
- SQL module: `/usr/local/etc/raddb/mods-available/sql`
- Clients: `/usr/local/etc/raddb/clients.conf`

Add Supervisor program config (foreground mode):

```bash
cat <<'EOF' | sudo tee /etc/supervisor/conf.d/freeradius.conf
[program:freeradius]
command=/usr/local/sbin/radiusd -f -d /usr/local/etc/raddb
autostart=true
autorestart=true
stderr_logfile=/var/log/freeradius.err.log
stdout_logfile=/var/log/freeradius.out.log
EOF
```

---

## 3) Install Supervisor 4.1.0

```bash
sudo apt update
sudo apt install -y python3-pip
sudo pip3 install supervisor==4.1.0
echo_supervisord_conf | sudo tee /etc/supervisor/supervisord.conf >/dev/null
```

Ensure include directive exists:

```bash
sudo mkdir -p /etc/supervisor/conf.d
grep -q "^\[include\]" /etc/supervisor/supervisord.conf || cat <<'EOF' | sudo tee -a /etc/supervisor/supervisord.conf

[include]
files = /etc/supervisor/conf.d/*.conf
EOF
```

Create systemd service:

```bash
cat <<'EOF' | sudo tee /etc/systemd/system/supervisord.service
[Unit]
Description=Supervisor process control system
After=network.target

[Service]
Type=forking
ExecStart=/usr/local/bin/supervisord -c /etc/supervisor/supervisord.conf
ExecStop=/usr/local/bin/supervisorctl -c /etc/supervisor/supervisord.conf shutdown
ExecReload=/usr/local/bin/supervisorctl -c /etc/supervisor/supervisord.conf reload
KillMode=process
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF
```

Enable/start + verify:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now supervisord
sudo supervisorctl reread
sudo supervisorctl update
supervisord --version
```

---

## 4) Server permissions for Laravel (`www-data`)

Allow `www-data` to restart OpenVPN, FreeRADIUS, and Supervisord without password prompt:

```bash
cat <<'EOF' | sudo tee /etc/sudoers.d/laravel
www-data ALL=NOPASSWD: /bin/systemctl restart openvpn-server
www-data ALL=NOPASSWD: /bin/systemctl restart freeradius
www-data ALL=NOPASSWD: /bin/systemctl restart supervisord
EOF
sudo chmod 440 /etc/sudoers.d/laravel
sudo visudo -cf /etc/sudoers.d/laravel
```
