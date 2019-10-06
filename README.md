# PpSmwCustom: Semantic Medwiawiki customizations for the Perrypedia 

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

**Keine.** 

* Die Extension stellt zwei neue Ausgabeformate bereit. Die entsprechenden 
  Klassen werden nur dann geladen, wenn eine Seite angezeigt wird, auf der eine 
  SMW-Query erscheint, die das Ausgabeformat benutzt.

* Die Extension stellt zwei Parser-Funktionen bereit:
  * `{{#SortKey: ... }}` führt lediglich einfache Textmanipulationen für sein
    Argument durch.
  * `{{#isInCategory: ... | ... }}` muss als "expensive" gelten, beinträchtigt
    die Performance aber (höchstens) auf Seiten, auf denen diese Funktion 
    benutzt wird (momentan: die "todo"-Seiten der Portale).

Heißt: Wenn überhaupt wird nur die Ladezeit von Seiten erhöht, die die neuen
Ausagabeformate / `{{#isInCategory}}` benutzen. Auch dann ist es 
im ersten Fall unwahrscheinlich, dass die Ladezeit mehr erhöht wird als bei 
den von SMW bereitgestellten Ausgabeformaten, da die neuen Formate keine 
SMW-Funktionen benutzen, die nicht von den "nativen"  Formaten benutzt werden.

## Security-Implikationen

**Keine.** 

- *Für die Ausgabeformate:* Die einzigen Benutzereingaben, die die Extension 
  verarbeitet, sind Argumente zu den `#ask`- und `#show`-Funktionen von SMW. 

  Außerdem produzieren die bereitgestellten Ausgabeformate nur Wikitext, der 
  die üblichen Parser-Funktionen von MediaWiki durchläuft. Entsprechend greifen 
  die üblichen Sicherheitsmechanismen. 

- *Für die Parserfunktionen:* Keine der Funktionen schreibt etwas in die 
  Datenbank. `{{#isInCategory: ... | ... }}` fragt Informationen aus der 
  Datenbank ab, gibt aber nur `'TRUE'` oder `''` zurück, ist also nicht 
  geeignet, sensible Informationen abzufragen.