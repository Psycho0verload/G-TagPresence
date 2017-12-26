# openHAB 2: G-TagPresence
Zuverlässige Anwesenheitserkennung für openHAB2 mit Gigaset G-tags mit Hilfe von Nginx/PHP.
## Problemstellung
Es gibt viele verschiedene Möglichkeiten wie man in seinem heimischen Smarthomesystem mit openHAB2 eine Anwesenheitserkennung umsetzt. Wenn man sich in Google auf die Suche macht bekommt man viele Varianten/Techniken vorgeschlagen z.B. über Raspberry Pi als Beacon, Geofancy, FritzBox, Ping, usw. Ich habe viele davon getestet und jede Variante/Technik hat ihre ganz besonderen Vor- & Nachteile - häufig geht es zu Lasten vom jeweiligen Smartphone-Akkus und ist abhängig von GPS oder Internetverbindung. Für mich insgesammt noch keine zuverlässige und optimale Lösung.
### Anforderung
* von Smartphone unabhängig
* ohne aktive Internetverbindung
* Flexibilität innerhalb des Netzwerks
* Preiswerte/Effiziente Variante  

## Lösungsansatz
Das Raspberry Pi 3 ist ein ausgezeichneter Bluetooth Low Energy Sender/Empfänger. In den meisten Fällen läuft auf selbigen Pi auch das openHAB2 - so können wir hier auf vorhandene Hardware setzten.  

## Systemvoraussetzung
* [openHAB2](http://www.openhab.org/) mit aktiver REST API
* [Raspberry Pi 3](https://geizhals.de/raspberry-pi-3-modell-b-a1526643.html)
* [Gigaset G-tag Schlüsselfinder](https://geizhals.de/?cat=gsmzub&asuch=gigaset+g-tag&bpmin=&bpmax=&v=e&hloc=at&hloc=de&plz=&dist=&filter=aktualisieren&mail=&sort=t)

## Quelle
*[hausautomatisierung-koch.de](https://hausautomatisierung-koch.de/2017/01/07/anwesenheitserkennung-bluetooth-beacon/)
