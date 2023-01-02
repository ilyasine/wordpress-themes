<?php

add_action('ez_import_article', 'ez_import_dossier_data' );

add_filter('ez_import_article_post_type', 'ez_import_article_post_type_folder', 10, 2);

function ez_import_dossier_data($data){

	$post_id = $data[ 'post_id' ] ;
	$ez_content_type = $data[ 'ez_content_type'] ;
	$object_attribute = $data[ 'object_attribute'] ;

	if($ez_content_type == 'dossier' ){
		if(!empty($object_attribute['page']['data_text']))
		$page=xml_to_array($object_attribute['page']['data_text']);
		if(!empty($page['zone'][0]['content']['block'][0]['id'])){
			$block_id = ltrim( $page['zone'][0]['content']['block'][0]['id'], "id_");
			ez_dossier_get_childrens($block_id, $post_id);
		}
	}
}

function ez_dossier_get_childrens( $block_id, $post_id ){
	global $ez_db; 
	$query = "SELECT ezm_pool.node_id FROM ezm_pool, ezcontentobject_tree WHERE ezm_pool.block_id='$block_id' AND ezm_pool.ts_visible>0 AND ezm_pool.ts_hidden=0 AND ezcontentobject_tree.node_id = ezm_pool.node_id ORDER BY ezm_pool.priority DESC ";

	$ezm_pool = [] ;
	$posts = [] ;
	if ($result = $ez_db->query($query)) {
	    $items = $result->fetch_all(MYSQLI_ASSOC);

	    foreach ($items as $item) {
	    	$node_id = $item['node_id'];
	    	$ezm_pool[] = $node_id;
			$post = get_post_form_node_id($node_id) ;
			if(!empty($post->ID)){
				$posts[] = $post->ID ;
			}
	    }
	}

	if(count($posts)){
		update_post_meta($post_id, 'children', implode(',',$posts) );
	}
	if(count($ezm_pool)){
		update_post_meta($post_id, 'node_id_children', implode(',',$ezm_pool) );
	}
}

function ez_import_article_post_type_folder( $post_type, $data){
	if($data['ez_content_type'] == 'dossier'){
		$post_type = 'folder' ;
	}

	return $post_type;
}