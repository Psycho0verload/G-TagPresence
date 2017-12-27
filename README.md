# openHAB 2: G-TagPresence
Zuverlässige Anwesenheitserkennung für openHAB2 mit Gigaset G-tags mit Hilfe von Nginx/PHP.
## Problemstellung
Es gibt viele verschiedene Möglichkeiten wie man in seinem heimischen Smarthomesystem mit openHAB2 eine Anwesenheitserkennung umsetzt. Wenn man sich in Google auf die Suche macht bekommt man viele Varianten/Techniken vorgeschlagen z.B. über Raspberry Pi als Beacon, Geofancy, FritzBox, Ping, usw. Ich habe viele davon getestet und jede Variante/Technik hat ihre ganz besonderen Vor- & Nachteile - häufig geht es zu Lasten vom jeweiligen Smartphone-Akkus und ist abhängig von GPS oder Internetverbindung. Für mich insgesammt noch keine zuverlässige und optimale Lösung.
### Anforderung
* von Smartphone unabhängig
* ohne aktive Internetverbindung
* Flexibilität innerhalb des Netzwerks
* Preiswerte/Effiziente Variante
* Kompatibilität

### Lösungsansatz
Das Raspberry Pi 3 ist ein ausgezeichneter Bluetooth Low Energy Sender/Empfänger. In den meisten Fällen läuft auf selbigen Pi auch das openHAB2 - so können wir hier auf vorhandene Hardware setzten. Man könnte das Rasberry Pi als Beacon verwenden und das Smartphone mit entsprechender App als Empfänger zu nutzen, welcher dann über ein Script und eine Netzwerkverbindung an openHAB2 weitergeleitet wird. Dies ist grundsätzliches eine gute Variante aber birgt doch einige potenzielle Probleme.

Warum aber nicht das Raspberry Pi 3 als klassischen Empfänger nutzen?
Viele Menschen verlassen Ihr Haus/Wohnung nicht ohne ihr Handy aber auch nicht ohne ihren Schlüsselbund. Mit einem preiswerten/effizienten Bluetooth Low Energy Sender welchen man an seinem Schlüsselbund trägt kann man eine deutlich zuverlässigere Variante bauen und ich möchte zeigen und teilen wie ich das gemacht habe.

## Systemkomponenten
* [openHAB2](http://www.openhab.org/) mit aktiver REST API
* [Raspberry Pi 3](https://geizhals.de/raspberry-pi-3-modell-b-a1526643.html)
* [Gigaset G-tag Schlüsselfinder](https://geizhals.de/?cat=gsmzub&asuch=gigaset+g-tag&bpmin=&bpmax=&v=e&hloc=at&hloc=de&plz=&dist=&filter=aktualisieren&mail=&sort=t)

## Installation
Das Sktipt ist so aufgebaut, dass es von PHP5+ unterstützt wird und sollte auch mit Apache2 funktionieren.

Paketliste aktualisieren
```
sudo apt update
```
Nginx installieren
```
sudo apt install nginx
```
PHP + Curl installieren 
```
sudo apt install php5-fpm php5-curl
```
Ihr könnt euch PHP generell Einstellen wie man möchte. Für ein wenig mehr Sicherheit: 
```
sudo nano /etc/php5/fpm/php.ini
# ;cgi.fix_pathinfo=1 zu cgi.fix_pathinfo=0 ändern
```
Kurz PHP neustarten
```
sudo service php5-fpm restart
```
```
*/1 * * * * sh /var/www/html/presence/cronjob.sh > /var/log/cron_gtag.log 2>&1
```
## Quelle
*[hausautomatisierung-koch.de](https://hausautomatisierung-koch.de/2017/01/07/anwesenheitserkennung-bluetooth-beacon/)
