<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 *
 * @defgroup    Forum Forum
 * @ingroup     TridentModules
 *
 * @{
 */

require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");

class BxForumGrid extends BxTemplGrid
{
    protected $_oModule;

    public function __construct ($aOptions, $oTemplate = false)
    {
        parent::__construct ($aOptions, $oTemplate);

        $this->_oModule = BxDolModule::getInstance('bx_forum');
        $this->_sDefaultSortingOrder = 'DESC';
    }

    public function setBrowseType($sType)
    {
    	$sField = '';
    	$sDir = 'DESC';
    	switch($sType) {
    		case 'new':
                $sField = 'added';
                break;

			case 'updated':
				$sField = 'changed';
                break;

			case 'latest':
				$sField = 'lr_timestamp';
                break;

			case 'top':
				$sField = 'comments';
                break;

            case 'popular':
            	$sField = 'views';
                break;
    	}

    	$this->_aOptions['field_order'] = $sField;
    }

    protected function _getActionAdd ($sType, $sKey, $a, $isSmall = false, $isDisabled = false, $aRow = array())
    {
        $sUrl = BX_DOL_URL_ROOT . BxDolPermalinks::getInstance()->permalink('page.php?i=' . $this->_oModule->_oConfig->CNF['URI_ADD_ENTRY']);

        unset($a['attr']['bx_grid_action_independent']);
        $a['attr'] = array_merge($a['attr'], array(
    		"onclick" => "window.open('" . $sUrl . "','_self');"
    	));

        return parent::_getActionDefault ($sType, $sKey, $a, $isSmall, $isDisabled, $aRow);
    }

    protected function _getRowHeader ()
    {
    	return array();
    }

    protected function _getCellAuthor($mixedValue, $sKey, $aField, $aRow)
    {
    	$mixedValue = $this->_oModule->_oTemplate->getEntryAuthor($aRow);

        return parent::_getCellDefault($mixedValue, $sKey, $aField, $aRow);
    }

    protected function _getCellLrTimestamp($mixedValue, $sKey, $aField, $aRow)
    {
        $mixedValue = $this->_oModule->_oTemplate->getEntryPreview($aRow);

        return parent::_getCellDefault($mixedValue, $sKey, $aField, $aRow);
    }

    protected function _getCellComments($mixedValue, $sKey, $aField, $aRow)
    {
        $mixedValue = $this->_oModule->_oTemplate->getEntryLabel($aRow, array('show_count' => 1));

        return parent::_getCellDefault($mixedValue, $sKey, $aField, $aRow);
    }

    protected function _addJsCss()
    {
        parent::_addJsCss();

        $this->_oModule->_oTemplate->addJs('main.js');
        $this->_oModule->_oTemplate->addCss(array('main-media-tablet.css', 'main-media-desktop.css'));
    }

    protected function _getDataSql($sFilter, $sOrderField, $sOrderDir, $iStart, $iPerPage)
    {
    	$sJoinClause = $sWhereClause = '';

    	//--- Check status
    	$sWhereClause .= " AND `" . $this->_oModule->_oConfig->CNF['FIELD_STATUS'] . "`='active'";
    	$sWhereClause .= " AND `" . $this->_oModule->_oConfig->CNF['FIELD_STATUS_ADMIN'] . "`='active'";

    	//--- Check privacy
		$sPrivacy = $this->_oModule->_oConfig->CNF['OBJECT_PRIVACY_VIEW'];
		$oPrivacy = BxDolPrivacy::getObjectInstance($sPrivacy);
		$aCondition = $oPrivacy ? $oPrivacy->getContentPublicAsSQLPart() : array();
		if(isset($aCondition['join']))
			$sJoinClause .= $aCondition['join'];
		if(isset($aCondition['where']))
			$sWhereClause .= $aCondition['where'];

		$this->_aOptions['source'] = sprintf($this->_aOptions['source'], $sJoinClause, $sWhereClause);
		return parent::_getDataSql($sFilter, $sOrderField, $sOrderDir, $iStart, $iPerPage);
    }

    protected function _getDataSqlOrderClause($sOrderByFilter, $sOrderField, $sOrderDir, $bFieldsOnly = false)
    {
    	$sOrder = parent::_getDataSqlOrderClause($sOrderByFilter, $sOrderField, $sOrderDir, true);

    	return " ORDER BY `" . $this->_oModule->_oConfig->CNF['FIELD_STICK'] . "` DESC, " . $sOrder;
    }
}

/** @} */