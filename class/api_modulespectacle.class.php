<?php
/* Copyright (C) 2015   Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2019 SuperAdmin
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

use Luracast\Restler\RestException;

dol_include_once('/modulespectacle/class/spectacle.class.php');



/**
 * \file    modulespectacle/class/api_modulespectacle.class.php
 * \ingroup modulespectacle
 * \brief   File for API management of spectacle.
 */

/**
 * API class for modulespectacle spectacle
 *
 * @smart-auto-routing false
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class ModuleSpectacleApi extends DolibarrApi
{
    /**
     * @var array   $FIELDS     Mandatory fields, checked when create and update object
     */
    static $FIELDS = array(
        'name',
    );


    /**
     * @var spectacle $spectacle {@type spectacle}
     */
    public $spectacle;

    /**
     * Constructor
     *
     * @url     GET /
     *
     */
    function __construct()
    {
		global $db, $conf;
		$this->db = $db;
        $this->spectacle = new spectacle($this->db);
    }

    /**
     * Get properties of a spectacle object
     *
     * Return an array with spectacle informations
     *
     * @param 	int 	$id ID of spectacle
     * @return 	array|mixed data without useless information
	 *
     * @url	GET spectacles/{id}
     * @throws 	RestException
     */
    function get($id)
    {
		if(! DolibarrApiAccess::$user->rights->spectacle->read) {
			throw new RestException(401);
		}

        $result = $this->spectacle->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'spectacle not found');
        }

		if( ! DolibarrApi::_checkAccessToResource('spectacle',$this->spectacle->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		return $this->_cleanObjectDatas($this->spectacle);
    }


    /**
     * List spectacles
     *
     * Get a list of spectacles
     *
     * @param string	       $sortfield	        Sort field
     * @param string	       $sortorder	        Sort order
     * @param int		       $limit		        Limit for list
     * @param int		       $page		        Page number
     * @param string           $sqlfilters          Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
     * @return  array                               Array of order objects
     *
     * @throws RestException
     *
     * @url	GET /spectacles/
     */
    function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '')
    {
        global $db, $conf;

        $obj_ret = array();

        $socid = DolibarrApiAccess::$user->societe_id ? DolibarrApiAccess::$user->societe_id : '';

        $restictonsocid = 0;	// Set to 1 if there is a field socid in table of object

        // If the internal user must only see his customers, force searching by him
        $search_sale = 0;
        if ($restictonsocid && ! DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) $search_sale = DolibarrApiAccess::$user->id;

        $sql = "SELECT t.rowid";
        if ($restictonsocid && (!DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) || $search_sale > 0) $sql .= ", sc.fk_soc, sc.fk_user"; // We need these fields in order to filter by sale (including the case where the user can only see his prospects)
        $sql.= " FROM ".MAIN_DB_PREFIX."spectacle_mytable as t";

        if ($restictonsocid && (!DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) || $search_sale > 0) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc"; // We need this table joined to the select in order to filter by sale
        $sql.= " WHERE 1 = 1";

        // Example of use $mode
        //if ($mode == 1) $sql.= " AND s.client IN (1, 3)";
        //if ($mode == 2) $sql.= " AND s.client IN (2, 3)";

        $tmpobject = new spectacle($db);
        if ($tmpobject->ismultientitymanaged) $sql.= ' AND t.entity IN ('.getEntity('spectacle').')';
        if ($restictonsocid && (!DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) || $search_sale > 0) $sql.= " AND t.fk_soc = sc.fk_soc";
        if ($restictonsocid && $socid) $sql.= " AND t.fk_soc = ".$socid;
        if ($restictonsocid && $search_sale > 0) $sql.= " AND t.rowid = sc.fk_soc";		// Join for the needed table to filter by sale
        // Insert sale filter
        if ($restictonsocid && $search_sale > 0)
        {
            $sql .= " AND sc.fk_user = ".$search_sale;
        }
        if ($sqlfilters)
        {
            if (! DolibarrApi::_checkFilters($sqlfilters))
            {
                throw new RestException(503, 'Error when validating parameter sqlfilters '.$sqlfilters);
            }
	        $regexstring='\(([^:\'\(\)]+:[^:\'\(\)]+:[^:\(\)]+)\)';
            $sql.=" AND (".preg_replace_callback('/'.$regexstring.'/', 'DolibarrApi::_forge_criteria_callback', $sqlfilters).")";
        }

        $sql.= $db->order($sortfield, $sortorder);
        if ($limit)	{
            if ($page < 0)
            {
                $page = 0;
            }
            $offset = $limit * $page;

            $sql.= $db->plimit($limit + 1, $offset);
        }

        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);
            while ($i < $num)
            {
                $obj = $db->fetch_object($result);
                $spectacle_static = new spectacle($db);
                if($spectacle_static->fetch($obj->rowid)) {
                    $obj_ret[] = $this->_cleanObjectDatas($spectacle_static);
                }
                $i++;
            }
        }
        else {
            throw new RestException(503, 'Error when retrieve spectacle list');
        }
        if( ! count($obj_ret)) {
            throw new RestException(404, 'No spectacle found');
        }
		return $obj_ret;
    }

    /**
     * Create spectacle object
     *
     * @param array $request_data   Request datas
     * @return int  ID of spectacle
     *
     * @url	POST spectacles/
     */
    function post($request_data = null)
    {
        if(! DolibarrApiAccess::$user->rights->spectacle->create) {
            throw new RestException(401);
        }
        // Check mandatory fields
        $result = $this->_validate($request_data);

        foreach($request_data as $field => $value) {
            $this->spectacle->$field = $value;
        }
        if( ! $this->spectacle->create(DolibarrApiAccess::$user)) {
            throw new RestException(500);
        }
        return $this->spectacle->id;
    }

    /**
     * Update spectacle
     *
     * @param int   $id             Id of spectacle to update
     * @param array $request_data   Datas
     * @return int
     *
     * @url	PUT spectacles/{id}
     */
    function put($id, $request_data = null)
    {
        if(! DolibarrApiAccess::$user->rights->spectacle->create) {
            throw new RestException(401);
        }

        $result = $this->spectacle->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'spectacle not found');
        }

		if( ! DolibarrApi::_checkAccessToResource('spectacle',$this->spectacle->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

        foreach($request_data as $field => $value) {
            $this->spectacle->$field = $value;
        }

        if($this->spectacle->update($id, DolibarrApiAccess::$user))
            return $this->get($id);

        return false;
    }

    /**
     * Delete spectacle
     *
     * @param   int     $id   spectacle ID
     * @return  array
     *
     * @url	DELETE spectacle/{id}
     */
    function delete($id)
    {
    	if(! DolibarrApiAccess::$user->rights->spectacle->delete) {
			throw new RestException(401);
		}
        $result = $this->spectacle->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'spectacle not found');
        }

        if( ! DolibarrApi::_checkAccessToResource('spectacle',$this->spectacle->id)) {
            throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
        }

		if( !$this->spectacle->delete(DolibarrApiAccess::$user, 0))
        {
            throw new RestException(500);
        }

         return array(
            'success' => array(
                'code' => 200,
                'message' => 'spectacle deleted'
            )
        );
    }


    /**
     * Clean sensible object datas
     *
     * @param   object  $object    Object to clean
     * @return    array    Array of cleaned object properties
     */
    function _cleanObjectDatas($object)
    {
    	$object = parent::_cleanObjectDatas($object);

    	/*unset($object->note);
    	unset($object->address);
    	unset($object->barcode_type);
    	unset($object->barcode_type_code);
    	unset($object->barcode_type_label);
    	unset($object->barcode_type_coder);*/

    	return $object;
    }

    /**
     * Validate fields before create or update object
     *
     * @param array $data   Data to validate
     * @return array
     *
     * @throws RestException
     */
    function _validate($data)
    {
        $spectacle = array();
        foreach (spectacleApi::$FIELDS as $field) {
            if (!isset($data[$field]))
                throw new RestException(400, "$field field missing");
            $spectacle[$field] = $data[$field];
        }
        return $spectacle;
    }
}
