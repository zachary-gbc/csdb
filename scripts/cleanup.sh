#!/bin/bash

. /var/www/conf/csdb.conf
lanip=$(hostname -I | tr -d ' ')
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting cleanup" >> /home/pi/log/csdb/$log.log

# Delete old log files
find /home/pi/log -mtime +30 -type f -delete

if [ "$database_ip" == "$lanip" ]
then
    # Delete old database backups
    find /var/www/html/dbbackup -mtime +30 -type f -delete
fi
