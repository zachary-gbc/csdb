#!/bin/bash

. /var/www/conf/csdb.conf
lanip=$(hostname -I)
if [[ "${lanip: -1}" == " " ]]; then lanip=${lanip:0:-1}; fi

curl -Ss "http://$database_ip/other/updateconf.php?updateconf=true&deviceip=$lanip" >> /var/www/conf/csdb.download

charcount=$(wc -m < "/var/www/conf/csdb.download")
if [[ $charcount > 60 ]]
then
    if [ "$main_or_remote" == "main" ]
    then
        sed -i '10,$d' /var/www/conf/csdb.conf
        cat /var/www/conf/csdb.download >> /var/www/conf/csdb.conf
    else
        mv /var/www/conf/csdb.download /var/www/conf/csdb.conf
    fi
fi

rm /var/www/conf/csdb.download
