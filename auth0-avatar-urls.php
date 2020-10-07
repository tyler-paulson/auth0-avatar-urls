<?php

/*
Plugin Name: Login by Auth0 - Avatar URLs
Description: Return Auth0 avatars when leveraging get_avatar_url() or get_avatar_data()
Version:     1.0.0
Requires at least: 4.2.0
Author:      Tyler Paulson Design & Development, Inc
Author URI:  https://tylerpaulson.com
License:     GPLv3
*/

defined( 'ABSPATH' ) or die('No script kiddies please!');

function tpdd_wp_auth0_filter_get_avatar_url($url, $id_or_email) {

    if(!(function_exists('wp_auth0_get_option'))) {
        return $url;
    }

	if ( ! wp_auth0_get_option( 'override_wp_avatars' ) ) {
		return $url;
	}

	$user_id = null;

	if ( $id_or_email instanceof WP_User ) {
		$user_id = $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Comment ) {
		$user_id = $id_or_email->user_id;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$user_id = $id_or_email->post_author;
	} elseif ( is_email( $id_or_email ) ) {
		$maybe_user = get_user_by( 'email', $id_or_email );

		if ( $maybe_user instanceof WP_User ) {
			$user_id = $maybe_user->ID;
		}
	} elseif ( is_numeric( $id_or_email ) ) {
		$user_id = absint( $id_or_email );
	}

	if ( ! $user_id ) {
		return $url;
	}

	$auth0Profile = get_auth0userinfo( $user_id );

	if ( ! $auth0Profile || empty( $auth0Profile->picture ) ) {
		return $url;
	}

    return $auth0Profile->picture;
    
}

add_filter( 'get_avatar_url', 'tpdd_wp_auth0_filter_get_avatar_url', 10, 2 );