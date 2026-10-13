<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2021 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0
 */

global $helptxt;

// Configuration area
$helptxt['sp_ConfigurationArea'] = 'Qui puoi configurare SimplePortal in base alle tue esigenze.';

// General settings
$helptxt['portalactive'] = 'Attiva la pagina del portale.';
$helptxt['sp_portal_mode'] = 'SimplePortal può funzionare in diverse modalità. Con questa opzione scegli quella che vuoi usare. Le modalità disponibili sono:<br /><br />
<strong>Disattivato:</strong> disattiva completamente il portale.<br /><br />
<strong>Pagina iniziale:</strong> è l\'impostazione predefinita. I visitatori vengono accolti dalla pagina del portale invece che dall\'indice del forum. Gli utenti possono raggiungere l\'indice del forum con l\'azione "forum", accessibile dal pulsante "forum".<br /><br />
<strong>Integrazione:</strong> disattiva la pagina del portale. I blocchi possono essere usati solo all\'interno del forum.<br /><br />
<strong>Indipendente:</strong> il portale viene mostrato a un indirizzo diverso, separato dal forum. La pagina del portale si trova all\'indirizzo indicato nell\'opzione "URL del portale indipendente". Per i dettagli, consulta il file PortalStandalone.php nella cartella principale del forum.';
$helptxt['sp_maintenance'] = 'Con la manutenzione attiva, il portale è visibile solo agli utenti con il permesso di moderare SimplePortal.';
$helptxt['sp_standalone_url'] = 'Indirizzo completo del file del portale indipendente.<br /><br />Esempio: http://mioforum.it/portal.php';
$helptxt['portaltheme'] = 'Scegli il tema da usare per il portale.';
$helptxt['sp_disableForumRedirect'] = 'Se questa casella non è selezionata, dopo l\'accesso o l\'uscita gli utenti vengono reindirizzati al portale. Se è selezionata, vengono reindirizzati all\'indice del forum.';
$helptxt['sp_disableColor'] = 'Disattiva i colori dei gruppi nel portale (tranne che nell\'elenco di chi è online).';
$helptxt['sp_disableMobile'] = 'Impostazione generale per disattivare il portale su tutti i dispositivi riconosciuti come mobili (telefoni, non tablet). Per disattivare solo alcuni blocchi, lascia questa opzione attiva e usa i profili di visibilità.';
$helptxt['sp_disable_random_bullets'] = 'Disattiva i colori casuali dei punti elenco usati nelle liste del portale.';
$helptxt['sp_disable_php_validation'] = 'Disattiva la validazione del codice dei blocchi PHP, che serve a evitare errori di sintassi e del database nel codice.';
$helptxt['sp_disable_side_collapse'] = 'Disattiva la possibilità di chiudere le colonne di sinistra e di destra del portale.';
$helptxt['sp_resize_images'] = 'Ridimensiona a 300x300 px le immagini negli articoli e nelle notizie dalle sezioni, per evitare che escano dallo spazio disponibile.';
$helptxt['sp_disableUserArrange'] = 'Impedisce agli utenti di riordinare i blocchi del portale e mostra sempre la disposizione predefinita del sito';

// Block settings
$helptxt['showleft'] = 'Attiva i blocchi a sinistra nel portale e all\'interno del forum.';
$helptxt['showright'] = 'Attiva i blocchi a destra nel portale e all\'interno del forum.';
$helptxt['leftwidth'] = 'Se i blocchi a sinistra sono attivi, qui puoi indicarne la larghezza, in pixel (px) o in percentuale (%).';
$helptxt['rightwidth'] = 'Se i blocchi a destra sono attivi, qui puoi indicarne la larghezza, in pixel (px) o in percentuale (%).';
$helptxt['sp_IntegrationHide'] = 'Usa questa impostazione per nascondere i blocchi in alcune aree del forum.';

// Article settings
$helptxt['sp_articles_index'] = 'Mostra gli articoli nel portale, indipendentemente da un blocco articoli';
$helptxt['sp_articles_index_per_page'] = 'Numero massimo di articoli mostrati per pagina.';
$helptxt['sp_articles_index_total'] = 'Numero totale di articoli disponibili nella pagina del portale';
$helptxt['sp_articles_length'] = 'Limita il numero di caratteri di un articolo mostrati nella pagina del portale. Se l\'articolo supera il limite viene accorciato e termina con un link a forma di puntini di sospensione (...), che permette di leggerlo per intero.';
$helptxt['sp_articles_per_page'] = 'Numero massimo di articoli mostrati per pagina nell\'elenco degli articoli';
$helptxt['sp_articles_comments_per_page'] = 'Numero massimo di commenti agli articoli mostrati per pagina';
$helptxt['sp_articles_attachment_dir'] = 'La cartella in cui salvare gli allegati caricati con gli articoli. La cartella deve esistere ed essere scrivibile. Non usare la cartella standard degli allegati e non modificare questa impostazione se non sai cosa stai facendo.';

// Blocks area
$helptxt['sp_BlocksArea'] = 'I blocchi sono riquadri che possono essere mostrati nel portale o all\'interno del forum. Da questa sezione puoi modificare i blocchi esistenti e crearne di nuovi.';

// Block list
$helptxt['sp-blocksLeftList'] = 'Questi blocchi sono mostrati sul lato sinistro del portale e del forum.';
$helptxt['sp-blocksTopList'] = 'Questi blocchi sono mostrati al centro, nella parte superiore del portale e del forum.';
$helptxt['sp-blocksBottomList'] = 'Questi blocchi sono mostrati al centro, nella parte inferiore del portale e del forum.';
$helptxt['sp-blocksRightList'] = 'Questi blocchi sono mostrati sul lato destro del portale e del forum.';
$helptxt['sp-blocksHeaderList'] = 'Questi blocchi sono mostrati in cima al portale e al forum.';
$helptxt['sp-blocksFooterList'] = 'Questi blocchi sono mostrati in fondo al portale e al forum.';

// Add/Edit blocks
$helptxt['sp-blocksAdd'] = 'Da quest\'area puoi personalizzare e configurare il blocco selezionato.';
$helptxt['sp-blocksSelectType'] = 'Da quest\'area puoi creare i blocchi per la pagina del portale. Scegliendo le opzioni appropriate puoi creare facilmente blocchi predefiniti o blocchi con contenuti personalizzati.';
$helptxt['sp-blocksEdit'] = 'Da quest\'area puoi personalizzare e configurare il blocco selezionato.';
$helptxt['sp-blocksCustomDisplayOptions'] = 'Le query personalizzate permettono un controllo più avanzato della visibilità grazie a una sintassi speciale.<br /><br />
<strong>Azioni speciali:</strong><br /><br />
<strong>all:</strong> ovunque<br />
<strong>allaction:</strong> tutte le azioni<br />
<strong>allboard:</strong> tutte le sezioni<br />
<strong>allpage:</strong> tutte le pagine<br />
<strong>allcategory:</strong> tutte le categorie<br />
<strong>allarticle:</strong> tutti gli articoli<br /><br />
<strong>Tilde (~)</strong><br />
Questo simbolo funziona da carattere jolly e permette di includere azioni dinamiche come ../index.php?issue=* o ../index.php?game=*. Si usa così: ~azione<br /><br />
<strong>Barra verticale (|)</strong><br />
Un altro carattere jolly, che permette di indicare un valore preciso per un\'azione dinamica come ../index.php?issue=1.0 o ../index.php?game=xyz. Va usato insieme alla tilde, dopo il nome dell\'azione: ~azione|valore<br /><br />
<strong>Negazione (-)</strong><br />
Questo simbolo esclude azioni normali e dinamiche. Va messo prima del nome dell\'azione per le azioni normali e prima della tilde per quelle dinamiche: -azione e -~azione';

// Articles area
$helptxt['sp_ArticlesArea'] = 'Gli articoli sono contenuti mostrati nel portale. Da questa sezione puoi modificare gli articoli esistenti e crearne di nuovi.';

// Add/Edit articles
$helptxt['sp-articlesAdd'] = 'Da quest\'area puoi aggiungere articoli alle categorie.';
$helptxt['sp-articlesEdit'] = 'Da quest\'area puoi cambiare la categoria o lo stato degli articoli.';
$helptxt['sp-articlesCategory'] = 'Scegli una categoria per questo articolo.';
$helptxt['sp-articlesApproved'] = 'Gli articoli approvati compaiono nell\'area articoli del portale.';
$helptxt['sp-articlesTopics'] = 'Scegli le discussioni da mostrare come articoli nel portale.';
$helptxt['sp-articlesBoards'] = 'Scegli una sezione in cui cercare le discussioni.';

// Categories area
$helptxt['sp_CategoriesArea'] = 'Le categorie contengono gli articoli. Da questa sezione puoi modificare le categorie esistenti e crearne di nuove. Per creare un articolo deve esistere almeno una categoria.';

// Add/Edit categories
$helptxt['sp-categoriesAdd'] = 'Da questa sezione puoi creare le categorie per gli articoli. Per creare articoli deve esistere almeno una categoria.';
$helptxt['sp-categoriesEdit'] = 'Da questa sezione puoi modificare le categorie.';
$helptxt['sp-categoriesCategories'] = 'Questa pagina elenca le categorie di articoli esistenti. Per creare articoli deve esistere almeno una categoria.';
$helptxt['sp-categoriesDelete'] = 'Eliminando una categoria, gli articoli che contiene vengono eliminati oppure spostati in un\'altra categoria.';

// Pages area
$helptxt['sp_PagesArea'] = 'Le pagine sono contenuti in BBC, PHP o HTML mostrati in una pagina a sé all\'interno del forum. Da questa sezione puoi creare, modificare e configurare le pagine.';

// Shoutbox area
$helptxt['sp_ShoutboxArea'] = 'Le shoutbox vanno create in questa sezione, dove puoi anche configurarle. Per mostrare la shoutbox creata serve poi un blocco shoutbox.';

// Add/Edit shoutboxes
$helptxt['sp-shoutboxesWarning'] = 'Il messaggio di avviso che imposti qui viene mostrato nella shoutbox, a chiunque la usi.';
$helptxt['sp-shoutboxesBBC'] = 'Con questa impostazione scegli i BBC che si possono usare in questa shoutbox.<br /><br />Tieni premuto il tasto CTRL per selezionare o deselezionare un singolo BBC.<br /><br />Per selezionare una serie di BBC consecutivi, fai clic sul primo, tieni premuto il tasto MAIUSC e fai clic sull\'ultimo.';

// Menus area
$helptxt['sp_MenusArea'] = 'In quest\'area puoi modificare il menu principale del forum, e creare e modificare altri menu personalizzati.';

// Profiles area
$helptxt['sp_ProfilesArea'] = 'Questa opzione permette di applicare i permessi ai blocchi. Le prime tre opzioni sono le più semplici da usare e da capire.
<ul>
	<li><strong>Ospiti:</strong> chiunque non sia registrato o non abbia effettuato l\'accesso <em>vedrà</em> questo blocco. Gli utenti che hanno effettuato l\'accesso (amministratori compresi) <em>non lo vedranno</em>.</li>
	<li><strong>Utenti registrati:</strong> chiunque abbia effettuato l\'accesso (amministratori compresi) <em>vedrà</em> questo blocco.</li>
	<li><strong>Tutti:</strong> tutti gli utenti, che abbiano effettuato l\'accesso o no, <em>vedranno</em> questo blocco.</li>
	<li><strong>Personalizzato:</strong> mostra l\'area dei permessi personalizzati, dove per ogni gruppo si può scegliere tra tre opzioni.
		<ul>
			<li><strong>C:</strong> Consentito, gli utenti di questo gruppo <em>vedranno</em> questo blocco.</li>
			<li><strong>X:</strong> Non consentito, gli utenti di questo gruppo <em>non vedranno</em> questo blocco, a meno che non facciano parte anche di un gruppo con il permesso Consentito.</li>
			<li><strong>N:</strong> Negato, gli utenti di questo gruppo <em>non vedranno mai</em> questo blocco. Questa impostazione prevale su Consentito per qualunque gruppo di cui l\'utente faccia parte, quindi <strong>usala con attenzione</strong>.</li>
		</ul>
	</li>
</ul>';

// Block parameters
$helptxt['sp_param_sp_latestMember_limit'] = 'Quanti utenti mostrare.';
$helptxt['sp_param_sp_boardStats_averages'] = 'Mostra le statistiche medie.';
$helptxt['sp_param_sp_topPoster_limit'] = 'Quanti utenti più attivi mostrare.';
$helptxt['sp_param_sp_topPoster_type'] = 'Periodo di tempo su cui calcolare gli utenti più attivi.';
$helptxt['sp_param_sp_recent_limit'] = 'Quanti messaggi o discussioni recenti mostrare.';
$helptxt['sp_param_sp_recent_type'] = 'Mostra i messaggi o le discussioni recenti.';
$helptxt['sp_param_sp_recentPosts_limit'] = 'Quanti messaggi recenti mostrare.';
$helptxt['sp_param_sp_recentTopics_limit'] = 'Quante discussioni recenti mostrare.';
$helptxt['sp_param_sp_topTopics_type'] = 'Ordina le discussioni per risposte o per visite.';
$helptxt['sp_param_sp_topTopics_limit'] = 'Quante discussioni mostrare.';
$helptxt['sp_param_sp_topBoards_limit'] = 'Quante sezioni mostrare.';
$helptxt['sp_param_sp_showPoll_topic'] = 'L\'ID della discussione che contiene il sondaggio da mostrare.';
$helptxt['sp_param_sp_showPoll_type'] = 'Scegli come mostrare i sondaggi. Normale mostra un sondaggio preciso indicato dall\'ID della discussione, Recente mostra il sondaggio pubblicato più di recente, A caso mostra un sondaggio scelto a caso.';
$helptxt['sp_param_sp_boardNews_board'] = 'L\'ID della sezione da cui prendere le discussioni. Lascia vuoto per prenderle da tutte le sezioni visibili.';
$helptxt['sp_param_sp_boardNews_limit'] = 'Numero massimo di notizie da mostrare.';
$helptxt['sp_param_sp_boardNews_start'] = 'L\'ID di un messaggio da cui partire (altrimenti si parte dal primo risultato).';
$helptxt['sp_param_sp_boardNews_length'] = 'Se indicato, i messaggi che superano questo limite vengono accorciati e terminano con dei puntini di sospensione (...) o con un link "Leggi tutto".';
$helptxt['sp_param_sp_boardNews_avatar'] = 'Mostra l\'avatar dell\'utente che ha pubblicato la notizia.';
$helptxt['sp_param_sp_boardNews_attachment'] = 'Mostra il primo allegato come immagine a sinistra del testo, per dare al blocco l\'aspetto di un blog. Se c\'è un allegato inserito nel testo, viene usato quello.';
$helptxt['sp_param_sp_boardNews_per_page'] = 'Quanti messaggi mostrare per pagina. Lascia vuoto per disattivare la paginazione.';
$helptxt['sp_param_sp_attachmentImage_limit'] = 'Quante immagini allegate di recente mostrare.';
$helptxt['sp_param_sp_attachmentImage_direction'] = 'Le immagini allegate possono essere allineate in orizzontale o in verticale.';
$helptxt['sp_param_sp_attachmentRecent_limit'] = 'Quanti allegati recenti mostrare.';
$helptxt['sp_param_sp_calendar_events'] = 'Mostra gli eventi del calendario.';
$helptxt['sp_param_sp_calendar_birthdays'] = 'Mostra i compleanni del calendario.';
$helptxt['sp_param_sp_calendar_holidays'] = 'Mostra le festività del calendario.';
$helptxt['sp_param_sp_calendarInformation_events'] = 'Mostra gli eventi del calendario.';
$helptxt['sp_param_sp_calendarInformation_future'] = 'Scegli per quanti giorni in avanti mostrare i prossimi eventi del calendario. Richiede che sia attiva la visualizzazione degli eventi del calendario. Per mostrare solo gli eventi di oggi, usa "0".';
$helptxt['sp_param_sp_calendarInformation_birthdays'] = 'Mostra i compleanni del calendario.';
$helptxt['sp_param_sp_calendarInformation_holidays'] = 'Mostra le festività del calendario.';
$helptxt['sp_param_sp_rssFeed_url'] = 'Inserisci l\'indirizzo completo del feed RSS.';
$helptxt['sp_param_sp_rssFeed_show_title'] = 'Mostra i titoli del feed.';
$helptxt['sp_param_sp_rssFeed_show_content'] = 'Mostra i contenuti del feed.';
$helptxt['sp_param_sp_rssFeed_show_date'] = 'Mostra le date del feed.';
$helptxt['sp_param_sp_rssFeed_strip_preserve'] = 'Tag HTML da mantenere nel contenuto del feed, separati da virgole.';
$helptxt['sp_param_sp_rssFeed_count'] = 'Quanti elementi mostrare.';
$helptxt['sp_param_sp_rssFeed_limit'] = 'Quanti caratteri mostrare del contenuto del feed RSS.';
$helptxt['sp_param_sp_staff_lmod'] = 'Esclude dall\'elenco i moderatori locali.';
$helptxt['sp_param_sp_articles_category'] = 'La categoria da cui prendere gli articoli.';
$helptxt['sp_param_sp_articles_limit'] = 'Quanti articoli mostrare.';
$helptxt['sp_param_sp_articles_type'] = 'Mostra articoli a caso oppure gli articoli più recenti.';
$helptxt['sp_param_sp_articles_view'] = 'Compatta mostra un semplice elenco di titoli con il link all\'articolo. Completa mostra il testo intero dell\'articolo, oppure fino al limite di caratteri';
$helptxt['sp_param_sp_articles_length'] = 'Se indicato, gli articoli completi che superano questo limite vengono accorciati e terminano con un link a forma di puntini di sospensione (...).';
$helptxt['sp_param_sp_articles_avatar'] = 'Mostra l\'avatar dell\'autore insieme all\'articolo.';
$helptxt['sp_param_sp_articles_attachment'] = 'Mostra il primo allegato dell\'articolo come immagine a sinistra del testo, per dare al blocco l\'aspetto di un blog. Se un articolo contiene immagini allegate inserite nel testo, per quell\'articolo vengono usate quelle.';
$helptxt['sp_param_sp_gallery_limit'] = 'Quanti elementi mostrare.';
$helptxt['sp_param_sp_gallery_type'] = 'Mostra elementi della galleria a caso oppure i più recenti.';
$helptxt['sp_param_sp_gallery_direction'] = 'Le immagini della galleria possono essere allineate in orizzontale o in verticale.';
$helptxt['sp_param_sp_html_content'] = 'Inserisci in questo riquadro il contenuto HTML personalizzato.';
$helptxt['sp_param_sp_bbc_content'] = 'Inserisci in questo riquadro il contenuto BBC personalizzato.';
$helptxt['sp_param_sp_php_content'] = 'Inserisci in questo riquadro il codice PHP personalizzato.';
