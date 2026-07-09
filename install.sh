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
    mainip=$lanip
    echo "Please make sure device has been set to static ip address. Press enter to continue."
    read nothing
else
    echo "Input Main Host IP Address To Copy Settings:"
    read mainip
fi

mainip=$(echo $mainip | xargs)
mkdir -p /home/pi/scripts/csdb
install_log="/home/pi/log/csdb/install.log"
echo "Initiating Install" > $install_log
mkdir -p /home/pi/log/csdb

sudo apt-get update
sudo apt-get upgrade -y
appstoinstall=(apache2 php php-mysql php-curl mariadb-server git wget curl)

for app in ${appstoinstall[@]}
do
    echo "--------------------" >> $install_log
    echo "Installing $app" >> $install_log
    sudo apt-get -qq install $app -y
    echo "Completed Install of $app" >> $install_log
    echo "--------------------" >> $install_log
    echo "" >> $install_log
done

sudo mv -f /home/pi/csdb/scripts/* /home/pi/scripts/csdb/
sudo cp -f /home/pi/csdb/csdb.cron /etc/cron.d/csdb
sudo chown root:root /etc/cron.d/csdb
sudo mkdir -p /var/www/conf
sudo mkdir -p /var/www/html/other
sudo chown pi:pi /var/www/html
sudo chown pi:pi /var/www/conf
sudo chown pi:pi /var/www/html/other
cp /home/pi/csdb/csdb.conf /var/www/conf/csdb.conf
sudo rsync -avu "/home/pi/csdb/website/" "/var/www/html/other"
sudo rm -f /var/www/html/index.html
echo "never" > /home/pi/csdb_lastupdatecommit

phpversion=$(php -i | grep "PHP Version")
phpversionnumber=${phpversion:15:3}
systemtimezone=$(cat /etc/timezone)
timezone=("date.timezone = $systemtimezone")
sudo sed -i 's/upload_max_filesize.*/upload_max_filesize = 800M/' /etc/php/$phpversionnumber/apache2/php.ini
sudo sed -i 's/;max_input_vars.*/max_input_vars = 2000/' /etc/php/$phpversionnumber/apache2/php.ini
sudo sed -i "s|;date.timezone.*|$timezone|" /etc/php/$phpversionnumber/apache2/php.ini
sudo sed -i 's/post_max_size.*/post_max_size = 800M/' /etc/php/$phpversionnumber/apache2/php.ini
sudo sed -i 's/bind-address.*/#bind-address = 127.0.0.1/' /etc/mysql/mariadb.conf.d/50-server.cnf

sudo sed -i "s/date.timezone.*/date.timezone = $timezone/" /etc/php/$phpversionnumber/apache2/php.ini

if [[ $maininstall == "Y" ]] || [[ $maininstall == "y" ]]
then
    sudo mysql --user='root' -e "GRANT ALL PRIVILEGES ON *.* TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass'"
    sudo mysql --user='root' -e "GRANT ALL PRIVILEGES ON *.* TO '$dbuser'@'$dblan%' IDENTIFIED BY '$dbpass'"
    sudo mysql --user='root' -e "CREATE DATABASE IF NOT EXISTS $dbname"
    sudo mysql --user="$dbuser" --password="$dbpass" --database="$dbname" < /home/pi/csdb/db.txt

    sudo sed -i "s/database_name.*/database_name=\"$dbname\"/" /var/www/conf/csdb.conf
    sudo sed -i "s/database_username.*/database_username=\"$dbuser\"/" /var/www/conf/csdb.conf
    sudo sed -i "s/database_password.*/database_password=\"$dbpass\"/" /var/www/conf/csdb.conf
else
    sudo sed -i "s/database_name.*/remote=\"true\"/" /var/www/conf/csdb.conf
    sudo sed -i "s/database_username.*//" /var/www/conf/csdb.conf
    sudo sed -i "s/database_password.*//" /var/www/conf/csdb.conf
fi

sudo sed -i "s/database_ip.*/database_ip=\"$mainip\"/" /var/www/conf/csdb.conf
curl http://$mainip/other/dbupdate.php?type=new\&device=$mac\&lanip=$lanip
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

( sleep 1; rm -r -f /home/pi/csdb ) & 
