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
```

Mit den Kommandos hciconfig und hcitool kann man dann versuchen den Dongle zu aktivieren und die Tags zu scannen:
```
hciconfig hci0 up
hcitool lescan
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
# ;cgi.fix_pathinfo=1 zu cgi.fix_pathinfo=0 ändern
```
Kurz PHP neustarten
```
sudo service php5-fpm restart
```
Nginx-Konfiguration (/etc/nginx/sites-available/default)
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
Kurz Nginx überprüfen und neustarten
```
sudo nginx -t
sudo service nginx restart
```

Die Dateien aus diesem GIT-Repository müssen, wie man der Konfiguration entnehmen kann unter ```/var/www/html/presence/``` abgelegt werden.

Nun müssen wir noch die richtigen Rechte für die Datei, welche später den Scan übernimmt vergeben:
```
chmod 500 /var/www/html/presence/script/scanspecifictag.sh 
chown root:root /var/www/html/presence/script/scanspecifictag.sh
```
Als nächstes erlauben wir dem PHP-Script, das Scan-Script auszuführen. Dies erfolgt mit Hilfe des folgenden Kommandos und Hinzufügen des Eintrags für www-data:
```
sudo visudo
www-data ALL = NOPASSWD:/var/www/html/presence/script/scanspecifictag.sh 
```
Passt noch die Adressen (IP/Domain zu openHAB2 REST API) und Pfade (Zur scanspecifictag.sh-Datei) in der
```/var/www/html/presence/scanspecifictag.php```
an.

Wenn wir alles richtig gemacht haben können wir das Script aus der Kommandozeile heraus testen:
``````

```
*/1 * * * * sh /var/www/html/presence/cronjob.sh > /var/log/cron_gtag.log 2>&1
```
## Quelle
*[hausautomatisierung-koch.de](https://hausautomatisierung-koch.de/2017/01/07/anwesenheitserkennung-bluetooth-beacon/)
