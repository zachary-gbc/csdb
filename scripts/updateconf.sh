#!/bin/bash

. /var/www/conf/csdb.conf
lanip=$(hostname -I)
if [[ "${lanip: -1}" == " " ]]; then lanip=${lanip:0:-1}; fi

if [ "$main_or_remote" == "main" ]
then
    sed -i '10,$d' /var/www/conf/csdb.conf
fi

curl -Ss "http://$database_ip/other/updateconf.php?deviceip=$lanip" > /var/www/conf/csdb.conf
