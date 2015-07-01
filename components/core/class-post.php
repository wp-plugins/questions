<?php

class Questions_Post{
	var $id;
	var $post;
	var $meta;
	var $comments;
	
	public function __construct( $post_id ){
		if( empty( $post_id ) )
			return FALSE;

        $this->id = $post_id;
		$this->post = get_post( $post_id );
		$this->meta = get_post_meta( $post_id );
		$this->comments = get_comments( array( 'post_id' => $post_id ) );
	}
	
	public function duplicate( $copy_meta = TRUE, $copy_comments = TRUE, $draft = FALSE ){
		$copy = clone $this->post;
		$copy->ID = '';
		
		if( $draft )
			$copy->post_status = 'draft';
		
		$copy->post_date = '';
		$copy->post_modified = '';
		$copy->post_date_gmt = '';
		$copy->post_modified_gmt = '';
		
		$post_id = wp_insert_post( $copy );
		
		if( $copy_meta ):
			$this->duplicate_meta( $post_id );
		endif;
			
		if( $copy_comments ):
			$this->duplicate_comments( $post_id );
		endif;
		
		return $post_id;
	}
	
	public function duplicate_comments( $post_id ){
		$comment_transfer = array();
		
		if( empty( $post_id ) )
			return FALSE;
		
		foreach( $this->comments AS $comment ):
			$comment = (array) $comment;
			$comment[ 'comment_post_ID' ] = $post_id;
			$old_comment_id = $comment[ 'comment_ID' ];
			$new_comment_id = wp_insert_comment( $comment );
			$comment_transfer[ $old_comment_id ] = $new_comment_id;
		endforeach;
		
		// Running all new comments and updating parents
		foreach( $comment_transfer AS $old_comment_id => $new_comment_id ):
			$comment = get_comment( $new_comment_id, ARRAY_A );
			
			// If comment has parrent comment
			if( 0 != $comment[ 'comment_parent' ] ):
				$comment[ 'comment_parent' ] = $comment_transfer[ $comment[ 'comment_parent' ] ];
				wp_update_comment( $comment );
			endif;
		endforeach;
	}
	
	public function duplicate_meta( $post_id ){
		$forbidden_keys = array(
			'_edit_lock',
			'_edit_last'
		);
		if( empty( $post_id ) )
			return FALSE;
				
		foreach( $this->meta AS $meta_key => $meta_value ):
			if( !in_array( $meta_key, $forbidden_keys ) )
				add_post_meta( $post_id, $meta_key, $meta_value );
		endforeach;
	}
}