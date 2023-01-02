<?php

add_action('ez_import_article', 'ez_import_event_data' );

add_filter('ez_import_article_post_type', 'ez_import_article_post_type_evenement', 10, 2);

function ez_import_event_data(array $data): void
{

	$post_id = $data[ 'post_id' ] ;
	$ez_content_type = $data[ 'ez_content_type'] ;
	$object_attribute = $data[ 'object_attribute'] ;

	if (!empty($object_attribute)) 
	{
		if($ez_content_type == 'event' )
		{
			/*add event metas*/
			if (!empty($date_beg = $object_attribute['date_beg']['data_int'])) 
			{
				$start_date = date('Y-m-d', $date_beg);
				update_post_meta($post_id, 'event_start_date', $start_date);
			}
			if (!empty($date_end = $object_attribute['date_end']['data_int'])) 
			{
				$end_date = date('Y-m-d', $date_end);
				update_post_meta($post_id, 'event_end_date', $end_date);
			}
			if (!empty($dates = $object_attribute['dates']['data_text'])) 
			{
				update_post_meta($post_id, 'event_dates', $dates);
			}
			if (!empty($object_attribute['promoter']['contentobject_id'])) 
			{

				$xml_promoter = simplexml_load_string($object_attribute['promoter']['data_text']);

				if (!empty($xml_promoter)) 
				{
					foreach($xml_promoter->{'relation-list'}->{'relation-item'}->attributes() as $key => $value) 
					{
					    if ($key == 'contentobject-id') 
					    {
					    	$promoter_object_id = (string)$value;
					    }
					}
					if (isset($promoter_object_id)) 
					{
						$promoter_id = get_postid_by_meta($promoter_object_id, 'organisateur');
						update_post_meta($post_id, 'event_organisateur', $promoter_id);
					}
				}
			}
			if (!empty($object_attribute['place']['data_text'])) 
			{
				$xml_place = simplexml_load_string($object_attribute['place']['data_text']);

				if (!empty($xml_place)) 
				{
					foreach($xml_place->{'relation-list'}->{'relation-item'}->attributes() as $key => $value) 
					{
					    if ($key == 'contentobject-id') 
					    {
					    	$place_object_id = (string)$value;
					    }
					}

					if (isset($place_object_id)) 
					{
						$place_id = get_postid_by_meta($place_object_id, 'lieu');
						update_post_meta($post_id, 'event_lieu', $place_id);
					}
				}
			}

		}
		elseif ($data['ez_content_type'] == 'place')
		{
			/*add place metas*/
			if (!empty($address = $object_attribute['address']['data_text'])) 
			{
				update_post_meta($post_id, 'adress', $address);
			}
			if (!empty($city = $object_attribute['city']['data_text'])) 
			{
				update_post_meta($post_id, 'city', $city);
			}
			if (!empty($phone = $object_attribute['phone']['data_text'])) 
			{
				update_post_meta($post_id, 'phone', $phone);
			}
			if (!empty($country = $object_attribute['country']['data_text'])) 
			{
				update_post_meta($post_id, 'pays', $country);
			}
			if (!empty($latitude = $object_attribute['latitude']['data_text'])) 
			{
				update_post_meta($post_id, 'latitude', $latitude);
			}
			if (!empty($longitude = $object_attribute['longitude']['data_text'])) 
			{
				update_post_meta($post_id, 'longitude', $longitude);
			}
			if (!empty($website = $object_attribute['website']['data_text'])) 
			{
				update_post_meta($post_id, 'siteweb', $website);
			}
			if (!empty($info = $object_attribute['info']['data_text'])) 
			{
				update_post_meta($post_id, 'informations', $info);
			}
			if (!empty($dates = $object_attribute['dates']['data_text'])) 
			{
				update_post_meta($post_id, 'dates', $dates);
			}
			if (!empty($dates = $object_attribute['zipcode']['data_text'])) 
			{
				update_post_meta($post_id, 'codepostal', $dates);
			}
		}
		elseif ($data['ez_content_type'] == 'promoter')
		{
			/*add promoter metas*/
			if (!empty($phoneOrganisateur = $object_attribute['phone']['data_text'])) 
			{
				update_post_meta($post_id, 'phoneOrganisateur', $phoneOrganisateur);
			}
			if (!empty($sitewebOrganisateur = $object_attribute['website']['data_text'])) 
			{
				update_post_meta($post_id, 'sitewebOrganisateur', $sitewebOrganisateur);
			}
			if (!empty($infOrganisateur = $object_attribute['info']['data_text'])) 
			{
				update_post_meta($post_id, 'informationsOrganisateur', $infOrganisateur);
			}	
		}
	}
}

function get_postid_by_meta(string $meta_value, string $post_type , string $meta_key = 'contentobject_id'): int
{
	global $wpdb;
	$query = $wpdb->prepare("SELECT post_id FROM wp_postmeta, wp_posts where wp_posts.ID = wp_postmeta.post_id and post_type = '%s' and post_status = 'publish' and meta_key ='%s' and meta_value = '%d' ", $post_type, $meta_key, $meta_value);

	$res = $wpdb->get_results( $query );

	$id = false;

	if ( !empty($res[0]) ) {
	    $id = $res[0]->post_id;
	}

	return $id;
}


function ez_import_article_post_type_evenement(string $post_type, array $data): string
{
	if($data['ez_content_type'] == 'event')
	{
		$post_type = 'evenement' ;
	} 
	elseif ($data['ez_content_type'] == 'place')
	{
		$post_type = 'lieu' ;
	}
	elseif ($data['ez_content_type'] == 'promoter')
	{
		$post_type = 'organisateur' ;
	}

	return $post_type;
}