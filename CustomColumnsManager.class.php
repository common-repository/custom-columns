<?php
/**
 * Custom Columns Manager
 */
class CustomColumnsManager extends CustomColumnsBase
{
	/**
	 * saved options
	 *
	 * @since
	 *
	 * @var	array
	 */
	protected $aOptions = null;
	
	/**
	 * Constructor
	 *
	 * @since	1.0
	 * @updated	1.1
	 */
	public function __construct()
	{
		foreach( $this->getConfig() as $sPosttype => $oOptions ){
			// if no tab defined, continue
			if( ! count( $oOptions->aTabs ) ) continue;
			
			// we need other action/filters for media
			if( $sPosttype == 'media' ){
				add_filter( 'manage_media_columns', array( $this, 'addColumnHeader' ) );
				
				add_filter( 'manage_media_custom_column', array( $this, 'addColumnOutput' ), 10, 2 );
				
				// Register sortable
				add_filter( 'manage_upload_sortable_columns', array( $this, 'addSortableColumns' ) );
			} else {
				// Register column header
				add_filter( 'manage_' . $sPosttype . '_posts_columns', array( $this, 'addColumnHeader' ) );
				
				// Register column output
				add_filter( 'manage_' . $sPosttype . '_posts_custom_column', array( $this, 'addColumnOutput' ), 10, 2 );
				
				// Register sortable
				add_filter( 'manage_edit-' . $sPosttype . '_sortable_columns', array( $this, 'addSortableColumns' ) );
			}
		}
		
		// Handle sort
		add_filter( 'request', array( $this, 'handleSort' ) );
		
		add_filter( 'posts_clauses', array( $this, 'editPostsClauses' ), 10, 2 );
	}
	
	
	/**
	 * Handle post clauses
	 *
	 * @since	1.0
	 *
	 * @param	array $aClauses
	 * @param	object $oQuery
	 *
	 * @return	array
	 */
	public function editPostsClauses( $aClauses, $oQuery )
	{
		if( ! is_admin() ) return $aClauses;
		
		global $wpdb;

		$aTabs = $this->getPosttypeOptions( fpFunctions::getCurrentPosttype(true) )->aTabs;

		foreach( $aTabs as $sIdent => $aTab ){
			if( $oQuery->query_vars[ 'orderby' ] != $sIdent ) continue;

			if( $aTab[ 'aOptions' ][ 'sortable' ][ 'sType' ] == 'posts_table' ){
				// modifie sort-query...
				$aClauses[ 'orderby' ] = $wpdb->posts .' .' . $aTab[ 'aOptions' ][ 'sortable' ][ 'posts_table' ];
				$aClauses[ 'orderby' ] .= ' ' . $oQuery->query_vars[ 'order' ];
				
			} elseif( $aTab[ 'aOptions' ][ 'sortable' ][ 'sType' ] == 'taxonomies' ){
				$aClauses[ 'join' ] .= ' 
				LEFT JOIN 
					' . $wpdb->term_relationships . '
				ON
					' . $wpdb->posts . '.ID = ' . $wpdb->term_relationships . '.object_id
				LEFT JOIN
					' . $wpdb->term_taxonomy . '
				ON
					' . $wpdb->term_relationships . '.term_taxonomy_id = ' . $wpdb->term_taxonomy . '.term_taxonomy_id
				LEFT JOIN
					' . $wpdb->terms . '
				ON
					' . $wpdb->term_taxonomy . '.term_id = ' . $wpdb->terms . '.term_id';
				
				$aClauses[ 'where' ] .= $wpdb->prepare( ' AND ' . $wpdb->term_taxonomy . '.taxonomy = %s',
													$aTab[ 'aOptions' ][ 'sortable' ][ 'taxonomies' ] );
													
				$aClauses[ 'orderby' ] = $wpdb->terms . '.name ' . $oQuery->query_vars[ 'order' ];
				
				$aClauses[ 'groupby' ] = $wpdb->term_relationships . '.object_id';
			}
		}

		return $aClauses;
	}
	
	/**
	 * Handle sort
	 *
	 * @param	array $aVars
	 *
	 * @return	array
	 */
	public function handleSort( $aVars )
	{
		if( ! is_admin() ) return $aVars;
		if( ! isset( $aVars[ 'orderby' ] ) ) return $aVars;
		
		$aTabs = $this->getPosttypeOptions( fpFunctions::getCurrentPosttype(true) )->aTabs;
		
		foreach( $aTabs as $sIdent => $aTab ){
			if( $aVars[ 'orderby' ] != $sIdent ) continue;
			
			if( $aTab[ 'aOptions' ][ 'sortable' ][ 'sType' ] == 'posts_table' ){
				// Must be handled in editPostsClauses
				break;
			}elseif( $aTab[ 'aOptions' ][ 'sortable' ][ 'sType' ] == 'post_meta' ){
				$aVars[ 'orderby' ] = 'meta_value';
				$aVars[ 'meta_key' ] = $aTab[ 'aOptions' ][ 'sortable' ][ 'post_meta' ];
			}
		}
		
		return $aVars;
	}
	
	
	/**
	 * Make columns sortable
	 *
	 * @since	1.0
	 *
	 * @param	array $aColumns
	 *
	 * @return	array
	 */
	public function addSortableColumns( $aColumns )
	{
		// put all columns to the array and merge with default
		$aTabs = $this->getPosttypeOptions( fpFunctions::getCurrentPosttype(true) )->aTabs;
		
		foreach( $aTabs as $sIdent => $aTab ){
			if( $aTab[ 'aOptions' ][ 'sortable' ][ 'blMakeSortable' ] == 1 ){
				$aColumns[ $sIdent ] = $sIdent;
			}
		}
		
		return $aColumns;
	}
	
	
	/**
	 * Add column output
	 *
	 * @since	1.0
	 *
	 * @param	string $sColumn
	 * @param	integer $iID
	 */
	public function addColumnOutput( $sColumn, $iID )
	{
		$aTabs = $this->getPosttypeOptions( fpFunctions::getCurrentPosttype(true) )->aTabs;
		
		if( isset( $aTabs[ $sColumn ] ) ){
			$aTab = $aTabs[ $sColumn ];
			
			foreach( $aTab[ 'aElements' ] as $aElement ){
				$this->_columnOutput( $aElement, $iID );
			}
		}
	}
	
	
	/**
	 * the acutal column output
	 *
	 * @since	1.0
	 *
	 * @param	array $aElement
	 * @param	integer $iID
	 */
	protected function _columnOutput( $aElement, $iID )
	{
		switch( $aElement[ 'sType' ] ){
			case 'posts_table':
					global $post;

					$sColumn = $aElement[ 'posts_table' ][ 'sColumn' ];
					
					if( isset( $post->$sColumn ) ){
						echo $post->$sColumn;
					}
				break;	
				
			case 'post_meta':
					echo get_post_meta( $iID, $aElement[ 'post_meta' ][ 'sMetakey' ], true );
				break;
				
			case 'taxonomies':
					$aTermList = wp_get_post_terms( $iID, $aElement[ 'taxonomies' ][ 'sTaxonomy' ], array( 'fields' => 'all' ) );
					$iTermCount = count( $aTermList );
					$sDelimiter = ( $aElement[ 'taxonomies' ][ 'sDelimiter' ] == '' ) ? ', ' : $aElement[ 'taxonomies' ][ 'sDelimiter' ];
					
					// output all terms
					foreach( $aTermList as $iIndex => $oTerm ){
						$sOutput = '%1$s';

						if( $aElement[ 'taxonomies' ][ 'sLinkto' ] != '' ){
							// set link
							$sOutput = '<a ';
							
							// build link based on linkto-type
							switch( $aElement[ 'taxonomies' ][ 'sLinkto' ] ){
								case 'admin_edit':
										// href
										$sOutput .= 'href="';
										$sOutput .= get_admin_url();
										$sOutput .= 'edit-tags.php?action=edit&taxonomy=';
										$sOutput .= $oTerm->taxonomy;
										$sOutput .= '&tag_ID=' . $oTerm->term_taxonomy_id;
										$sOutput .= '&post_type=' . fpFunctions::getCurrentPosttype();
										$sOutput .= '" ';
									break;
							}
							
							$sOutput .= '>%1$s</a>';
						}
						
						printf( $sOutput, $oTerm->name );
						
						if( $iIndex + 1 < $iTermCount ){
							echo $sDelimiter;
						}	
					}
				break;
				
			case 'linebreak':
					echo '<br />';
				break;
				
			case 'html':
					echo $aElement[ 'html' ][ 'sHTML' ];
				break;
		}
	}
	
	
	/**
	 * Add column header
	 *
	 * @since	1.0
	 *
	 * @param	array $aColumns
	 *
	 * @return	array $aColumns
	 */
	public function addColumnHeader( $aColumns )
	{
		$aCustomColumns = array();

		// put all columns to the array and merge with default
		$aTabs = $this->getPosttypeOptions( fpFunctions::getCurrentPosttype(true) )->aTabs;

		foreach( $aTabs as $sIdent => $aTab ){
			$aCustomColumns[ $sIdent ] = __( $aTab[ 'sTitle' ], 'custom-columns-user' );	
		}
		
		$aColumns = array_merge( $aColumns, $aCustomColumns );
		
		return $aColumns;
	}
	
	
	/**
	 * Return class-saved config options
	 *
	 * @since	1.0
	 *
	 * @return	array
	 */
	protected function getConfig()
	{
		if( $this->aOptions === null ){
			$this->aOptions = $this->getCustomColumnsConfig();
		}
		
		return $this->aOptions;
	}
}