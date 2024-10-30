<?php
if( ! class_exists( 'fpFunctions' ) ){
/**
 * Functions for fpModules
 */
class fpFunctions
{
	/**
	* Prüft, ob die angezeigt Post-Type-Seite "neu" ist.
	* @since		0.1
	* @access		public
	* @return 		bool
	*/
	public static function isPosttypeNew()
	{
		if( strpos( $_SERVER['REQUEST_URI'], 'post-new.php' ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	* Checks, if it is a edit-posttype-page
	*
	* @since		0.1
	*
	* @access		public
	*
	* @return 		bool
	*/
	public static function isPosttypeOverview()
	{
		if( strpos( $_SERVER['REQUEST_URI'], 'edit.php' ) ) {
			return true;
		}
		
		return false;
	}
	
	
	/**
	* Prüft, ob die aktuelle aufgerufene Seite die Bearbeitung eines Posts zulässt
	* @since		0.1
	* @access		public
	* @return 		bool
	*/
	public static function isPosttypeEdit()
	{
		if( strpos( $_SERVER['REQUEST_URI'], 'post.php' ) ) {
			return true;
		}
		
		return false;
	}
	
	
	/**
	* Prüft, ob der angegeben Post-Type der aktuell aufgerufene ist..
	* @since		0.1
	* @access		public
	* @return		bool
	*/
	public static function isPosttype( $posttype ){
		if( fpFunctions::isPosttypePage() ){
			if( isset( $_GET['post_type'] ) ) {
				if ( $_GET['post_type'] == $posttype ) {
					return true;
				}
			} elseif( get_post_type( @$_GET['post'] ) == $posttype ) {
				return true;
			}
		}
		
		return false;
	}
	
	
	/**
	* Prüft, ob die aktuelle aufgerufene Seite eine Posttype-Seite ist
	* @since		0.1
	* @access		public
	* @return		bool
	*/
	public static function isPosttypePage(){
		$return = false;
		
		if( fpFunctions::isPosttypeNew() ){
			$return = true;
		} elseif( fpFunctions::isPosttypeOverview() ) {
			$return = true;
		} elseif( fpFunctions::isPosttypeEdit() ){
			$return = true;
		}
		
		return $return;
	}
	
	
	/**
	 * gets the current post type in the WordPress Admin
	 *
	 * @since	1.0
	 * @updated	1.1
	 *
	 * @param	boolean $blRewriteAttachment
	 *
	 * @return	string
	 */
	public static function getCurrentPosttype( $blRewriteAttachment = false ) 
	{
		$sPosttype = self::_getCurrentPosttype();
		
		if( $blRewriteAttachment && $sPosttype !== null && $sPosttype == 'attachment' ){
			$sPosttype = 'media';
		}
		
		return $sPosttype;
	}
	
	
	/**
	 * Gets the current post type
	 *
	 * @since	1.1
	 *
	 * @return	string
	 */
	protected function _getCurrentPosttype()
	{
		global $post, $typenow, $current_screen;
	
	  //we have a post so we can just get the post type from that
	  if ( $post && $post->post_type )
	    return $post->post_type;
	    
	  //check the global $typenow - set in admin.php
	  elseif( $typenow )
	    return $typenow;
	    
	  //check the global $current_screen object - set in sceen.php
	  elseif( $current_screen && $current_screen->post_type )
	    return $current_screen->post_type;
	  
	  //lastly check the post_type querystring
	  elseif( isset( $_REQUEST['post_type'] ) )
	    return sanitize_key( $_REQUEST['post_type'] );
		
	  if( fpFunctions::isPosttypeOverview() && ! isset( $_GET['post_type'] ) )
		return 'post';
		
	  if( strpos( $_SERVER['REQUEST_URI'], 'upload.php' ) ){
	  	return 'media';
      }
	
	  //we do not know the post type!
	  return null;
	}
}
}