<?php
/**
* WPPIZZA_DB Class
*
* @package     WPPIZZA
* @subpackage  WPPIZZA_DB
* @copyright   Copyright (c) 2015, Oliver Bach
* @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
* @since       3.0
*
*/
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/


/************************************************************************************************************************
*
*
*	WPPIZZA_DB
*
*
************************************************************************************************************************/
class WPPIZZA_DB{


	function __construct() {
		/* on orderpage, initialize/save session data to db - sanitized */
		add_action('wp', array( $this, 'order_initialize'));
	}


	/**********************************************
		get the most recent completed order of current blog
		used for templates preview
	**********************************************/
	function get_last_completed_blog_order_id($user_id = null){
		global $wpdb;
		/*
			get last completed order of this blog
		*/
		$last_order_id = $wpdb->get_row("SELECT MAX(id) as id FROM ".$wpdb->prefix . WPPIZZA_TABLE_ORDERS." WHERE payment_status='COMPLETED' ", ARRAY_A);

		/*
			fetch_order_details() below to be replaced by this in a soon/future versions
		*/
		//$args = array(
		//	'query' => array(
		//		'order_id' => $last_order_id
		//	),
		//);
		//$order = $this->get_orders($args);
		//if(!empty($order['orders'])){
		//	$order = reset($order['orders']);
		//	return $order;
		//}
		//return false;



		$order = $this->fetch_order_details(false, $last_order_id['id'], false, false, $user_id);/* set $user_id to false to not restrict by logged in user */
		if(!empty($order)){
			$order = $order['orders'][0];
		}

	return $order;
	}

	/**************************************************************************************************

		update order

	**************************************************************************************************/
	function update_order($blog_id, $order_id, $hash, $update_values, $where_payment_status = false) {
		global $wpdb;


		/**
			if there's neither a hash nor an order id
			bail early
		**/
		if(empty($order_id) && empty($hash)){
			return false;
		}

		/**
			order table
		**/
		$order_table = $this->order_table($blog_id);

		/**
			where | orderid/hash | where_format
		**/
		$where = array();
		$where_format = array();
		if($order_id){
			$where['id'] = $order_id;
			$where_format[] = '%d';
		}
		if($hash){
			$where['hash'] = $hash;
			$where_format[] = '%s';
		}

		/**
			where | payment_status | where_format
		**/
		if(!empty($where_payment_status)){
			$where['payment_status'] = ''.$where_payment_status.'';
			$where_format[] = '%s';
		}

		/*
			set update vars
		*/
		$order = array();
		$order['data'] = array();
		$order['type'] = array();
		foreach($update_values as $key=>$val){
			$order['data'][$key] = $val['data'] ;
			$order['type'][] = $val['type'];
		}

		/*
			update order
		*/
		$update_order = $wpdb->update( $order_table , $order['data'], $where , $order['type'], $where_format);
		/*
			return bool db updated or not
		*/
		$update_order = !empty($update_order) ? true : false;



	return $update_order;
	}


	/***************************************************************************************************
		get right table depending on blog id
	***************************************************************************************************/
	function order_table($set_blog_id = false){
		global $wpdb, $blog_id;
		$order_table = $wpdb->prefix;
		if($set_blog_id && $set_blog_id != $blog_id && $set_blog_id>1){
			$order_table .= $set_blog_id.'_';
		}
		$order_table .= WPPIZZA_TABLE_ORDERS;

	return $order_table;
	}

	/**************************************************************************************************
	*
	*
	*	cancel order
	*
	*
	**************************************************************************************************/
	function cancel_order($blog_id, $order_id, $hash, $limit_days = true) {
		global $wpdb;

		/**
			order table
		**/
		$order_table = $this->order_table($blog_id);


		$where = array();
		$where_format = array();
		/**
			where | orderid/hash | where_format
		**/
		if(!empty($order_id)){
			$where['id'] = array('clause'=> '=' , 'value' => (int)$order_id);
		}
		if(!empty($hash)){
			$hash = (string)sanitize_key($hash);
			$where['hash'] = array('clause'=> '=' , 'value' => "'".$hash."'");
		}

		/**
			where | payment_status
			return true for already/previously cancelled orders too
		**/
			$where['payment_status'] = array('clause'=> 'IN' , 'value' => "('INITIALIZED', 'CANCELLED', 'INPROGRESS')");

		/**
			restrict to last 7 days
			unless specifically bypassed
		**/
		if($limit_days){
			$where['order_date'] = array('clause'=> '>' , 'value' => ' TIMESTAMPADD(WEEK,-1,NOW()) ');
		}


		/**
			columns to update
		**/
		$update_values = array();
		/** amend order update */
		$update_values['order_update'] 		= array('data' =>date('Y-m-d H:i:s', WPPIZZA_WP_TIME));
		/* set status, cancelled */
		$update_values['payment_status'] 	= array('data' => 'CANCELLED');


		/*
			set update vars for query
		*/
		$data= array();
		foreach($update_values as $key=>$val){
			$data[$key] = "".$key." = '".$val['data']."'";
		}
		$data = implode(', ', $data);

		/*
			set where clause for query
		*/
		$where_clause= array();
		foreach($where as $key=>$val){
			$where_clause[$key] = "" . $key . ' ' . $val['clause'] . ' ' . $val['value'] . "";
		}
		$where_clause = implode(' AND ', $where_clause);


		/*
			run query
			we cannot use $wpdb->update with IN or > in where clause
		*/
		$sql = 'UPDATE '.$order_table.' SET '.$data.' WHERE '.$where_clause.'';
		$update_order = $wpdb->query($sql);
		$update_order = empty($update_order) ? false : true ;/* because we can */


	return $update_order;
	}

	/**************************************************************************************************
	*
	*
	*	delete order
	*
	*
	**************************************************************************************************/
	function delete_order($delete_id, $blog_id = false) {
		global $wpdb;

		$order_table = $this->order_table($blog_id);

		$wpdb->delete( $order_table, array( 'id' => $delete_id ), array( '%d' ) );
	}

	/**************************************************************************************************
	*
	*
	*	initialize order
	* 	insert session into db when coming to orderpage
	*	updates too if adding tips for example
	*
	**************************************************************************************************/
	/*
		insert session into db when coming to orderpage
	*/

	function order_initialize() {
		global $wpdb, $blog_id, $post;
		/* for the time being, set this to false */
		$is_ajax = ( defined('DOING_AJAX') && DOING_AJAX ) ? true : false;

		/**
			check if an orderpage widget is on page ,
			to override check for is_orderpage
			initialize as false
		**/
		$has_orderpage_widget = wppizza_has_orderpage_widget();

		/*
			check is_orderpage
			if it is not an ajax request (to do perhaps, at the moment its always false )
			and do not insert or update
			if we cannot checkout yet anyway

		*/
		if(!$is_ajax && !wppizza_is_orderpage() && !$has_orderpage_widget){
			return;
		}

		/*
			get userdata session to either update or insert order
		*/
		$user_session = WPPIZZA()->session->get_userdata();

		/*
			get mapped order data, generating new hash
		*/
		$order_session = $this->map_order($user_session);


		/*
			which table (blog) should we be using ?
		*/
		$order_table = $this->order_table($blog_id);


		/* check if theres an initialized order already with that hash and update instead of insert new*/
		$update_id = false;
		$insert_id = false;
		if(!empty($user_session[''.WPPIZZA_SLUG.'_hash'])){
			$get_order = $wpdb->get_row( "SELECT id FROM ".$order_table." WHERE hash = '".$user_session[''.WPPIZZA_SLUG.'_hash']."' AND payment_status = 'INITIALIZED' ", ARRAY_A);
			/* update if theres an order in session */
			if (!empty($order_session) && null !== $get_order ) {
				$update_id = $get_order['id'];
			}
		}

		/*
			update db
		*/
		if($update_id){
			$is_update = $wpdb->update( $order_table , $order_session['data'], array( 'id' => $update_id ), $order_session['type'], array( '%d' ));
			/* something failed, let's do a new one to be safe*/
			if(false === $is_update){
				$update_id = false;
			}
		}

		/*
			insert new into db if not update or delete and there's an order in session
		*/
		if(!$update_id){//&& !$delete_id && !empty($order_session)

			/*
				add hash to db and session when inserting new
			*/
			$wppizza_hash = wppizza_mkHash($order_session);
			$order_session['data']['hash'] = $wppizza_hash;
			$order_session['type'][] = '%s';

			WPPIZZA()->session->set_order_hash($wppizza_hash);
			$user_session = WPPIZZA()->session->get_userdata();
			$order_session['data']['customer_ini'] = maybe_serialize($user_session);
			$order_session['type'][] = '%s';

			$wpdb->insert( $order_table , $order_session['data'], $order_session['type']);
			$insert_id = $wpdb->insert_id;

			/*
				add order id to user session when inserting new
				needed for gateways that use overlay and need to pass on the order id
			*/
			WPPIZZA()->session->set_order_id($insert_id);
			$order_id = $insert_id;
		}

	return;
	}

	/**************************************************************************************************************************************************************************************
	*
	*
	*
	*	admin get customers by search or id
	*
	*
	*
	**************************************************************************************************************************************************************************************/
	function get_customers($selected_user_id = false, $set_limit = 10 ){
		global $wppizza_options, $wpdb, $blog_id;

		/*ini return array*/
		$customers=array();
		$customers['customers_on_page']=array();
		$customers['total_number_of_customers']=0;
		$customers['results_set']=array();

		/*for consistancy add it here*/
		$sql_payment_status = "'".implode("','",explode(",",WPPIZZA_PAYMENT_STATUS_SUCCESS))."'";

		/*pagination and sliceoffset if set*/
		$paged=0;
		if(!empty($_GET['paged']) && is_numeric($_GET['paged'])){
			$paged=(int)$_GET['paged']-1;
		}

		/**
			search for customers by $_GET['s'] or $_GET['uid']
		**/
		/* if we have a distinctly set user id, set _GET['s'] variable accordingly */
		if(!empty($selected_user_id)){
			$_GET['s'] = (int)$selected_user_id;
		}
		if(!empty($_GET['s']) || !empty($_GET['uid'])){
			$is_customer_search = true;
		}

		/**
			[restrict by user id > 0]
			will be replaced with wp_user_id IN(1,2,3)
			if search applies
		**/
		$user_id_restrict=' > 0 ';

		/*************************************
			getting orders from all subsites
			only multisite->parent site and only if enabled
		*************************************/
		$multisite_all_orders = apply_filters('wppizza_filter_order_history_all_sites',false);

		/*********************************************************************************************

			getting order tables to query

		********************************************************************************************/
			$blog_tables = array();
			$k=0;
			/* all blogs */
			if ($multisite_all_orders){
	 	   		/*get all and loop through blogs*/
	 	   		$blogs = $wpdb->get_results("SELECT blog_id FROM ".$wpdb->blogs."", ARRAY_A);
				if ($blogs) {
		        	foreach($blogs as $blog) {
		        		switch_to_blog($blog['blog_id']);
		        			/*make sure plugin is active*/
		        			if(is_plugin_active(WPPIZZA_PLUGIN_INDEX)){
								$blog_tables[$blog['blog_id']] = $wpdb->prefix . WPPIZZA_TABLE_ORDERS;
		        			$k++;
		        			}
						restore_current_blog();
		        	}
				}
			}
			/* curent blog only */
			if (!$multisite_all_orders){
				$blog_tables[$blog_id] = $wpdb->prefix . WPPIZZA_TABLE_ORDERS;
			}

		/*****************************************************************************
		*
		*	[doing search of some sort, get possible user id's]
		*
		******************************************************************************/
		if(!empty($is_customer_search)){

			/**
				search for customers by $_GET['s']
			**/
			if(!empty($_GET['s'])){

				$search=esc_sql(wppizza_validate_string($_GET['s']));
				/*searching for id*/
				$id_search = (int)$search ;
				$search_int=(is_numeric($search) && !empty($id_search) ) ? $id_search : false;


				/*searching display_name, user_nicename, user email*/
				$search_columns=array();
				if(!$search_int){
					$search_columns[]= "".$wpdb->base_prefix ."users.display_name LIKE '%".$search."%' ";
					$search_columns[]= "".$wpdb->base_prefix ."users.user_nicename LIKE '%".$search."%' ";
					$search_columns[]= "".$wpdb->base_prefix ."users.user_email LIKE '%".$search."%' ";
				}
				/*if numeric, just search id*/
				if($search_int){
					$search_columns[]= "".$wpdb->base_prefix ."users.ID = ".$search_int." ";
				}

				/**construct the query*/
				$customers_query="";
				$customers_query.= " SELECT " . $wpdb->base_prefix . "users.ID" . PHP_EOL;
				$customers_query.= " FROM " . $wpdb->base_prefix . "users " . PHP_EOL;
				$customers_query.= " WHERE ".PHP_EOL." " . implode(''.PHP_EOL.' OR '.PHP_EOL.'', $search_columns) . " " . PHP_EOL;
			}

			/*get results*/
			$customers_search_results = $wpdb->get_results($customers_query);
			/*
				no search results , bail right now
			*/
			if(empty($customers_search_results)){
				return ;
			}


			$customer_search_found_ids=array();
			if(is_array($customers_search_results)){
			foreach($customers_search_results as $vars){
				$customer_search_found_ids[$vars->ID]=$vars->ID;
			}}
			/*overwrite user id search */
			$user_id_restrict =' IN ('.implode(',',$customer_search_found_ids).') ';
		}

		/*****************************************************************************
		*
		*
		*	[get all customer id's from all blogtables with user_registered date etc]
		*	[sort by user_registered DESC]
		*
		*	[only if not search]
		******************************************************************************/


				$colums=array();
				$colums['user_order_by']	= $wpdb->base_prefix.'users.user_registered as user_order_by';
				$colums['user_registered']	= $wpdb->base_prefix.'users.user_registered';
				$colums['user_nicename']	= $wpdb->base_prefix.'users.user_nicename';
				$colums['user_email']		= $wpdb->base_prefix.'users.user_email';
				$colums['display_name']		= $wpdb->base_prefix.'users.display_name';
				$customers_query='';

				/*****************************************************************************
				*
				*	[list all customrs with completed orders]
				*
				******************************************************************************/
				$c=0;
				foreach($blog_tables as $k=>$blog_table){
					//if($c>0){$customers_query.=PHP_EOL.'UNION ALL'.PHP_EOL;}/*union if more than one table */
					if($c>0){$customers_query.=PHP_EOL.'UNION'.PHP_EOL;}/*union if more than one table */
					$customers_query.= ' SELECT ' . implode(',',$colums) . ', ' . $blog_table . '.wp_user_id' . PHP_EOL;
					$customers_query.= ' FROM ' . $blog_table . ' ' . PHP_EOL;
					$customers_query.= ' LEFT JOIN ' . $wpdb->base_prefix . 'users ON ' . $blog_table . '.wp_user_id = ' . $wpdb->base_prefix . 'users.ID ' . PHP_EOL;
					$customers_query.= ' WHERE ' . $blog_table . '.wp_user_id '.$user_id_restrict.' AND payment_status IN ('.$sql_payment_status.') ' . PHP_EOL;
					$customers_query.= ' GROUP BY ' . $blog_table . '.wp_user_id ' . PHP_EOL;
					//$customers_query.= ' LIMIT '.($paged*$set_limit).','.$set_limit.' ' . PHP_EOL;//dont limit as we want to get all*/
				$c++;
				}


				/* add order by clause to end */
				$customers_query.= ' ORDER BY user_order_by DESC ' . PHP_EOL;

				/*get results*/
				$customers_results = $wpdb->get_results($customers_query, ARRAY_A);

				/**sort by user_registered desc**/
				if($customers_results){
					arsort($customers_results);
				}

			/**********************
			*
			*
			*	[total no of customers according to query ]
			*	for pagination
			*
			***********************/
			$customers['total_number_of_customers'] = is_array($customers_results) ? count($customers_results) : 0;


			/**********************
			*
			*
			*	[slice for customers we need to display on page]
			*	[offset and limited
			*
			***********************/
			$slice_offset= $paged * $set_limit;
			$customers_results = array_slice($customers_results, $slice_offset, $set_limit);


			/**********************
			*
			*
			*	[all user id's of customers displayed on page]
			*	[offset and limited
			*
			***********************/
			$ids_on_page_array = wppizza_array_column($customers_results, 'wp_user_id');
			$ids_on_page=implode(',',$ids_on_page_array);

			/**********************
			*
			*
			*	[get values from all tables for each customer displayed on page]
			*
			*
			***********************/
			$customer_values_query='';
			if(count($blog_tables) >1 ){
				$customer_values_query=PHP_EOL."SELECT wp_user_id as wp_user_id, SUM(table_count) as table_count , SUM(table_total_value) as table_total_value, SUM(table_total_items) as table_total_items  " . PHP_EOL;
				$customer_values_query.=" FROM (" . PHP_EOL;
			}
			$c = 0;
			foreach($blog_tables as $k=>$blog_table){
				if($c > 0){
					$customer_values_query.=" UNION ALL " . PHP_EOL;
				}
				$customer_values_query.=" SELECT wp_user_id, COUNT(*) as table_count,  SUM(order_total) as table_total_value, SUM(order_no_of_items) as table_total_items " . PHP_EOL;
				$customer_values_query.=" FROM ".$blog_table."" . PHP_EOL;
				$customer_values_query.=" WHERE wp_user_id IN (".$ids_on_page.") AND payment_status IN (".$sql_payment_status.") " . PHP_EOL;
				$customer_values_query.=" GROUP BY wp_user_id " . PHP_EOL;
			$c++;
			}
			if(count($blog_tables) >1 ){
				$customer_values_query.=" ) tmp" . PHP_EOL;
				$customer_values_query.=" GROUP BY wp_user_id" . PHP_EOL;
			}

			/**get results - however, if there are no orders (and therefore no customers, just return empty array) **/
			$customer_values_results = empty($ids_on_page_array) ? array() : $wpdb->get_results($customer_values_query, OBJECT_K);

			/**add user data to results set resultset on page**/
			$customers['results_set'] = array();
			foreach($customers_results as $k=>$val){
				$uid=$val['wp_user_id'];/*for convenience*/

				$user_meta_name=array();
				$user_meta_name[]=get_user_meta($uid, 'first_name', true);
				$user_meta_name[]=get_user_meta($uid, 'last_name', true);


				$customers['results_set'][$uid]['user_registered']			=	$val['user_registered'];
				$customers['results_set'][$uid]['user_email']				=	$val['user_email'];
				$customers['results_set'][$uid]['user_name']				=	trim(implode(' ',$user_meta_name));
				$customers['results_set'][$uid]['user_display_name']		=	$val['display_name'];
				$customers['results_set'][$uid]['user_user_nicename']		=	$val['user_nicename'];
				$customers['results_set'][$uid]['user_orders_order_count']	=	$customer_values_results[$uid]->table_count;
				$customers['results_set'][$uid]['user_orders_total_value']	=	$customer_values_results[$uid]->table_total_value;
				$customers['results_set'][$uid]['user_orders_total_items']	=	$customer_values_results[$uid]->table_total_items;
				$customers['results_set'][$uid]['user_orders_avg_spent']	=	($customer_values_results[$uid]->table_total_value / $customer_values_results[$uid]->table_count);
				$customers['results_set'][$uid]['wp_user_id']				=	$uid;
			}


	return $customers;
	}

	/**************************************************************************************************
	*
	*	replacement of previous get_orders_orderhistory() that can now also be used externally
	* 	via wrapper function to query completed orders
	*	(including failed, unconfirmed, or orders that have subsequently been rejected or refunded)
	*
	*	@ since 3.5
	*	@param array
	*	@return array
	**************************************************************************************************/
	function get_orders($args = false){

		global $wpdb, $blog_id, $wppizza_options;
		$force_all_rows = $args === null ? true : false; //force getting all rows
		$no_arguments_passed = $args === false ? true : false; //force default pagination limits


		/*********************************************
		*
		*
		*	sanitise arguments
		*
		*
		*********************************************/
		/*\/*\/*
		#	$args['query']['wp_user_id']
		#	@param int
		*\/*\/*/
		$args['query']['wp_user_id'] = (isset($args['query']['wp_user_id']) && is_numeric($args['query']['wp_user_id'])) ? abs((int)$args['query']['wp_user_id']) : false;

		/*\/*\/*
		#	$args['query']['email']
		#	@param str|array
		*\/*\/*/
		/* cast to array first of all */
		$sanitized_email = !empty($args['query']['email']) ? ( (!is_array($args['query']['email'])) ? array($args['query']['email']) : $args['query']['email'] ) : array();
		$sanitized_email = array_map('sanitize_text_field', $sanitized_email);// sanitize
		$args['query']['email'] = !empty($sanitized_email) ? $sanitized_email : false;


			//$args['query']['custom_status'] = !empty($sanitized_custom_status) ? $sanitized_custom_status : false;
		//$args['query']['email'] = !empty($args['query']['email']) ? ( strtoupper($args['query']['email']) === 'NULL' ? 'NULL' : sanitize_text_field($args['query']['email']) ) : false;

		/*\/*\/*
		#	$args['query']['order_id']
		#	@param int
		#	query for specific order id.
		#	in confunction with $args['query']['blogs'], this could also return (an) order(s) from (a) different blog(s). otherwise will by default query current blog only
		*\/*\/*/
		$args['query']['order_id'] = (isset($args['query']['order_id']) && is_numeric($args['query']['order_id'])) ? abs((int)$args['query']['order_id']) : false;

		/*\/*\/*
		#	$args['query']['hash']
		#	@param int
		#	query for specific hash.
		#	in confunction with $args['query']['blogs'], this could also return (an) order(s) from (a) different blog(s). otherwise will by default query current blog only
		*\/*\/*/
		$args['query']['hash'] = !empty($args['query']['hash']) ? (string)sanitize_key($args['query']['hash']) : false;

		/*\/*\/*
		#	$args['query']['order_date_after']
		#	@param timestamp
		#	query for an order date that is after a set timestamp.
		#
		*\/*\/*/
		$args['query']['order_date_after'] = !empty($args['query']['order_date_after']) && (int)$args['query']['order_date_after'] >0 ? (int)$args['query']['order_date_after'] : false;


		/*\/*\/*
		#	$args['query']['payment_status']
		#	@param str | array | 'NULL'
		#	Note: if set to 'NULL' (string) payments_status query will be forefully removed
		*\/*\/*/
		$default_payment_status=explode(',',WPPIZZA_PAYMENT_STATUS_SUCCESS);//COMPLETED
		$default_payment_status[]='UNCONFIRMED';
		$default_payment_status[]='REFUNDED';
		$default_payment_status[]='REJECTED';
		/*
			restrict to these (for now) as otherwsie there would be a ton of php notices
			as many things will not be available yet for any of the other statusses
		*/
		$allowed_payment_status=explode(',',WPPIZZA_PAYMENT_STATUS_SUCCESS);//COMPLETED
		$allowed_payment_status[]='UNCONFIRMED';
		$allowed_payment_status[]='REFUNDED';
		$allowed_payment_status[]='REJECTED';
		$allowed_payment_status[]='FAILED';
		$allowed_payment_status[]='INPROGRESS';
		/*
			cast to array if it is not
			sanitize and intersect with allowed status
		*/
		$sanitized_payment_status = !empty($args['query']['payment_status']) ? ( (!is_array($args['query']['payment_status'])) ? array($args['query']['payment_status']) : $args['query']['payment_status'] ) : array();
		$sanitized_payment_status = array_map('strtoupper',array_map('wppizza_validate_alpha_only', $sanitized_payment_status));// make it case insensitive and sanitize
		$sanitized_payment_status = array_values(array_intersect($allowed_payment_status, $sanitized_payment_status));//intersect and reindex
		$args['query']['payment_status'] = (isset($args['query']['payment_status']) && is_string($args['query']['payment_status']) && strtoupper($args['query']['payment_status']) === 'NULL') ? NULL : ((!empty($args['query']['payment_status']) ? $sanitized_payment_status : $default_payment_status));

		/*\/*\/*
		#	$args['query']['order_status']
		#	@param string
		*\/*\/*/
		/*
			all available db ENUM values
		*/
		$available_order_status = array('NEW','ACKNOWLEDGED','ON_HOLD','PROCESSED','DELIVERED','REJECTED','REFUNDED','OTHER','CUSTOM_1','CUSTOM_2','CUSTOM_3','CUSTOM_4');
		/* cast to array if it is not */
		$sanitized_order_status = !empty($args['query']['order_status']) ? ( (!is_array($args['query']['order_status'])) ? array($args['query']['order_status']) : $args['query']['order_status'] ) : array();
		$sanitized_order_status = array_map('strtoupper',array_map('wppizza_validate_alpha_only', $sanitized_order_status));// make it case insensitive and sanitize
		$sanitized_order_status = array_values(array_intersect($available_order_status, $sanitized_order_status));//intersect and reindex

		$args['query']['order_status'] = !empty($sanitized_order_status) ? $sanitized_order_status : false;



		/*\/*\/*
		#	$args['query']['custom_status']
		#	@param str
		# 	default  ''
		*\/*\/*/
		if(!empty($args['query']['custom_status'])){
			/* cast to array if it is not */
			$sanitized_custom_status = !empty($args['query']['custom_status']) ? ( (!is_array($args['query']['custom_status'])) ? array($args['query']['custom_status']) : $args['query']['custom_status'] ) : array();
			$sanitized_custom_status = array_map('esc_html',array_map('esc_sql', $sanitized_custom_status));// make it case insensitive and sanitize

			$args['query']['custom_status'] = !empty($sanitized_custom_status) ? $sanitized_custom_status : false;
		}

		/*\/*\/*
		#	getting orders from all subsites
		#	set multisite , overriding default filter (only multisite->parent site and only if enabled in settings)
		#
		#	$args['query']['blogs']
		#	@param bool|array
		*\/*\/*/
		/* filtered as per wppizza->settings*/
		$_multisite_orders = apply_filters('wppizza_filter_order_history_all_sites', false);

		if(is_multisite() && isset($args['query']['blogs'])){
			if($args['query']['blogs'] === false){
				$_multisite_orders = false;
			}
			if($args['query']['blogs'] === true){
				$_multisite_orders = true;
			}
			if(is_array($args['query']['blogs']) && !empty($args['query']['blogs'])){
				$_multisite_orders = true;
				$_multisite_blogs = array_flip(array_filter(array_map( 'abs', $args['query']['blogs'] )));//make sure to only have int >=1 and flip id as key for uniqueness and faster index check
			}
			if(is_string($args['query']['blogs']) && is_numeric($args['query']['blogs']) && !empty($args['query']['blogs'])){
				$_multisite_orders = true;
				$selected_blog = (int)$args['query']['blogs'];
				$_multisite_blogs[$selected_blog] = $selected_blog ;//set blog id as key for uniqueness and faster index check
			}
		}

		/*\/*\/*
		#	only getting the count results for the query
		#	ignores $args['pagination'] | $args['format'] | $args['blog_options']
		#
		#	$args['query']['summary']
		#	@param bool
		*\/*\/*/
		$args['query']['summary'] = !empty($args['query']['summary']) ? true : false;


		/*\/*\/*
		#	allow setting of additional arbitrary where clause parameters
		#
		#	$args['query']['custom_parameters']
		#	@param str
		*\/*\/*/
		$args['query']['custom_query'] = (!empty($args['query']['custom_query']) && is_string($args['query']['custom_query']) ) ? $args['query']['custom_query'] : false ;

		/*\/*\/*
		#	setting any pagination / limits
		#	$args['pagination']
		#	@param array
		*\/*\/*/
		$args['pagination']['paged'] = ( !isset($args['pagination']['paged']) || empty($args['pagination']['paged']) || !is_numeric($args['pagination']['paged']) ) ? 0 : (abs((int)$args['pagination']['paged']) - 1 );
		$args['pagination']['limit'] = ( !isset($args['pagination']['limit']) || empty($args['pagination']['limit']) || !is_numeric($args['pagination']['limit']) ) ? ( $no_arguments_passed === true ? $wppizza_options['settings']['admin_order_history_max_results'] : false ): abs((int)$args['pagination']['limit']);


		/*\/*\/*
		#	adding blogoptions and/or class_idents to formatted orders
		#	as they may differ for each in a multiste setup dependng on blog
		#	$args['format']['blog_options']
		#	$args['format']['class_idents']
		#
		#	@param bool
		# 	default false
		#
		# note, dont set $args['format']['blog_options|class_idents'] at all if not set otherwise $args['format'] will return true!
		*\/*\/*/
		$format_blog_options = false;
		if(isset($args['format']['blog_options']) && !empty($args['format']['blog_options'])){
			$args['format']['blog_options'] = true;
			$format_blog_options = true;
		}

		$format_class_idents = false;
		if(isset($args['format']['class_idents']) && !empty($args['format']['class_idents'])){
			$args['format']['class_idents'] = true;
			$format_class_idents = true;
		}

		/*\/*\/*
		#	format output into a somewhat more easily dealt with object
		#	$args['format']
		#	@param bool|array
		# 	default true
		*\/*\/*/
		$args['format'] = (isset($args['format']) && empty($args['format'])) ? false : ( isset($args['format']) && is_array($args['format']) ? $args['format'] : true);

		/*********************************************
		*
		*
		*	prepare where clause from arguments
		*
		*
		*********************************************/
			$where_clause = array();


			/*******************
			#
			#	query by wp_user_id
			#
			*******************/
			/* prepare */
			if($args['query']['wp_user_id'] !== false){
				$where_clause['wp_user_id'] = $wpdb->prepare("wp_user_id = %d", $args['query']['wp_user_id']);
			}

			/*******************
			#
			#	query by email
			#
			*******************/
			/* prepare */
			if($args['query']['email'] !== false){
				/* single value */
				if(count($args['query']['email'])==1){
					/* only getting not set statusses */
					if($args['query']['email'][0] === 'NULL'){
						$where_clause['email'] = "email IS NULL";
					}else{
						$where_clause['email'] = $wpdb->prepare("email = %s ", $args['query']['email'][0]);
					}
				}else{
					$prepare = array();
		    		foreach($args['query']['email'] as $k => $v){
		    			// remove 'NULL' from prepare statement */
		    			if($v != 'NULL'){
		    				$prepare[] = $wpdb->prepare('%s', $v);
		    			}
		    		}

					/* [not-set] was not in array */
					if(!in_array('NULL', $args['query']['email'])){
						$where_clause['email'] = "email IN (".implode(',',$prepare).") ";
					}else{
						$where_clause['email'] = "(email IN (".implode(',',$prepare).") OR email IS NULL )";
					}
				}
			}

			/*******************
			#
			#	query by order_id
			#
			*******************/
			/* prepare */
			if($args['query']['order_id'] !== false){
				$where_clause['id'] = $wpdb->prepare("id = %d", $args['query']['order_id']);
			}

			/*******************
			#
			#	query by hash
			#
			*******************/
			/* prepare */
			if($args['query']['hash'] !== false){
				$where_clause['hash'] = $wpdb->prepare("hash = %s", $args['query']['hash']);
			}
			/*******************
			#
			#	query by order date later than
			#
			*******************/
			/* prepare */
			if($args['query']['order_date_after'] !== false){
				$where_clause['order_date'] = $wpdb->prepare("order_date > %s", date('Y-m-d H:i:s',$args['query']['order_date_after']));
			}

			/*******************
			#
			#	query by payment_status , unless it's null
			#
			*******************/
			/* prepare */
			if($args['query']['payment_status'] !== NULL ){
				/* only one parameter passed */
				if(count($args['query']['payment_status'])==1){
					$where_clause['payment_status'] = $wpdb->prepare("payment_status = %s ", $args['query']['payment_status'][0]);
				}else{
					$prepare = array();
		    		foreach($args['query']['payment_status'] as $k => $v){
		    			$prepare[] = $wpdb->prepare('%s', $v);
		    		}
					$where_clause['payment_status'] = "payment_status IN (".implode(',',$prepare).") ";
				}
			}

			/*******************
			#
			#	query by order_status
			#
			*******************/
			/* prepare */
			if(!empty($args['query']['order_status'])){
				/* only one parameter passed */
				if(count($args['query']['order_status'])==1){
					$where_clause['order_status'] = $wpdb->prepare("order_status = %s ", $args['query']['order_status'][0]);
				}else{
					$prepare = array();
		    		foreach($args['query']['order_status'] as $k => $v){
		    			$prepare[] = $wpdb->prepare('%s', $v);
		    		}
					$where_clause['order_status'] = "order_status IN (".implode(',',$prepare).") ";
				}
			}

			/*******************
			#
			#	query by custom_status
			#
			*******************/
			/* prepare */
			if(!empty($args['query']['custom_status'])){
				/* only one parameter passed */
				if(count($args['query']['custom_status'])==1){
					/* only getting not set statusses */
					if($args['query']['custom_status'][0] == '[not-set]'){
						$where_clause['order_status_user_defined'] = "(order_status_user_defined = '' OR order_status_user_defined IS NULL)";
					}else{
						$where_clause['order_status_user_defined'] = $wpdb->prepare("order_status_user_defined = %s ", $args['query']['custom_status'][0]);
					}
				}else{
					$prepare = array();
		    		foreach($args['query']['custom_status'] as $k => $v){
		    			// remove [not-set] from prepare statement */
		    			if($v != '[not-set]'){
		    				$prepare[] = $wpdb->prepare('%s', $v);
		    			}
		    		}

					/* [not-set] was not in array */
					if(!in_array('[not-set]', $args['query']['custom_status'])){
						$where_clause['order_status_user_defined'] = "order_status_user_defined IN (".implode(',',$prepare).") ";
					}else{
						$where_clause['order_status_user_defined'] = "(order_status_user_defined IN (".implode(',',$prepare).") OR order_status_user_defined = '' OR order_status_user_defined IS NULL )";
					}
				}
			}

			/*******************
			#
			#	query by custom_parameters if set in arguments specifically
			#
			*******************/
			if(!empty($args['query']['custom_query'])){
				$where_clause['custom_query'] = $args['query']['custom_query'];
			}

		/*********************************************************************************************

			getting tables to query
			getting blogoptions at the same time to be able to add those to the respective results set

		********************************************************************************************/
		$blog_tables = array();
		$blog_info = array();
		$blog_options = array();
		$date_format = array();

		/*
			multiple blogs
		*/
		if($_multisite_orders){
 	   		/*get all and loop through blogs*/
 	   		$blogs = $wpdb->get_results("SELECT blog_id FROM ".$wpdb->blogs."", ARRAY_A);
 	   		$max_table_columns = array();
			if ($blogs) {
	        	foreach($blogs as $blog) {
	        		/* if we have specific blogs set skip others */
					if(empty($_multisite_blogs) || isset($_multisite_blogs[$blog['blog_id']])){
	        		switch_to_blog($blog['blog_id']);
	        			/* make sure function exists even if outside admin */
	        			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	        			/*make sure plugin is active*/
	        			if(is_plugin_active(WPPIZZA_PLUGIN_INDEX)){

							/* full orders table name */
							$blog_tables[$blog['blog_id']] = $wpdb->prefix . WPPIZZA_TABLE_ORDERS;

							/* all columns from table */
							$table_columns = $wpdb->get_col("DESC ".$wpdb->prefix . WPPIZZA_TABLE_ORDERS."", 0);
							$blog_columns[$blog['blog_id']] = array_combine($table_columns, $table_columns);
							/* add to max table columns as different tables might have more/or less columns (keys are set with array_combine, to avoid duplicates)*/
							$max_table_columns += $blog_columns[$blog['blog_id']];

							/*bloginfo from blog - multisite, cast to array*/
							$blog_info[$blog['blog_id']] = WPPIZZA() -> helpers -> wppizza_blog_details($blog['blog_id']);

							/*wppizza options, before any filters,  from blog we switched to*/
							if(!empty($args['format'])){
								$blog_options[$blog['blog_id']] = get_option(WPPIZZA_SLUG);
							}

							/* get date options for that blog */
							$date_format[$blog['blog_id']]= array('date' => get_option('date_format'), 'time' => get_option('time_format'));
	        			}
					restore_current_blog();
					}
	        	}
				/*
					set distinct select table columns for each blog,
					based on $max_table_columns to account for columns that do not exist
					in a table
				*/
				$blog_select_columns = array();
				if(!empty($blog_columns)){
				foreach($blog_columns as $bID => $blog_table_columns){
						$this_blog_columns = array();
						foreach($max_table_columns as $column){
							if(isset($blog_table_columns[$column])){
								$this_blog_columns[] = ''.$column.'';
							}else{
								$this_blog_columns[] = 'Null as '.$column.'';
							}

						}
					$blog_select_columns[$bID] = implode(', ',$this_blog_columns);
				}}
				/* end getting select columns */
			}
		}

		/*
			current blog only
		*/
		if (!$_multisite_orders){

			/* full orders table name */
			$blog_tables[$blog_id] = $wpdb->prefix . WPPIZZA_TABLE_ORDERS;

			/*bloginfo from blog - get_blog_details does not exist in non network setups*/
			$blog_info[$blog_id] = WPPIZZA() -> helpers -> wppizza_blog_details($blog_id);

			/* get options, before any filters if formatting*/
			if(!empty($args['format'])){
				$blog_options[$blog_id] = get_option(WPPIZZA_SLUG);
			}
			/* get date options for that blog */
			$date_format[$blog_id]= array('date' => get_option('date_format'), 'time' => get_option('time_format'));
		}

		/************************************************************************************************************
		*
		*
		*	construct and run the queries
		*
		*
		************************************************************************************************************/

			/*
				make sure we have some where clause constructed
				unless $args are specifically set to NULL to get all rows
			*/
			if(empty($where_clause) && !empty($force_all_rows)){
				return __('Error: Empty Query Parameters', 'wppizza-admin');
			}

			/***************************
			*	query getting count only
			*	, no limit
			***************************/
			$table_query = array();
			$totals_query = array();
			$t=0;
			foreach($blog_tables as $blogId => $table){
				/* counts and totals */
				$table_query[$t] = "SELECT ";
				$table_query[$t] .= "COUNT(id) as order_count ";
				$table_query[$t] .= ", SUM(order_total) as order_totals ";
				$table_query[$t] .= "FROM ";
				$table_query[$t] .= "" . $table . " ";
				if(empty($force_all_rows)){
					$table_query[$t] .= "WHERE ";
					$table_query[$t] .= implode(' AND ', $where_clause);
				}
				/* allow filtering - 2nd parameter to identify count query */
				$table_query[$t] = apply_filters('wppizza_filter_orders_query', $table_query[$t], 'count');

			$t++;
			}

			/*
				construct query to get number of results
				and totals before pagination limits
				looping through blogs if necessary
			*/
			if(count($table_query) > 1){
				/* count */
				$query ="SELECT SUM(order_table.order_count) as order_count, SUM(order_table.order_totals) as order_totals FROM (";
				$query .= implode(' UNION ALL ', $table_query );
				$query .=" ) order_table ";
			}else{
				$query = $table_query[0];
			}

			/**********************************
			#
			#	[ini return vars]
			#
			***********************************
			/***
				add blog options to array if set by argument
				and not in multisite setup
			***/
			if(!is_multisite() && !empty($blog_options[$blog_id])){
				$results['blog_options'] = $blog_options[$blog_id];
			}

			/*
				BEFORE LIMIT COUNTS/SUMS
				run the query to get result count and sum before limits
			*/
			$result_sums_before_limit = $wpdb->get_results( $query, ARRAY_A);
			$results['total_number_of_orders'] = !empty($result_sums_before_limit[0]['order_count']) ? $result_sums_before_limit[0]['order_count'] : 0 ;/* just to simplify a bit */
			$results['total_value_of_orders'] = !empty($result_sums_before_limit[0]['order_totals']) ? $result_sums_before_limit[0]['order_totals'] : 0 ;/* just to simplify a bit */

			/**
				holds all used gateway idents
			**/
			$results['gateways_idents'] = array();
			/**
				total value sum of orders query result, LIMITED
			**/
			$results['value_orders_on_page'] = 0;

			/**
				number of orders query result, LIMITED
			**/
			$results['number_orders_on_page'] = 0;

			/********************************************
				only return the counts/totals if set
				and skip full query
			*********************************************/
			if(!empty($args['query']['summary'])){

				$query_totals = array();

				$query_totals['total_number_of_orders'] = $results['total_number_of_orders'];

				$query_totals['total_value_of_orders'] = $results['total_value_of_orders'];

			return $query_totals;
			}

			/***************************
			*	query getting results (limited if set)
			*	,sorted by date
			***************************/
			$table_query = array();
			$t=0;
			foreach($blog_tables as $blogId => $table){
				$table_query[$t] = "SELECT ";
				/*
					if quering multiple tables, we need to set distinct SELECT columns
					to make sure we have the same number of columns (any missing ones will be forced to null)
				*/
				$table_query[$t] .= !empty($blog_select_columns[$blogId]) ? ''.$blog_select_columns[$blogId].'' : '*' ;
				$table_query[$t] .= ", order_date as date_sort, '".$blogId."' as blog_id ";
				$table_query[$t] .= "FROM ";
				$table_query[$t] .= "" . $table . " ";
				if(empty($force_all_rows)){
					$table_query[$t] .= "WHERE ";
					$table_query[$t] .= implode(' AND ', $where_clause);
				}
				/* allow filtering - 2nd parameter to identify select/limit query */
				$table_query[$t] = apply_filters('wppizza_filter_orders_query', $table_query[$t], 'select');
			$t++;
			}
			/*
				construct query , limit, sort
				looping / union all through blogs if necessary
			*/
			if(count($table_query) > 1){
				$query ="";
				$query .= implode(' UNION ALL ', $table_query );
				$query .=" " ;
			}else{
				$query = $table_query[0] ;
			}
			$query .=" ORDER BY date_sort DESC " ;

			/**********************************
			#
			#	- provided we are not querying multiple tables -
			#	determine if we are really only expecting a single row
			#	and if so, limit query
			#	as querying for specific order id, or hash
			#	will(should) only ever return one result
			#
			***********************************/
			if(count($table_query) == 1 && ($args['query']['hash'] !== false || $args['query']['order_id'] !== false)){
				$query .=" LIMIT 0, 1";
			}else{

				/* if pagination/limits are set */
				if( $args['pagination']['limit']>0 || !empty($args['pagination']['limit']) || ($no_argumens_passed === true)){
					$query .=" LIMIT ";
					/* no, limit set , but pagination set to > 0 */
					$query .= (empty($args['pagination']['limit'])) ? $args['pagination']['paged'] : ($args['pagination']['paged'] * $args['pagination']['limit']);
					/* limit set */
					$query .= (!empty($args['pagination']['limit'])) ? ', '.$args['pagination']['limit'] : '';
				}
			}

			/**********************************
			#
			#	run the query (limited if set) to get orders results set
			#
			***********************************/
			$orders = $wpdb->get_results($query, ARRAY_A);

			/******************************************************************
				CONSTRUCT RESULTS SET :
				add date format , blog options,
				unserialize order_ini, customer_ini
				format selected parameters for consistency
			******************************************************************/
			$results['orders'] = array();

			foreach($orders as $key=>$order){

				/* returned from query */
				$order_blog_id = $order['blog_id'];
				/* create unique key made up of blog id and order id */
				$key = $order['blog_id'].'_'.$order['id'];


				/* create as array */
				$results['orders'][$key] = array();

				/* add unique order key made up from blog id and order id */
				$results['orders'][$key]['uoKey'] = $key;

				/* add all order parameters as object, formatting/unserializing some data as required for consistency throughout*/
				foreach($order as $column_key=>$column_val){
					if($column_key == 'initiator' ){/* uppercase gateway */
						$column_val = strtoupper($column_val);
						$initiator = $column_val;
					}
					if($column_key == 'order_status' ){/* lowercase order_status */
						$column_val = strtolower($column_val);
					}
					if($column_key == 'payment_status' ){/* lowercase payment_status */
						$column_val = strtolower($column_val);
					}
					if($column_key == 'order_ini' ){/* unserialize order_ini */
						$column_val = maybe_unserialize($column_val);
					}
					if($column_key == 'customer_ini' ){/* unserialize customer_ini */
						$column_val = maybe_unserialize($column_val);
					}
					if($column_key == 'user_data' ){/* unserialize user_data */
						$column_val = maybe_unserialize($column_val);
					}
					/* some parameters we want to add to the global values returned */
					if($column_key == 'initiator' ){/* uppercase gateway */
						$initiator = $column_val;
					}
					/* some parameters we want to add to the global values returned */
					if($column_key == 'order_total' ){/* uppercase gateway */
						$order_total = $column_val;
					}

				$results['orders'][$key][$column_key] = $column_val;
				}

				/** add blog info */
				$results['orders'][$key]['blog_info'] = $blog_info[$order_blog_id];
				/** add blogs date options/format to order */
				$results['orders'][$key]['date_format'] = $date_format[$order_blog_id];
				/** blog_options per order as in  multisite they might be different for orders from different blogs, simply omit if not formatting **/
				if(!empty($args['format'])){
					$results['orders'][$key]['blog_options'] = $blog_options[$order_blog_id];
				}


				/**
					purely for convenience, using currency set per order
					However
					 - for pre v3.x orders - ['param']['currency'] does not actually exist.
					 - if $args['format'] == false(in backend admin order history), $blog_options do not exist either (as we specifically don't add them above as they are really only needed when outputting an order formatted) so we simply set currency to 'false' to make wppizza_format_price use the global blog options
					 - this would also ONLY really ever become an issue if currencies are DIFFERENT for DIFFERENT ORDERS (or multisite blogs) AND an order IS PRE-V3.X
					so let's make a judgement call and not pollute coding and parameters more than necessary and stick with the above
				**/
				$results['orders'][$key]['currency'] = (!empty($results['orders'][$key]['order_ini']['param']['currency'])) ? $results['orders'][$key]['order_ini']['param']['currency'] : (empty($blog_options[$order_blog_id]['order_settings']['currency_symbol']) ? false : $blog_options[$order_blog_id]['order_settings']['currency_symbol'] );


				/**
					format order (default) if not set to false
				**/
				if(!empty($args['format'])){
					/* format */
					$results['orders'][$key] = WPPIZZA()->order->results_formatted('get_orders', $results['orders'][$key]);
					/* simplify */
					$results['orders'][$key] = WPPIZZA()->order->simplify_order_values($results['orders'][$key],  $format_blog_options,  $format_class_idents);

				}

				/**
					add used gateway ident using key to end up with unique array
				**/
				$results['gateways_idents'][$initiator] = $initiator;
				/**
					add to total ordered amount of shown items WITHIN LIMITS
				**/
				$results['value_orders_on_page'] += $order_total;

				/**
					add to total orderes WITHIN LIMITS
				**/
				$results['number_orders_on_page']++;

			}

	return $results;
	}
	/**************************************************************************************************
	*
	*
	*	get a single order order by blog/hash/wp_user_id/session
	*	to be depracated in favour of get_orders() above
	*
	**************************************************************************************************/
	function fetch_order_details($set_blog_id = false, $order_id = false, $hash = false , $payment_status = false, $user_id = null) {//, $multisite = false , $maxpp = 10
		global $wpdb, $blog_id;

		/* bit overkill, but doesnt hurt:  also restrict query to user id, bypassed for ipn requests and thank you page (dealt with it separately there) */
		if($user_id === null){
			if(is_user_logged_in()){
				$whereclause[] =' wp_user_id = '.get_current_user_id().'';
			}else{
				$whereclause[] =' wp_user_id = 0 ';
			}
		}
		/* getting orders for distinctly set user id  */
		if(is_numeric($user_id)){
			$whereclause[] =' wp_user_id = '.(int)$user_id.'';
		}
		/** in case there's some tampering going on, unless we have specifically set userid to false, make sure there's no result returned */
		if($user_id !== null && !is_numeric($user_id) && $user_id !== false ){
			$whereclause[] =' wp_user_id = -1 ';
		}

		/** restrict to where  by session id */
		if(!is_user_logged_in() && $user_id !== false){
			$whereclause[] ="session_id ='".session_id()."'";
		}
		/* get by order id */
		if($order_id){
			$whereclause[] ='id='.(int)$order_id.'';
		}
		/* get by hash */
		if($hash){
			$whereclause[] ="hash ='".$hash."'";
		}
		/* restrict to paymnt status, if false, get completed only */
		if($payment_status){
			if(is_array($payment_status)){
				$whereclause[] ="payment_status IN ('".implode("','",$payment_status)."') ";
			}else{
				$whereclause[] ="payment_status='".$payment_status."' ";
			}
		}else{
			$whereclause[] ="payment_status='COMPLETED' ";
		}

		/* max per page if quering for multiple */
		$limit_results = '';
//		if(is_numeric($user_id)){
//			$limit_results .= ' LIMIT ';
//			if(!isset($_GET['pg']) || (int)$_GET['pg']<1){
//				$limit_results .= ' 0';
//			}else{
//				$limit_results .=(int)($_GET['pg']-1)*(int)$maxpp;
//			}
//			$limit_results .= ','.(int)$maxpp.' ';
//		}

		$order_table = $wpdb->prefix . WPPIZZA_TABLE_ORDERS;
		/* switching blogs */
		if($set_blog_id && $set_blog_id != $blog_id && $set_blog_id>1){
			/*
				capture the current site's blog id
				as blog_id will change when switch_to_blog is active
			*/
			$current_site_blog_id = $blog_id;
			/*
				switch blog to get blog info and wppizza_options from
				the relevant blog this order is associated with
			*/
			switch_to_blog($set_blog_id);
			/*set order table form blog*/
			$order_table = $wpdb->prefix . WPPIZZA_TABLE_ORDERS;

			/**switch and get wppizza options from blog we switched to **/
			$blog_options[$current_site_blog_id]=get_option(WPPIZZA_SLUG);

			/**switch and get blog details blog we switched to **/
			$blog_info[$current_site_blog_id] = WPPIZZA() -> helpers -> wppizza_blog_details($set_blog_id);

			/* get date options for that blog */
			$date_format[$current_site_blog_id]['date'] = get_option('date_format');
			$date_format[$current_site_blog_id]['time'] = get_option('time_format');

			/**restore current**/
			restore_current_blog();
		}else{
			global $wppizza_options;
			$blog_options[$blog_id] = $wppizza_options;
			$blog_info[$blog_id] = WPPIZZA() -> helpers -> wppizza_blog_details($blog_id);
			/* get date options for that blog */
			$date_format[$blog_id]['date'] = get_option('date_format');
			$date_format[$blog_id]['time'] = get_option('time_format');
		}

		/*******************************************************************
			get single row only - when querying for a specific order by id or hash
		*******************************************************************/
		//if(!$user_id)
		if($user_id === null || $user_id === false ){
			/* set query */
			$query = "SELECT * FROM ".$order_table." WHERE ".implode(' AND ', $whereclause)."  ";
			/* run query*/
			$get_orders = $wpdb->get_row($query, ARRAY_A);
			if ( $get_orders !== null ) {
				/* return even single results as array with key [0] for consistency */
				$orders['orders'][0] = $get_orders;
				$orders['count'] = 1;
			}
		}

		/*******************************************************************
			get all completed orders for this user - order history
			limited to max per page and from all subsites if enabled and multisite
		*******************************************************************/
		if($user_id !== null && $user_id !== false ){

			$query = "SELECT * FROM ".$order_table." WHERE ".implode(' AND ', $whereclause)."  ORDER BY order_date DESC LIMIT 0, 1";
			/* run query */
			$get_orders = $wpdb->get_row($query, ARRAY_A);


			/*
				run query
			*/
			if ( $get_orders !== null ) {
				/*
					now we know there are results, get the count too, no point running it any earlier
				*/
				$orders['orders'] = $get_orders;
				/* run same query without limit and order by */
				$orders['count'] = $wpdb->get_var( "SELECT COUNT(*) FROM  ".$order_table." WHERE ".implode(' AND ', $whereclause)." " );//temp disabled AND payment_status = 'COMPLETED'
			}
		}

		if (isset($orders['count']) && $orders['count']>=1) {
			/** add date format , blog options and unserialize order_ini and customer_ini for each result*/
			foreach($orders['orders'] as $key=>$order){
				/** add blogs date options/format to order */
				$orders['orders'][$key]['date_format'] = $date_format[$blog_id];
				/** blog_info (name etc) as they might be different for different blogs */
				$orders['orders'][$key]['blog_info'] = $blog_info[$blog_id];
				/** blog_options as they might be different for different blogs */
				$orders['orders'][$key]['blog_options'] = $blog_options[$blog_id];
				/** unserialize order ini */
				$orders['orders'][$key]['order_ini'] = maybe_unserialize($order['order_ini']);
				/** unserialize customer ini */
				$orders['orders'][$key]['customer_ini'] = maybe_unserialize($order['customer_ini']);
			}
		}else{
			/* return false if order count <=0 */
			$orders = false;
		}

	return $orders;
	}

	/**************************************************************************************************
	*
	*
	*	map session order details to db fields
	*	returns false if we cannot checkout yet
	*
	*
	**************************************************************************************************/
	function map_order($user_session, $checkout_parameters_only = false) {
		global $current_user, $wpdb, $blog_id, $wppizza_options;
		/*
			grab order details in current session
			, unset irrelevant for storing,
			add some unique id and session id(to make sure it is really unique)
		*/
		$order_session = WPPIZZA()->session->sort_and_calculate_cart(true);
		$order_session_checkout_parameters = $order_session['checkout_parameters'];
		/** only get is_checkout, can_checkout etc - orderpage **/
		if(!empty($checkout_parameters_only)){
			return $order_session_checkout_parameters;
		}

		/* unset unnecessary parameters that are only used when calculating things in pages etc but are not relevant when storing data in db*/
		unset($order_session['checkout_parameters']);


		/* add session id */
		$order_session['info']['session_id'] = session_id();
		/* add unique ident */
		$order_session['info']['unique_id'] = (function_exists('microtime')) ? microtime(true) : time();

		/*
			current date based on WP_time
		*/
		$order_date = date('Y-m-d H:i:s', WPPIZZA_WP_TIME );

		/*
			UTC
		*/
		$order_date_utc = date('Y-m-d H:i:s', WPPIZZA_UTC_TIME );

		/*
			customer
		*/
		$customer_data = apply_filters('wppizza_filter_add_to_customer_ini', $user_session, 'session');

		/*
			email
			maybe serialize(just to be sure)
			and truncate to 64 max (as db field is indexed VARCHAR 64)
		*/
		$cemail = !empty($customer_data['cemail']) ? substr(maybe_serialize($customer_data['cemail']),0, 63) : '' ;


		/*

			map data

		*/
		$wp_user_id 				= $current_user->ID; /* user id or 0 if not logged in */
		$order_date					= $order_date; /* current time based on WP timezone */
		$order_date_utc				= $order_date_utc; /* utc */
		$order_update				= '0000-00-00 00:00:00';/* 0  until status change, notes added or similar */
		$order_delivered			= '0000-00-00 00:00:00'; /* initialize as 0 when adding to db */
		$customer_details 			= '';
		$order_details				= '';
		$order_status 				= 'NEW';
		$order_ini 					= maybe_serialize(apply_filters('wppizza_filter_add_to_order_ini',$order_session));
		$order_no_of_items 			= $order_session['summary']['number_of_items'];
		$order_items_total 			= $order_session['summary']['total_price_items'];
		$order_discount 			= $order_session['summary']['discount'];
		$order_taxes 				= !empty($order_session['summary']['taxes']) ?  $order_session['summary']['taxes'] : 0 ;
		$order_taxes_included 		= !empty($order_session['param']['tax_included']) ?  'Y' : 'N' ;
		$order_delivery_charges 	= $order_session['summary']['delivery_charges'];
		$order_handling_charges 	= $order_session['summary']['handling_charges'];
		$order_tips 				= $order_session['summary']['tips'];
		$order_self_pickup 			= !empty($order_session['summary']['self_pickup']) ?  'Y' : 'N' ;
		$order_total 				= $order_session['summary']['total'];
		$order_refund 				= 0;
		$customer_ini 				= maybe_serialize($customer_data);/* allow arbitrary array data to be added/stored  in customer_ini (i.e user session) to - perhaps -	save some additional values without outputting them anywhere by default */
		$payment_status 			= 'INITIALIZED';
		$transaction_id 			= '';
		$transaction_details 		= '';
		$transaction_errors 		= '';
		$display_errors 			= '';
		$validate_initiator 		= wppizza_validate_alpha_only($user_session[''.WPPIZZA_SLUG.'_gateway_selected']);/* php 5.3 */
		$initiator 					= !empty($validate_initiator) ? $validate_initiator : 'COD';
		$mail_sent 					= 'N';
		$mail_error 				= '';
		$notes 						= '';
		$session_id 				= session_id();
		$email 						= !empty($cemail) ? wppizza_maybe_encrypt_decrypt($cemail, true, 256, true) : '' ;//store email encrypted using WPPIZZA_CRYPT_KEY (so it can be decrypted for db queries - notably privacy export)
		$ip_address 				= ( empty($wppizza_options['tools']['privacy']) || (!empty($wppizza_options['tools']['privacy']) && !empty($wppizza_options['tools']['privacy_keep_ip_address'])) ) ? $_SERVER['REMOTE_ADDR'] : wppizza_anonymize_data('ip_address', $_SERVER['REMOTE_ADDR']) ;//store ip addresses anonimised if privacy enabled unless specifically set
		$user_defined 				= maybe_serialize(apply_filters('wppizza_db_column_user_defined','', $order_session, $user_session));/* a text field that can be freely used - serialized if necessary*/
		$user_data=array();
			$user_data['HTTP_USER_AGENT']=!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '--n/a--';
			$user_data['HTTP_REFERER']=!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '--n/a--';
		$user_data = empty($wppizza_options['tools']['privacy']) || !empty($wppizza_options['tools']['privacy_keep_browser_data']) ? maybe_serialize($user_data) : '' ;
		/*

			store data in db

		*/
		$order_data = array();
		/** map fields to data, all fields, but autoinserts or irrelevant for first insert commented out */
		/*only inserting or updateing INITIALIZED order */
		$order_data['wp_user_id'] = array('data' => $wp_user_id, 'type' => '%d' );
		$order_data['order_date'] = array('data' => $order_date, 'type' => '%s' );
		$order_data['order_date_utc'] = array('data' => $order_date_utc, 'type' => '%s' );
		$order_data['order_update'] = array('data' => $order_update, 'type' => '%s' );
		$order_data['order_delivered'] = array('data' => $order_delivered, 'type' => '%s' );
		$order_data['customer_details'] = array('data' => $customer_details, 'type' => '%s' );
		$order_data['order_details'] = array('data' => $order_details, 'type' => '%s' );
		$order_data['order_status'] = array('data' => $order_status, 'type' => '%s' );
		$order_data['order_ini'] = array('data' => $order_ini, 'type' => '%s' );
		$order_data['order_no_of_items'] = array('data' => $order_no_of_items, 'type' => '%d' );
		$order_data['order_items_total'] = array('data' => $order_items_total, 'type' => '%f' );
		$order_data['order_discount'] = array('data' => $order_discount, 'type' => '%f' );
		$order_data['order_taxes'] = array('data' => $order_taxes, 'type' => '%f' );
		$order_data['order_taxes_included'] = array('data' => $order_taxes_included, 'type' => '%s' );
		$order_data['order_delivery_charges'] = array('data' => $order_delivery_charges, 'type' => '%f' );
		$order_data['order_handling_charges'] = array('data' => $order_handling_charges, 'type' => '%f' );
		$order_data['order_tips'] = array('data' => $order_tips, 'type' => '%f' );
		$order_data['order_self_pickup'] = array('data' => $order_self_pickup, 'type' => '%s' );
		$order_data['order_total'] = array('data' => $order_total, 'type' => '%s' );
		$order_data['order_refund'] = array('data' => $order_refund, 'type' => '%f' );
		$order_data['customer_ini'] = array('data' => $customer_ini, 'type' => '%s' );
		$order_data['payment_status'] = array('data' => $payment_status, 'type' => '%s' );
		$order_data['transaction_id'] = array('data' => $transaction_id, 'type' => '%s' );
		$order_data['transaction_details'] = array('data' => $transaction_details, 'type' => '%s' );
		$order_data['transaction_errors'] = array('data' => $transaction_errors, 'type' => '%s' );
		$order_data['display_errors'] = array('data' => $display_errors, 'type' => '%s' );
		$order_data['initiator'] = array('data' => $initiator, 'type' => '%s' );
		$order_data['mail_sent'] = array('data' => $mail_sent, 'type' => '%s' );
		$order_data['mail_error'] = array('data' => $mail_error, 'type' => '%s' );
		$order_data['notes'] = array('data' => $notes, 'type' => '%s' );
		$order_data['session_id'] = array('data' => $session_id, 'type' => '%s' );
		$order_data['email'] = array('data' => $email, 'type' => '%s' );
		$order_data['ip_address'] = array('data' => $ip_address, 'type' => '%s' );
		$order_data['user_data'] = array('data' => $user_data, 'type' => '%s' );
		$order_data['user_defined'] = array('data' => $user_defined, 'type' => '%s' );



		/**
			added filtering - not used in plugin
			to allow other plugins to add their own data if - for example - they
			have added their own columns (or indeed change what goes in it)
		**/
		$order_data = apply_filters('wppizza_filter_db_column_data', $order_data, $user_session);


		$order = array();
		$order['data'] = array();
		$order['type'] = array();
		foreach($order_data as $key=>$val){
			$order['data'][$key] = $val['data'];
			$order['type'][] = $val['type'];
		}
		/* get session checkout parameters */
		$order['checkout_parameters'] = $order_session_checkout_parameters;

	return $order;
	}
}
?>