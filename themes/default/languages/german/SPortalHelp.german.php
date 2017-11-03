<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2017 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 RC1
 */

global $helptxt;

// Configuration area
$helptxt['sp_ConfigurationArea'] = 'Hier kannst du SimplePortal nach deinen Wünschen und Bedürfnissen gestalten.';

// General settings
$helptxt['portalactive'] = 'Diese Option aktiviert das Portal.';
$helptxt['sp_portal_mode'] = 'SimplePortal kann auf verschiedene Weisen genutzt werden. Diese Option ermöglicht es dir zu wählen, welchen Modus du verwenden möchtest. Unterstützt werden:<br /><br />
<strong>Deaktiviert:</strong> Dadurch wird das Portal komplett deaktiviert.<br /><br />
<strong>Startseite:</strong> Das ist die Standardeinstellung. Die Portalseite begrüßt die Betrachter anstelle der Forumsseite. Durch die "action forum" können Benutzer mit dem "Forum-Button" zur Forenansicht, dem Board Index, wechseln.<br /><br />
<strong>Integration:</strong> Dadurch wird die Portalseite deaktiviert. Blöcke sind nur im Forum nutzbar.<br /><br />
<strong>Unabhängig:</strong> Dies ermöglicht es, eine andere URL für das Portal zu verwenden, ganz unabhängig vom Forum. Die Portalseite erscheint mit der URL, die bei der Option "Unabhängige URL" eingegeben wurde. Für Einzelheiten bitte die Datei PortalStandalone.php überprüfen, die sich im Hauptordner des Forums befindet.';
$helptxt['sp_maintenance'] = 'Wird der Wartungsmodus aktiviert, ist das Portal nur für Benutzer sichtbar, die Berechtigungen haben SimplePortal zu moderieren.';
$helptxt['sp_standalone_url'] = 'Hier die vollständige URL zur Standalone Datei eingeben.<br /><br />Beispiel: http://meinforum.com/portal.php';
$helptxt['portaltheme'] = 'Wähle das Theme aus, das für das Portal genutzt werden soll.';
$helptxt['sp_disableForumRedirect'] = 'Mit dieser Option wird die "action forum" Weiterleitung deaktiviert. Ist hier kein Häkchen gesetzt, werden die Benutzer nach dem An- oder Abmelden zum Portal weitergeleitet. Ist hier das Häkchen gesetzt, dann werden die Benutzer zum Board Index weitergeleitet.';
$helptxt['sp_disableColor'] = 'Diese Option deaktiviert mehrfarbige Benutzernamen-Links im Portal. Sie erscheinen dann nicht mehr in der Farbe der jeweiligen Benutzergruppe, sondern in der Farbe der Standard-Links. Im Portal-Block "Wer ist online?" erscheinen die Benutzernamen weiterhin mehrfarbig.';
$helptxt['sp_disableMobile'] = 'Deaktiviert das Portal auf mobilen Geräten, die als Smartphone oder Phablet erkannt werden. Auf Tablets bleibt es aktiviert.';
$helptxt['sp_disable_random_bullets'] = 'Deaktiviert die bunten Punkte, die in Portal-Listen verwendet werden. Sie erscheinen dann nur noch in einer Farbe.';
$helptxt['sp_disable_php_validation'] = 'Deaktiviert die Überprüfung von PHP-Block-Codes, die Syntax- und Datenbankfehler im Code verhindern soll.';
$helptxt['sp_disable_side_collapse'] = 'Deaktiviert die Möglichkeit, die linke und rechte Seite des Portals zusammenzuklappen.';
$helptxt['sp_resize_images'] = 'Diese Option verkleinert Bilder in Artikeln und in den "News" auf 300px x 300px, um mögliche Überschneidungen zu verhindern. Auch die "Aktuellen Bilder" in Portalblöcken werden mit dieser Option verkleinert dargestellt. Diese Pixel-Werte können in der portal.css verändert werden';

// Block settings
$helptxt['showleft'] = 'Diese Option aktiviert die Blöcke auf der linken Seite im Portal und im Forum.';
$helptxt['showright'] = 'Diese Option aktiviert die Blöcke auf der rechten Seite im Portal und im Forum.';
$helptxt['leftwidth'] = 'Sind die linken Blöcke aktiviert, kann hier deren Breite festgelegt werden. Die Breite kann in Pixel (px) oder in Prozent (%) angegeben werden.';
$helptxt['rightwidth'] = 'Sind die rechten Blöcke aktiviert, kann hier deren Breite festgelegt werden. Die Breite kann in Pixel (px) oder in Prozent (%) angegeben werden.';
$helptxt['sp_enableIntegration'] = 'Diese Einstellung aktiviert Blöcke innerhalb des Forums. Sie erlaubt die gezielte Verwendung der <em>Erweiterten Anzeigeoptionen</em> für jeden einzelnen Block.';
$helptxt['sp_IntegrationHide'] = 'Damit lassen sich Blöcke in bestimmten Abschnitten des Forums verbergen. Die Option <em>Blöcke im Forum anzeigen</em> muss aktiviert sein, damit dies funktioniert.';

// Article settings
$helptxt['sp_articles_index'] = 'Diese Option aktiviert die Darstellung der Artikel im Portal. Wird das Häkchen entfernt, sind die Artikel nicht zu sehen.';
$helptxt['sp_articles_index_per_page'] = 'Hier wird festgelegt, wie viele Artikel pro Seite im Portal erscheinen sollen.';
$helptxt['sp_articles_index_total'] = 'Hier wird festgelegt, wie viele Artikel insgesamt im Portal erscheinen sollen.';
$helptxt['sp_articles_length'] = 'Diese Einstellung erlaubt es dir festzulegen, wieviele Zeichen eines Artikels im Portal angezeigt werden sollen. Hat ein Artikel dieses Limit erreicht, wird er verkürzt. Am Ende erscheint ein Link mit Auslassungszeichen (...) Er ermöglicht es den Benutzern, den gesamten Artikel zu lesen.';
$helptxt['sp_articles_per_page'] = 'Hier wird festgelegt, wie viele Artikel pro Seite auf der Artikel-Liste erscheinen sollen.';
$helptxt['sp_articles_comments_per_page'] = 'Hier wird festgelegt, wie viele Artikel-Kommentare pro Seite erscheinen sollen.';

// Blocks area
$helptxt['sp_BlocksArea'] = 'Blöcke sind Boxen, die im Portal oder im Forum erscheinen können. Hier kannst du vorhandene Blöcke verändern oder neue Blöcke erstellen.';

// Block list
$helptxt['sp-blocksLeftList'] = 'Diese Blöcke erscheinen auf der linken Seite von Portal und Forum.';
$helptxt['sp-blocksTopList'] = 'Diese Blöcke erscheinen oben im mittleren Bereich von Portal und Forum.';
$helptxt['sp-blocksBottomList'] = 'Diese Blöcke erscheinen unten im mittleren Bereich von Portal und Forum.';
$helptxt['sp-blocksRightList'] = 'Diese Blöcke erscheinen auf der rechten Seite von Portal und Forum.';
$helptxt['sp-blocksHeaderList'] = 'Diese Blöcke erscheinen ganz oben, im Kopfbereich von Portal und Forum.';
$helptxt['sp-blocksFooterList'] = 'Diese Blöcke erscheinen ganz unten, im Fußbereich von Portal und Forum.';

// Add/Edit blocks
$helptxt['sp-blocksAdd'] = 'Hier kannst du den gewählten Block-Typ erstellen und bearbeiten.';
$helptxt['sp-blocksSelectType'] = 'Dieser Bereich ermöglicht es dir, Blöcke für Portal oder Forum zu erstellen. Für weitere voreingestellte Blöcke oder Blöcke mit eigenen Inhalten einfach den gewünschten Block-Typ auswählen.';
$helptxt['sp-blocksEdit'] = 'Hier kannst du den ausgewählten Block bearbeiten.';
$helptxt['sp-blocksDisplayOptions'] = 'Hier kannst du auswählen wo der Block erscheinen soll.';
$helptxt['sp-blocksCustomDisplayOptions'] = 'Die eigenen Anzeigeoptionen (Custom Display Options) bieten dir erweitere Kontroll- und Einstellungsmöglichkeiten, wo ein Block mit einer speziellen Syntax erscheinen soll.<br /><br />
<strong>Spezielle Aktionen beinhalten:</strong><br /><br />
<strong>all:</strong> Auf allen Seiten im Forum<br />
<strong>portal:</strong> Auf der Portalseite mit sub-actions<br />
<strong>forum:</strong> Auf dem Board Index<br />
<strong>sforum:</strong> Bei allen "Actions" und in allen Boards, außer im Portal<br />
<strong>allaction:</strong> Bei allen "Actions"<br />
<strong>allboard:</strong> In allen Boards<br /><br />
<strong>Wavy (~)</strong><br />
Dieses Symbol dient als Platzhalter und ermöglicht dynamische Aktionen, wie ../index.php?issue=* oder ../index.php?game=*. Wird bei ~action verwendet.<br /><br />
<strong>Idkin (|)</strong><br />
Ein weiteres Platzhalter-Symbol, das es ermöglicht einen genauen Wert für eine dynamische Aktion anzugeben, wie ../index.php?issue=1.0 oder ../index.php?game=xyz. Sollte mit "wavy" und nach der "action" verwendet werden, wie ~action|value<br /><br />
<strong>Negator (-)</strong><br />
Dieses Symbol ermöglich es, reguläre und dynamische Aktionen auszuschließen. Es sollte vor der Bezeichnung der "action" bei regulären Aktionen und vor dem "wavy" bei dynamischen Aktionen gesetzt werden. Wird bei -action und bei -~action verwendet.';
$helptxt['sp-blocksStyleOptions'] = 'Hier kannst du das CSS-Styling für diesen Block festlegen.';

// Articles area
$helptxt['sp_ArticlesArea'] = 'Artikel sind spezielle Themen, von denen jeweils nur der erste Beitrag im Portal erscheint. Hier kannst du bestehende Artikel überarbeiten und neue Artikel erstellen, die im Portal erscheinen sollen.';

// Add/Edit articles
$helptxt['sp-articlesAdd'] = 'In diesem Bereich kannst du Artikel zu Kategorien von deinen Boards hinzufügen.';
$helptxt['sp-articlesEdit'] = 'In diesem Bereich kannst du die Kategorie oder den Status der Artikel ändern.';
$helptxt['sp-articlesCategory'] = 'Wähle eine Kategorie für diesen Artikel';
$helptxt['sp-articlesApproved'] = 'Zugelassene Artikel werden im Portal im Bereich für Artikel erscheinen.';
$helptxt['sp-articlesTopics'] = 'Wähle die Themen aus, die als Artikel im Portal erscheinen sollen.';
$helptxt['sp-articlesBoards'] = 'Wähle ein Board für die Suche nach Themen.';

// Categories area
$helptxt['sp_CategoriesArea'] = 'Kategorien beinhalten Artikel. Hier kannst du bestehende Kategorien ändern und neue erstellen. Damit Artikel erstellt werden können, muss mindestens eine Kategorie vorhanden sein.';

// Add/Edit categories
$helptxt['sp-categoriesAdd'] = 'Hier können Kategorien für Artikel erstellt werden. Damit Artikel erstellt werden können, muss mindestens eine Kategorie vorhanden sein.';
$helptxt['sp-categoriesEdit'] = 'Hier können Kategorien geändert werden.';
$helptxt['sp-categoriesCategories'] = 'Diese Seite zeigt eine Liste aller derzeit vorhandenen Kategorien für Artikel. Damit Artikel erstellt werden können, muss mindestens eine Kategorie vorhanden sein.';
$helptxt['sp-categoriesDelete'] = 'Das Löschen einer Kategorie wird auch die Artikel darin löschen. Du kannst sie vorher in eine andere Kategorie verschieben.';

// Pages area
$helptxt['sp_PagesArea'] = 'Seiten sind BBC-, PHP- oder HTML-Code-Blöcke, die auf ihren eigenen Seiten im Forum angezeigt werden. In diesem Abschnitt kannst du Seiten erstellen, bearbeiten und konfigurieren.';

// Shoutbox area
$helptxt['sp_ShoutboxArea'] = 'In diesem Bereich kannst du Shoutboxes erstellen und konfigurieren. Danach muss noch ein Shoutbox-Block erstellt werden, damit die Shoutbox auch dargestellt wird und genutzt werden kann.';

// Add/Edit shoutboxes
$helptxt['sp-shoutboxesWarning'] = 'Ein Hinweis oder eine Warnmeldung, die hier eingegeben wird, erscheint oben in der Shoutbox. Alle Shoutbox-Benutzer werden diese Nachricht sehen.';
$helptxt['sp-shoutboxesBBC'] = 'Hier kannst du festlegen, welche BBC-Tags in der Shoutbox verwendet werden dürfen.<br /><br />Halte die STRG-Taste gedrückt um einen BBC-Tag zu aktivieren oder zu deaktivieren. <br /><br />Wenn du eine Reihe von aufeinander folgenden Tags auswählen möchtest, klicke auf den ersten BBC-Tag, halte die Umschalttaste gedrückt und klicke dann auf den letzten BBC-Tag in der Reihe, den du auswählen möchtest.';

$helptxt['sp_ProfilesArea'] = 'Hier können Berechtigungsprofile für die Blöcke erstellt und verändert werden. Beim Erstellen eines Blocks wird das jeweils gewünschte Berechtigungsprofil ausgewählt. Damit wird festgelegt, welche Benutzergruppen den Block sehen und nutzen können.<br /><br />
Mit den Optionen "erlaubt", "nicht erlaubt" oder "verboten" werden in einem Portal-Berechtigungsprofil für jede im Forum vorhandene Benutzergruppe die Zugriffsrechte definiert. Auch für die ersten drei hier bereits vorhandenen Berechtigungsprofile "Alle", "Gäste" und "Mitglieder" müssen diese Zugriffsrechte noch vergeben werden.<br /><br />
Weitere, eigene Berechtigungsprofile werden nach dem gleichen Prinzip erstellt und definiert.<br /><br />
<ul>
    <li><strong>Vorhandene Berechtigungsprofile:</strong></li>
    <li><strong>Alle:</strong> Alle Benutzer, egal ob sie eingeloggt sind oder nicht, können diesen Block sehen.</li>
	<li><strong>Gäste:</strong> Jeder Benutzer, der nicht registriert oder nicht eingeloggt ist, kann diesen Block sehen. Eingeloggte Benutzer (inklusive Administratoren) können diesen Block nicht sehen.</li>
	<li><strong>Mitglieder:</strong> Jeder Benutzer, der eingeloggt ist, (inklusive Administratoren) kann diesen Block sehen.</li><br />
	<li>Damit die Berechtigungsprofile "Alle", "Gäste" und "Mitglieder" so greifen, wie oben beschrieben ist, müssen die Optionen innerhalb der Berechtigungsprofile richtig gesetzt werden.<br /><br />
	<li><strong>Diese drei Optionen stehen zur Verfügung:</strong></li> 
		<ul>
			<li><strong>E:</strong> "Erlaubt" - Alle User in der jeweiligen Benutzergruppe können den Block sehen, für den dieses Profil gewählt wird.</li>
			<li><strong>X:</strong> "Nicht erlaubt" - Alle User in der jeweiligen Benutzergruppe können den Block nicht sehen, für den dieses Profil gewählt wird. Gehört der Benutzer auch einer anderen Benutzergruppe an, der der Zugriff erlaubt ist, kann er den Block sehen, für den dieses Berechtigungsprofil gewählt wird.</li>
			<li><strong>V:</strong> "Verboten" - Alle User in der jeweiligen Benutzergruppe können den Block nicht sehen, für den dieses Profil gewählt wird. Die Option "verboten" überschreibt die Option "erlaubt" für jede andere Gruppe, der der Benutzer angehört. Deshalb mit dieser Option <strong>vorsichtig</strong> sein.</li>
		</ul>
	</li>
</ul>';

// Block parameters
$helptxt['sp_param_sp_latestMember_limit'] = 'Hier die Anzahl der zuletzt registrierten Mitglieder eintragen, die im Block erscheinen sollen.';
$helptxt['sp_param_sp_boardStats_averages'] = 'Zeigt zusätzlich die Board Statistiken im Durchschnitt an.';
$helptxt['sp_param_sp_topPoster_limit'] = 'Hier wird die Anzahl der Top Autoren eingetragen, die im Block erscheinen sollen.';
$helptxt['sp_param_sp_topPoster_type'] = 'Hier wird festgelegt, für welchen Zeitraum die Top-Autoren angezeigt werden sollen.';
$helptxt['sp_param_sp_recent_limit'] = 'Hier wird festgelegt, wie viele Beiträge oder Themen im Block gezeigt werden sollen.';
$helptxt['sp_param_sp_recent_type'] = 'Hier wird festgelegt, ob Beiträge oder Themen im Block gezeigt werden sollen.';
$helptxt['sp_param_sp_recentPosts_limit'] = 'Hier wird festgelegt, wie viele Beiträge im Block gezeigt werden sollen.';
$helptxt['sp_param_sp_recentTopics_limit'] = 'Hier wird festgelegt, wie viele Themen im Block gezeigt werden sollen.';
$helptxt['sp_param_sp_topTopics_type'] = 'Hier wird festgelegt, ob die Top Themen nach der Anzahl der Beiträge oder der Anzahl der Aufrufe sortiert werden sollen.';
$helptxt['sp_param_sp_topTopics_limit'] = 'Hier wird festgelegt, wie viele Top Themen im Block gezeigt werden sollen.';
$helptxt['sp_param_sp_topBoards_limit'] = 'Hier wird festgelegt, wie viele Top Boards im Block gezeigt werden sollen.';
$helptxt['sp_param_sp_showPoll_topic'] = 'Hier kannst du die ID des Themas eingeben, dessen Umfrage im Portalblock erscheinen soll.';
$helptxt['sp_param_sp_showPoll_type'] = 'Hier kannst du auswählen, auf welche Weise Umfragen im Portal erscheinen sollen. "Normal" ermöglicht es, dass eine ganz bestimmte Umfrage im Portal erscheint. Dafür wird im Feld "Themen ID" zusätzlich die ID des jeweiligen Umfragethemas eingegeben. "Aktuell" zeigt die zuletzt im Forum erstellte Umfrage und "zufällig" irgendeine der bestehenden Umfragen.';
$helptxt['sp_param_sp_boardNews_board'] = 'Hier kannst du die Boards auswählen, von denen Themen in den News gezeigt werden sollen.';
$helptxt['sp_param_sp_boardNews_limit'] = 'Hier kannst du die Anzahl der Themen eintragen, aus denen News gezeigt werden sollen.';
$helptxt['sp_param_sp_boardNews_start'] = 'Hier kannst du die ID eines bestimmten Beitrags eingeben, mit dem die News beginnen sollen. Bleibt das Feld leer, wird der erste Beitrag im Thema der Start-Beitrag.';
$helptxt['sp_param_sp_boardNews_length'] = 'Hier kannst du eintragen, wieviele Zeichen der Beiträge im Portal bei den News angezeigt werden sollen. Hat ein Beitrag dieses Limit erreicht, wird er verkürzt dargestellt. Am Ende erscheint der Link "mehr lesen" Er ermöglicht es den Benutzern, das gesamte Thema zu lesen.';
$helptxt['sp_param_sp_boardNews_avatar'] = 'Ist hier das Häkchen gesetzt, werden verkleinerte Avatare der Autoren in den News angezeigt.';
$helptxt['sp_param_sp_boardNews_per_page'] = 'Hier kannst du eintragen, wieviele Beiträge pro Seite gezeigt werden sollen. Bleibt das Feld leer, gibt es keine Seitennummerierung.';
$helptxt['sp_param_sp_attachmentImage_limit'] = 'Hier kannst du eintragen, wieviele zuletzt angehängte Bilder gezeigt werden sollen.';
$helptxt['sp_param_sp_attachmentImage_direction'] = 'Die Bilder können horizontal (waagrecht) oder vertikal(senkrecht) angeordnet sein.';
$helptxt['sp_param_sp_attachmentRecent_limit'] = 'Hier kannst du eintragen, wieviele der aktuellsten Dateianhänge gezeigt werden sollen.';
$helptxt['sp_param_sp_calendar_events'] = 'Zeigt Ereignisse im Kalender an.';
$helptxt['sp_param_sp_calendar_birthdays'] = 'Zeigt Geburtstage im Kalender an.';
$helptxt['sp_param_sp_calendar_holidays'] = 'Zeigt Feiertage im Kalender an.';
$helptxt['sp_param_sp_calendarInformation_events'] = 'Zeigt Ereignisse aus dem Kalender.';
$helptxt['sp_param_sp_calendarInformation_future'] = 'Hier kannst du festlegen, wie viele Tage im voraus künftige Ereignisse angezeigt werden sollen. Sollen nur heutige Ereignisse angezeigt werden, dann gebe "0" ein.';
$helptxt['sp_param_sp_calendarInformation_birthdays'] = 'Zeigt Geburtstage aus dem Kalender';
$helptxt['sp_param_sp_calendarInformation_holidays'] = 'Zeigt Feiertage aus dem Kalender.';
$helptxt['sp_param_sp_rssFeed_url'] = 'Füge die vollständige URL des RSS-Feed ein.';
$helptxt['sp_param_sp_rssFeed_show_title'] = 'Feed-Titel werden angezeigt.';
$helptxt['sp_param_sp_rssFeed_show_content'] = 'Feed-Inhalte werden angezeigt.';
$helptxt['sp_param_sp_rssFeed_show_date'] = 'Feed-Datum wird angezeigt.';
$helptxt['sp_param_sp_rssFeed_strip_preserve'] = 'HTML-Tags für Feed-Inhalte, durch Komma getrennt.';
$helptxt['sp_param_sp_rssFeed_count'] = 'Hier kannst du festlegen wieviele Inhalte angezeigt werden sollen.';
$helptxt['sp_param_sp_rssFeed_limit'] = 'Hier kannst du festlegen wieviele Zeichen im RSS-Feed angezeigt werden sollen.';
$helptxt['sp_param_sp_staff_lmod'] = 'Na, was mag das wohl bedeuten? Richtig. Die Board-Moderatoren erscheinen nicht mit auf der Team-Liste. Arme Kerlchen.';
$helptxt['sp_param_sp_articles_category'] = 'Hier kannst du die Kategorien auswählen, aus denen Artikel im Portal erscheinen sollen.';
$helptxt['sp_param_sp_articles_limit'] = 'Hier wird festgelegt, wie viele Artikel im Portal erscheinen sollen.';
$helptxt['sp_param_sp_articles_type'] = 'Zeigt zufällige oder die aktuellsten Artikel.';
$helptxt['sp_param_sp_articles_length'] = 'Hier kannst du eintragen, wieviele Zeichen eines Artikels im Portal angezeigt werden sollen. Hat ein Artikel dieses Limit erreicht, wird er verkürzt. Am Ende erscheint ein Link mit Auslassungszeichen (...) Er ermöglicht es den Benutzern, den gesamten Artikel zu lesen.';
$helptxt['sp_param_sp_articles_avatar'] = 'Ist hier das Häkchen gesetzt, erscheinen die Avatare der Autoren in den Artikeln.';
$helptxt['sp_param_sp_gallery_limit'] = 'Hier kannst du festlegen wieviele Galerie-Inhalte angezeigt werden sollen.';
$helptxt['sp_param_sp_gallery_type'] = 'Zeigt zufällige oder die aktuellsten Galerie-Inhalte.';
$helptxt['sp_param_sp_gallery_direction'] = 'Galerie-Bilder können horizontal (waagrecht) oder vertikal(senkrecht) angeordnet sein.';
$helptxt['sp_param_sp_html_content'] = 'Füge HTML-Inhalte für einen selbst erstellten Block in dieses Feld ein.';
$helptxt['sp_param_sp_bbc_content'] = 'Für einen selbst erstellten BBC-Block kannst du hier im Editor schreiben.';
$helptxt['sp_param_sp_php_content'] = 'Füge PHP-Inhalte für einen selbst erstellten Block in dieses Feld ein.';