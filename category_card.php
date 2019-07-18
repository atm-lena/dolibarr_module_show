<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       spectacle_category.php
 *		\ingroup    modulespectacle
 *		\brief      Page to create category
 */


// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include "../main.inc.php";
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/modulespectacle/class/category.class.php');
//dol_include_once('/modulespectacle/lib/modulespectacle_spectacle.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("modulespectacle@modulespectacle","other"));

// Get parameters
$id			= GETPOST('id', 'int');
$action		= GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage= GETPOST('contextpage','aZ')?GETPOST('contextpage','aZ'):'spectaclecard';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical objects
$object=new Category($db);
$diroutputmassaction=$conf->modulespectacle->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('categorycard','globalcard'));     // Note that conf->hooks_modules contains array


// Initialize array of search criterias
//$search_all=trim(GETPOST("search_all",'alpha'));
//$search=array();
//foreach($object->fields as $key => $val)
//{
//	if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
//}
//
//if (empty($action) && empty($id) && empty($ref)) $action='view';

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

// Security check - Protection if external user
//if ($user->societe_id > 0) access_forbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
//$isdraft = (($object->statut == spectacle::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'modulespectacle', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);


/*
 * Actions
 *
 * Put here all code to do according to value of "action" parameter
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	$error=0;

	$permissiontoadd = $user->rights->modulespectacle->write;
	$permissiontodelete = $user->rights->modulespectacle->delete || ($permissiontoadd && $object->status == 0);
    	$backurlforlist = dol_buildpath('/modulespectacle/category_list.php',1);
	if (empty($backtopage)) {
	    if (empty($id)) $backtopage = $backurlforlist;
	    else $backtopage = dol_buildpath('/modulespectacle/category_card.php?id=',1).($id > 0 ? $id : '__ID__');
    	}
	$triggermodname = 'MODULESPECTACLE_SPECTACLE_MODIFY';	// Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, delete or clone
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';		// Must be include, not include_once

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to send emails
	$trigger_name='SPECTACLE_SENTBYMAIL';
	$autocopy='MAIN_MAIL_AUTOCOPY_SPECTACLE_TO';
	$trackid='spectacle'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */

$form=new Form($db);
$formfile=new FormFile($db);

llxHeader('','category','');

// Example : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_myfunc();
	});
});
</script>';


// Part to create
if ($action == 'create')
{
	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Category")));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

	dol_fiche_head(array(), '');

	print '<table class="border centpercent">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	dol_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	print '<input type="'.($backtopage?"submit":"button").'" class="button" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'"'.($backtopage?'':' onclick="javascript:history.go(-1)"').'>';	// Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit')
{
    print load_fiche_titre($langs->trans("ShowM"));

    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="update">';
    print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
    print '<input type="hidden" name="id" value="'.$object->id.'">';

    dol_fiche_head();

    print '<table class="border centpercent">'."\n";

    // Common attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

    print '</table>';

    dol_fiche_end();

    print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
    print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
    print '</div>';

    print '</form>';

}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
{
    $res = $object->fetch_optionals();

//    $head = categoryPrepareHead($object);
//    dol_fiche_head($head, 'card', $langs->trans("ShowM"), -1, 'spectacle@modulespectacle');

    $formconfirm = '';

    // Confirmation to delete
    if ($action == 'delete')
    {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteCategory'), $langs->trans('ConfirmDeleteCategory'), 'confirm_delete', '', 0, 1);
    }

    // Clone confirmation
    if ($action == 'clone') {
        // Create an array for form
        $formquestion = array();
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('CloneCategory'), $langs->trans('ConfirmCloneCategory', $object->label), 'confirm_clone', $formquestion, 'yes', 1);
    }


    // Call Hook formConfirm
    $parameters = array('lineid' => $lineid);
    $reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
    if (empty($reshook)) $formconfirm.=$hookmanager->resPrint;
    elseif ($reshook > 0) $formconfirm=$hookmanager->resPrint;

    // Print form confirm
    print $formconfirm;


    // Object card
    // ------------------------------------------------------------
    $linkback = '<a href="' .dol_buildpath('/modulespectacle/category_list.php',1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

    $morehtmlref='<div class="refidno">';

    $morehtmlref.='</div>';


    dol_banner_tab($object, 'label', $linkback, 1, 'id', 'label', $morehtmlref);


    print '<div class="fichecenter">';
    print '<div class="fichehalfleft">';
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border centpercent">'."\n";

    // Common attributes
    //$keyforbreak='fieldkeytoswithonsecondcolumn';
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

    print '</table>';
    print '</div>';
    print '</div>';

    print '<div class="clearboth"></div><br>';

    dol_fiche_end();


    // Buttons for actions
    if ($action != 'presend' && $action != 'editline') {
        print '<div class="tabsAction">'."\n";
        $parameters=array();
        $reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
        if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

        if (empty($reshook))
        {
//            // Send
//            print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>'."\n";

            // Modify
            if ($user->rights->modulespectacle->write)
            {
                print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=edit">'.$langs->trans("Modify").'</a>'."\n";
            }
            else
            {
                print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
            }

            // Clone
//            if ($user->rights->modulespectacle->write)
//            {
                print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;socid=' . $object->socid . '&amp;action=clone&amp;object=order">' . $langs->trans("ToClone") . '</a></div>';
//            }


            if ($user->rights->modulespectacle->delete)
            {
                print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete">'.$langs->trans('Delete').'</a>'."\n";
            }
            else
            {
                print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Delete').'</a>'."\n";
            }
        }
        print '</div>'."\n";
    }

}

// End of page
llxFooter();
$db->close();

