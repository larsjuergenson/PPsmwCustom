# PpSmwCustom: Semantic Medwiawiki customizations the Perrypedia 

This Mediawiki extension provides customizations specific to the Mediawiki installation of the [Perrypedia](http://www.perrypedia.proc).

In its current form, it is probably not of much use for anyone else, as it is 
tailored to the particular current needs of the Perrypedia. I might extract some 
part (e.g., the AbcListPrinter) into an extension for general use in the future.

The rest of this is in German, the language of the Perrypedia.

## Abhängigkeiten

* [MediaWiki](https://www.mediawiki.org) >= 1.31
* [Semantic Mediawiki](https://www.semantic-mediawiki.org) >= 3.0

## Installation

Wie üblich:

* Das Archiv im `extensions/`-Verzeichnis der MediaWiki-Instalation entpacken.
* Die Extension aktivieren, indem 
```
wfLoadExtension( 'PpSmwCustom' );
```
in `LocalSettings.php` eingefügt wird, nach `enableSemantics( ... );`.

Bislang hat die Extension keine Konfigurationsoptionen, und berührt keine Datenbanktabellen. Entsprechend ist ein Aufruf von `update.php` nicht notwendig.

## Allgemeine Performance-Implikationenen

**Keine.** Momentan stellt die Extension nur zwei neue Ausgabeformate bereit. 
Die Klassen der Extension werden nur dann geladen, wenn eine Seite angezeigt 
wird, auf der eine SMW-Query erscheint, die das Ausgabeformat benutzt.

Heißt: Wenn überhaupt wird nur die Ladezeit von Seiten erhöht, die die neuen
Ausagabeformate benutzen. Auch dann ist es unwahrscheinlich, dass die Ladezeit
mehr erhöht wird als bei den von SMW bereitgestellten Ausgabeformaten, da die
neuen Formate keine SMW-Funktionen benutzen, die nicht von den "nativen" 
Formaten benutzt werden.

## Security-Implikationen

**Keine.** Die einzigen Benutzereingaben, die die Extension verarbeitet, sind 
Argumente zu den #ask- und #show-Funktionen. 

Außerdem produzieren die bereitgestellten Ausgabeformate nur Wikitext, der 
die üblichen Parser-Funktionen von MediaWiki durchläuft. Entsprechend greifen 
die üblichen Sicherheitsmechanismen. 