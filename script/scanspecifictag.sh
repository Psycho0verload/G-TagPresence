#!/bin/bash
filename=/tmp/bluetooth_devices.$$
hcitool lescan > $filename & sleep 10
pkill --signal SIGINT hcitool
sleep 1
searchresult=$(grep -c $1 $filename)
if [ $searchresult -gt 0 ]; then
        echo 1
else
        echo 0
fi
rm $filename
