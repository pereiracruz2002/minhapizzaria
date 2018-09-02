<?php
/**
* WPPIZZA_FILTER_ORDERS Class
*
* @package     WPPIZZA
* @subpackage  WPPIZZA_FILTER_ORDERS
* @copyright   Copyright (c) 2015, Oliver Bach
* @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
* @since       3.0
*
*/
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/


/************************************************************************************************************************
*
*
*	WPPIZZA_FILTERS
*
*
************************************************************************************************************************/
class WPPIZZA_FILTER_ORDERS{
	function __construct() {

		/**

			ORDER/TEMPLATE FILTERS - skip on install:

		**/		
		/**get site details based on set blog id**/

		/**get parent site details based on set blog id**/

		/*return/filter formatted get order parameters with label from db or session data**/
		add_filter( 'wppizza_filter_get_ordervars_formatted', array( $this, 'wppizza_filter_get_ordervars_formatted'), 10, 5);	




		/** only dealing with summary variables (for consistency in cart (from session), pages (from session or db), orders (from db) */ 
		/*
			
			note to self - todo: 
			use the same filter for summary variables returned (and currently dealt with ) in wppizza_filter_get_order_details_formatted above
		
		*/
		add_filter( 'wppizza_filter_get_order_summary_formatted_from_session', array( $this, 'wppizza_filter_get_order_summary_formatted'), 10, 3);


		/**map order variables to their respective template sections to be used in ouutput*/
		//add_filter( 'wppizza_filter_map_order_to_template_parameters', array( $this, 'wppizza_filter_map_order_to_template_parameters'), 10, 3);	

	} 

/***********************************************************************************************************************************************************
*
*
*
* 	ORDER/TEMPLATE FILTERS: 
*	- GET AND FORMAT SAVED/EXECUTED ORDER DETAILS FROM DB  
*	- GET AND FORMAT ORDER DETAILS FROM SESSION  FRO CART, ORDER PAGES ETC  
*	- GET DRAG/DROP TEMPLATE PARAMETERS  
*	- SANITIZE ORDER AND CUSTOMER DATA TO BE SAVED IN DB  
*
*
*
***********************************************************************************************************************************************************/


	function wppizza_filter_get_ordervars_formatted($order, $called_by = false){

		/*
			unserialize order values to use some of its values
		*/
		$order_details = maybe_unserialize($order->order_ini);
		/*
			gateway selected
		*/
		$gw = $order->initiator;
		$gw_settings = WPPIZZA()->gateways->gwobjects->$gw;
		/*
			order total
		*/
		$order_total = wppizza_format_price($order->order_total, $order_details['param']['currency'], $order_details['param']['currency_position'], $order_details['param']['decimals']);
		$order_paid = wppizza_format_price(0, $order_details['param']['currency'], $order_details['param']['currency_position'], $order_details['param']['decimals']);

		/* 
			ini array 
		*/
		$order_parameters = array();
		/* 
			wp user id 
		*/
		$order_parameters['wp_user_id']['class'] = 'user-id';
		$order_parameters['wp_user_id']['label'] = $order->blog_options['localization']['common_label_order_wp_user_id'] ;
		$order_parameters['wp_user_id']['value'] = $order->wp_user_id ;
		/* 
			order id 
		*/
		$order_parameters['order_id']['class'] = 'order-id';
		$order_parameters['order_id']['label'] =  $order->blog_options['localization']['common_label_order_order_id'] ;
		$order_parameters['order_id']['value'] = apply_filters('wppizza_filter_order_id', $order->id, $order->transaction_id);/* use filter with caution and TEST **/
		/*
			payment due if cod==total , if prepay == 0 
		*/
		$order_parameters['payment_due']['class'] = 'payment-due';
		$order_parameters['payment_due']['label'] = $order->blog_options['localization']['common_label_order_payment_outstanding'];
		$order_parameters['payment_due']['value'] = ($gw_settings->gateway_type == 'cod') ? $order_total : $order_paid ;
		/*
			pickup or delivery
		*/
		//$order_parameters['pickup_delivery']['class'] = '';
		//$order_parameters['pickup_delivery']['label'] = $order->blog_options['localization']['common_label_order_delivery_type'];
		//$order_parameters['pickup_delivery']['value'] = empty($pickup_delivery) ? '' : $pickup_delivery ;		
		
		/*
			payment_type
		*/
		$order_parameters['payment_type']['class'] = 'payment-type';
		$order_parameters['payment_type']['label'] = $order->blog_options['localization']['common_label_order_payment_type'];
		$order_parameters['payment_type']['value'] = $gw_settings->label ;		
		/*
			payment method
		*/
		$order_parameters['payment_method']['class'] = 'payment-method';
		$order_parameters['payment_method']['label'] = $order->blog_options['localization']['common_label_order_payment_method'];
		$order_parameters['payment_method']['value'] = ($gw_settings->gateway_type == 'cod') ? $order->blog_options['localization']['common_value_order_cash'] : $order->blog_options['localization']['common_value_order_credit_card'] ;

		/* 
			transaction id 
		*/
		$order_parameters['transaction_id']['class'] = 'transaction-id';
		$order_parameters['transaction_id']['label'] = $order->blog_options['localization']['common_label_order_transaction_id'] ;
		$order_parameters['transaction_id']['value'] = apply_filters('wppizza_filter_transaction_id', $order->transaction_id, $order->id);	
		/* 
			order_date 
		*/
		$order_parameters['order_date']['class'] = 'order-date';
		$order_parameters['order_date']['label'] = $order->blog_options['localization']['common_label_order_order_date'] ;
		$order_parameters['order_date']['value'] = apply_filters('wppizza_filter_order_date',$order->order_date, $order->date_format) ;		
		/* 
			order_update [currently no label in localization]
		*/
		$order_parameters['order_update']['class'] = 'order-update';
		$order_parameters['order_update']['label'] = __('Last Update','wppizza-admin') ;
		$order_parameters['order_update']['value'] = apply_filters('wppizza_filter_order_date', $order->order_update, $order->date_format) ;		

		/* 
			order_delivered label [user purchase history] 
		*/
		$order_parameters['order_delivered']['class'] = 'order-delivered';
		$order_parameters['order_delivered']['label'] = $order->blog_options['localization']['history_order_delivered_label'] ;
		$order_parameters['order_delivered']['value'] = apply_filters('wppizza_filter_order_date', $order->order_delivered, $order->date_format) ;

		
		/*
			order_notes [currently no label in localization]
		*/
		$order_parameters['notes']['class'] = 'notes';
		$order_parameters['notes']['label'] = __('Notes','wppizza-admin') ;
		$order_parameters['notes']['value'] = $order->notes ;		
		/*
			payment gateway ID [currently no label in localization]
		*/
		$order_parameters['payment_gateway']['class'] = 'gateway';
		$order_parameters['payment_gateway']['label'] = __('Gateway Ident','wppizza-admin') ;
		$order_parameters['payment_gateway']['value'] = $order->initiator ;		
		/* 
			payment_status [currently no label in localization]			
		*/
		$order_parameters['payment_status']['class'] = 'payment-status';
		$order_parameters['payment_status']['label'] = __('Status','wppizza-admin');
		$order_parameters['payment_status']['value'] = $order->payment_status ;		
		/*
			total add here too. might come in useful in places
		*/
		$order_parameters['total']['class'] = 'total';
		$order_parameters['total']['label'] = $order->blog_options['localization']['order_total'];
		$order_parameters['total']['value'] = $order_total ;		
		/*
			user data [currently no label in localization]
		*/								
		$order_parameters['user_data']['class'] = 'user-data';			
		$order_parameters['user_data']['label'] = __('User Data','wppizza-admin');
		$order_parameters['user_data']['value'] = maybe_unserialize($order->user_data) ;													

	return $order_parameters; 
	}

	/*********************************************************************************
	*
	*	[filter: sanitize order details returned from db ]
	*
	*********************************************************************************/

	/**
	
	summary variables from order / session for consistency across the board
	@return array();
	**/	
	function wppizza_filter_get_order_summary_formatted($order_values, $blog_options, $called_by){
		
		/*
			for convenience
		*/
		$currency = !empty($order_values['param']['currency']) ? wppizza_decode_entities($order_values['param']['currency']) : wppizza_decode_entities($blog_options['order_settings']['currency']) ;
		$taxes_included = empty($order_values['param']['taxes_included']) ? false : true;
		$can_checkout = !isset($order_values['session']['can_checkout'])  ? true : $order_values['session']['can_checkout'];/* always tru if not specifically set to false in session */

		
		/*
			ini  summary array
		*/
		$summary = array();

		/* 
			items sum 
		*/
		if(!empty($order_values['summary']['total_price_items'])){
			$summary['total_price_items']['sort']				=	10;
			$summary['total_price_items']['class_ident']		=	'total-items';
			$summary['total_price_items']['label']				=	$blog_options['localization']['order_items'];		
			$summary['total_price_items']['value_formatted']	=	!empty($order_values['summary']['total_price_items']) ? wppizza_format_price($order_values['summary']['total_price_items'], $currency) : '' ;
		}

		/*
			discount 
		*/
		if(!empty($order_values['summary']['discount']) && $can_checkout){
			$summary['discount']['sort']						=	30;
			$summary['discount']['class_ident']					=	'discount';
			$summary['discount']['label']						=	$blog_options['localization']['discount'];
			$summary['discount']['value_formatted']				=	!empty($order_values['summary']['discount']) ? '- '.wppizza_format_price($order_values['summary']['discount'], $currency) : '' ;
		}

		/*
			delivery charges - if not self pickup -  show delivery charges or free delivery 
		*/
		if(empty($order_values['summary']['self_pickup']) && $can_checkout){
			$summary['delivery_charges']['sort']				=	40;
			$summary['delivery_charges']['class_ident']			=	'delivery';
			$summary['delivery_charges']['label']				=	!empty($order_values['summary']['delivery_charges']) ? $blog_options['localization']['delivery_charges'] : $blog_options['localization']['free_delivery'] ;
			$summary['delivery_charges']['value_formatted']		=	!empty($order_values['summary']['delivery_charges']) ? wppizza_format_price($order_values['summary']['delivery_charges'], $currency) : ' ' ;/* add space to force empty td in templates*/	
		}

		/*
			handling charges - automatically 0 if not on checkout page
		*/	
		if(!empty($order_values['summary']['handling_charges']) && $can_checkout){	
			$summary['handling_charge']['sort']					=	50;
			$summary['handling_charge']['class_ident']			=	'handling-charge';
			$summary['handling_charge']['label']				=	$blog_options['localization']['handling_charges'];
			$summary['handling_charge']['value_formatted']		=	wppizza_format_price($order_values['summary']['handling_charges'], $currency) ;
		}

		/*
			taxes - included sort @ 20 excluded(added) sort @60
		*/
		if(!empty($order_values['summary']['taxes']) && $can_checkout){				
			$summary['taxes']['sort']							=	!empty($taxes_included) ?  20 : 60 ;/*after items if taxes added, before tips if taxes included*/
			$summary['taxes']['class_ident']					=	!empty($taxes_included) ?  'tax' : 'tax-included' ;
			$summary['taxes']['label']							=	!empty($taxes_included) ?  $blog_options['localization']['taxes_included'] : $blog_options['localization']['item_tax_total'] ;//sprintf($blog_options['localization']['item_tax_total'], $taxrates_sprintf) : sprintf($blog_options['localization']['taxes_included'], $taxrates_sprintf);
			$summary['taxes']['value_formatted']				=	wppizza_format_price($order_values['summary']['taxes'], $currency) ;
		}

		/*
			tips
		*/
		if(!empty($order_values['summary']['tips']) && $can_checkout){
			$summary['tips']['sort']							=	70;
			$summary['tips']['class_ident']						=	'tips';
			$summary['tips']['label']							=	$blog_options['localization']['tips'];
			$summary['tips']['value_formatted']					=	wppizza_format_price($order_values['summary']['tips'], $currency) ;
		}
	
		/*
			total
		*/
		if(!empty($order_values['summary']['total']) && $can_checkout){			
			$summary['total']['sort']							=	80;
			$summary['total']['class_ident']					=	'total';
			$summary['total']['label']							=	$blog_options['localization']['order_total'];
			$summary['total']['value_formatted']				=	wppizza_format_price($order_values['summary']['total'], $currency) ;
		}

		/* sort by sort flag */	
		asort($summary);
	
	return $summary;
	}

}
/***************************************************************
*
*	[ini]
*
***************************************************************/
$WPPIZZA_FILTER_ORDERS = new WPPIZZA_FILTER_ORDERS();
?>