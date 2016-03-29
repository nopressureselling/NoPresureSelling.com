<?php 

class UberMenuConditionalWalker extends UberMenuWalker{

	/* Recursive function to remove all children */
	function clear_children( &$children_elements , $id ){

		if( empty( $children_elements[ $id ] ) ) return;

		foreach( $children_elements[ $id ] as $child ){
			$this->clear_children( $children_elements , $child->ID );
		}
		unset( $children_elements[ $id ] );
	}

	/**
	 * Traverse elements to create list from elements.
	 *
	 * Calls parent function in UberMenuWalker.class.php
	 */
	function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {

		if ( !$element )
			return;

		$id_field = $this->db_fields['id'];
		$id = $element->$id_field;

		$display_on = apply_filters( 'uberMenu_display_item' , true , $this , $element , $max_depth, $depth, $args );

		if( !$display_on ){
			$this->clear_children( $children_elements , $id );
			return;
		}
		
		//Walker_Nav_Menu::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
		UberMenuWalker::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

}