#!/bin/bash

sleep 60

. /var/www/conf/csdb.conf
lastupdate=$(</home/pi/csdb_lastupdatecommit)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting ghupdate" >> /home/pi/log/csdb/$log.log

sudo rm -r -f /home/pi/csdb
git clone --depth=1 https://github.com/zachary-gbc/csdb /home/pi/csdb
cd /home/pi/csdb
lastcommit=$(git log --pretty=format:"%H")

if [[ $lastcommit != $lastupdate ]]
then
    mv /home/pi/csdb/scripts/ghupdate.sh /home/pi/csdbghupdate.sh
    ( sleep 60; mv /home/pi/csdbghupdate.sh /home/pi/scripts/csdb/ghupdate.sh ) & 

    # Scripts
    sudo mv -f /home/pi/csdb/scripts/* /home/pi/scripts/csdb/

    # Crons
    sudo mv -f /home/pi/csdb/csdb.cron /etc/cron.d/csdb
    sudo chown root:root /etc/cron.d/csdb
    sudo chmod 600 /etc/cron.d/csdb

    # Website
    sudo mv /home/pi/csdb/website/index.php /var/www/html/index.php
    sudo rsync -avu "/home/pi/csdb/website/" "/var/www/html/other"

    echo $lastcommit > /home/pi/csdb_lastupdatecommit
fi

sudo rm -r -f /home/pi/csdb
