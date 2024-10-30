<?php
/**
 * Creates the options-page
 */
class CustomColumnsPage extends CustomColumnsBase
{
	/**
	 * @var		string default post type
	 *
	 * @since	1.0
	 */
	private $sDefaultPosttype = 'post';
	
	
	/**
	 * @var		array aviable post types
	 *
	 * @since	1.0
	 */
	private $aAviablePosttypes = null;
	
	
	/**
	 * Register admin-menu
	 * Init save-function
	 *
	 * @since	1.0
	 */
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'addMenuPages' ) );
		add_action( 'admin_init', array( $this, 'saveOptions' ) );
	}
	
	
	/**
	 * Save options page
	 *
	 * @since	1.0
	 */
	public function saveOptions()
	{
		if( ! isset( $_POST[ 'custom-column-check' ] ) || $_POST[ 'custom-column-check' ] != 1 ) return;
		if( ! isset( $_POST[ '_wpnonce' ] ) || check_admin_referer( 'custom-columns' ) == false ) return;
		if( ! isset( $_POST[ 'action' ] ) || $_POST[ 'action' ] != 'save' ) return;
		
		
		// Create options-object
		$oOptions = new stdClass();
		
		$oOptions->aOptions = $_POST[ 'aOptions' ];
		$oOptions->aTabs = $_POST[ 'aTabs' ];
		
		$this->savePosttypeOptions( $oOptions );
	}
	
	
	/**
	 * Get the current post type
	 *
	 * If the form is submittet, set the post type
	 * if not, set to default
	 *
	 * @OVERRIDE
	 *
	 * @since	1.0
	 *
	 * @return	string
	 */
	protected function getCurrentPosttype()
	{
		if( $this->sCurrentPosttype === null ){
			$this->sCurrentPosttype = $this->sDefaultPosttype;
			
			if( isset( $_POST[ 'posttype' ] ) ){
				$this->sCurrentPosttype = (string) $_POST[ 'posttype' ];
			}
		}
		
		return $this->sCurrentPosttype;	
	}
	 
	
	
	/**
	 * Add menupages
	 * 
	 * @since	1.0
	 *
	 * @return	void
	 */
	public function addMenuPages()
	{
		$sMenuSlug = add_options_page(
			__( 'Custom Columns Options', 'custom-columns' ),
			__( 'Custom Columns', 'custom-columns' ),
			CUSTOM_COLUMNS_RIGHTS,
			'custom-columns',
			array( $this, 'outputOptionsPage' )
		);
		
		// Add scripts and styles
		add_action( 'admin_print_scripts-' . $sMenuSlug, array( $this, 'addCSS' ) );
		add_action( 'admin_print_scripts-' . $sMenuSlug, array( $this, 'addJS' ) );
	}
	
	
	
	/**
	 * Returns aviable post types
	 *
	 * @since	1.0
	 * @updated	1.1
	 *
	 * @return	array
	 */
	private function getPosttypes()
	{
		if( $this->aAviablePosttypes === null ){
			$aPosttypes = get_post_types(array(
				'show_ui'	=>	true,
			), 'objects' );
			
			// rewrite for medias
			$aMedia = $aPosttypes[ 'attachment' ];
			unset( $aPosttypes[ 'attachment' ] );
			$aPosttypes[ 'media' ] = $aMedia;
			
			$this->aAviablePosttypes = $aPosttypes;
		}
		
		return $this->aAviablePosttypes;
	}
	
	
	/**
	 * Outputs the options page
	 *
	 * @since	1.0
	 *
	 */
	public function outputOptionsPage()
	{
		$oConfig = $this->getPosttypeOptions();
		?>
		<div class="wrap nav-menus-php">
			<h2><?php _e( 'Custom Columns Options', 'custom-columns' ); ?></h2>
		
			<div class="manage-menus">
				<form action="" method="post">
					<?php wp_nonce_field( 'custom-columns' ); ?>
					<input type="hidden" value="edit" name="action" />
					<input type="hidden" value="1" name="custom-column-check" />
					<label class="selected-menu" for="posttype">
						<?php _e( 'Choose Posttype', 'custom-columns' ); ?>
					</label>
					
					<select id="posttype" name="posttype">
						<?php foreach( $this->getPosttypes() as $sSlug => $oPosttype ): ?>
							<option <?php selected( $this->getCurrentPosttype(), $sSlug ); ?> value="<?php echo $sSlug; ?>"><?php echo $oPosttype->label; ?></option>
						<?php endforeach; ?>
					</select>

					<span class="submit-btn">
						<input type="submit" class="button-secondary" value="<?php _e( '- Choose -', 'custom-columns' ); ?>" />
					</span>
					
				</form>
			</div>
			
			<form method="post" action="">
				<?php wp_nonce_field( 'custom-columns' ); ?>
				<input type="hidden" value="save" name="action" />
				<input type="hidden" value="<?php echo $this->getCurrentPosttype(); ?>" name="posttype" />
				<input type="hidden" value="1" name="custom-column-check" />
			
			
				<div id="nav-menus-frame">
					<div id="menu-settings-column" class="metabox-holder">
						<div class="clear"></div>
						
						<div id="side-sortables" class="accordion-container">
							<ul class="outer-border">
								<li id="cc_options" class="control-section accordion-section open top">
									<h3 class="accordion-section-tittle hndle" title="<?php _e( 'Options', 'custom-columns' ); ?>">
										<?php _e( 'Options', 'custom-columns' ); ?>
									</h3>
									<div class="accordion-section-content">
										<div class="inside">
											<h4><?php _e( 'Column behavior', 'custom-columns' ); ?></h4>
											<label for="cco_add_column">
												<input type="radio" value="0" <?php checked( $oConfig->aOptions[ 'iColumnBehavior' ], 0 ); ?> name="aOptions[iColumnBehavior]" /> <?php _e( 'Add Columns', 'custom-columns' ); ?>
											</label><br />
											<label style="display:none;" for="cco_replace_column">
												<input type="radio" disabled="disabled" value="1" <?php checked( $oConfig->aOptions[ 'iColumnBehavior' ], 1 ); ?> name="aOptions[iColumnBehavior]" /> <?php _e( 'Replace Columns', 'custom-columns' ); ?>
											</label>
										</div>
									</div>
								</li>
							</ul>
						</div>
					</div>
					
					<div id="menu-management-liquid">
						<div id="menu-management">
							<div class="menu-edit">
								<div id="nav-menu-header">
									<div class="major-publishing-actions">
										<h3 class="column-manager">
											<?php _e( 'Manage Columns', 'custom-columns' ); ?>	
										</h3>
										<div class="publishing-action">
											<input type="submit" class="button button-pimary" value="<?php _e( 'Save', 'custom-columns' ); ?>" />
										</div>
									</div>
								</div>
								<div id="custom-columns-tabs">
									<div id="add-column" class="light">
										<span></span>
									</div>
									<div id="columns-to-left" class="light">
										<span></span>
									</div>
									<div id="columns-tabber">
										<ul class="tabs">
											
										</ul>
									</div>
									<div id="columns-to-right" class="light">
										<span></span>
									</div>
								</div>
								<div id="columns-tab-content">

								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
			
			<div class="donate">
				<?php _e( 'If you like the Plugin, please support my future development by a "Thank You" via PayPal (thankyou@florian-palme.de) or send me a gift via <a href="http://www.amazon.de/registry/wishlist/2TY8FR3NGUC6A" target="_blank" title="Amazon">Amazon</a>.', 'custom-columns' ); ?>	
			</div>
			
			<!-- JS Templates -->
			<textarea id="templateTab" style="display:none;"><?php $this->addTemplateTab(); ?></textarea>
			<textarea id="templateTabContent" style="display:none;"><?php $this->addTemplateTabContent(); ?></textarea>
			<textarea id="templateElement" style="display:none;"><?php $this->addTemplateElement(); ?></textarea>
			<div class="clear"></div>
		</div>
		<script language="javascript" type="text/javascript">
		jQuery(function(){
			// add saved tabs to the editor
			<?php foreach( $oConfig->aTabs as $sIdent => $aTab ): ?>
			customcolumns.addTab( "<?php echo $sIdent; ?>", "<?php echo esc_attr( $aTab[ 'sTitle' ] ); ?>" );
			
				<?php if( isset( $aTab[ 'aElements' ] ) ): ?>
					<?php foreach( $aTab[ 'aElements' ] as $sElementIdent => $aElement ): ?>
					customcolumns.addElement( "<?php echo $sIdent; ?>", "<?php echo $sElementIdent; ?>" );
					customcolumns.setElementOptions( "<?php echo $sIdent; ?>", "<?php echo $sElementIdent; ?>", "<?php echo $aElement[ 'sType' ]; ?>", '<?php echo json_encode( $aElement[ $aElement[ 'sType' ] ] ); ?>' );
					<?php endforeach; ?>
				<?php endif; ?>
				
				<?php if( isset( $aTab[ 'aOptions' ] ) ): ?>
					<?php foreach( $aTab[ 'aOptions' ] as $sOptionType => $aOption ): ?>
					customcolumns.setTabOptions( "<?php echo $sIdent; ?>", "<?php echo $sOptionType; ?>", '<?php echo json_encode( $aOption ); ?>' );
					<?php endforeach; ?>
				<?php endif; ?>
				
			<?php endforeach; ?>
		});	
		</script>
		<div class="clear"></div>
		<?php	
	}
	
	
	/**
	 * Adds CSS to the options page
	 *
	 * @since	1.0
	 */
	public function addCSS()
	{
		wp_enqueue_style(
			'custom-columns',
			CUSTOM_COLUMNS_PLUGINS_DIR . 'src/css/style.css',
			array(),
			'1.0'
		);
	}
	
	/**
	 * Adds JS to the options page
	 *
	 * @since	1.0
	 */
	public function addJS()
	{
		wp_enqueue_script(
			'custom-columns',
			CUSTOM_COLUMNS_PLUGINS_DIR . 'src/js/main.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			'1.0'
		);
		
		wp_localize_script( 'custom-columns', 'ccLang', array(
			'defaultTabTitle'	=>	__( 'Edit Column-Title' ),
		));
	}
	
	
	/**
	 * Return tab template
	 *
	 * @since	1.0
	 */
	protected function addTemplateTab()
	{
		?>
		<li data-tab-group="%%sIdent%%">
			<span class="inline-edit">%%sTitle%%</span>
			<input type="text" value="%%sTitle%%" name="aTabs[%%sIdent%%][sTitle]" class="hidden" />
			<span class="remove" data-tab-group="%%sIdent%%">&nbsp;</span>
		</li>
		<?php
	}
	
	
	/**
	 * Return the Column Tabs Content
	 *
	 * @since	1.0
	 * @updated	1.1
	 */
	protected function addTemplateTabContent()
	{
		$aMetakeys = $this->getMetakeysForPosttype();
		$aTaxonomies = $this->getPosttypeTaxonomies();
		?>
		<div class="post-body" data-tab-group="%%sIdent%%">
				<div class="post-body-content">
					<div class="options">
						<span class="toggle closed" data-element-group="%%sElementIdent%%"></span>
						<h4 class="element-manager"><?php _e( 'Options', 'custom-columns' ); ?></h4>
					</div>
					<!-- options content -->
					<div class="options-content">
						<!-- sortable -->
						<div class="options-content-sortable">
							<label for="tab-options-%%sIdent%%-options-sortable-makesortable"><?php _e( 'Make column sortable:', 'custom-columns' ); ?>
								<input type="hidden" value="0" name="aTabs[%%sIdent%%][aOptions][sortable][blMakeSortable]" />
								<input type="checkbox" value="1" name="aTabs[%%sIdent%%][aOptions][sortable][blMakeSortable]" id="tab-options-%%sIdent%%-options-sortable-makesortable" />
							</label>
							
							<!-- sortable options -->
							<div class="options-content-sortable-content" rel="options">
								<label for="tab-options-%%sIdent%%-options-sortable-type"><?php _e( 'Choose sort type', 'custom-columns' ); ?></label>
								<select data-tab-group="%%sIdent%%" id="tab-options-%%sIdent%%-options-sortable-type" name="aTabs[%%sIdent%%][aOptions][sortable][sType]">
									<option value="posts_table"><?php _e( 'Posts-Table', 'custom-columns' ); ?></option>
									
									<?php if( count( $aMetakeys ) ): ?>	
									<option value="post_meta"><?php _e( 'Post Meta', 'custom-columns' ); ?></option>
									<?php endif; ?>	
									
									<?php if( count( $aTaxonomies ) ): ?>
									<option value="taxonomies"><?php _e( 'Taxonomy', 'custom-columns' ); ?></option>
									<?php endif; ?>
								</select>
								
								<!-- sortable types -->
								<div id="tab-options-%%sIdent%%-options-sortable-type-posts_table">
									<label for="tab-options-%%sIdent%%-options-sortable-type-posts_table-value"><?php _e( 'Choose table column', 'custom-columns' ); ?></label>
									<select id="tab-options-%%sIdent%%-options-sortable-type-posts_table-value" name="aTabs[%%sIdent%%][aOptions][sortable][posts_table]">
										<option value="ID"><?php _e( 'ID', 'custom-columns' ); ?></option>
										<option value="author"><?php _e( 'Author', 'custom-columns' ); ?></option>
										<option value="post_date"><?php _e( 'Date', 'custom-columns' ); ?></option>
										<option value="post_date_gmt"><?php _e( 'Date GMT', 'custom-columns' ); ?></option>
										<option value="post_content"><?php _e( 'Content', 'custom-columns' ); ?></option>
										<option value="post_title"><?php _e( 'Title', 'custom-columns' ); ?></option>
										<option value="post_excerpt"><?php _e( 'Excerpt', 'custom-columns' ); ?></option>
										<option value="post_status"><?php _e( 'Status', 'custom-columns' ); ?></option>
										<option value="comment_status"><?php _e( 'Comment status', 'custom-columns' ); ?></option>
										<option value="ping_status"><?php _e( 'Ping status', 'custom-columns' ); ?></option>
										<option value="post_password"><?php _e( 'Post password', 'custom-columns' ); ?></option>
										<option value="post_name"><?php _e( 'Slug', 'custom-columns' ); ?></option>
										<option value="to_ping"><?php _e( 'Ping', 'custom-columns' ); ?></option>
										<option value="pinged"><?php _e( 'Pinged', 'custom-columns' ); ?></option>
										<option value="post_modified"><?php _e( 'Modified', 'custom-columns' ); ?></option>
										<option value="post_modified_gmt"><?php _e( 'Modified GMT', 'custom-columns' ); ?></option>
										<option value="post_content_filtered"><?php _e( 'Content filtered', 'custom-columns' ); ?></option>
										<option value="post_parent"><?php _e( 'Post parent', 'custom-columns' ); ?></option>
										<option value="guid"><?php _e( 'Url', 'custom-columns' ); ?></option>
										<option value="menu_order"><?php _e( 'Menu order', 'custom-columns' ); ?></option>
										<option value="post_type"><?php _e( 'Post type', 'custom-columns' ); ?></option>
										<option value="post_mime_type"><?php _e( 'Post mime type', 'custom-columns' ); ?></option>
										<option value="comment_count"><?php _e( 'Comment count', 'custom-columns' ); ?></option>
									</select>
								</div>
								
								<div id="tab-options-%%sIdent%%-options-sortable-type-post_meta">
									<label for="tab-options-%%sIdent%%-options-sortable-type-post_meta-value"><?php _e( 'Choose a meta key', 'custom-columns' ); ?></label>
									<select id="tab-options-%%sIdent%%-options-sortable-type-post_meta-value" name="aTabs[%%sIdent%%][aOptions][sortable][post_meta]">
										<?php foreach( $aMetakeys as $oMetakey ): ?>
										<option value="<?php echo $oMetakey->meta_key; ?>"><?php echo $oMetakey->meta_key; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								
								<div id="tab-options-%%sIdent%%-options-sortable-type-taxonomies">
									<label for="tab-options-%%sIdent%%-options-sortable-type-taxonomies-value"><?php _e( 'Choose a taxonomy', 'custom-columns' ); ?></label>
									<select id="element_type_taxonomies_%%sElementIdent%%_taxonomies" name="aTabs[%%sIdent%%][aOptions][sortable][taxonomies]">
										<?php foreach( $aTaxonomies as $sTaxonomyId => $oTaxonomy ): ?>
										<option value="<?php echo $sTaxonomyId; ?>"><?php echo $oTaxonomy->labels->name; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								
								
								<!-- // sortable types -->
							</div>
							<!-- // sortable options -->
						</div>
						<!-- // sortable -->
					</div>
					<!-- // options content -->
					<div class="top-bar">
						<h4 class="element-manager"><?php _e( 'Elements', 'custom-columns' ); ?></h4>
						
						<span class="add-field button" data-tab-group="%%sIdent%%">&nbsp;</span>	
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
					<div class="sortable">
						
					</div>
				</div>
			</div>
		<?php	
	}
	
	
	/**
	 * Return all meta_keys, assigned to a post type
	 *
	 * @since	1.0
	 *
	 * @param	string $sPosttype
	 *
	 * @return	array
	 */
	protected function getMetakeysForPosttype( $sPosttype = null )
	{
		global $wpdb;
		
		$sPosttype = ( $sPosttype === null ) ? $this->getCurrentPosttype() : $sPosttype;
		
		$sSQL = 'SELECT
					postmeta.meta_key
				FROM
					' . $wpdb->prefix . 'postmeta as postmeta
				INNER JOIN
					' . $wpdb->prefix . 'posts as posts
				ON
					postmeta.post_id = posts.ID
				WHERE
					posts.post_type = %s
				GROUP BY
					postmeta.meta_key
		';
		
		$sSQL = $wpdb->prepare( $sSQL, array( $sPosttype ) );
		
		return $wpdb->get_results( $sSQL );
	}
	
	
	
	/**
	 * Return all taxonomies attached to a posttype
	 *
	 * @since	1.0
	 *
	 * @param	string $sPosttype
	 *
	 * @return	array
	 */
	protected function getPosttypeTaxonomies( $sPosttype = null )
	{
		$sPosttype = ( $sPosttype === null ) ? $this->getCurrentPosttype() : $sPosttype;
		
		return get_object_taxonomies( $sPosttype, 'object' );
	}
	
	
	
	
	/**
	 * Return element template
	 *
	 * @since	1.0
	 */
	protected function addTemplateElement()
	{
		$aMetakeys = $this->getMetakeysForPosttype();
		$aTaxonomies = $this->getPosttypeTaxonomies();
		?>
		<div class="column-element" data-element-group="%%sElementIdent%%">
			<div class="header">
				<span class="toggle" data-element-group="%%sElementIdent%%"></span>
				<label for="element-type-%%sElementIdent%%"><?php _e( 'Element Type', 'custom-columns' ); ?></label>
				<select id="element-type-%%sElementIdent%%" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][sType]">
					<option value="posts_table"><?php _e( 'Posts-Table', 'custom-columns' ); ?></option>	
					<option value="post_meta"><?php _e( 'Post Meta', 'custom-columns' ); ?></option>	
					<option value="taxonomies"><?php _e( 'Taxonomy', 'custom-columns' ); ?></option>	
					<option value="linebreak"><?php _e( 'Linebreak', 'custom-columns' ); ?></option>	
					<option value="html"><?php _e( 'Custom HTML', 'custom-columns' ); ?></option>	
				</select>
				<span class="remove button" data-element-group="%%sElementIdent%%"></span>
			</div>	
			<div class="content">
				
				<!-- posts table -->
				<div class="element_type_posts_table">
					<label for="element_type_posts_table_%%sElementIdent%%_column"><?php _e( 'Choose table column', 'custom-columns' ); ?></label>
					<select id="element_type_posts_table_%%sElementIdent%%_column" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][posts_table][sColumn]">
						<option value="ID"><?php _e( 'ID', 'custom-columns' ); ?></option>
						<option value="post_author"><?php _e( 'Author', 'custom-columns' ); ?></option>
						<option value="post_date"><?php _e( 'Date', 'custom-columns' ); ?></option>
						<option value="post_date_gmt"><?php _e( 'Date GMT', 'custom-columns' ); ?></option>
						<option value="post_content"><?php _e( 'Content', 'custom-columns' ); ?></option>
						<option value="post_title"><?php _e( 'Title', 'custom-columns' ); ?></option>
						<option value="post_excerpt"><?php _e( 'Excerpt', 'custom-columns' ); ?></option>
						<option value="post_status"><?php _e( 'Status', 'custom-columns' ); ?></option>
						<option value="comment_status"><?php _e( 'Comment status', 'custom-columns' ); ?></option>
						<option value="ping_status"><?php _e( 'Ping status', 'custom-columns' ); ?></option>
						<option value="post_password"><?php _e( 'Post password', 'custom-columns' ); ?></option>
						<option value="post_name"><?php _e( 'Slug', 'custom-columns' ); ?></option>
						<option value="to_ping"><?php _e( 'Ping', 'custom-columns' ); ?></option>
						<option value="pinged"><?php _e( 'Pinged', 'custom-columns' ); ?></option>
						<option value="post_modified"><?php _e( 'Modified', 'custom-columns' ); ?></option>
						<option value="post_modified_gmt"><?php _e( 'Modified GMT', 'custom-columns' ); ?></option>
						<option value="post_content_filtered"><?php _e( 'Content filtered', 'custom-columns' ); ?></option>
						<option value="post_parent"><?php _e( 'Post parent', 'custom-columns' ); ?></option>
						<option value="guid"><?php _e( 'Url', 'custom-columns' ); ?></option>
						<option value="menu_order"><?php _e( 'Menu order', 'custom-columns' ); ?></option>
						<option value="post_type"><?php _e( 'Post type', 'custom-columns' ); ?></option>
						<option value="post_mime_type"><?php _e( 'Post mime type', 'custom-columns' ); ?></option>
						<option value="comment_count"><?php _e( 'Comment count', 'custom-columns' ); ?></option>
					</select>
				</div>
				<!-- ende posts table -->
				
				
				<!-- post meta -->
				<div class="element_type_post_meta">
					<?php if( count( $aMetakeys ) ): ?>
					<label for="element_type_post_meta_%%sElementIdent%%_metakey"><?php _e( 'Choose a meta key', 'custom-columns' ); ?></label>
					<select id="element_type_post_meta_%%sElementIdent%%_metakey" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][post_meta][sMetakey]">
						<?php foreach( $aMetakeys as $oMetakey ): ?>
						<option value="<?php echo $oMetakey->meta_key; ?>"><?php echo $oMetakey->meta_key; ?></option>
						<?php endforeach; ?>
					</select>
					<?php else: ?>
					<input type="hidden" value="" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][post_meta][sMetakey]" />
					<?php _e( 'Could not find any meta key for this posttype', 'custom-column' ); ?>
					<?php endif; ?>
				</div>
				<!-- ende post meta -->
				
				
				<!-- taxonomies -->
				<div class="element_type_taxonomies">
					<?php if( count( $aTaxonomies ) ): ?>
					<label for="element_type_taxonomies_%%sElementIdent%%_taxonomy"><?php _e( 'Choose a meta key', 'custom-columns' ); ?></label>
					<select id="element_type_taxonomies_%%sElementIdent%%_taxonomy" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][taxonomies][sTaxonomy]">
						<?php foreach( $aTaxonomies as $sTaxonomyId => $oTaxonomy ): ?>
						<option value="<?php echo $sTaxonomyId; ?>"><?php echo $oTaxonomy->labels->name; ?></option>
						<?php endforeach; ?>
					</select>
					<br />
					<label for="element_type_taxonomies_%%sElementIdent%%_delimiter"><?php _e( 'Define delimiter', 'custom-columns' ); ?></label>
					<input id="element_type_taxonomies_%%sElementIdent%%_delimiter" type="text" class="regular-text" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][taxonomies][sDelimiter]" placeholder="<?php _e( 'Default: , ', 'custom-column' ); ?>" />
					<br />
					<label for="element_type_taxonomies_%%sElementIdent%%_linkto"><?php _e( 'Link to', 'custom-columns' ); ?></label>
					<select id="element_type_taxonomies_%%sElementIdent%%_linkto" class="regular-text" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][taxonomies][sLinkto]">
						<option value=""><?php _e( '- None -', 'custom-column' ); ?></option>
						<option value="admin_edit"><?php _e( 'Admin edit page', 'custom-column' ); ?></option>
					</select>
					<?php else: ?>
					<input type="hidden" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][taxonomies][sTaxonomy]" value="" />
					<input type="hidden" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][taxonomies][sDelimiter]" value="" />
					<input type="hidden" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][taxonomies][sLinkto]" value="" />
					<?php _e( 'Could not find any taxonomies for this posttype', 'custom-column' ); ?>
					<?php endif; ?>
				</div>
				<!-- ende taxonomies -->
				
				
				<!-- linebreak -->
				<div class="element_type_linebreak">
					<?php _e( 'There are no options for "Linebreak"', 'custom-columns' ); ?>	
				</div>
				<!-- //linebreak -->
				
				<!-- html -->
				<div class="element_type_html">
					<label for="element_type_html_%%sElementIdent%%_html"><?php _e( 'Enter HTML', 'custom-columns' ); ?></label>
					<input type="text" class="regular-text" id="element_type_html_%%sElementIdent%%_html" name="aTabs[%%sIdent%%][aElements][%%sElementIdent%%][html][sHTML]" />	
				</div>
				<!-- //html -->
			</div>
		</div>
		<?php	
	}
}