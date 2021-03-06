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
Benötigte Pakete installieren
```
sudo apt install nginx php5-fpm php5-curl bluez
# beziehungsweise
sudo apt install nginx php7.0-fpm php7.0-curl bluez
```
Mit den Kommandos hciconfig und hcitool kann man dann versuchen den Dongle zu aktivieren und die Tags zu scannen:
```
sudo hciconfig hci0 up
sudo hcitool lescan
```
Ausgabe wird wie folgt aussehen:
```
LE Scan ...
7C:2F:80:CE:EF:44 (unknown)
7C:2F:80:CE:EF:44 Gigaset G-tag
```

Sollte dies nicht funktionieren bitte ich euch Google zu bemühen - es gibt diverse Tutorials. Das der Scan funktioniert ist die Grundvoraussetzung.

Ihr könnt euch PHP generell Einstellen wie Ihr es benötig. Für ein wenig mehr Sicherheit sollte man folgendes deaktivieren: 
```
sudo nano /etc/php5/fpm/php.ini
```
oder
```
sudo nano /etc/php/7.0/fpm/php.ini
# ;cgi.fix_pathinfo=1 zu cgi.fix_pathinfo=0 ändern
```
Kurz PHP neustarten
```
sudo service php5-fpm restart
```
bzw.
```
sudo service php7.0-fpm restart
```
Nginx-Konfiguration (sudo nano /etc/nginx/sites-available/default)
```
server {
        listen 80 default_server;
        listen [::]:80 default_server;
        root /var/www/html/presence/;
        index index.php index.html index.htm index.nginx-debian.html;
        server_name 172.16.0.2;
        location / {
                try_files $uri $uri/ =404;
        }
        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php5-fpm.sock;
        }
        location ~ /\.ht {
                deny all;
        }
}
```
Für PHP 7:
```
server {
        listen 80 default_server;
        listen [::]:80 default_server;
        root /var/www/html/presence/;
        index index.php index.html index.htm index.nginx-debian.html;
        server_name 172.16.0.2;
        location / {
                try_files $uri $uri/ =404;
        }
        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
        }
        location ~ /\.ht {
                deny all;
        }
}
```
Kurz Nginx überprüfen und neustarten
```
sudo nginx -t
sudo service nginx restart
```

Die Dateien aus diesem GIT-Repository müssen, wie man der Konfiguration entnehmen kann unter ```/var/www/html/presence/``` abgelegt werden.

```
sudo apt install git-core
sudo git clone https://github.com/Psycho0verload/G-tagPresence.git /var/www/html/presence
```

Nun müssen wir noch die richtigen Rechte für die Datei, welche später den Scan übernimmt vergeben:
```
sudo chmod 500 /var/www/html/presence/script/scanspecifictag.sh 
sudo chown -cR root:root /var/www/html/presence/script
```
Als nächstes erlauben wir dem PHP-Script, das Scan-Script auszuführen. Dies erfolgt mit Hilfe des folgenden Kommandos und Hinzufügen des Eintrags für www-data:
```
sudo visudo
www-data ALL = NOPASSWD:/var/www/html/presence/script/scanspecifictag.sh
# Unter: User privilege specification
```
Passt noch die Adressen (IP/Domain zu openHAB2 REST API) und Pfade (Zur scanspecifictag.sh-Datei) in der
```/var/www/html/presence/scanspecifictag.php```
an.

Wenn wir alles richtig gemacht haben können wir das Script aus der Kommandozeile heraus testen:
```
/usr/bin/php /var/www/html/presence/scanspecifictag.php tagMac=7C:2F:80:CE:EF:44 item=PresenceGtag_1
```
Wenn der Test funktioniert hat können wir noch einen Cronjob einrichten um die Anwesenheit des Tags regelmäßig abzufragen. Da ich mehrer Tags abfrage und der Bluthooth-Service immer nur durch einen Cron genutzt werden kann habe ich eine Datei angelegt, welche ich durch den Cronjob aufrufen lasse, welche alle Abfragen nach der Reihe durchführt. Siehe ```cronjob.sh```

```
sudo crontab -e
*/1 * * * * sh /var/www/html/presence/cronjob.sh > /var/log/cron_gtag.log 2>&1
```
### Hinweis
Das Script kann alternativ auch über eine URL augerufen werden:
```
http://deinedomain.local/scanspecifictag.php?tagMac=7C:2F:80:CE:EF:44&item=PresenceGtag_1
```

Ursprüngliche habe ich Teile dieses Scripts für die Verwendung von Geofancy bzw. Raspberry Pi als Beacon geschrieben. Diese Funktion habe ich in dieser überarbeiteten Variante weiterhin implementiert. So ist es möglich mit folgenden Parametern im URL den State eines Switches zu ändern:
```
http://öffentlichedomain.de/scanspecifictag.php?item=Presence_1&itemValue=OFF
```
Mit dieser Funktion kann man diverse Apps (z.B. [Locative für iOS](https://itunes.apple.com/de/app/locative/id725198453?mt=8)) verwenden um den Status an Hand von z.B. Geolocation zu ändern. Dafür ist es notwendig über das Internet auf das Script zugriff zu haben. Eine der Punkte, welche Fehleranfällig ist - warum ich mich für die hier beschriebene Variante ausspreche.
## Tags
G-tag; openHAB2; Geofancy; Anwesenheit; Anwesenheitserkennung; Beacon; Raspberry Pi; Nginx; Bluetooth Low Energy;
## Quelle
* [hausautomatisierung-koch.de](https://hausautomatisierung-koch.de/2017/01/07/anwesenheitserkennung-bluetooth-beacon/)
* [loxwiki.eu](http://www.loxwiki.eu/display/LOX/Anwesenheitserkennung+via+Bluetooth+%28BLE%29+und+G-Tags)
* [php.net](http://php.net)
