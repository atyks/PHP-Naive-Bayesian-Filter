<?php
/*
  ***** BEGIN LICENSE BLOCK *****
   This file is part of PHP Naive Bayesian Filter.

   The Initial Developer of the Original Code is
   Loic d'Anterroches [loic_at_xhtml.net].
   Portions created by the Initial Developer are Copyright (C) 2003
   the Initial Developer. All Rights Reserved.

   Contributor(s):

   PHP Naive Bayesian Filter is free software; you can redistribute it
   and/or modify it under the terms of the GNU General Public License as
   published by the Free Software Foundation; either version 2 of
   the License, or (at your option) any later version.

   PHP Naive Bayesian Filter is distributed in the hope that it will
   be useful, but WITHOUT ANY WARRANTY; without even the implied
   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
   See the GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Foobar; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

  ***** END LICENSE BLOCK *****
*/

/* Voici un petit exemple pour vous donner une idee de l'utilisation de */
/* cette class. Vous devez d'abord creer les tables necessaires avec le */
/* fichier de definition mysql.sql Attention, ne prenez pas exemple sur */
/* ce script, il est de conception rapide, juste pour presenter         */
/* l'utilisation du filtre. Il n'y a aucun controle d'erreur etc...     */

/* DEBUT CONFIGURATION */
$login  = 'root';
$pass   = '';
$db     = 'nb';
$server = 'localhost';
/* FIN CONFIGURATION */

include_once 'class.naivebayesian.php';
include_once 'class.naivebayesianstorage.php';
include_once 'class.mysql.php';


$nbs = new NaiveBayesianStorage($login, $pass, $server, $db);
$nb  = new NaiveBayesian($nbs);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>PHP Naive Bayesian Filter</title>
	<style>
	.succes { font-weight: 600; color: #00CC00; }
	.erreur { font-weight: 600; color: #CC0000; }

	</style>
</head>

<body>
<h1>PHP Naive Bayesian Filter</h1>
<?php

switch ($_REQUEST['action']) {
case 'addcat':
    addcat();
    break;
case 'remcat':
    remcat();
    break;
case 'train':
    train();
    break;
case 'untrain':
    untrain();
    break;
case 'cat':
    cat();
    break;
}

function addcat()
{
	global $_REQUEST, $login, $pass, $server, $db;
	$cat = trim(strip_tags($_REQUEST['cat']));
	$cat = strtr($cat, ' ', '');
	if (strlen($cat) == 0) {
        echo '<p class="erreur"><strong>Erreur:</strong> Vous devez donner un nom de catégorie.</p>';
    } else {
        $con = new Connection($login, $pass, $server, $db);
        $con->execute("INSERT INTO nb_categories (category_id) VALUES ('".$con->escapeStr($cat)."')");
        echo "<p class='succes'>La catégorie vient d'être ajoutée.</p>";
    }
}

function remcat()
{
	global $_REQUEST, $login, $pass, $server, $db, $nb;
	$cat = trim(strip_tags($_REQUEST['cat']));
	$cat = strtr($cat, ' ', '');
	if (strlen($cat) == 0) {
        echo '<p class="erreur"><strong>Erreur:</strong> Vous devez donner un nom de catégorie.</p>';
    } else {
        $con = new Connection($login, $pass, $server, $db);
        $con->execute("DELETE FROM nb_categories WHERE category_id='".$con->escapeStr($cat)."'");
        $con->execute("DELETE FROM nb_references WHERE category_id='".$con->escapeStr($cat)."'");
        $con->execute("DELETE FROM nb_wordfreqs WHERE category_id='".$con->escapeStr($cat)."'");
        $nb->updateProbabilities();
        echo "<p class='succes'>La catégorie vient d'être supprimée.</p>";
    }
}

function train()
{
	global $_REQUEST, $login, $pass, $server, $db, $nb;
	$docid = trim(strip_tags($_REQUEST['docid']));
	$docid = strtr($docid, ' ', '');
	if (strlen($docid) == 0) {
        echo '<p class="erreur"><strong>Erreur:</strong> Vous devez donner un identifiant pour le document.</p>';
        return;
    }
	$cat = trim(strip_tags($_REQUEST['cat']));
	$cat = strtr($cat, ' ', '');
	if (strlen($cat) == 0) {
        echo '<p class="erreur"><strong>Erreur:</strong> Vous devez donner un identifiant pour la catégorie.</p>';
        return;
    }
	$doc = trim($_REQUEST['document']);
	if (strlen($doc) == 0) {
        echo '<p class="erreur"><strong>Erreur:</strong> Vous devez donner un document.</p>';
        return;
    }
    if ($nb->train($docid, $cat, $doc)) {
        $nb->updateProbabilities();
        echo "<p class='succes'>Le filtre vient d'être entraîné.</p>";
    } else {
        echo "<p class='erreur'>Erreur: Erreur dans l'entraînement du filtre.</p>";
    }
}

function untrain()
{
	global $_REQUEST, $login, $pass, $server, $db, $nb;
	$docid = trim(strip_tags($_REQUEST['docid']));
	$docid = strtr($docid, ' ', '');
	if (strlen($docid) == 0) {
        echo '<p class="erreur"><strong>Erreur:</strong> Vous devez donner un identifiant pour le document.</p>';
        return;
    }
    if ($nb->untrain($docid, $cat, $doc)) {
        $nb->updateProbabilities();
        echo "<p class='succes'>Le filtre vient d'être désentraîné.</p>";
    } else {
        echo "<p class='erreur'>Erreur: Erreur dans le désentraînement du filtre.</p>";
    }
}

function cat()
{
	global $_REQUEST, $login, $pass, $server, $db, $nb;
	$doc = trim($_REQUEST['document']);
	if (strlen($doc) == 0) {
        echo '<p class="erreur"><strong>Erreur:</strong> Vous devez donner un document.</p>';
        return;
    }
    $scores = $nb->categorize($doc);
    echo "<table><caption>Scores</caption>\n";
    echo "<tr><th>Catégories</th><th>Scores</th></tr>\n";
    while(list($cat,$score) = each($scores)) {
        echo "<tr><td>$cat</td><td>$score</td></tr>\n";
    }
    echo "</table>";
}


?>

<?php
$cats = $nbs->getCategories();
?>
<h2>Explications</h2>
<p>
Vous devez d'abord avoir au minimum deux catégories pour pouvoir avoir une comparaison. Par exemple <strong>spam</strong>
et <strong>nonspam</strong>. Les identifiants ne doivent pas avoir d'espaces et doivent contenir que des lettres
et des chiffres.
</p>
<p>
Ensuite vous pouvez entraîner votre filtre. Vous allez prendre une série de spams, choisir <strong>spam</strong>
comme catégorie et entraînez le filtre. Prenez aussi quelques mails qui ne sont pas des spams, choisissez
<strong>nonspam</strong> et entraînez le filtre.
</p>
<p>
Maintenant vous pouvez prendre un email au hasard, et essayez de voir si c'est un spam ou si c'est un email
normal. Pour cela utiliser la fonction de catégorisation. Plus le score est important, plus votre message a une
<emph>chance</emph> d'appartenir à cette catégorie. Il y a une normalisation automatique, cela donne souvent
0 ou 1 si vous n'avez que 2 catégories. Si vous avez des questions, posez les sur
<a href="http://www.xhtml.net/">xhtml.net</a>.
</p>

<h2>Ajouter une catégorie</h2>
<form action='index.php' method='POST'>
<fieldset>
<input type='hidden' name='action' value='addcat'/>
Identifiant de la catégorie : <input type='text' name='cat' value='' />
<input type='submit' name='Ajouter' value='Ajouter cette catégorie' />
</fieldset>
</form>
<h2>Entraîner le filtre</h2>
<form action='index.php' method='POST'>
<fieldset>
<input type='hidden' name='action' value='train'/>
Identifiant du document : <input type='text' name='docid' value='' /> (il doit être unique)<br />
Catégorie pour le document :
<select name='cat'>
<?php
reset($cats);
while(list($key,$val) = each($cats)) {
    echo "<option value='$key'>$key</option>\n";
}
?>
</select>
<br />
Copier/coller ici le document :<br />
<textarea name="document" cols='50' rows='20'></textarea><br />
<input type='submit' name='Ajouter' value='Entraîner le filtre avec ce document' />
</fieldset>
</form>

<h2>Trouver la catégorie pour un document</h2>

<form action='index.php' method='POST'>
<fieldset>
<input type='hidden' name='action' value='cat'/>
Copier/coller ici le document :<br />
<textarea name="document" cols='50' rows='20'></textarea><br />
<input type='submit' name='Ajouter' value='Trouver la catégorie de ce document' />
</fieldset>
</form>




<h2>Supprimer une catégorie</h2>
<form action='index.php' method='POST'>
<fieldset>
<input type='hidden' name='action' value='remcat'/>
Catégorie à supprimer :
<select name='cat'>
<?php
reset($cats);
while(list($key,$val) = each($cats)) {
    echo "<option value='$key'>$key</option>\n";
}
?>
</select>
<input type='submit' name='Ajouter' value='Supprimer cette catégorie' />
</fieldset>
</form>

<h2>Supprimer un document</h2>
<form action='index.php' method='POST'>
<fieldset>
<input type='hidden' name='action' value='untrain'/>
Document à supprimer :
<select name='docid'>
<?php
$con = new Connection($login, $pass, $server, $db);
$rs = $con->select("SELECT * FROM nb_references");
while (!$rs->EOF()) {
    echo "<option value='".$rs->f('id')."'>".$rs->f('id')." - ".$rs->f('category_id')."</option>\n";
    $rs->moveNext();
}
?>
</select>
<input type='submit' name='Ajouter' value='Supprimer ce document' />
</fieldset>
</form>

<pre>
   This file is part of PHP Naive Bayesian Filter.

   The Initial Developer of the Original Code is
   Loic d'Anterroches [loic xhtml.net].
   Portions created by the Initial Developer are Copyright (C) 2003
   the Initial Developer. All Rights Reserved.

   Contributor(s):

   PHP Naive Bayesian Filter is free software; you can redistribute it
   and/or modify it under the terms of the GNU General Public License as
   published by the Free Software Foundation; either version 2 of
   the License, or (at your option) any later version.

   PHP Naive Bayesian Filter is distributed in the hope that it will
   be useful, but WITHOUT ANY WARRANTY; without even the implied
   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
   See the GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Foobar; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
</pre>
</body>
</html>
