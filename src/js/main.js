jQuery(function($){
	// how much to scroll on each click?
	var tabberScroll = 150;
	
	/**
	 * Create tabber moveable
	 */
	$(window).resize(function(){
		customcolumns.handleTabberScrolling();
	}).resize();
	
	//add function to toleft-button
	$( '#columns-to-left' ).on( 'click', function(){
		if( $(this).hasClass( 'inactive' ) ) return;
		
		var $tabs = $( '#columns-tabber > ul.tabs' ),
			position = $tabs.position();

		if( position.left >= 0 ) return;
		
		iNextPosition = position.left + tabberScroll;

		if( iNextPosition > 0 ) iNextPosition = 0;

		$tabs.stop().animate({
			'left': iNextPosition
		}, 300);
	});
	
	
	//add function to toright-button
	$( '#columns-to-right' ).on( 'click', function(){
		if( $(this).hasClass( 'inactive' ) ) return;
		
		var $tabs = $( '#columns-tabber > ul.tabs' ),
			position = $tabs.position(),
			$columnTabber = $( '#columns-tabber' ),
			tabsWidth = 0;
			
		// calc li-total-width
		$tabs.find( '> li' ).each(function(){
			tabsWidth += $(this).outerWidth();
		});
		
		if( tabsWidth - position.left <= $columnTabber.width() ) return;
		
		iNextPosition = position.left - tabberScroll;

		if( tabsWidth + iNextPosition < $columnTabber.width() ) iNextPosition = 0 - (tabsWidth - $columnTabber.width());
		
		$tabs.stop().animate({
			'left':	iNextPosition
		}, 300);
	});
	
	
	// add column-function
	$( '#add-column' ).on( 'click', function(){
		var sIdent = customcolumns.randomString( 8 ),
			sTitle = ccLang.defaultTabTitle;
			
		customcolumns.addTab( sIdent, sTitle );
	});
	
	// On tab click
	$( '#columns-tabber > ul.tabs' ).on( 'click', '> li:not(.active)', function(){
		customcolumns.makeActive( $(this).data( 'tab-group' ) );
	});
	
	// make tab title editable
	$( '#columns-tabber > ul.tabs' ).on( 'dblclick', '> li', function(){
		var $span = $( this ).find( '> span.inline-edit' ),
			$input = $( this ).find( '> input' );
			
		$span.hide();
		$input.show().focus();
	});
	
	$( '#columns-tabber > ul.tabs' ).on( 'blur', '> li > input', function(){
		var $li = $( this ).parent( 'li' );
			$span = $li.find( '> span.inline-edit' ),
			$input = $( this );
			
		$span.text( $input.val() );
		$span.show();
		$input.hide();
	});
	
	// make tabs sortable
	$( '#columns-tabber > ul.tabs' ).sortable({
		placeholder: "highlight"
	});
	
	// remove tab
	$( '#columns-tabber > ul.tabs' ).on( 'click', '> li > span.remove', function(){
		var sIdent = $( this ).data( 'tab-group' );
		
		customcolumns.removeTab( sIdent );
	});
	
	// add new element on click
	$( '#columns-tab-content' ).on( 'click', 'span.add-field', function(){
		var sElementIdent = customcolumns.randomString(10),
			sIdent = $(this).data( 'tab-group' );
		
		customcolumns.addElement( sIdent, sElementIdent );
	});
	
	// remove element on click
	$( '#columns-tab-content' ).on( 'click', 'span.remove', function(){
		var sElementIdent = $(this).data( 'element-group' );
		
		customcolumns.removeElement( sElementIdent );
	});
	
	// toggle function
	$( '#columns-tab-content' ).on( 'click', '.sortable span.toggle', function(){
		var $toggle = $(this),
			sElementIdent = $toggle.data( 'element-group' ),
			$content = $toggle.parent( '.header' ).parent( '.column-element' ).find( '.content' );
			
		if( $toggle.hasClass( 'closed' ) ){
			$toggle.removeClass( 'closed' );
			$content.slideDown(300);
		} else {
			$toggle.addClass( 'closed' );
			$content.slideUp(300);
		}
	});
	
	$( '#columns-tab-content' ).on( 'click', '.options span.toggle', function(){
		var $toggle = $(this),
			$content = $toggle.parent( '.options' ).parent( '.post-body-content' ).find( '> .options-content' );
			
		if( $toggle.hasClass( 'closed' ) ){
			$toggle.removeClass( 'closed' );
			$content.slideDown(300);
		} else {
			$toggle.addClass( 'closed' );
			$content.slideUp(300);
		}
	});
	
	// change element type
	$( '#columns-tab-content' ).on( 'change', '.header select', function(){
		var value = $(this).val(),
			$element = $(this).parent( '.header' ).parent( '.column-element' );
		
		$element.find( '.content > div' ).hide();
		$element.find( '.content > div.element_type_' + value ).show();
	});
	
	// change column option sortable
	$( '#columns-tab-content' ).on( 'change', '.options-content-sortable-content select', function(){
		var sIdent = $( this ).data( 'tab-group' );
		
		$( this ).parent( '.options-content-sortable-content' ).find( '> div' ).hide();
		
		$( '#tab-options-' + sIdent + '-options-sortable-type-' + $(this).val() ).show();
	});
	
	// sortable checkbox
	$( '#columns-tab-content' ).on( 'change', '.options-content-sortable input[type=checkbox]', function(){
		var $sortableContent = $(this).parents( '.options-content-sortable:eq(0)' ).find( '.options-content-sortable-content' );
		
		if( $(this).attr('checked') == 'checked' ){
			$sortableContent.slideDown(300);
		} else {
			$sortableContent.slideUp(300);
		}
		
		$sortableContent.find( '> select' ).change();
	});
});

var customcolumns = {};

customcolumns = {
	
	// Functions for elements
	elementtype: {
		// post table
		postsTable: function( sIdent, sElementIdent, aData ){
			jQuery( '#element_type_posts_table_' + sElementIdent + '_column' ).val( aData.sColumn );
		},
		
		// post meta
		postMeta: function( sIdent, sElementIdent, aData ){
			jQuery( '#element_type_post_meta_' + sElementIdent + '_metakey' ).val( aData.sMetakey );
		},
		
		// Taxonomy
		taxonomies: function( sIdent, sElementIdent, aData ){
			jQuery( '#element_type_taxonomies_' + sElementIdent + '_taxonomy' ).val( aData.sTaxonomy );
			jQuery( '#element_type_taxonomies_' + sElementIdent + '_delimiter' ).val( aData.sDelimiter );
			jQuery( '#element_type_taxonomies_' + sElementIdent + '_linkto' ).val( aData.sLinkto );
		},
		
		// custom html
		html: function( sIdent, sElementIdent, aData ){
			jQuery( '#element_type_html_' + sElementIdent + '_html' ).val( aData.sHTML );
		}
	},
	
	
	// tab options
	taboptions: {
		sortable: function( sIdent, aData ){
			var $checkbox = jQuery( '#tab-options-' + sIdent + '-options-sortable-makesortable' ),
				$type = jQuery( '#tab-options-' + sIdent + '-options-sortable-type' );

			if( aData.blMakeSortable == "1" ){
				$checkbox.attr( 'checked', true ).change();
			}
			
			$type.val( aData.sType ).change();
			
			jQuery( '#tab-options-' + sIdent + '-options-sortable-type-posts_table-value' ).val( aData.posts_table );
			jQuery( '#tab-options-' + sIdent + '-options-sortable-type-post_meta-value' ).val( aData.post_meta );
			jQuery( '#tab-options-' + sIdent + '-options-sortable-type-taxonomies-value' ).val( aData.taxonomies );
		}
	},
	
	
	/**
	 * Handle tabber scrolling
	 */
	handleTabberScrolling: function(){
		var $tabberColumn = jQuery( '#columns-tabber' ),
			$tabs = $tabberColumn.find( '> ul.tabs' ),
			$tabberLeft = jQuery( '#columns-to-left' ),
			$tabberRight = jQuery( '#columns-to-right' ),
			tabsWidth = 0;
			
		// calc li-total-width
		$tabs.find( '> li' ).each(function(){
			tabsWidth += jQuery(this).outerWidth();
		});

		//show or hide nav-buttons
		if( $tabberColumn.width() < tabsWidth ){
			$tabberLeft.removeClass( 'inactive' );
			$tabberRight.removeClass( 'inactive' );
		} else {
			$tabs.css( 'left', 0 );	
			$tabberLeft.addClass( 'inactive' );
			$tabberRight.addClass( 'inactive' );
		}
	},
	
	/**
	 * makes a tab active
	 *
	 * @param	string sIdent
	 */
	makeActive: function( sIdent ){
		var $tabs = jQuery( '#columns-tabber > ul.tabs' ), 
			$tabsContent = jQuery( '#columns-tab-content' );
			
		$tabs.find( ' > li' ).removeClass( 'active' );
		$tabsContent.find( ' > div.post-body' ).removeClass( 'active' );
		
		$tabs.find( ' > li[data-tab-group="' + sIdent + '"]' ).addClass( 'active' );
		$tabsContent.find( ' > div.post-body[data-tab-group="' + sIdent + '"]' ).addClass( 'active' );
	},
	
	
	/**
	 * Add element to column
	 *
	 * @param	string sIdent
	 * @param	string sElementIdent
	 */
	addElement: function( sIdent, sElementIdent ){
		var htmlTemplateElement = jQuery( '#templateElement' ).val(),
			$tabContent = jQuery( '#columns-tab-content .post-body[data-tab-group="' + sIdent + '"] .sortable' );
			
		htmlTemplateElement = htmlTemplateElement.replace( /%%sElementIdent%%/g, sElementIdent );
		htmlTemplateElement = htmlTemplateElement.replace( /%%sIdent%%/g, sIdent );
		
		$tabContent.append( htmlTemplateElement );
		
		jQuery( '#element-type-' + sElementIdent ).change();
	},
	
	
	/**
	 * Set element options
	 *
	 * @param	string sElementIdent
	 * @param	string sElementType
	 * @param	string aElementData
	 */
	setElementOptions: function( sIdent, sElementIdent, sElementType, aElementData ){
		var aElementData = jQuery.parseJSON( aElementData );
		
		jQuery( '#element-type-' + sElementIdent ).val( sElementType ).change();
		
		if( sElementType == 'posts_table' ){
			customcolumns.elementtype.postsTable( sIdent, sElementIdent, aElementData );
		} else if( sElementType == 'post_meta' ){
			customcolumns.elementtype.postMeta( sIdent, sElementIdent, aElementData );
		} else if( sElementType == 'taxonomies' ){
			customcolumns.elementtype.taxonomies( sIdent, sElementIdent, aElementData );	
		} else if( sElementType == 'html' ){
			customcolumns.elementtype.html( sIdent, sElementIdent, aElementData );	
		}
	},
	
	
	/**
	 * Sets column options
	 *
	 * @param	string sIdent
	 * @param	string sOptionType
	 * @param	string sOptionData
	 */
	setTabOptions: function( sIdent, sOptionType, sOptionData ){
		var aOptionData = jQuery.parseJSON( sOptionData );
		
		if( sOptionType == 'sortable' ){
			customcolumns.taboptions.sortable( sIdent, aOptionData );	
		}
	},
	
	
	/**
	 * Removes a element from column
	 *
	 * @param	string sElementIdent
	 */
	removeElement: function( sElementIdent ){
		jQuery( '#columns-tab-content div.column-element[data-element-group="' + sElementIdent + '"]' ).remove();
	},
	
	
	/**
	 * Add a new column
	 *
	 * @param	string sIdent
	 * @param	string sTitle
	 */
	addTab: function( sIdent, sTitle ){
		var htmlTemplateTab = jQuery( '#templateTab' ).val(),
			htmlTemplateTabContent = jQuery( '#templateTabContent' ).val(),
			$tabs = jQuery( '#columns-tabber > ul.tabs' ), 
			$tabsContent = jQuery( '#columns-tab-content' );
			
		htmlTemplateTab = htmlTemplateTab.replace( /%%sIdent%%/g, sIdent );
		htmlTemplateTab = htmlTemplateTab.replace( /%%sTitle%%/g, sTitle );
		
		htmlTemplateTabContent = htmlTemplateTabContent.replace( /%%sIdent%%/g, sIdent );
		
		$tabs.append( htmlTemplateTab );
		$tabsContent.append( htmlTemplateTabContent );
		
		customcolumns.makeActive( sIdent );
		jQuery(window).resize();
		
		// make elements sortable
		jQuery( '#columns-tab-content .post-body[data-tab-group="' + sIdent + '"] .sortable' ).sortable({
			handle: '.header',
			placeholder: 'column-element highlight'
		});
	},
	
	
	/**
	 * removes a tab
	 *
	 * @param	string sIdent
	 */
	removeTab: function( sIdent ){
		var $tabs = jQuery( '#columns-tabber > ul.tabs' );
		
		$tabs.find( '> li[data-tab-group="' + sIdent + '"]' ).remove();
		jQuery( '#columns-tab-content > div.post-body[data-tab-group="' + sIdent + '"]' ).remove();
	
		$tabs.find( '> li:eq(0)' ).click();
	},
	
	/** 
	 * Create random key
	 *
	 * @see http://stackoverflow.com/a/1349426
	 *
	 * @param	int iLength
	 *
	 * @return	string
	 */
	randomString: function( iLength ){
		var sString = '';
		
		var sPossible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		
		for( var i = 0; i < iLength; i++ ){
			sString += sPossible.charAt( Math.floor( Math.random() * sPossible.length ) );
		}
		
		return sString;
	}
};