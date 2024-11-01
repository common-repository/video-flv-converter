<?php
/*
Plugin Name: video-flv-converter
Plugin URI: 
Description: Convert user upload videos into .flv format 
Author: Anthony Niroshan De croos Fernandez
Author URI: 
Version: 1.0
*/
function fileuploads($attachmentID)
{
	global $wpdb;
		
	if(extension_loaded('ffmpeg'))
	{
		$postDetails = $wpdb->get_row( $wpdb->prepare("SELECT guid, post_mime_type FROM $wpdb->posts WHERE ID = %s", $attachmentID) );
		$attachement = $postDetails->guid;
		$type = explode("/", $postDetails->post_mime_type);
		$uploadType = strtolower($type[0]);
		
		if($uploadType=='video')
		{
			$urlInfo = parse_url($attachement);
			$originalFileUrl = $_SERVER['DOCUMENT_ROOT'].$urlInfo['path'];
			$fileDetails = pathinfo($originalFileUrl);
		
			if(strtolower(trim($fileDetails['extension'])) <> 'flv')
			{ 	
				$fileFound = true;
				$i='';
			
				while($fileFound)
				{
					$fname = $fileDetails['filename'].$i;		
					$newFile = $fileDetails['dirname'] .'/'.$fname.'.flv';
				
					if(file_exists($newFile))
						$i = $i=='' ? 1 : $i+1;			
					else
						$fileFound = false;
				}
						
				$str = "ffmpeg -i ".$originalFileUrl." ".$newFile;
				exec($str);
			
				if(file_exists($newFile))
				{
					$fileDetails = pathinfo($attachement);
					$newFile =  $fileDetails['dirname'] .'/'.$fname.'.flv';
				
					$wpdb->query( $wpdb->prepare("UPDATE $wpdb->posts SET guid = '".$newFile."', post_mime_type = 'video/x-flv' WHERE ID = %s", $attachmentID) );
			
					$metaDetails = $wpdb->get_row( $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %s AND meta_key = '_wp_attached_file'", $attachmentID) );
					$urlInfo = parse_url($metaDetails->meta_value);
					$fileDetails = pathinfo($urlInfo['path']);
					$newFile = $fileDetails['dirname'] .'/'.$fname.'.flv';
				
					$wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = '".$newFile."' WHERE post_id = %s AND meta_key = '_wp_attached_file'", $attachmentID) );
				
					unlink($originalFileUrl);			
				}			
			}
		}
	}	
}

add_action('edit_attachment', 'fileuploads');
add_action('add_attachment', 'fileuploads');
?>