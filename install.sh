#!/bin/bash

lanip=$(hostname -I)
dblan=${lanip%.*}
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')

echo "Is this the Main Database Instance? (y or n)"
read maininstall
if [[ $maininstall == "Y" ]] || [[ $maininstall == "y" ]]
then
  echo "Please Input a Database Name No Spaces Allowed (example churchname_prod)"
  read dbname
  echo "Please Input a User for the Database (No Spaces)"
  read dbuser
  echo "Please Input a Password for $dbuser (No Spaces)"
  echo "Keep it simple, this isn't Fort Knox"
  read dbpass
  echo ""
  dbip=$lanip
else
  echo "Input Main Host IP Address To Copy Settings:"
  read dbip
fi

install_log="/home/pi/csdb_install.log"
echo "Initiating Install" > $install_log
mkdir -p /home/pi/scripts
mkdir -p /home/pi/log

sudo apt-get update
sudo apt-get upgrade -y
appstoinstall=(at apache2 php php-mysql php-curl mariadb-server git wget curl)

for app in ${appstoinstall[@]}
do
  echo "--------------------" >> $install_log
  echo "Installing $app" >> $install_log
  sudo apt-get -qq install $app -y
  echo "Completed Install of $app" >> $install_log
  echo "--------------------" >> $install_log
  echo "" >> $install_log
done

cp /home/pi/csdb/csdbghupdate.sh /home/pi/scripts/csdbghupdate.sh
sudo cp -f /home/pi/csdb/csdb.cron /etc/cron.d/csdb
sudo chown root:root /etc/cron.d/csdb
sudo mkdir -p /var/www/conf
sudo mkdir -p /var/www/html/other
sudo chown pi:pi /var/www/html
sudo chown pi:pi /var/www/conf
sudo chown pi:pi /var/www/html/other
cp /home/pi/csdb/csdb.conf /var/www/conf/csdb.conf
cp /home/pi/csdb/dblogin.php /var/www/html/other/dblogin.php
sudo rm /var/www/html/index.html
echo "never" > /home/pi/csdb_lastupdatecommit

if [[ $maininstall == "Y" ]] || [[ $maininstall == "y" ]]
then
  sudo mysql --user='root' -e "GRANT ALL PRIVILEGES ON *.* TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass'"
  sudo mysql --user='root' -e "GRANT ALL PRIVILEGES ON *.* TO '$dbuser'@'$dblan%' IDENTIFIED BY '$dbpass'"
  sudo mysql --user='root' -e "CREATE DATABASE IF NOT EXISTS $dbname"
  sudo mysql --user="$dbuser" --password="$dbpass" --database="$dbname" < /home/pi/csdb/db.txt
  sudo mysql --user="$dbuser" --password="$dbpass" --database="$dbname" -e "INSERT INTO Variables(Var_Name, Var_Value) VALUES('Database-IP', '$lanip');"
  sudo mysql --user="$dbuser" --password="$dbpass" --database="$dbname" -e "INSERT INTO Variables(Var_Name, Var_Value) VALUES('Database-Name', '$dbname');"

  sudo sed -i "s/database_ip.*/database_ip=\"$dbip\"/" /var/www/conf/csdb.conf
  sudo sed -i "s/database_name.*/database_name=\"$dbname\"/" /var/www/conf/csdb.conf
  sudo sed -i "s/database_username.*/database_username=\"$dbuser\"/" /var/www/conf/csdb.conf
  sudo sed -i "s/database_password.*/database_password=\"$dbpass\"/" /var/www/conf/csdb.conf
else
  sudo curl -Ss "http://$dbip/csdb/conf/csdb.conf" --output /var/www/conf/csdb.conf
  sudo curl -Ss "http://$dbip/csdb/scripts/dbupdate.php?type=devicedetails&device=$mac&devname=$HOSTNAME" >> $install_log
fi

sudo apt autoremove -y

. /var/www/conf/csdb.conf

echo ""
echo "----------------------"
echo "-- Main Pi IP: $database_ip --"
echo "-- Check Conf if IP Incorrect --"
echo "----------------------"

if [ -z "$1" ]
then
  echo ""
  echo "----------------------"
  echo "-- Install Complete --"
  echo "----------------------"
  echo "-- Plase Reboot Now --"
  echo "----------------------"
fi
