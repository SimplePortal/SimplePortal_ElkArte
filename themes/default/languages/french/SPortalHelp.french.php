<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2021 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 RC2
 */

global $helptxt;

// Configuration area
$helptxt['sp_ConfigurationArea'] = 'Ici vous pouvez configurer SimplePortal comme vous voulez.';

// General settings
$helptxt['portalactive'] = 'Ceci activera la page du portail.';
$helptxt['sp_portal_mode'] = 'SimplePortal peut tourner sous plusieurs modes. Cette option vous permet de sélectionner le mode que vous souhaitez utiliser. Les modes supportés incluent:<br /><br />
<strong>Désactivé:</strong> Cela désactivera complètement le portail.<br /><br />
<strong>Page principale:</strong> C\'est la configuration par défaut. La page du portail saluera les spectateurs au lieu de l\'index du "forum". Les membres seront capables d\'accéder à l\'index du "forum" en utilisant l\'action du "forum" qui peut être accessible en cliquant sur le bouton du "forum".<br /><br />
<strong>Intégration:</strong> Cela désactivera la page de portail. Les blocs seront seulement utilisables dans forum.<br /><br />
<strong>Autonome:</strong> Cela permettra au portail d\'être affiché par une URL différente, loin du forum. La page du portail apparaît dans l\'URL définie comme option dans l\'"URL Autonome". Pour les détails, vérifiez le dossier SPStandalone.php trouvé à la racine du forum.';
$helptxt['sp_maintenance'] = 'Quand la maintenance est activée, le portail est seulement visible par les membres qui ont la permission de modération SimplePortal.';
$helptxt['sp_standalone_url'] = 'URL en mode autonome.<br /><br />Exemple: http://myforum.com/portal.php';
$helptxt['portaltheme'] = 'Sélectionner le thème qui sera affiché sur la page du portail.';
$helptxt['sp_disableForumRedirect'] = 'Cette option désactive la redirection ?action=forum, cela amènera à la page du portail à la place.';
$helptxt['sp_disableColor'] = 'Si le mod Member Color Link est installé, ceci désactivera le mod sur la page du portail (excepté dans la liste de qui est en ligne).';
$helptxt['sp_disableMobile'] = 'Cette option désactive le portail sur tous les périphériques mobiles (téléphones et non tablettes)';
$helptxt['sp_disable_random_bullets'] = 'Désactive le coloris aléatoire pour les images "puces" utilisées dans les listes du portail.';
$helptxt['sp_disable_php_validation'] = 'Désactive la validation du PHP dans les codes du bloc, pour prévenir les erreurs de syntaxe et de base de données dans le code.';
$helptxt['sp_disable_side_collapse'] = 'Désactive la contraction des colonnes gauche et droite du portail.';
$helptxt['sp_resize_images'] = 'Active le redimensionnement des images dans les articles et les nouvelles sections en 300x300px, pour prévenir d\'un possible débordement.';

// Block settings
$helptxt['showleft'] = 'Ceci activera les blocs de gauche sur la page du portail.';
$helptxt['showright'] = 'Ceci activera les blocs de droite sur la page du portail.';
$helptxt['leftwidth'] = 'Si les blocs de gauche sont activés, leur taille peut être spécifiée ici. La taille peut être spécifiée en pixels (px) ou en pourcentage (%).';
$helptxt['rightwidth'] = 'Si les blocs de droite sont activés, leur taille peut être spécifiée ici. La taille peut être spécifiée en pixels (px) ou en pourcentage (%).';
$helptxt['sp_enableIntegration'] = 'Ce paramètre active les blocs sur le forum. Cela vous permet de gérer les \'Options d\'affichage\' avancées pour chaque bloc.';
$helptxt['sp_IntegrationHide'] = 'Masquer les blocs dans certaines sections du forum. Le paramètre \'Afficher les blocs dans le forum\' doit être activé pour que cela fonctionne.';

// Article settings
$helptxt['sp_articles_index'] = 'Ce paramètre permet aux articles d\'être affichés sur la page du portail.';
$helptxt['sp_articles_index_per_page'] = 'Ceci règle le nombre maximum d\'articles montrés par page.';
$helptxt['sp_articles_index_total'] = 'Ce paramètre permet de fixer le nombre total d\'articles qui sont affichés sur le portail.';
$helptxt['sp_articles_length'] = 'Ce paramètre permet de fixer une limite sur le nombre de caractères qu\'un article peut afficher sur la page du portail. Si l\'article excède cette limite, il sera raccourci et aura un lien points de suspension à la fin, qui permettra à l\'utilisateur de voir l\'article entier.';
$helptxt['sp_articles_per_page'] = 'Ce paramètre permet de fixer le nombre d\'articles par page sur la liste des articles';
$helptxt['sp_articles_comments_per_page'] = 'Ce paramètre permet de fixer le nombre maximal de commentaires d\'article par page';

// Blocks area
$helptxt['sp_BlocksArea'] = 'Les blocs sont des boites affichées sur la page du portail. Cette section permet aux blocs existants d\'être modifiés, et aux nouveaux d\'être crées pour la page du portail.';

// Block list
$helptxt['sp-blocksLeftList'] = 'Ces blocs sont affichés sur le coté gauche du portail.';
$helptxt['sp-blocksTopList'] = 'Ces blocs sont centrés sur le haut de la page du portail.';
$helptxt['sp-blocksBottomList'] = 'Ces blocs sont centrés sur le bas de la page du portail.';
$helptxt['sp-blocksRightList'] = 'Ces blocs sont affichés sur le coté droit du portail.';
$helptxt['sp-blocksHeaderList'] = 'Ces blocs sont affichés sur le haut du portail et du forum.';
$helptxt['sp-blocksFooterList'] = 'Ces blocs sont affichés sur le bas du portail et du forum.';

// Add/Edit blocks
$helptxt['sp-blocksAdd'] = 'Cette page vous permet de personnaliser et configurer le bloc sélectionné.';
$helptxt['sp-blocksSelectType'] = 'Cette page permet aux blocs d\'être créés pour la page du portail. Des blocs préconstruits ou des blocs avec contenu personnalisé peuvent être créés facilement en sélectionnant les options appropriées.';
$helptxt['sp-blocksEdit'] = 'Cette page vous permet de personnaliser et configurer le bloc sélectionné.';
$helptxt['sp-blocksDisplayOptions'] = 'Options d\'affichage';
$helptxt['sp-blocksCustomDisplayOptions'] = 'Les options d\'affichage personnalisées autorisent un contrôle plus avancé partout ou est affiché le bloc avec sa syntaxe spéciale.<br /><br />
<strong>Les actions spéciales incluent :</strong><br /><br />
<strong>all:</strong> chaque page du forum.<br />
<strong>portal:</strong> page du portail et sous-actions.<br />
<strong>forum:</strong> toutes les actions des sections, excepté le portail.<br />
<strong>sforum:</strong> toutes les actions et sections, excepté le portail.<br />
<strong>allaction:</strong> toutes les actions.<br />
<strong>allboard:</strong> toutes les sections.<br /><br />
<strong>Tilde (~)</strong><br />
Ce symbole agit comme un joker, en vous autorisant à inclure des actions dynamiques comme ../index.php?issue=* ou ../index.php?game=*. Used as ~action<br /><br />
<strong>Tube (|)</strong><br />
Un autre symbole de joker qui vous permet de spécifier une valeur exacte pour une action dynamique comme ../index.php?issue=1.0 or ../index.php?game=xyz. Devrait être utilisé avec un ondulé et après l\'action comme; ~action|value<br /><br />
<strong>Moins (-)</strong><br />
Ce symbole est exclu des actions dynamiques. Devrait être utilisé avant le nom de l\'action pour les actions régulières et avant l\'ondulé pour les actions dynamiques. Used as -action and -~action';
$helptxt['sp-blocksStyleOptions'] = 'Ces options vous permettent de spécifier le CSS qui est appelé pour chaque bloc.';

// Articles area
$helptxt['sp_ArticlesArea'] = 'Les articles sont des sujets (Le premier post uniquement) qui sont affichés sur la page du portail. Cette section permet aux sujets existants d\'être modifiés, et aux nouveaux d\'être créés pour la page du portail.';

// Add/Edit articles
$helptxt['sp-articlesAdd'] = 'Cette partie permet d\'ajouter des articles aux catégories depuis vos sections.';
$helptxt['sp-articlesEdit'] = 'Dans cette partie, vous pouvez changer la catégorie ou le statut des articles.';
$helptxt['sp-articlesCategory'] = 'Sélectionnez une catégorie pour cet article.';
$helptxt['sp-articlesApproved'] = 'Les articles approuvés apparaîtront dans la partie Articles du portail.';
$helptxt['sp-articlesTopics'] = 'Sélectionnez les sujets qui seront affichés comme articles sur le portail.';
$helptxt['sp-articlesBoards'] = 'Sélectionnez une section pour rechercher des sujets.';

// Categories area
$helptxt['sp_CategoriesArea'] = 'Gestion des catégories d\'article. Cette section permet aux catégories existantes d\'être modifiées, et aux nouvelles d\'être créées pour les articles. Pour créer un article, il doit y avoir au moins une catégorie.';

// Add/Edit categories
$helptxt['sp-categoriesAdd'] = 'Cette section permet aux catégories d\'être créées pour les articles. Pour créer des articles, il doit y avoir au moins une catégorie.';
$helptxt['sp-categoriesEdit'] = 'Cette section permet aux catégories d\'être modifiées.';
$helptxt['sp-categoriesCategories'] = 'Cette page affiche une liste des catégories de l\'article actuel. Pour créer des articles, il doit y avoir au moins une catégorie.';
$helptxt['sp-categoriesDelete'] = 'Supprimer une catégorie soit supprimera les articles qu\'elle contient, soit les déplacera dans une autre catégorie.';

// Pages area
$helptxt['sp_PagesArea'] = 'Les pages BBC, PHP ou HTML de code du bloc sont montrés sur leur propre page dans votre forum. Cette section vous permet de créer, éditer et configurer vos pages.';

// Shoutbox area
$helptxt['sp_ShoutboxArea'] = 'Les chats ont besoin d\'être créés dans cette section. Cette section permet aux chats d\'être crééé et configurés. Un bloc de chat aura besoin alors d\'être utilisé pour afficher le chat qui aura été créé.';

// Add/Edit shoutboxes
$helptxt['sp-shoutboxesWarning'] = 'Le message d\'avertissement que vous avez mis ici sera montré dans lchat, toutes les personnes qui utiliseront le chat verront ce message.';
$helptxt['sp-shoutboxesBBC'] = 'Cette configuration vous permet de choisir les BBC qui peuvent être utilisés dans ce chat.<br /><br />Pressez la touche CTRL pour sélectionner ou déselectionner un BBC particulier. <br /><br />Si vous sélectionnez une série de BBC consécutifs, alors cliquez sur le premier BBC que vous voulez sélectionner, pressez sur la touche MAJ, et cliquez sur le dernier BBC que vous voulez sélectionner.';

$helptxt['sp_ProfilesArea'] = 'Cette option active les permissions à utiliser sur les blocs. Les trois premières options sont très simples à utiliser et à comprendre.
<ul>
	<li><strong>Invités :</strong> Tout utilisateur non-enregistré ou connecté <em>verra</em> ce bloc. Les utilisateurs connectés (incluant les administrateurs) <em>ne verront pas</em> ce bloc.</li>
	<li><strong>Membres :</strong> Tout utilisateur connecté (incluant les administrateurs) <em>verra</em> ce bloc.</li>
	<li><strong>Tout le monde :</strong> Tout utilisateur, connecté ou non, <em>verra</em> ce bloc.</li>
	<li><strong>Personnalisé :</strong> Sélectionnez ceci pour afficher la zone de permissions personnalisées. Dans les paramètres des permissions personnalisées, il y a trois options à choisir pour chaque groupe.
		<ul>
			<li><strong>A :</strong> Autorisé, tout utilisateur de ce groupe <em>verra</em> ce bloc.</li>
			<li><strong>I :</strong> Interdit, tout utilisateur de ce groupe <em>ne verra pas</em> ce bloc par défaut. L\'utilisateur pourra voir ce bloc s\'il fait partie d\'un groupe avec les permissions autorisées.</li>
			<li><strong>R :</strong> Refusé, tout utilisateur de ce groupe <em>ne verra jamais</em> ce bloc. Cela outrepassera la permission autorisée de tout groupe dont l\'utilisateur fera part, donc <strong>attention</strong> avec cette permission.</li>
		</ul>
	</li>
</ul>';

// Block parameters
$helptxt['sp_param_sp_latestMember_limit'] = 'Combien de membres à afficher.';
$helptxt['sp_param_sp_boardStats_averages'] = 'Affiche la moyenne des statistiques.';
$helptxt['sp_param_sp_topPoster_limit'] = 'Combien de Top posteurs à afficher.';
$helptxt['sp_param_sp_topPoster_type'] = 'Délai pour voir les tops posteurs.';
$helptxt['sp_param_sp_recent_limit'] = 'Combien de messages ou de sujets récents à afficher.';
$helptxt['sp_param_sp_recent_type'] = 'Affiche les messages ou les sujets récents.';
$helptxt['sp_param_sp_recentPosts_limit'] = 'Combien de messages récents à afficher.';
$helptxt['sp_param_sp_recentTopics_limit'] = 'Combien de sujets récents à afficher.';
$helptxt['sp_param_sp_topTopics_type'] = 'Trie les sujets par réponses ou vues.';
$helptxt['sp_param_sp_topTopics_limit'] = 'Combien de messages à afficher.';
$helptxt['sp_param_sp_topBoards_limit'] = 'Combien de sections à afficher.';
$helptxt['sp_param_sp_showPoll_topic'] = 'L\'ID de ce sujet contient un sondage à afficher.';
$helptxt['sp_param_sp_showPoll_type'] = 'Sélectionnez les sondages qui devraient être affichés. La fonction normale permet à un sondage spécifique d\'être appelé par l\'ID du sujet, affichage des sondages les plus récemment affichés, et un affichage aléatoire pour un sondage aléatoire.';
$helptxt['sp_param_sp_boardNews_board'] = 'L\'ID des sections pour les sujets. Laisser vide pour prendre les sujets sur toute les sections visibles.';
$helptxt['sp_param_sp_boardNews_limit'] = 'Nombre Maximum de nouveaux articles à afficher.';
$helptxt['sp_param_sp_boardNews_start'] = 'L\'ID d\'un poste particulier commence avec cet ID (autrement le premier résultat sera utilisé).';
$helptxt['sp_param_sp_boardNews_length'] = 'Si c\'est spécifié, les messages qui dépassent cette limite seront raccourcis et seront indiqués comme ceci (...), ou d\'un lien"Lire Plus" placé sur la fin du message..';
$helptxt['sp_param_sp_boardNews_avatar'] = 'Les avatars seront affichés pour les membres ayant mis un message dans les nouvelles sections.';
$helptxt['sp_param_sp_boardNews_per_page'] = 'Combien de messages à afficher par page. Laisser vide pour désactiver la pagination.';
$helptxt['sp_param_sp_attachmentImage_limit'] = 'Combien de fichiers images attachés récents à afficher.';
$helptxt['sp_param_sp_attachmentImage_direction'] = 'Les fichiers attachés images sont alignés horizontallement ou verticallement.';
$helptxt['sp_param_sp_attachmentRecent_limit'] = 'Combien de fichiers attachés récents à afficher.';
$helptxt['sp_param_sp_calendar_events'] = 'Permet des événements du calendrier a être affiché.';
$helptxt['sp_param_sp_calendar_birthdays'] = 'Afficher les anniversaires du calendrier.';
$helptxt['sp_param_sp_calendar_holidays'] = 'Afficher les fêtes du calendrier.';
$helptxt['sp_param_sp_calendarInformation_events'] = 'Permet d\'afficher les événements du calendrier.';
$helptxt['sp_param_sp_calendarInformation_future'] = 'Permet d\'afficher les événements à venir du calendrier. Ceci requière la capacité d\'afficher des événements du calendrier. Pour afficher uniquement les événements du jour, mettre "0".';
$helptxt['sp_param_sp_calendarInformation_birthdays'] = 'Afficher les anniversaires du calendrier.';
$helptxt['sp_param_sp_calendarInformation_holidays'] = 'Afficher les fêtes du calendrier.';
$helptxt['sp_param_sp_rssFeed_url'] = 'Entrer l\'adresse URL entière du flux RSS.';
$helptxt['sp_param_sp_rssFeed_show_title'] = 'Montrer les titres des fluxs.';
$helptxt['sp_param_sp_rssFeed_show_content'] = 'Afficher le Contenu.';
$helptxt['sp_param_sp_rssFeed_show_date'] = 'Afficher les Dates.';
$helptxt['sp_param_sp_rssFeed_strip_preserve'] = 'Les marqueurs HTML conservant le contenu du Flux sont séparés par les virgules.';
$helptxt['sp_param_sp_rssFeed_count'] = 'Combien d\'articles à afficher.';
$helptxt['sp_param_sp_rssFeed_limit'] = 'Combien de charactères à afficher pour le contenu du flux RSS.';
$helptxt['sp_param_sp_staff_lmod'] = 'Désactiver les modérateurs locaux qui sont listés.';
$helptxt['sp_param_sp_articles_category'] = 'Cette catégorie affiche des articles de:';
$helptxt['sp_param_sp_articles_limit'] = 'Combien d\'articles à afficher.';
$helptxt['sp_param_sp_articles_type'] = 'Afficher aléatoirement les articles, ou les derniers articles.';
$helptxt['sp_param_sp_articles_length'] = 'Si spécifié, les articles excédant cette limite seront coupés et auront une ellipse (...) placée à la fin.';
$helptxt['sp_param_sp_articles_avatar'] = 'Permet d\'afficher, avec l\'article, l\'avatar de l\'auteur.';
$helptxt['sp_param_sp_gallery_limit'] = 'Combien d\'articles à afficher.';
$helptxt['sp_param_sp_gallery_type'] = 'Afficher aléatoirement les derniers articles de la galerie.';
$helptxt['sp_param_sp_gallery_direction'] = 'La galerie d\'images sera alignée horizontallement ou verticallement.';
$helptxt['sp_param_sp_html_content'] = 'Saisiez le contenu HTML personnalisé dans cette boîte.';
$helptxt['sp_param_sp_bbc_content'] = 'Saisiez le contenu BBC personnalisé dans cette boîte.';
$helptxt['sp_param_sp_php_content'] = 'Saisiez le contenu PHP personnalisé dans cette boîte.';
