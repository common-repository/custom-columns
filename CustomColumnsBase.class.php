<?php
/**
 * Base class for custom-columns
 */
class CustomColumnsBase
{
	/**
	 * @var		string current post type
	 *
	 * @since	1.0
	 */
	protected $sCurrentPosttype = null;
	
	
	/**
	 * Load Posttype-Options from database
	 *
	 * @since	1.0
	 *
	 * @param	string $sPosttype
	 *
	 * @return	object
	 */
	protected function getPosttypeOptions( $sPosttype = null )
	{
		$sPosttype = ( $sPosttype === null ) ? $this->getCurrentPosttype() : $sPosttype;
		
		$aOption = $this->getCustomColumnsConfig();
		
		if( $sPosttype === null || ! isset( $aOption[ $sPosttype ] ) ){
			return $this->getDefaultColumnsConfig();	
		}
		
		return $aOption[ $sPosttype ];
	}
	
	
	/**
	 * Save options for a post type
	 *
	 * @since	1.0
	 *
	 * @param	object $oOptions
	 * @param	string $sPosttype
	 */
	protected function savePosttypeOptions( $oOptions, $sPosttype = null )
	{
		$sPosttype = ( $sPosttype === null ) ? $this->getCurrentPosttype() : $sPosttype;
		
		$aOption = $this->getCustomColumnsConfig();
		
		$aOption[ $sPosttype ] = $oOptions;
		
		$this->saveCustomColumnsConfig( $aOption );
	}
	
	
	/**
	 * Save the options
	 *
	 * @since	1.0
	 *
	 * @param	array $aOptions
	 */
	protected function saveCustomColumnsConfig( $aOptions )
	{
		$sOption = maybe_serialize( $aOptions );
		
		update_option( 'custom-columns', $sOption );
	}
	
	/**
	 * Return the saved options
	 *
	 * @since	1.0
	 *
	 * @return	array
	 */
	protected function getCustomColumnsConfig()
	{
		$sOption = get_option( 'custom-columns', array() );
		$aOption = maybe_unserialize( $sOption );
		
		return $aOption;
	}
	
	
	/**
	 * Return default config for custom columns
	 *
	 * @since	1.0
	 *
	 * @return	object
	 */
	protected function getDefaultColumnsConfig()
	{
		// wrapper-class
		$oConfig = new stdClass();
		
		// config options
		$aConfigOptions = array();
		
		$aConfigOptions[ 'iColumnBehavior' ] = 0;
		
		// Tabs
		$aTabs = array();
		
		
		// add config options to wrapper
		$oConfig->aOptions = $aConfigOptions;
		
		// add tabs
		$oConfig->aTabs = $aTabs;
		
		return $oConfig;
	}
	
	
	/**
	 * Get the current post type
	 *
	 * @since	1.0
	 *
	 * @return	string
	 */
	protected function getCurrentPosttype()
	{
		return $this->sCurrentPosttype;	
	}
}