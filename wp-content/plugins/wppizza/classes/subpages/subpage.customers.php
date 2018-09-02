<?php
/**
* WPPIZZA_CUSTOMERS Class
*
* @package     WPPIZZA
* @subpackage  Submenu Pages / Classes / WPPIZZA_CUSTOMERS
* @copyright   Copyright (c) 2015, Oliver Bach
* @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
* @since       3.0
*
*/
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/


/************************************************************************************************************************
*
*
*	WPPIZZA_CUSTOMERS
*
*
************************************************************************************************************************/
class WPPIZZA_CUSTOMERS{

	/*
	* class ident
	* @var str
	* @since 3.0
	*/
	private $class_key='customers';/*to help consistency throughout class in various places*/
	/*
	* titles/labels
	* @var str
	* @since 3.0
	*/
	private $submenu_page_header;
	private $submenu_page_title;
	private $submenu_caps_title;
	private $submenu_link_label;
	private $submenu_priority = 120;
	function __construct() {


		/*titles/labels throughout class*/
		add_action('init', array( $this, 'init_admin_lables') );
		/** registering submenu page -> priority 120 **/
		add_action('admin_menu', array( $this, 'wppizza_register_submenu_page'), $this->submenu_priority );
		/*register capabilities for this page*/
		add_filter('wppizza_filter_define_caps', array( $this, 'wppizza_filter_define_caps'), $this->submenu_priority);
		/**load admin ajax file**/
		add_action('wp_ajax_wppizza_admin_'.$this->class_key.'_ajax', array($this, 'set_admin_ajax') );	

	}
	/******************
	*	@since 3.0.26
    *	[admin ajax include file]
    *******************/
	public function init_admin_lables(){
		/*titles/labels throughout class*/
		$this->submenu_page_header	=	apply_filters('wppizza_filter_admin_label_page_header_'.$this->class_key.'', __('Customers','wppizza-admin'));
		$this->submenu_page_title	=	apply_filters('wppizza_filter_admin_label_page_title_'.$this->class_key.'', __('Manage Customers','wppizza-admin'));
		$this->submenu_caps_title	=	apply_filters('wppizza_filter_admin_label_caps_title_'.$this->class_key.'', __('Customers','wppizza-admin'));
		$this->submenu_link_label	=	apply_filters('wppizza_filter_admin_label_link_label_'.$this->class_key.'', __('&middot; Customers','wppizza-admin'));		
	}
	/******************
	*	@since 3.0
    *	[admin ajax include file]
    *******************/
	public function set_admin_ajax(){
		require(WPPIZZA_PATH.'ajax/admin.ajax.wppizza.php');
		die();
	}	
	/*********************************************************
	*
	*	[register submenu page]
	*	@since 3.0
	*
	*********************************************************/
	public function wppizza_register_submenu_page(){
		$submenu_page= array(
			'url' => 'edit.php?post_type='.WPPIZZA_SLUG.'',
			'title' => ''.WPPIZZA_NAME.' '.$this->submenu_page_title,
			'link_label' => $this->submenu_link_label,
			'caps' => 'wppizza_cap_'.$this->class_key.'',
			'key' => $this->class_key,
			'callback' => array($this, 'wppizza_admin_manage_sections')
		);
		/**add submenu page**/
		$wppizza_submenu_page=add_submenu_page($submenu_page['url'], $submenu_page['title'], $submenu_page['link_label'], $submenu_page['caps'], $submenu_page['key'], $submenu_page['callback']);
	}
	/*********************************************************
	*
	*	[echo manage settings]
	*
	*	wrap settings sections into div->form
	*	add uniquely identifiable id's / classes
	*	add h2 text
	*	add uniquely identifiable hidden input
	*	add submit button
	*
	*	@since 3.0
	*	@return str
	*
	*********************************************************/
	public function wppizza_admin_manage_sections(){
		/*
			wppizza post type only
		*/
		$screen = get_current_screen();
		if($screen->post_type != WPPIZZA_POST_TYPE){return;}
		
		
		/**wrap settings sections into div->form */
		echo'<div id="'.WPPIZZA_SLUG.'-'.$this->class_key.'" class="'.WPPIZZA_SLUG.'-wrap  wrap '.WPPIZZA_SLUG.'-'.$this->class_key.'-wrap">';


		echo"<div class='".WPPIZZA_SLUG."-admin-pageheader'>";
			echo"<h2>";
				echo"<span id='".WPPIZZA_SLUG."-header'>";

					echo"".WPPIZZA_NAME." ".$this->submenu_page_header."";

					/*
						skip displaying if numeric as we are only searching for one integer/user id and it would look silly
					*/
					$search_term = wppizza_validate_string((!empty($_GET['uid'])) ? $_GET['uid'] : (!empty($_GET['s']) ? $_GET['s'] : '' )) ;
					if(!empty($search_term) && !is_numeric($search_term)){
						echo' - "'.$search_term.'"';
					}
				echo"</span>";
			echo"</h2>";

		echo"</div>";

			/* search only if not uid*/
			//if(empty($_GET['uid'])){
				echo"<table id='".WPPIZZA_SLUG."_".$this->class_key."_search'>";
					echo"<tbody>";
						echo"<tr>";
							echo"<td>";
								echo"<form action='".$_SERVER['PHP_SELF']."' method='GET'>";
									echo"<label>";
										echo "<input type='hidden' name='post_type' size='20' value='".WPPIZZA_SLUG."' />";
										echo "<input type='hidden' name='page' size='20' value='".$this->class_key."' />";

										/* only non numeric ones, the rest is silly really */
										$s = !empty($_GET['s']) ? wppizza_validate_string($_GET['s']) : '';
										$s=(!empty($s) && !is_numeric($s))? $s : '' ;
										echo "<input type='text' id='".WPPIZZA_SLUG."_".$this->class_key."_search_value' name='s' size='20' value='".$s."' />";

										echo "<input type='submit' id='".WPPIZZA_SLUG."_".$this->class_key."_do_search' class='button' value='".__('Search Customers', 'wppizza-admin')."' />";
									echo'</label>';
								echo'</form>';
							echo"</td>";
						echo"</tr>";
					echo"</tbody>";
				echo"</table>";
			//}

			/*customer_list*/
			echo"<div id='".WPPIZZA_SLUG."_".$this->class_key."_results'>";
				echo $this->wppizza_customer_list_markup();
			echo"</div>";


		echo'</div>';
	}

	/*********************************************************
	*
	*	[define caps]
	*	@since 3.0
	*
	*********************************************************/
	function wppizza_filter_define_caps($caps){
		/**add editing capability for this page**/
		$caps[$this->class_key]=array('name'=>$this->submenu_caps_title ,'cap'=>'wppizza_cap_'.$this->class_key.'');
		// let's not enable/list this option for now....probably not required anyway as one should also delete/reassign orders to someone else ...
		//$caps[$this->class_key.'-delete-customers']=array('name'=>__('Delete Customers', 'wppizza-admin') ,'cap'=>'wppizza_cap_delete_customers');
		return $caps;
	}

	/*********************************************************
	*
	*	[helper]
	* 	get customer list
	*	@since 3.0
	*
	*********************************************************/
	function wppizza_customer_list_markup($limit = 10){
		//global $wppizza_options;
		$get_blog_url = get_bloginfo('url');

		/****

			get customers info and no of customers for pagination

		****/
		$customers = WPPIZZA()->db->get_customers(false, $limit);

		/****

			get pagination

		*****/
		$pagination = WPPIZZA()->admin_helper->admin_pagination($customers['total_number_of_customers'], $limit, false);

		/****

			pagination counts markup

		****/
		$markup_pagination_info = array();
		$markup_pagination_info['span_left'] = '<span class="'.WPPIZZA_SLUG.'-pagination-left">'.$pagination['on_page'].' '.__('of','wppizza-admin').' '.$pagination['total_count'].'</span>';
		$markup_pagination_info['span_right'] = '<span class="'.WPPIZZA_SLUG.'-pagination-right">'.$pagination['pages'] .'</span>';
		/**
			allow filtering of pagination_info
		**/
		$markup_pagination_info = apply_filters('wppizza_filter_'.$this->class_key.'_pagination_info', $markup_pagination_info, $pagination);
		$markup_pagination_info =implode('',$markup_pagination_info);


		/****

			header/footer markup

		****/
		$markup_header_footer = array();

		$markup_header_footer['tro'] = "<tr>";

			$markup_header_footer['tho_user_id'] = "<th scope='col' class='manage-column ".WPPIZZA_SLUG."-".$this->class_key."-column-user_id'>";
				$markup_header_footer['th_user_id'] = __('ID','wppizza-admin');
			$markup_header_footer['thc_user_id'] = "</th>";

			$markup_header_footer['tho_name'] = "<th scope='col' class='manage-column ".WPPIZZA_SLUG."-".$this->class_key."-column-name'>";
				$markup_header_footer['th_name'] = __('Name','wppizza-admin');
			$markup_header_footer['thc_name'] = "</th>";

			$markup_header_footer['tho_email'] = "<th scope='col' class='manage-column ".WPPIZZA_SLUG."-".$this->class_key."-column-email'>";
				$markup_header_footer['th_email'] = __('Email','wppizza-admin');
			$markup_header_footer['thc_email'] = "</th>";

			$markup_header_footer['tho_purchase'] = "<th scope='col' class='manage-column ".WPPIZZA_SLUG."-".$this->class_key."-column-purchases'>";
				$markup_header_footer['th_purchase'] = __('Orders / Items','wppizza-admin');
			$markup_header_footer['thc_purchase'] = "</th>";

			$markup_header_footer['tho_avg'] = "<th scope='col' class='manage-column ".WPPIZZA_SLUG."-".$this->class_key."-column-averages'>";
				$markup_header_footer['th_avg'] = "".__('Avg. / Order','wppizza-admin')."";
			$markup_header_footer['thc_avg'] = "</th>";

			$markup_header_footer['tho_spent'] = "<th scope='col' class='manage-column ".WPPIZZA_SLUG."-".$this->class_key."-column-total-spent'>";
				$markup_header_footer['th_spent'] = "".__('Total Spent','wppizza-admin')."";
			$markup_header_footer['thc_spent'] = "</th>";

			$markup_header_footer['tho_date_created'] = "<th scope='col' class='manage-column ".WPPIZZA_SLUG."-".$this->class_key."-column-date_created'>";
				$markup_header_footer['th_date_created'] = "".__('Date Created','wppizza-admin')."";
			$markup_header_footer['thc_date_created'] = "</th>";

			$markup_header_footer['tho_icons'] = "<th scope='col' class='manage-column ".WPPIZZA_SLUG."-".$this->class_key."-column-user_id'>";
				$markup_header_footer['th_icons'] = __('Profile','wppizza-admin');
			$markup_header_footer['thc_icons'] = "</th>";

		$markup_header_footer['trc'] = "</tr>";
		/**
			allow filtering of header footer markup
		**/
		$markup_header_footer = apply_filters('wppizza_filter_'.$this->class_key.'_header_footer', $markup_header_footer, $customers);
		$markup_header_footer =implode('',$markup_header_footer);


		/**************************************************************************************
		*
		*
		*
		*	markup to return/output
		*
		*
		*
		**************************************************************************************/
		$markup=array();

			/**
				pagination top
			**/
			$markup['pagination_top']='<div class="widefat '.WPPIZZA_SLUG.'-pagination '.WPPIZZA_SLUG.'-pagination-top">';
				$markup['pagination_top'].=$markup_pagination_info;
			$markup['pagination_top'].='</div>';


			/**
				customer list table
			**/
			$markup['orders_table_open']="<table id='".WPPIZZA_SLUG."_list_".$this->class_key."' class='widefat fixed striped'>";
				/**
					orders table header
				**/
				$markup['thead']="<thead>";
					$markup['thead'].=$markup_header_footer;
				$markup['thead'].="</thead>";

				/**
					orders table footer
				**/
				$markup['tfoot']="<tfoot>";
					$markup['tfoot'].=$markup_header_footer;
				$markup['tfoot'].="</tfoot>";


				/**
					the customers list
				**/
				$markup['tbody_open']="<tbody id='the-list'>";

				/*no customers .....*/
				if(count($customers['results_set'])<=0){
					$markup['tbody_no_results']="<tr><td colspan='8' id='".WPPIZZA_SLUG."-".$this->class_key."-no-results'>".__('no results found','wppizza-admin')."</td></tr>";
				}

				if(count($customers['results_set'])>0){
				foreach($customers['results_set'] as $cID=>$customer){
					/**
						ini new empty array for this customer
					**/
					$customer_markup = array();

					/****************************************************************************
					*
					*	[row]
					*
					****************************************************************************/
					/*open tr*/
					$customer_markup['tro'] = "<tr id='".WPPIZZA_SLUG."-".$this->class_key."-".$cID."' class='".WPPIZZA_SLUG."-".$this->class_key."'>";

						/*
							user id
						*/
						$customer_markup['tdo_user_id'] ="<td  id='".WPPIZZA_SLUG."-".$this->class_key."-column-user_id-".$cID."' class='".WPPIZZA_SLUG."-".$this->class_key."-column-user_id'>";
							$customer_markup['user_id'] = (empty($customer['user_registered'])) ? '# '.$customer['wp_user_id'].'' : '<a href="'.$get_blog_url.'/wp-admin/edit.php?post_type='.WPPIZZA_SLUG.'&page='.$this->class_key.'&s='.$customer['wp_user_id'].'"># '.$customer['wp_user_id'].'</a>';
						$customer_markup['tdc_user_id'] = "</td>";
						/*
							user name
						*/
						$customer_markup['tdo_name'] ="<td  id='".WPPIZZA_SLUG."-".$this->class_key."-column-name-".$cID."' class='".WPPIZZA_SLUG."-".$this->class_key."-column-name'>";
							$customer_markup['name'] = (empty($customer['user_name'])) ? '' : $customer['user_name'];
							$customer_markup['nicename'] = (!empty($customer['user_name'])) ? '<br />['.$customer['user_user_nicename'].']' : ''.$customer['user_user_nicename'].'';
						$customer_markup['tdc_name'] = "</td>";
						/*
							email
						*/
						$customer_markup['tdo_email'] ="<td  id='".WPPIZZA_SLUG."-".$this->class_key."-column-email-".$cID."' class='".WPPIZZA_SLUG."-".$this->class_key."-column-email'>";
							$customer_markup['email'] = (empty($customer['user_email'])) ? __('unknown','wppizza-admin') : $customer['user_email'];
						$customer_markup['tdc_email'] = "</td>";
						/*
							Orders
						*/
						$customer_markup['tdo_purchases'] ="<td  id='".WPPIZZA_SLUG."-".$this->class_key."-column-purchases-".$cID."' class='".WPPIZZA_SLUG."-".$this->class_key."-column-purchases'>";
							$customer_markup['purchases'] = $customer['user_orders_order_count'].' / '.$customer['user_orders_total_items'];
						$customer_markup['tdc_purchases'] = "</td>";

						/*
							Averages
						*/
						$customer_markup['tdo_averages'] ="<td  id='".WPPIZZA_SLUG."-".$this->class_key."-column-averages-".$cID."' class='".WPPIZZA_SLUG."-".$this->class_key."-column-averages'>";
							$customer_markup['averages'] = wppizza_format_price($customer['user_orders_avg_spent']);
						$customer_markup['tdc_averages'] = "</td>";
						/*
							Total Spent
						*/
						$customer_markup['tdo_total_spent'] ="<td  id='".WPPIZZA_SLUG."-".$this->class_key."-column-date_created-".$cID."' class='".WPPIZZA_SLUG."-".$this->class_key."-column-total_spent'>";
							$customer_markup['total_spent'] = wppizza_format_price($customer['user_orders_total_value']);
						$customer_markup['tdc_total_spent'] = "</td>";
						/*
							Date Created
						*/
						$customer_markup['tdo_date_registered'] ="<td  id='".WPPIZZA_SLUG."-".$this->class_key."-column-date_registered-".$cID."' class='".WPPIZZA_SLUG."-".$this->class_key."-column-date_registered'>";
							$ts=strtotime($customer['user_registered']);
							$customer_markup['date_registered'] = (empty($customer['user_registered'])) ? __('unknown','wppizza-admin') : date('d M Y',$ts).'<br/>'.date('H:i',$ts);
						$customer_markup['tdc_date_registered'] = "</td>";
						/*
							icons/orders
						*/
						$customer_markup['tdo_icons'] ="<td  id='".WPPIZZA_SLUG."-".$this->class_key."-column-icons-".$cID."' class='".WPPIZZA_SLUG."-".$this->class_key."-column-icons'>";
							if(!empty($customer['user_registered'])){
								$customer_markup['icons'] ="<a href='".$get_blog_url."/wp-admin/edit.php?post_type=".WPPIZZA_POST_TYPE."&page=orderhistory&uid=".$customer['wp_user_id']."' class='".WPPIZZA_SLUG."-dashicons dashicons-chart-line' title='".__('Show orders for user', 'wppizza-admin').": ".$customer['wp_user_id']."'></a>";
								$customer_markup['icons'] .="<a href='".$get_blog_url."/wp-admin/user-edit.php?user_id=".$customer['wp_user_id']."' class='".WPPIZZA_SLUG."-dashicons dashicons-edit' title='".__('Edit user profile', 'wppizza-admin').": ".$customer['wp_user_id']."'></a>";
							}
						$customer_markup['tdc_icons'] = "</td>";

					/*close tr*/
					$customer_markup['trc'] = "</tr>";

					/****************************************************************************
					*
					*	[implode tr for output]
					*
					****************************************************************************/
					$markup[$cID] = implode('',$customer_markup);
				}}
				/**********************************
					end customer tr
				**********************************/

				$markup['tbody_close']="</tbody>";

			$markup['orders_table_close']='</table>';

			/**
				pagination bottom
			**/
			$markup['pagination_bottom']='<div class="widefat '.WPPIZZA_SLUG.'-pagination '.WPPIZZA_SLUG.'-pagination-bottom">';
				$markup['pagination_bottom'].=$markup_pagination_info;
			$markup['pagination_bottom'].='</div>';

		/**
			allow filtering of entire markup
		**/
		$markup= apply_filters('wppizza_filter_'.$this->class_key.'_markup', $markup);
		$markup=implode('',$markup);

		return $markup;
	}
}

/***************************************************************
*
*	[ini]
*
***************************************************************/
$WPPIZZA_CUSTOMERS = new WPPIZZA_CUSTOMERS();
?>