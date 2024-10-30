<?php
class CustomColumns
{
	/**
	 * @var		string		The Plugin-Version
	 * @since	0.1
	 */
	const VERSION = '0.3';
	
	
	/**
	 * Class-Construct
	 *
	 * Set required data, add_action's
	 *
	 * @since	1.0
	 */
	public function __construct()
	{
		// Init options-page
		$oPage = new CustomColumnsPage();
		$oManager = new CustomColumnsManager();
	}
}