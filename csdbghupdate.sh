#!/bin/bash

sleep 60

. /var/www/html/conf/csdb.conf
lastupdate=$(</home/pi/csdb_lastupdatecommit)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting ghupdate" >> /home/pi/log/$log.log

sudo rm -r -f /home/pi/csdb
git clone --depth=1 https://github.com/zachary-gbc/csdb /home/pi/csdb
cd /home/pi/csdb
lastcommit=$(git log --pretty=format:"%H")

if [[ $lastcommit != $lastupdate ]]
then
  mv /home/pi/csdb/csdbghupdate.sh /home/pi/scripts/csdbghupdate.sh

  # Crons
  sudo mv -f /home/pi/csdb/csdb.cron /etc/cron.d/csdb
  sudo chown root:root /etc/cron.d/csdb

  echo $lastcommit > /home/pi/csdb_lastupdatecommit
fi

sudo rm -r -f /home/pi/csdb
