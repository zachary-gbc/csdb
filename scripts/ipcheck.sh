#!/bin/bash

. /var/www/conf/csdb.conf
if [[ ! -f /home/pi/scripts/csdb/lanip ]]
then
  echo "1.1.1.1" > /home/pi/scripts/csdb/lanip
  echo "1.1.1.1" > /home/pi/scripts/csdb/wanip
fi

mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
curlan=$(cat /home/pi/scripts/csdb/lanip)
curwan=$(cat /home/pi/scripts/csdb/wanip)
lanip=$(hostname -I)
wanip=$(curl https://ipecho.net/plain)
laniplength=${#lanip}
waniplength=${#wanip}
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S')
echo "MESSAGE $datetime: Starting ipcheck" >> /home/pi/log/csdb/$log.log

if [ $waniplength > 5 ] && [ $laniplength > 5 ]
then
  if [ $curwan != $wanip ] || [ $curlan != $lanip ]
  then
    if [[ $pushover_configured == "yes" ]] && [[ $alert_on_ip_change == "yes" ]]
    then
      bash /home/pi/scripts/csdb/pushover.sh "$HOSTNAME IP Changed" "none" "WAN: $wanip | LAN: $lanip"
    fi
    echo $lanip > /home/pi/scripts/csdb/lanip
    echo $wanip > /home/pi/scripts/csdb/wanip
    curl http://$database_ip/other/dbupdate.php?type=ipchange\&device=$mac\&lanip=$lanip
  fi
fi