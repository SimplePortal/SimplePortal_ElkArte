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
$helptxt['sp_ConfigurationArea'] = 'Ici vous pouvez configurer SimplePortal comme vous le souhaitez.';

// General settings
$helptxt['portalactive'] = 'Ceci activera la page du portail.';
$helptxt['sp_portal_mode'] = 'SimplePortal peut tourner sous plusieurs modes. Cette option vous permet de sélectionner le mode que vous souhaitez utiliser. Les modes supportés sont : <br /><br />
<strong>Désactivé :</strong> Cela désactivera complètement le portail.<br /><br />
<strong>Page d\'accueil :</strong> C\'est la configuration par défaut. La page du portail accueillera les visiteurs au lieu de l\'index du "Forum". Les membres pourront accéder à l\'index du "forum" en utilisant le bouton du "Forum" dans le menu.<br /><br />
<strong>Intégration :</strong> Cela désactivera la page de portail. Les blocs seront seulement utilisables dans le Forum.<br /><br />
<strong>Autonome :</strong> Cela permettra au portail d\'être affiché par une URL différente, externe au forum. La page du portail apparaît dans l\'URL définie comme option dans l\'"URL Autonome". Pour les détails, vérifiez le fichier PortalStandalone.php trouvé à la racine du Forum.';
$helptxt['sp_maintenance'] = 'Quand la maintenance est activée, le portail est seulement visible par les membres qui ont la permission de modération SimplePortal.';
$helptxt['sp_standalone_url'] = 'URL pour le mode autonome.<br /><br />Exemple : http://myforum.com/portal.php';
$helptxt['portaltheme'] = 'Sélectionnez le thème qui sera affiché sur la page du portail.';
$helptxt['sp_disableForumRedirect'] = 'Si cette option est désactivée, les utilisateurs seront redirigées vers le portail après leur connexion ou déconnexion. Si l\'option est activée, les utilisateurs seront redirigées vers l\'index du Forum.';
$helptxt['sp_disableColor'] = 'Cette option désactive la coloration des utilisateurs en fonction de leur groupe d\'appartenance sur le portail (excepté dans la liste de Qui est en ligne).';
$helptxt['sp_disableMobile'] = 'Cette option désactive le portail sur tous les périphériques mobiles (téléphones, pas les tablettes). Pour désactiver par bloc, laissez cette option activée et utilisez les profils de visibilité.';
$helptxt['sp_disable_random_bullets'] = 'Désactive le coloris aléatoire pour les puces utilisées dans les listes du portail.';
$helptxt['sp_disable_php_validation'] = 'Désactive la validation du code PHP dans les blocs, pour prévenir les erreurs de syntaxe et de base de données dans le code.';
$helptxt['sp_disable_side_collapse'] = 'Désactive la possibilité de réduire les colonnes gauche et droite du portail.';
$helptxt['sp_resize_images'] = 'Active le redimensionnement des images dans les articles et les nouvelles en 300x300px, pour prévenir d\'un possible débordement.';

// Block settings
$helptxt['showleft'] = 'Ceci activera les blocs de gauche sur la page du portail et dans le forum.';
$helptxt['showright'] = 'Ceci activera les blocs de droite sur la page du portail et dans le forum.';
$helptxt['leftwidth'] = 'Si les blocs de gauche sont activés, leur taille peut être spécifiée ici. La taille peut être spécifiée en pixels (px) ou en pourcentage (%).';
$helptxt['rightwidth'] = 'Si les blocs de droite sont activés, leur taille peut être spécifiée ici. La taille peut être spécifiée en pixels (px) ou en pourcentage (%).';
$helptxt['sp_enableIntegration'] = 'Ce paramètre active les blocs sur le forum. Cela vous permet de gérer les \'Options d\'affichage\' avancées pour chaque bloc.';
$helptxt['sp_IntegrationHide'] = 'Masquer les blocs dans certaines sections du forum. Le paramètre \'Afficher les blocs dans le forum\' doit être activé pour que cela fonctionne.';

// Article settings
$helptxt['sp_articles_index'] = 'Ce paramètre permet aux articles d\'être affichés sur la page du portail, indépendament d\'un bloc article.';
$helptxt['sp_articles_index_per_page'] = 'Ceci règle le nombre maximum d\'articles affichés par page.';
$helptxt['sp_articles_index_total'] = 'Ce paramètre permet de fixer le nombre total d\'articles qui sont affichés l\'index du portail.';
$helptxt['sp_articles_length'] = 'Ce paramètre permet de limiter le nombre de caractères qu\'un article peut afficher sur la page du portail. Si l\'article excède cette limite, il sera raccourci et aura un lien points de suspension à la fin (...), qui permettra à l\'utilisateur de voir l\'article entier.';
$helptxt['sp_articles_per_page'] = 'Ce paramètre permet de fixer le nombre d\'articles affichés par page avec la liste des articles';
$helptxt['sp_articles_comments_per_page'] = 'Ce paramètre permet de fixer le nombre maximal de commentaires d\'article par page';
$helptxt['sp_articles_attachment_dir'] = 'Il s\'agit du dossier dans lequel enregistrer les fichiers joints téléversés avec des articles. Le dossier doit exister et avoir des droits d\'écriture. N\'utilisez pas le dossier standard pour des fichiers joints et ne modifiez pas cela si vous ne savez pas exactement ce que vous faites.';

// Blocks area
$helptxt['sp_BlocksArea'] = 'Les blocs sont des boites affichées sur la page du portail ou dans le forum. Cette section permet de modifier les blocs existants, et d\'en créer de nouveaux.';

// Block list
$helptxt['sp-blocksLeftList'] = 'Ces blocs sont affichés sur le coté gauche du portail.';
$helptxt['sp-blocksTopList'] = 'Ces blocs sont centrés sur le haut de la page du portail.';
$helptxt['sp-blocksBottomList'] = 'Ces blocs sont centrés sur le bas de la page du portail.';
$helptxt['sp-blocksRightList'] = 'Ces blocs sont affichés sur le coté droit du portail.';
$helptxt['sp-blocksHeaderList'] = 'Ces blocs sont affichés dans l\'en-tête du portail.';
$helptxt['sp-blocksFooterList'] = 'Ces blocs sont affichés dans le pied de page du portail.';

// Add/Edit blocks
$helptxt['sp-blocksAdd'] = 'Cette page vous permet de personnaliser et configurer le bloc sélectionné.';
$helptxt['sp-blocksSelectType'] = 'Cette page permet de créer des blocs sur la page du portail. Des blocs préconstruits ou des blocs avec des contenus personnalisés peuvent être créés facilement en sélectionnant les options appropriées.';
$helptxt['sp-blocksEdit'] = 'Cette page vous permet de personnaliser et configurer le bloc sélectionné.';
$helptxt['sp-blocksDisplayOptions'] = 'Options d\'affichage';
$helptxt['sp-blocksCustomDisplayOptions'] = 'Les options d\'affichage personnalisées autorisent un contrôle plus avancé du bloc avec les options de visiblilité et leur syntaxe spéciale.<br /><br />
<strong>Les actions spéciales incluent :</strong><br /><br />
<strong>all :</strong> chaque page du forum.<br />
<strong>allaction:</strong> toutes les actions<br />
<strong>allboard:</strong> toutes les sections<br />
<strong>allpage:</strong> toutes les pages<br />
<strong>allcategory:</strong> toutes les categories<br />
<strong>allarticle:</strong> tous les articles<br /><br />
<strong>portail :</strong> page du portail et sous-actions.<br />
<strong>forum:</strong> toutes les actions des sections, excepté le portail.<br />
<strong>Tilde (~)</strong><br />
Ce symbole agit comme un joker, en vous autorisant à inclure des actions dynamiques comme ../index.php?issue=* ou ../index.php?game=*. Exemple : ~action<br /><br />
<strong>Tube (|)</strong><br />
Un autre symbole de joker qui vous permet de spécifier une valeur exacte pour une action dynamique comme ../index.php?issue=1.0 or ../index.php?game=xyz. Devrait être utilisé avec un tilde et après l\'action. Par exemple : ~action|value<br /><br />
<strong>Moins (-)</strong><br />
Ce symbole est exclu des actions dynamiques. Devrait être utilisé avant le nom de l\'action pour les actions régulières et avant le tilde pour les actions dynamiques. Par exemple : -action et -~action';
$helptxt['sp-blocksStyleOptions'] = 'Ces options vous permettent de spécifier le CSS qui est appelé pour chaque bloc.';

// Articles area
$helptxt['sp_ArticlesArea'] = 'Les articles sont des sujets (Le premier message uniquement) qui sont affichés sur la page du portail. Cette section permet aux sujets existants d\'être modifiés, et aux nouveaux d\'être créés pour la page du portail.';

// Add/Edit articles
$helptxt['sp-articlesAdd'] = 'Cette partie permet d\'ajouter des articles aux catégories depuis vos sections.';
$helptxt['sp-articlesEdit'] = 'Dans cette partie, vous pouvez changer la catégorie ou le statut des articles.';
$helptxt['sp-articlesCategory'] = 'Sélectionnez une catégorie pour cet article.';
$helptxt['sp-articlesApproved'] = 'Les articles approuvés apparaîtront dans la partie Articles du portail.';
$helptxt['sp-articlesTopics'] = 'Sélectionnez les sujets qui seront affichés comme articles sur le portail.';
$helptxt['sp-articlesBoards'] = 'Sélectionnez une section pour rechercher des sujets.';

// Categories area
$helptxt['sp_CategoriesArea'] = 'Les catégories destinées à accueillir des articles. Cette section permet de modifier les catégories existantes, et d\'en créer de nouvelles. Pour créer un article, il doit y avoir au moins une catégorie.';

// Add/Edit categories
$helptxt['sp-categoriesAdd'] = 'Cette section permet de créer des catégories pour les articles. Pour créer des articles, il doit y avoir au moins une catégorie.';
$helptxt['sp-categoriesEdit'] = 'Cette section permet de modifier les catégories.';
$helptxt['sp-categoriesCategories'] = 'Cette page affiche une liste des catégories actuelles d\'article. Pour créer des articles, il doit y avoir au moins une catégorie.';
$helptxt['sp-categoriesDelete'] = 'Supprimer une catégorie supprimera les articles qu\'elle contient, ou les déplacera dans une autre catégorie.';

// Pages area
$helptxt['sp_PagesArea'] = 'Les pages sont des blocs de code BBC, PHP ou HTML affichés sur leur propre page. Cette section vous permet de créer, modifier et configurer vos pages.';

// Shoutbox area
$helptxt['sp_ShoutboxArea'] = 'Les chats doivent être créés dans cette section. Cette section permet de créer et configurer les chats. Il faudra créer un bloc de chat pour afficher le chat créé ici.';

// Add/Edit shoutboxes
$helptxt['sp-shoutboxesWarning'] = 'Le message d\'avertissement que vous configurez ici sera affiché dans le chat : toutes les personnes qui utiliseront le chat verront ce message.';
$helptxt['sp-shoutboxesBBC'] = 'Ce paramétrage vous permet de choisir les BBC qui peuvent être utilisés dans ce chat.<br /><br />Pressez la touche CTRL pour sélectionner ou déselectionner un BBC particulier. <br /><br />Si vous voulez sélectionne une série de BBC consécutifs, alors cliquez sur le premier BBC que vous voulez sélectionner, pressez la touche MAJ, et cliquez sur le dernier BBC que vous voulez sélectionner.';

// Menus area
$helptxt['sp_MenusArea'] = 'Le menu principal peut être modifié dans cette zone. Des menus personnalisés supplémentaires peuvent également être créés et modifiés ici.';

// Profiles area
$helptxt['sp_ProfilesArea'] = 'Cette option active les permissions à utiliser sur les blocs. Les trois premières options sont les plus simples à utiliser et à comprendre.
<ul>
	<li><strong>Invités :</strong> Tout utilisateur non-enregistré ou connecté <em>verra</em> ce bloc. Les utilisateurs connectés (incluant les administrateurs) <em>ne verront pas</em> ce bloc.</li>
	<li><strong>Membres :</strong> Tout utilisateur connecté (incluant les administrateurs) <em>verra</em> ce bloc.</li>
	<li><strong>Tout le monde :</strong> Tous les utilisateurs, connectés ou non, <em>verront</em> ce bloc.</li>
	<li><strong>Personnalisé :</strong> Activer pour afficher la zone de permissions personnalisées. Dans les paramètres des permissions personnalisées, il y a trois options à choisir pour chaque groupe.
		<ul>
			<li><strong>A :</strong> Autorisé, tout utilisateur de ce groupe <em>verra</em> ce bloc.</li>
			<li><strong>I :</strong> Interdit, par défaut tout utilisateur de ce groupe <em>ne verra pas</em> ce bloc. L\'utilisateur pourra voir ce bloc s\'il fait partie d\'un groupe avec les permissions autorisées.</li>
			<li><strong>R :</strong> Refusé, tout utilisateur de ce groupe <em>ne verra jamais</em> ce bloc. Cela outrepassera la permission Autorisé de tout groupe dont l\'utilisateur fait partie, donc <strong>attention</strong> avec cette permission.</li>
		</ul>
	</li>
</ul>';

// Block parameters
$helptxt['sp_param_sp_latestMember_limit'] = 'Nombre de membres à afficher.';
$helptxt['sp_param_sp_boardStats_averages'] = 'Affiche la moyenne des statistiques.';
$helptxt['sp_param_sp_topPoster_limit'] = 'Nombre de meilleurs posteurs à afficher.';
$helptxt['sp_param_sp_topPoster_type'] = 'Voir les meilleurss posteurs pendant combien de temps.';
$helptxt['sp_param_sp_recent_limit'] = 'Nombre de messages ou de sujets récents à afficher.';
$helptxt['sp_param_sp_recent_type'] = 'Affiche les messages ou les sujets récents.';
$helptxt['sp_param_sp_recentPosts_limit'] = 'Nombre de messages récents à afficher.';
$helptxt['sp_param_sp_recentTopics_limit'] = 'Nombre de sujets récents à afficher.';
$helptxt['sp_param_sp_topTopics_type'] = 'Trie les sujets par réponses ou vues.';
$helptxt['sp_param_sp_topTopics_limit'] = 'Nombre de sujets à afficher.';
$helptxt['sp_param_sp_topBoards_limit'] = 'Nombre de sections à afficher.';
$helptxt['sp_param_sp_showPoll_topic'] = 'Indiquer l\'ID du sujet contenant le sondage à afficher.';
$helptxt['sp_param_sp_showPoll_type'] = 'Sélectionnez la façon dont les sondages devraient être affichés. Normale permet à un sondage spécifique d\'être appelé par l\'ID du sujet, Récent affiche les sondages les plus récemment postés, et Aléatoire affichera un sondage aléatoirement.';
$helptxt['sp_param_sp_boardNews_board'] = 'L\'ID de la/les section(s) dont proviennent les sujets. Laisser vide pour prendre les sujets sur toute les sections visibles.';
$helptxt['sp_param_sp_boardNews_limit'] = 'Nombre Maximum d\'actualités à afficher.';
$helptxt['sp_param_sp_boardNews_start'] = 'L\'ID du message avec lequel commencer (autrement le premier résultat sera utilisé).';
$helptxt['sp_param_sp_boardNews_length'] = 'Si spécifié, les messages qui dépassent cette limite seront raccourcis et seront indiqués comme ceci (...), ou avec un lien "Lire Plus" placé sur la fin du message.';
$helptxt['sp_param_sp_boardNews_avatar'] = 'Permet d\'afficher l\'avatar du membres ayant posté.';
$helptxt['sp_param_sp_boardNews_attachment'] = 'Permet à la première pièce jointe d\'être affichée en tant qu\'image flottante à gauche, donnant un aspect de blog au bloc. Si une pièce jointe en ligne est trouvée, elle sera utilisée à la place.';
$helptxt['sp_param_sp_boardNews_per_page'] = 'Nombre de messages à afficher par page. Laisser vide pour désactiver la pagination.';
$helptxt['sp_param_sp_attachmentImage_limit'] = 'Nombre d\'images récentes en pièces jointes à afficher.';
$helptxt['sp_param_sp_attachmentImage_direction'] = 'Les images en pièces jointes peuvent être alignées horizontallement ou verticallement.';
$helptxt['sp_param_sp_attachmentRecent_limit'] = 'Nombre de fichiers joints récents à afficher.';
$helptxt['sp_param_sp_calendar_events'] = 'Permet d\'afficher des événements du calendrier.';
$helptxt['sp_param_sp_calendar_birthdays'] = 'Afficher les anniversaires du calendrier.';
$helptxt['sp_param_sp_calendar_holidays'] = 'Afficher les vacancs du calendrier.';
$helptxt['sp_param_sp_calendarInformation_events'] = 'Permet d\'afficher les événements du calendrier.';
$helptxt['sp_param_sp_calendarInformation_future'] = 'Permet d\'indiquer le nombre de jours à venir pour lesquels les événements à venir du calendrier seront affichés. Ceci requière la permission d\'afficher des événements du calendrier. Pour afficher uniquement les événements du jour, mettre "0".';
$helptxt['sp_param_sp_calendarInformation_birthdays'] = 'Afficher les anniversaires du calendrier.';
$helptxt['sp_param_sp_calendarInformation_holidays'] = 'Afficher les vacances du calendrier.';
$helptxt['sp_param_sp_rssFeed_url'] = 'Entrer l\'adresse URL complète du flux RSS.';
$helptxt['sp_param_sp_rssFeed_show_title'] = 'Montrer les titres des flux.';
$helptxt['sp_param_sp_rssFeed_show_content'] = 'Afficher les Contenus des flux.';
$helptxt['sp_param_sp_rssFeed_show_date'] = 'Afficher les Dates des flux.';
$helptxt['sp_param_sp_rssFeed_strip_preserve'] = 'Les marqueurs HTML à conserver dans le contenu du Flux sont séparés par des virgules.';
$helptxt['sp_param_sp_rssFeed_count'] = 'Nombre d\'éléments à afficher.';
$helptxt['sp_param_sp_rssFeed_limit'] = 'Nombre de charactères du Flux RSS à afficher.';
$helptxt['sp_param_sp_staff_lmod'] = 'Empêche les modérateurs locaux d\'être listés.';
$helptxt['sp_param_sp_articles_category'] = 'Catégorie à laquelle appartiennent les articles à afficher :';
$helptxt['sp_param_sp_articles_limit'] = 'Nombre d\'articles à afficher.';
$helptxt['sp_param_sp_articles_type'] = 'Affiche aléatoirement les articles, ou les derniers articles.';
$helptxt['sp_param_sp_articles_view'] = 'Compact affichera une simple liste des titres d\'articles. Complet affichera le texte intégral de l\'article, ou jusqu\'à la limite de caractères';
$helptxt['sp_param_sp_articles_length'] = 'Si spécifié, les articles excédant cette limite seront coupés et auront un lien (...) placée à la fin.';
$helptxt['sp_param_sp_articles_avatar'] = 'Permet d\'afficher, avec l\'article, l\'avatar de l\'auteur.';
$helptxt['sp_param_sp_articles_attachment'] = 'Permet à la première pièce jointe de l\'article d\'être affichée en tant qu\'image flottante à gauche, donnant un aspect de blog au bloc d\'article. Les pièces jointes d\'images en ligne remplaceront ces paramètres par article de base.';
$helptxt['sp_param_sp_gallery_limit'] = 'Nombre d\'élémentss à afficher.';
$helptxt['sp_param_sp_gallery_type'] = 'Afficher aléatoirement les derniers éléments de la galerie.';
$helptxt['sp_param_sp_gallery_direction'] = 'La galerie d\'images peut être alignée horizontallement ou verticallement.';
$helptxt['sp_param_sp_html_content'] = 'Saisissez le contenu HTML personnalisé dans cette boîte.';
$helptxt['sp_param_sp_bbc_content'] = 'Saisissez le contenu BBC personnalisé dans cette boîte.';
$helptxt['sp_param_sp_php_content'] = 'Saisissez le contenu PHP personnalisé dans cette boîte.';
