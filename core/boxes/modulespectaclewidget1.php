<?php
/* Copyright (C) 2004-2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018       Frédéric France     <frederic.france@netlogic.fr>
 * Copyright (C) 2019 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    modulebuilder/template/core/boxes/modulewidget1.php
 * \ingroup modulespectacle
 * \brief   Widget provided by ModuleSpectacle
 *
 * Put detailed description here.
 */

/** Includes */
include_once DOL_DOCUMENT_ROOT . "/core/boxes/modules_boxes.php";

/**
 * Class to manage the box
 *
 * Warning: for the box to be detected correctly by dolibarr,
 * the filename should be the lowercase classname
 */
class modulespectaclewidget1 extends ModeleBoxes
{
    /**
     * @var string Alphanumeric ID. Populated by the constructor.
     */
    public $boxcode = "modulespectaclebox";

    /**
     * @var string Box icon (in configuration page)
     * Automatically calls the icon named with the corresponding "object_" prefix
     */
    public $boximg = "modulespectacle@modulespectacle";

    /**
     * @var string Box label (in configuration page)
     */
    public $boxlabel;

    /**
     * @var string[] Module dependencies
     */
    public $depends = array('modulespectacle');

    /**
     * @var DoliDb Database handler
     */
    public $db;

    /**
     * @var mixed More parameters
     */
    public $param;

    /**
     * @var array Header informations. Usually created at runtime by loadBox().
     */
    public $info_box_head = array();

    /**
     * @var array Contents informations. Usually created at runtime by loadBox().
     */
    public $info_box_contents = array();

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     * @param string $param More parameters
     */
    public function __construct(DoliDB $db, $param = '')
    {
        global $user, $conf, $langs;
        $langs->load("boxes");
        $langs->load('modulespectacle@modulespectacle');

        parent::__construct($db, $param);

        $this->boxlabel = $langs->transnoentitiesnoconv("MyWidget");

        $this->param = $param;

        //$this->enabled = $conf->global->FEATURES_LEVEL > 0;         // Condition when module is enabled or not
        //$this->hidden = ! ($user->rights->modulespectacle->myobject->read);   // Condition when module is visible by user (test on permission)
    }

    /**
     * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
     *
     * @param int $max Maximum number of records to load
     * @return void
     */
    public function loadBox($max = 5)
    {
        global $langs, $db, $conf;


        // Use configuration value for max lines count
        $this->max = $max;


        include_once DOL_DOCUMENT_ROOT . "/custom/modulespectacle/class/spectacle.class.php";
        $showstatic = new Spectacle($db);

        // Populate the head at runtime
        $text = $langs->trans("ModuleSpectacleBoxDescription", $max);


        $sql = "SELECT s.rowid, s.ref, s.label, s.amount, s.tms, s.date FROM " . MAIN_DB_PREFIX . "modulespectacle_spectacle as s";


        $sql .= $db->order('s.tms', 'DESC');
        $sql .= $db->plimit($max, 0);


        $result = $db->query($sql);


        if ($result) {
            $num = $db->num_rows($result);
            $line = 0;
            while ($line < $num) {
                $objs = $db->fetch_object($result);
                $datem = $db->jdate($objs->tms);
                $showstatic->id = $objs->rowid;
                $showstatic->ref = $objs->ref;
                $showstatic->label = $objs->label;
                $showstatic->amount = $objs->amount;
                $showstatic->date = $objs->date;

                $this->info_box_head = array(

                    // Title text
                    'text' => $text,
                    // Add a link
                    //			'sublink' => 'http://example.com',
                    // Sublink icon placed after the text
//                    'subpicto' => 'modulespectacle@modulespectacle',
                    // Sublink icon HTML alt text
                    'subtext' => '',
                    // Sublink HTML target
                    'target' => '',
                    // HTML class attached to the picto and link
                    'subclass' => 'center',
                    // Limit and truncate with "…" the displayed text lenght, 0 = disabled
                    'limit' => 0,
                    // Adds translated " (Graph)" to a hidden form value's input (?)
                    'graph' => false
                );

                $this->info_box_contents[$line][] = array(
                    'td' => 'class="tdoverflowmax100 maxwidth100onsmartphone"',
                    'text' => $showstatic->getNomUrl(1),
                    'asis' => 1,
                );

                $this->info_box_contents[$line][] = array(
                    'td' => 'class="tdoverflowmax100 maxwidth100onsmartphone"',
                    'text' => $objs->label,
                );

                $this->info_box_contents[$line][] = array(
                    'td' => 'class="right"',
                    'text' => price($objs->amount),
                );

                $this->info_box_contents[$line][] = array(
                    'td' => 'class="right"',
                    'text' => dol_print_date($objs->date, 'day'),
                );

                $this->info_box_contents[$line][] = array(
                    'td' => 'class="right"',
                    'text' => dol_print_date($datem, 'day'),
                );

                $line++;
            }
            if ($num == 0)
                $this->info_box_contents[$line][0] = array(
                    'td' => 'align="center"',
                    'text' => $langs->trans("NoRecordedShoxs"),
                );

            $db->free($result);
        }
    }



//		// Populate the contents at runtime
//		$this->info_box_contents = array(
//			0 => array( // First line
//				0 => array( // First Column
//					//  HTML properties of the TR element. Only available on the first column.
//					'tr'           => 'align="left"',
//					// HTML properties of the TD element
//					'td'           => '',
//
//					// Main text for content of cell
//					'text'         => 'First cell of first line',
//					// Link on 'text' and 'logo' elements
//					'url'          => 'http://example.com',
//					// Link's target HTML property
//					'target'       => '_blank',
//					// Fist line logo (deprecated. Include instead logo html code into text or text2, and set asis property to true to avoid HTML cleaning)
//					//'logo'         => 'monmodule@monmodule',
//					// Unformatted text, added after text. Usefull to add/load javascript code
//					'textnoformat' => '',
//
//					// Main text for content of cell (other method)
//					//'text2'        => '<p><strong>Another text</strong></p>',
//
//					// Truncates 'text' element to the specified character length, 0 = disabled
//					'maxlength'    => 0,
//					// Prevents HTML cleaning (and truncation)
//					'asis'         => false,
//					// Same for 'text2'
//					'asis2'        => true
//				),
//				1 => array( // Another column
//					// No TR for n≠0
//					'td'   => '',
//					'text' => 'Second cell',
//				)
//			),
//			1 => array( // Another line
//				0 => array( // TR
//					'tr'   => 'align="left"',
//					'text' => 'Another line'
//				),
//				1 => array( // TR
//					'tr'   => 'align="left"',
//					'text' => ''
//				)
//			),
//			2 => array( // Another line
//				0 => array( // TR
//					'tr'   => 'align="left"',
//					'text' => ''
//				),
//				0 => array( // TR
//					'tr'   => 'align="left"',
//					'text' => ''
//				)
//			),
//		);

	/**
	 * Method to show box. Called by Dolibarr eatch time it wants to display the box.
	 *
	 * @param array $head       Array with properties of box title
	 * @param array $contents   Array with properties of box lines
     * @param int   $nooutput   No print, only return string
	 * @return void
	 */
	public function showBox($head = null, $contents = null, $nooutput = 0)
	{
		// You may make your own code here…
		// … or use the parent's class function using the provided head and contents templates
		parent::showBox($this->info_box_head, $this->info_box_contents);
	}
}
