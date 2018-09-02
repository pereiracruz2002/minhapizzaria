<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*
*
*	general helper functions that could be used for 3rd party plugin development
*	or used in custom functions outside wppizza environment (functions.php and watnot)
*
*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\*/

/********************************************************************************
	get completed orders (including failed, unconfirmed, or orders that have subsequently been rejected or refunded) 
	from order table(s) depending on arguments set and optionally format
	see documentation at https://docs.wp-pizza.com/developers/?section=function-wppizza_get_orders

	@ since 3.5
	@ param array
	@ return array 	 
************************************************************************************/
function wppizza_get_orders($args = false){
	$orders = WPPIZZA() -> db -> get_orders($args);
return $orders;
}
/********************************************************************************
	if outputting results from wppizza_get_orders you could use the below to get 
	appropriate pagination 
	see documentation at https://docs.wp-pizza.com/developers/?section=function-wppizza_get_orders

	@ since 3.5
	@ param int
	@ param int|false	
	@ param bool	
	@ param false|int
	@ return str 	 
************************************************************************************/
function wppizza_orders_pagination($no_of_orders, $limit, $ellipsis = false, $pagination_info = true, $post_id = false){
	$pagination = WPPIZZA() -> markup_pages -> orderhistory_pagination($no_of_orders, $limit, $ellipsis, $pagination_info, $post_id);
return $pagination;
}
/********************************************************************************
	get all available customer form fields set in wppizza->order form
	
	default: excluding tips 
	optionally, enabled only form fields
	optionally, include confirmation form
	
	
	@ since 3.7
	@ param bool
	@ param bool	
	@ return array 	 
************************************************************************************/
function wppizza_customer_checkout_fields($args = array('enabled_only' => false, 'confirmation_fields' => false, 'tips_excluded' => true, 'sort' => true)){
	global $wppizza_options;
	
	
	$ff = array();
		
	/* default , get all */	
	if(!$args['enabled_only']){
		foreach($wppizza_options['order_form'] as $k=>$arr){
			$ff[$k] = $arr;
		}	
	}

	/* if we want enabled only , get them here */
	if($args['enabled_only']){
		foreach($wppizza_options['order_form'] as $k=>$arr){
			if(!empty($arr['enabled'])){
				$ff[$k] = $arr;
			}
		}	
	}

	// by default we exclude tips
	if($args['tips_excluded']){
		unset($ff['ctips']);
	}	

	
	/* get confirmation form too */	
	if($args['confirmation_fields']){
		foreach($wppizza_options['confirmation_form'] as $k=>$arr){
			$ff[$k] = $arr;
		}	
	}	
	if($args['sort']){
		asort($ff);
	}
	
return $ff;
}
?>