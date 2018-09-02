<?php
/**
* WPPIZZA_REPORTS Class
*
* @package     WPPIZZA
* @subpackage  Submenu Pages / Classes / Reports
* @copyright   Copyright (c) 2015, Oliver Bach
* @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
* @since       3.0
*
*
*/
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/


/************************************************************************************************************************
*
*
*	WPPIZZA_REPORTS
*
*
************************************************************************************************************************/
class WPPIZZA_REPORTS{
	/*
	* class ident
	* @var str
	* @since 3.0
	*/
	private $class_key='reports';/*to help consistency throughout class in various places*/
	/*
	* saved wppizza option array key - not required here
	* @var str
	* @since 3.0
	*/
	//private $option_key='reports';
	/*
	* titles/lables
	* @var str
	* @since 3.0
	*/
	private $submenu_page_header;
	private $submenu_page_title;
	private $submenu_caps_title;
	private $submenu_link_label;
	private $submenu_priority = 110;
	/******************************************************************************************************************
	*
	*	[CONSTRUCTOR]
	*
	*	Setup wppizza_meal_sizes subpage
	*	@since 3.0
	*
	******************************************************************************************************************/

	function __construct() {

		/*titles/labels throughout class*/
		add_action('init', array( $this, 'init_admin_lables') );
		/** registering submenu page -> priority 110 **/
		add_action( 'admin_menu', array( $this, 'wppizza_register_submenu_page'), $this->submenu_priority );

		/*register capabilities for this page*/
		add_filter('wppizza_filter_define_caps', array( $this, 'wppizza_filter_define_caps'), $this->submenu_priority);
		/**enqueue css/js**/
		add_action('admin_enqueue_scripts', array( $this, 'wppizza_enqueue_admin_scripts_and_styles'));


		/*execute some helper functions once to use their return multiple times */
		add_action('current_screen', array( $this, 'wppizza_add_helpers') );

		/**admin ajax**/
		add_action('wp_ajax_wppizza_admin_'.$this->class_key.'_ajax', array($this, 'set_admin_ajax') );
	}
	
	/******************
	*	@since 3.0.26
    *	[admin ajax include file]
    *******************/
	public function init_admin_lables(){
		/*titles/labels throughout class*/
		$this->submenu_page_header	=	apply_filters('wppizza_filter_admin_label_page_header_'.$this->class_key.'', __('Reports','wppizza-admin'));
		$this->submenu_page_title	=	apply_filters('wppizza_filter_admin_label_page_title_'.$this->class_key.'', __('Manage Reports','wppizza-admin'));
		$this->submenu_caps_title	=	apply_filters('wppizza_filter_admin_label_caps_title_'.$this->class_key.'', __('Reports','wppizza-admin'));
		$this->submenu_link_label	=	apply_filters('wppizza_filter_admin_label_link_label_'.$this->class_key.'', __('&middot; Reports','wppizza-admin'));
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
	*	[add global helpers and enque js]
	*	@since 3.0
	*
	*********************************************************/
	public function wppizza_add_helpers($current_screen){
		if($current_screen->id == ''.WPPIZZA_POST_TYPE.'_page_'.$this->class_key.'' && $current_screen->post_type == WPPIZZA_POST_TYPE){
			/**
				[get data set]
			**/
			$export = !empty($_GET['export']) ? true : false;
			$this->report_data_set=$this->wppizza_report_dataset($export);
			/**
				[export]
			**/
			$this->wppizza_report_export($this->report_data_set);
		}
	}


	/*********************************************************
	*
	*		[add scripts and styles for reports screen]
	*
	*********************************************************/
	function wppizza_enqueue_admin_scripts_and_styles(){
		global $current_screen, $wp_styles, $wp_scripts;
      	/**include reporting js**/
      	if($current_screen->id == ''.WPPIZZA_POST_TYPE.'_page_'.$this->class_key.'' && $current_screen->post_type == WPPIZZA_POST_TYPE){

			/* Get the WP built-in jquery-ui-core version to use for jquery ui*/
			$jquery_ui_core_version = $wp_scripts->registered['jquery-ui-core']->ver;

			/************
				css
			***********/
			/*datepicker*/
			wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/'.$jquery_ui_core_version.'/themes/smoothness/jquery-ui.css');

			/************
				js
			***********/
			wp_enqueue_script('jquery-ui-datepicker');

    		wp_register_script(WPPIZZA_SLUG.'_'.$this->class_key.'', plugins_url( 'js/scripts.admin.'.$this->class_key.'.js', WPPIZZA_PLUGIN_PATH ), array('jquery'), WPPIZZA_VERSION ,true);
    		wp_enqueue_script(WPPIZZA_SLUG.'_'.$this->class_key.'');

	      	wp_register_script(WPPIZZA_SLUG.'-flot', plugins_url( 'js/jquery.flot.min.js', WPPIZZA_PLUGIN_PATH ), array('jquery'), WPPIZZA_VERSION ,true);
	      	wp_enqueue_script(WPPIZZA_SLUG.'-flot');

      		wp_register_script(WPPIZZA_SLUG.'-flotcats', plugins_url( 'js/jquery.flot.categories.min.js', WPPIZZA_PLUGIN_PATH ), array('jquery'), WPPIZZA_VERSION ,true);
      		wp_enqueue_script(WPPIZZA_SLUG.'-flotcats');
      	}
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
		echo'<div id="'.WPPIZZA_SLUG.'-'.$this->class_key.'" class="'.WPPIZZA_SLUG.'-wrap  '.WPPIZZA_SLUG.'-'.$this->class_key.'-wrap">';

		echo"<div class='".WPPIZZA_SLUG."-admin-pageheader'>";
			echo"<h2><span id='".WPPIZZA_SLUG."-header'>".WPPIZZA_NAME." ".$this->submenu_page_header."</span></h2>";
		echo"</div>";

		echo $this->wppizza_report_range_select($this->report_data_set);

		echo $this->wppizza_report_markup($this->report_data_set);
		$this->wppizza_report_js($this->report_data_set);

		echo'</div>';
	}

	private function wppizza_report_range_select($data_set){
		/**make some vars to use**/
		$selectedReport=!empty($_GET['report']) ? $_GET['report'] : '';
		$fromVal=!empty($_GET['from']) ? $_GET['from'] : '';
		$toVal=!empty($_GET['to']) ? $_GET['to'] : '';
		$exportLabel=($data_set['view']=='ini') ? __('Export All','wppizza-admin') : __('Export Range','wppizza-admin');

		$output='';
		$output.='<div id="wppizza-reports-range"  class="button">';

			$output.='<span id="wppizza-reports-range-select">';
				$output.='<select id="wppizza-reports-set-range">';
					$output.='<option value="" >'.__('Overview','wppizza-admin').'</option>';
					foreach($data_set['reportTypes'] as $rkey=>$rArr){
						$sel=($selectedReport==$rkey) ? 'selected="selected"' : '' ;
						$output.='<option value="'.$rkey.'" '.$sel.'>'.$rArr['lbl'].'</option>';
					}
					if(isset($_GET['from']) && isset($_GET['to'])){
						$output.='<option selected="selected">'.__('Custom Range','wppizza-admin').'</option>';
					}
				$output.='</select>';
			$output.='</span>';

			$output.='<span id="wppizza-reports-range-set">';
				$output.=''.__('Custom range','wppizza-admin').' : ';
				$output.='<input type="text" size="9" placeholder="yyyy-mm-dd" value="'.$fromVal.'" name="wppizza_reports_start_date" id="wppizza_reports_start_date" readonly="readonly" />';
				$output.='<input type="text" size="9" placeholder="yyyy-mm-dd" value="'.$toVal.'" name="wppizza_reports_end_date" id="wppizza_reports_end_date" readonly="readonly" />';
				$output.='<input type="button" class="button" value="'.__('Go','wppizza-admin').'" id="wppizza_reports_custom_range" />';
			$output.='</span>';


			$output.='<span id="wppizza-reports-range-export"><input type="button" class="button" value="'.$exportLabel.'" id="wppizza_reports_export" /></span>';

		$output.='</div>';

		return $output;
	}

	private function wppizza_report_markup($data_set){

		$output=array();
		$output[]='<!--  boxes and graphs -->';
		$output[]='<div id="wppizza-reports-details">';

			$output[]='<!--  sidebar boxes -->';
			$output[]='<div id="wppizza-sidebar-reports" class="wppizza-sidebar">';
			foreach($data_set['boxes'] as $vals){
				$output[]='<div id="'.$vals['id'].'" class="postbox wppizza-reports-postbox">';
				$output[]='<h3 class="button">'.$vals['lbl'].'</h3>';
				$output[]=''.$vals['val'].'';
				$output[]='</div>';
			}
			$output[]='</div>';

			$output[]='<!--  flot graphs -->';
			$output[]='<div id="wppizza-reports-canvas-wrap">';
				$output[]='<h4>'.$data_set['graphs']['label'].'</h4>';
				$output[]='<div style="min-height:150px" id="wppizza-reports-canvas"></div>';
				$output[]='<ul id="wppizza-report-choices"></ul>';
			$output[]='</div>';


			$output[]='<div id="wppizza-sidebar-reports-right" class="wppizza-sidebar-right">';
			foreach($data_set['boxesrt'] as $vals){
				$output[]='<div id="'.$vals['id'].'" class="postbox wppizza-reports-postbox-right '.$vals['class'].'">';
				$output[]='<h3 class="button">'.$vals['lbl'].'</h3>';
				$output[]=''.$vals['val'].'';
				$output[]='</div>';
			}
			$output[]='</div>';


		$output[]='</div>';
		/*implode*/
		$output = implode(PHP_EOL, $output);

		return $output;
	}

	private function wppizza_report_js($data_set){
	?>
		<script>
		jQuery(document).ready(function($){
		$(function() {
				var datasets = {
					<?php
						$i=0;
						foreach($data_set['graphs']['data'] as $gk=>$gv){
							if($i>0){print",";};
							print'"'.$gk.'":{'.$gv.'}';
						$i++;
						}
					?>
				};
				/*********tooltip hover*****/
				$("<div id='wppizza-reports-tooltip'></div>").appendTo("body");
				$("#wppizza-reports-canvas").bind("plothover", function (event, pos, item) {
						if (item) {
							var x = item.datapoint[0],
								y = item.datapoint[1].toFixed(2);

							$("#wppizza-reports-tooltip").html(y)
								.css({top: item.pageY-<?php echo $data_set['graphs']['hoverOffsetTop'] ?>, left: item.pageX+<?php echo $data_set['graphs']['hoverOffsetLeft'] ?>})
								.fadeIn(200);
						} else {
							$("#wppizza-reports-tooltip").hide();
						}
				});
				/************colours***************/
				var i = 1;
				$.each(datasets, function(key, val) {
					val.color = i;
					++i;
				});
				/************radios***************/
				var choiceContainer = $("#wppizza-report-choices");
				$.each(datasets, function(key, val) {
					if(key=='sales_value'){var valchkd='checked="checked"';}else{var valchkd='';}
					choiceContainer.append("<li><label for='" + key + "'><input type='radio' name='wppizza-graph-select' "+valchkd+" id='" + key + "' />"+ val.label + "</label></li>");
				});
				choiceContainer.find("input").click(plotAccordingToChoices);
				/************format legend***************/
				function legendFormatter(v, axis) {
					if(axis.n==1){
						return "<?php echo $data_set['currency'] ?> "+v.toFixed(2);
					}else{
						return v.toFixed(0);
					}
				}
				/************plot***************/
				function plotAccordingToChoices() {
					var data = [];
					choiceContainer.find("input:checked").each(function () {
						var key = $(this).attr("id");
						if (key && datasets[key]) {
							data.push(datasets[key]);
						}
					});
					if (data.length > 0) {
						$.plot("#wppizza-reports-canvas", data,{
							series: {
								lines: {
									show: <?php echo $data_set['graphs']['series']['lines'] ?>
								},
								bars: {
									show: <?php echo $data_set['graphs']['series']['bars'] ?>,
									barWidth: 0.6,
									align: "center"
								},
								points: {
									show: <?php echo $data_set['graphs']['series']['points'] ?>
								}
							},
							grid: {
								hoverable: true
							},
							xaxis: {
								mode: "categories"
							},
							yaxis: {
								min:0,
								tickDecimals: 0,
								tickFormatter: legendFormatter
							}
						});
					}
				}
				plotAccordingToChoices();
			});
		});
		</script>
<?php
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
		return $caps;
	}

/*********************************************************
*
*		[reports dataset]
*
*********************************************************/
function wppizza_report_dataset($export = false, $transient_expiry = false, $dashboard_widget = false){

		global $wppizza_options;

		if( version_compare( PHP_VERSION, '5.3', '<' )) {
			print"<div id='wppizza-report-error'>".__('Sorry, reporting is only available with php >=5.3','wppizza-admin')."</div>";
			return;
		}

		global $wpdb,$blog_id;


			/*
				using transients
			*/
			if($transient_expiry){
				if (false !== ( $transient_dataset_results = get_transient( WPPIZZA_TRANSIENT_REPORTS_NAME.'_'.$transient_expiry.'' ) ) ) {
					return $transient_dataset_results;
				}
			}


			$wpTime = WPPIZZA_WP_TIME;
			$wpYesterday = strtotime('-1 day', $wpTime);
			/* 
				get all completed business days (i.e days where closing time is before now) 
				within the last week start and end times (might cross midnight) 
				omitting days where shop is closed
			*/
			$completedBusinessDays = wppizza_completed_businessdays($wpTime);
				
			$firstDayCurrentMonth = strtotime('first day of this month 00:00:00', $wpTime);
			$firstDayLastMonth = mktime(0, 0, 0, date("m")-1, 1, date("Y"));

			$reportCurrency=$wppizza_options['order_settings']['currency_symbol'];
			$reportCurrencyIso=$wppizza_options['order_settings']['currency'];
			$dateformat=get_option('date_format');
			$timeformat=get_option('time_format');
			$processOrder=array();


			/************************************************************************
				get all wppizza menu items by id and size
			************************************************************************/
			$getWppizzaMenuItems = wppizza_get_menu_items();
			$wppizzaMenuItems=array();
			if(count($getWppizzaMenuItems)>0){
				/*loop through items*/
				foreach($getWppizzaMenuItems as $menuItem){
					$meta=get_post_meta($menuItem->ID, WPPIZZA_POST_TYPE, true );
					$sizes=$wppizza_options['sizes'][$meta['sizes']];
					/*loop through sizes*/
					if(is_array($sizes)){
					foreach($sizes as $sizekey=>$size){
						/*make key from id and size*/
						$miKey=$menuItem->ID.'.'.$sizekey;
						$wppizzaMenuItems[$miKey]=array('ID'=>$menuItem->ID,'title'=>$menuItem->post_title,'sizekey'=>$sizekey,'price_label'=>$size['lbl']);
					}}
				}
			}
			/**for ease of use, store above as purchased menu items to unset if bought*/
			$unsoldMenuItems=$wppizzaMenuItems;

			/************************************************************************
				overview query. do not limit by date to get totals
				any other query, add date range to query
			************************************************************************/
			$reportTypes=array();
			$reportTypes['today'] = array('lbl'=>__('Today','wppizza-admin'));
			/*
				completed business days in last week
				(omitting closed days)
			*/
			if(!empty($completedBusinessDays)){
				foreach($completedBusinessDays as $bDayKey=>$bDay){
					$reportTypes[$bDay['date']] = array('lbl'=>$bDay['lbl']);	
				}
			}
			//$reportTypes['yesterday'] = array('lbl'=>__('Yesterday','wppizza-admin'));
			$reportTypes['7d'] = array('lbl'=>__('Last 7 days','wppizza-admin'));
			$reportTypes['14d'] = array('lbl'=>__('Last 14 days','wppizza-admin'));			
			$reportTypes['ytd'] = array('lbl'=>__('Year to date','wppizza-admin'));
			$reportTypes['ly'] = array('lbl'=>__('Last year','wppizza-admin'));
			$reportTypes['tm'] = array('lbl'=>__('This month','wppizza-admin'));
			$reportTypes['lm'] = array('lbl'=>__('Last month','wppizza-admin'));
			$reportTypes['12m'] = array('lbl'=>__('Last 12 month','wppizza-admin'));

			
		
			
			$overview=empty($_GET['report']) || !in_array($_GET['report'],array_keys($reportTypes)) ? true : false;
			$customrange=!empty($_GET['from']) && !empty($_GET['to'])  ? true : false;


			/******************************
			*
			*	[overview]
			*
			******************************/
			if($overview && !$customrange){
				$granularity='Y-m-d';/*days*/
				$daysSelected=30;
				$xaxisFormat='D, d M';
				$serieslines='true';
				$seriesbars='false';
				$seriespoints='true';
				$hoverOffsetLeft=5;
				$hoverOffsetTop=15;
				$firstDateTimestamp=mktime(date('H',$wpTime),date('i',$wpTime),date('s',$wpTime),date('m',$wpTime),date('d',$wpTime)-$daysSelected+1,date('Y',$wpTime));
				$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
				$lastDateReport="".date('Y',$wpTime)."-".date('m',$wpTime)."-".date('d',$wpTime)." 23:59:59";
				$oQuery='';
				/***graph label**/
				$graphLabel="".__('Details last 30 days','wppizza-admin')." : ";
			}

			/******************************
			*
			*	[custom range]
			*
			******************************/
			if($customrange){
					$selectedReport='customrange';
					$from=explode('-',$_GET['from']);
					$to=explode('-',$_GET['to']);

					$firstDateTs=mktime(0, 0, 0, $from[1], $from[2], $from[0]);
					$lastDateTs=mktime(23, 59, 59, $to[1], $to[2], $to[0]);
					/*invert dates if end<start**/
					if($firstDateTs>$lastDateTs){
						$firstDateTimestamp=$lastDateTs;
						$lastDateTimestamp=$firstDateTs;
					}else{
						$firstDateTimestamp=$firstDateTs;
						$lastDateTimestamp=$lastDateTs;
					}

					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport="".date('Y-m-d H:i:s',$lastDateTimestamp)."";
					/*override get vars**/
					$_GET['from']=$firstDateReport;
					$_GET['to']=date('Y-m-d',$lastDateTimestamp);
					/**from/to formatted**/
					$fromFormatted=date($dateformat,$firstDateTimestamp);
					$toFormatted=date($dateformat,$lastDateTimestamp);

					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".$fromFormatted." - ".$toFormatted." : ";
			}
			/******************************
			*
			*	[predefined reports]
			*
			******************************/
			if(!$overview){
				$selectedReport=$_GET['report'];
				$oQuery='';

				/************************
					year to date
				************************/
				if($selectedReport=='ytd'){
					$firstDateTimestamp=mktime(0, 0, 0, 1, 1, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport="".date('Y',$wpTime)."-".date('m',$wpTime)."-".date('d',$wpTime)." 23:59:59";
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Year to date','wppizza-admin')." : ";
				}
				/************************
					last year
				************************/
				if($selectedReport=='ly'){
					$firstDateTimestamp=mktime(0, 0, 0, 1, 1, date('Y',$wpTime)-1);
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23,59,59,12,31,date('Y',$wpTime)-1));
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Last Year','wppizza-admin')." : ";
				}
				/************************
					this month
				************************/
				if($selectedReport=='tm'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime), 1, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport="".date('Y-m-d H:i:s',mktime(23, 59, 59, date('m',$wpTime)+1, 0, date('Y',$wpTime)))."";
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('This Month','wppizza-admin')." : ";
				}
				/************************
					last month
				************************/
				if($selectedReport=='lm'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime)-1, 1, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23,59,59,date('m',$wpTime),0,date('Y',$wpTime)));
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Last Month','wppizza-admin')." : ";
				}

				/************************
					last 12month
				************************/
				if($selectedReport=='12m'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime)-12, date('d',$wpTime)+1, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23, 59, 59, date('m',$wpTime), date('d',$wpTime), date('Y',$wpTime)));
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Last 12 Month','wppizza-admin')." : ";
				}

				/************************
					today
				************************/
				if($selectedReport=='today'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime), date('d',$wpTime), date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23, 59, 59, date('m',$wpTime), date('d',$wpTime), date('Y',$wpTime)));
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Today','wppizza-admin')." : ";
				}

				/************************
					completed business days 
					in last week (omitting closed days)
				************************/
				if(!empty($completedBusinessDays)){
					foreach($completedBusinessDays as $bDayKey=>$bDay){
						if($selectedReport == $bDay['date']){
							$firstDateTimestamp = $bDay['open'];
							$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
							$lastDateReport=date('Y-m-d H:i:s',$bDay['close']);
							/* set query */
							$oQuery="AND order_date >='".$bDay['open_formatted']."'  AND order_date <= '".$bDay['close_formatted']."' ";
							/***graph label**/
							$graphLabel="".$bDay['lbl']." : ";						
						}
					}
				}


//				/************************
//					yesterday
//				************************/
//				if($selectedReport=='yesterday'){
//					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime), date('d',$wpTime)-1, date('Y',$wpTime));
//					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
//					$lastDateReport=date('Y-m-d H:i:s',mktime(23, 59, 59, date('m',$wpTime), date('d',$wpTime)-1, date('Y',$wpTime)));
//					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
//					/***graph label**/
//					$graphLabel="".__('Yesterday','wppizza-admin')." : ";
//				}

				/************************
					last 7 days
				************************/
				if($selectedReport=='7d'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime), date('d',$wpTime)-6, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23, 59, 59, date('m',$wpTime), date('d',$wpTime), date('Y',$wpTime)));
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Last 7 days','wppizza-admin')." : ";
				}
				/************************
					last 14 days
				************************/
				if($selectedReport=='14d'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime), date('d',$wpTime)-13, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23, 59, 59, date('m',$wpTime), date('d',$wpTime), date('Y',$wpTime)));
					/***graph label**/
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					$graphLabel="".__('Last 14 days','wppizza-admin')." : ";
				}

			}

			if(!$overview || $customrange){
				$firstDate = new DateTime($firstDateReport);
				$firstDateFormatted = $firstDate->format($dateformat);
				$lastDate = new DateTime($lastDateReport);
				$lastDateFormatted = $lastDate->format($dateformat);
				$dateDifference = $firstDate->diff($lastDate);
				$daysSelected=($dateDifference->days)+1;
				$monthAvgDivider=($dateDifference->m)+1;
				$monthsSelected=$dateDifference->m;
				$yearsSelected=$dateDifference->y;
				/*set granularity to months if months>0 or years>0*/
				if($monthsSelected>0 || $yearsSelected>0 ){
					$granularity='Y-m';/*months*/
					$xaxisFormat='M Y';
					$serieslines='false';
					$seriesbars='true';
					$seriespoints='false';
					$hoverOffsetLeft=-22;
					$hoverOffsetTop=2;
				}else{
					$granularity='Y-m-d';/*days*/
					$xaxisFormat='D, d M';
					$serieslines='true';
					$seriesbars='false';
					$seriespoints='true';
					$hoverOffsetLeft=5;
					$hoverOffsetTop=15;
				}
			}


			/************************************************************************
				multisite install
				all orders of all sites (blogs)
				but only for master blog and if enabled (settings)
			************************************************************************/
			$menu_items_and_categories = array();
			$menu_items_and_categories['posts'] = 0;
			$menu_items_and_categories['categories'] = 0;
			$category_names = array();


			if(apply_filters('wppizza_filter_reports_all_sites',false)){
				$ordersQueryRes=array();
		 	   	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
		 	   		if ($blogs) {
		        	foreach($blogs as $blog) {
		        		switch_to_blog($blog['blog_id']);
		        		/*make sure plugin is active*/
	        			if(is_plugin_active(WPPIZZA_PLUGIN_INDEX)){

	        				/************************
	        					number of wppizza posts
	        				************************/
							$menu_items_and_categories['posts'] += wp_count_posts(WPPIZZA_POST_TYPE)->publish;
	        				/************************
	        					number of wppizza categories
	        				************************/
							$terms = get_terms(WPPIZZA_TAXONOMY);
							if ( $terms && !is_wp_error( $terms ) ){
								$menu_items_and_categories['categories'] += count($terms);
								foreach($terms as $t){
									$category_names[$t->term_id] = array('name'=>$t->name, 'slug'=>$t->slug, 'description'=>$t->description);
								}
							}
							/************************
								[make and run query]
							*************************/
							$ordersQuery=$this->wppizza_report_mkquery($wpdb->prefix, $oQuery);
							$ordersQuery= $wpdb->get_results($ordersQuery);

							/**merge array**/
							$ordersQueryRes=array_merge($ordersQuery,$ordersQueryRes);
	        			}
						restore_current_blog();
		        	}}
			}else{

	        	/************************
	        		number of wppizza posts
	        	************************/
	        	$menu_items_and_categories['posts'] += wp_count_posts(WPPIZZA_POST_TYPE)->publish;
				/************************
					number of wppizza categories
				************************/
				$terms = get_terms(WPPIZZA_TAXONOMY);
				if ( $terms && !is_wp_error( $terms ) ){
					$menu_items_and_categories['categories'] += count($terms);
					foreach($terms as $t){
						$category_names[$t->term_id] = array('name'=>$t->name, 'slug'=>$t->slug, 'description'=>$t->description);
					}
				}
				/************************
					[make and run query]
				*************************/
				$ordersQuery=$this->wppizza_report_mkquery($wpdb->prefix, $oQuery);
				$ordersQueryRes = $wpdb->get_results($ordersQuery);
			}

			/**************************
				ini dates
			**************************/
			$graphDates=array();
			for($i=0;$i<$daysSelected;$i++){
				$dayFormatted=mktime(date('H',$firstDateTimestamp),date('i',$firstDateTimestamp),date('s',$firstDateTimestamp),date('m',$firstDateTimestamp),date('d',$firstDateTimestamp)+$i,date('Y',$firstDateTimestamp));
				$graphDates[]=date($granularity,$dayFormatted);
			}

			/******************************************************************************************************************************************************
			*
			*
			*
			*	[create dataset from orders]
			*
			*
			*
			******************************************************************************************************************************************************/
					/*********************************************
						only when exporting to file
						sum/count of the same item in period
					*********************************************/
					if($export){
						$itemsSummed=array();
						$gatewaysSummed=array();
						$orderStatusSummed=array();
						$orderCustomStatusSummed=array();
					}

					/*ini tax*/
					$orderTaxTotals=array();
					$orderTaxTotals['included']=0;
					$orderTaxTotals['added']=0;

					$orderTaxByRate = array();
					$orderTaxByRate['included'] = array();
					$orderTaxByRate['added'] = array();
					$orderTaxByRate['total'] = array();

					/**********************************************
					*
					*	[get and tidy up order first]
					*
					**********************************************/
					foreach($ordersQueryRes as $qKey=>$order){
						if($order->order_ini!=''){

							/**
								get order details per order and sum / format for later output
							**/
							if(!empty($order->order_total)){

								/**
									un-serialize the items
								**/
								$orderItems = maybe_unserialize($order->order_ini);/**unserialize order details**/


								$orderDetails = array();/* ini array */
								$orderDetails['order_date']=substr($order->oDate,0,10);
								$orderDetails['order_date_formatted']=date($granularity,$order->order_date);
								$orderDetails['order_date_timestamp']=$order->order_date;
								$orderDetails['wp_user_id']=$order->wp_user_id;
								$orderDetails['blog_id']=$order->blog_id;/* add blog id as that might come in useful */
								$orderDetails['order_id']=$order->id;
								$orderDetails['total_price_items']=$order->order_items_total;
								$orderDetails['order_total']=$order->order_total;
								$orderDetails['order_items_count']=$order->order_no_of_items;
								$orderDetails['initiator']=$order->initiator;

								/* taxes this order */
								$taxes_included = ($order->order_taxes_included == 'Y') ? $order->order_taxes : 0;
								$taxes_added = ($order->order_taxes_included == 'N') ? $order->order_taxes : 0;

								$orderDetails['taxes_included']=$taxes_included;
								$orderDetails['taxes_added']=$taxes_added;

								/**add up taxes totals*/
								$orderTaxTotals['included'] += $taxes_included;
								$orderTaxTotals['added'] += $taxes_added;


								/**taxes by rates */
								foreach($orderItems['summary']['tax_by_rate'] as $rates){
									/* included taxes */
									if($order->order_taxes_included == 'Y'){
										if(!isset($orderTaxByRate['included'][''.$rates['rate'].'%'])){
											$orderTaxByRate['included'][''.$rates['rate'].'%'] = !empty($rates['total']) ? $rates['total'] : 0 ;
										}else{
											$orderTaxByRate['included'][''.$rates['rate'].'%'] += !empty($rates['total']) ? $rates['total'] : 0 ;
										}
									}

									/* added taxes */
									if($order->order_taxes_included == 'N'){
										if(!isset($orderTaxByRate['added'][''.$rates['rate'].'%'])){
											$orderTaxByRate['added'][''.$rates['rate'].'%'] = !empty($rates['total']) ? $rates['total'] : 0 ;
										}else{
											$orderTaxByRate['added'][''.$rates['rate'].'%'] += !empty($rates['total']) ? $rates['total'] : 0 ;
										}
									}

									/* total taxes */
									if(!isset($orderTaxByRate['total'][''.$rates['rate'].'%'])){
										$orderTaxByRate['total'][''.$rates['rate'].'%'] = !empty($rates['total']) ? $rates['total'] : 0 ;
									}else{
										$orderTaxByRate['total'][''.$rates['rate'].'%'] += !empty($rates['total']) ? $rates['total'] : 0 ;
									}

								}

								/** account for orders pre 3.0 ([item])  post 3.0 its [items]*/
								$orderDetailsItems = !empty($orderItems['items']) ? $orderItems['items'] : $orderItems['item'];
								$itemDetails=array();
								foreach($orderDetailsItems as $k=>$uniqueItems){
									/* account for pre 3.0 where post_id was postId */
									$uniqueItems['post_id'] = isset($uniqueItems['post_id']) ? $uniqueItems['post_id'] : (isset($uniqueItems['postId']) ? $uniqueItems['postId'] : '' );
									/* if there's still no post id, explode key by . and use first -  very very old wppizza versions */
									if(empty($uniqueItems['post_id'])){
										$key_vals = explode('.',$k);
										$uniqueItems['post_id'] = $key_vals[0];
									}

									/* account for pre 3.0 where we use name instead of title */
									$uniqueItems['title'] = isset($uniqueItems['title']) ? $uniqueItems['title'] : $uniqueItems['name'] ;
									/* account for pre 3.0 where we use size instead of price_label */
									$uniqueItems['price_label'] = isset($uniqueItems['price_label']) ? $uniqueItems['price_label'] : $uniqueItems['size'] ;


									//$itemDetails[$k]['postId']=$uniqueItems['postId'];
									$itemDetails[$k]['title'] = $uniqueItems['title'] ;
									$itemDetails[$k]['price_label'] =  $uniqueItems['price_label'] ;
									$itemDetails[$k]['quantity'] = $uniqueItems['quantity'];
									$itemDetails[$k]['price'] = $uniqueItems['price'];
									$itemDetails[$k]['pricetotal'] = $uniqueItems['pricetotal'];
									$itemDetails[$k]['category_id'] = $uniqueItems['cat_id_selected'];

									/*sum/count of the same item in period . export only*/
									/*make unique by name too as it may have changed over time*/
									if($export){
										/* make a key consisting of id and size and md5 of name (as it may have changed over time) to sum it up*/
										$mkKey=''.$uniqueItems['post_id'].'.'.$uniqueItems['price_label'].'.'.MD5($uniqueItems['title']);
										if(!isset($itemsSummed[$mkKey])){
											$itemsSummed[$mkKey]=array('quantity'=>$uniqueItems['quantity'], 'title'=>$uniqueItems['title'].' ['.$uniqueItems['price_label'].']', 'pricetotal'=>$uniqueItems['pricetotal']);
										}else{
											$itemsSummed[$mkKey]['quantity']+=$uniqueItems['quantity'];
											$itemsSummed[$mkKey]['pricetotal']+=$uniqueItems['pricetotal'];
										}
									}
								}
								/**add relevant item info to array**/
								$orderDetails['items'] = $itemDetails;

								$processOrder[]=$orderDetails;

								/*sum by gateway and order status*/
								if($export){
									/*per gateway*/
									if(!isset($gatewaysSummed[$order->initiator])){
										$gatewaysSummed[$order->initiator] = $order -> order_total;
									}else{
										$gatewaysSummed[$order->initiator] += $order -> order_total;
									}

									/* per order status */
									if(!isset($orderStatusSummed[$order->order_status])){
										$count[$order->order_status] = 1;
										$value[$order->order_status] = $order -> order_total;
										$orderStatusSummed[$order->order_status] = array('count' => $count[$order->order_status], 'value' => $value[$order->order_status]);
									}else{
										$count[$order->order_status] ++;
										$value[$order->order_status] += $order -> order_total;
										$orderStatusSummed[$order->order_status] = array('count' => $count[$order->order_status], 'value' => $value[$order->order_status]);
									}

									/* per custom order status */
									if(!isset($orderCustomStatusSummed[$order->order_status_user_defined])){
										$count[$order->order_status_user_defined] = 1;
										$value[$order->order_status_user_defined] = $order -> order_total;
										$orderCustomStatusSummed[$order->order_status_user_defined] = array('count' => $count[$order->order_status_user_defined], 'value' => $value[$order->order_status_user_defined]);
									}else{
										$count[$order->order_status_user_defined] ++;
										$value[$order->order_status_user_defined] += $order -> order_total;
										$orderCustomStatusSummed[$order->order_status_user_defined] = array('count' => $count[$order->order_status_user_defined], 'value' => $value[$order->order_status_user_defined]);
									}
								}
							}
						}
					}

					/*sort distinct items - export only***/
					if($export && !empty($itemsSummed)){
						arsort($itemsSummed);
					}

					/**********************************************************************************
					*
					*
					*	lets do the calculations, to get the right dataset
					*
					*
					**********************************************************************************/

					/**************************************
						[initialize array and values]
					**************************************/
					$datasets=array();
					/*totals*/
					$datasets['sales_value_total']=0;/**total of sales/orders INCLUDING taxes, discounts, charges etc**/
					$datasets['sales_count_total']=0;/**total count of sales**/
					$datasets['sales_order_tax']=0;/**tax on order**/
					$datasets['items_value_total']=0;/**total of items EXLUDING taxes, discounts, charges etc**/
					$datasets['items_count_total']=0;/**total count of items**/


					/*totals this month*/
					$datasets['sales_this_month_value_total']=0;/**total of sales/orders INCLUDING taxes, discounts, charges etc**/
					$datasets['sales_this_month_count_total']=0;/**total count of sales**/
					$datasets['items_this_month_value_total']=0;/**total of items EXLUDING taxes, discounts, charges etc**/
					$datasets['items_this_month_count_total']=0;/**total count of items**/

					/*totals last month*/
					$datasets['sales_last_month_value_total']=0;/**total of sales/orders INCLUDING taxes, discounts, charges etc**/
					$datasets['sales_last_month_count_total']=0;/**total count of sales**/
					$datasets['items_last_month_value_total']=0;/**total of items EXLUDING taxes, discounts, charges etc**/
					$datasets['items_last_month_count_total']=0;/**total count of items**/

					/*per gateway*/
					$datasets['gateway_sales'] = array();
					
					/*users*/
					$datasets['users_registered']=array();/** unique registered users (i.e wp_user_id!=0) **/
					$datasets['users_registered_count']=0;
					$datasets['users_registered_total_value']=0;
					$datasets['users_registered_total_items']=0;
					$datasets['users_guest_count']=0;
					$datasets['users_guest_total_value']=0;
					$datasets['users_guest_total_items']=0;

					/*taxes*/
					$datasets['tax_total']=($orderTaxTotals['included'] + $orderTaxTotals['added']);/**total tax**/
					$datasets['tax_by_rate'] = !empty($orderTaxByRate) ? $orderTaxByRate : array();/* tax by rate**/

					/*misc*/
					$datasets['sales']=array();/*holds data on a per day/month basis*/
					$datasets['bestsellers']=array('by_volume'=>array(),'by_value'=>array());

					if($export){
						/*per item*/
						$datasets['items_summary'] = $itemsSummed;
						/*per gateway*/
						$datasets['gateways_summary'] = $gatewaysSummed;
						/*per order status*/
						$datasets['order_status_summary']=$orderStatusSummed;
						/*per custom order status*/
						$datasets['order_custom_status_summary']=$orderCustomStatusSummed;
					}
					/**************************************
						[loop through orders and do things]
						creating datasets
					**************************************/
					$j=1;
					/**************************************
						[array of orders to be sliced to last 5 only]
					**************************************/
					$recent_orders = array();
					foreach($processOrder as $k=>$order){

						/**************************************
							[array of orders to be sliced to last 5 only]
						**************************************/
						$recent_orders[] = array('timestamp'=> $order['order_date_timestamp'], 'total'=> $order['order_total'], 'blog_id'=> $order['blog_id'], 'order_id'=> $order['order_id'], 'wp_user_id'=> $order['wp_user_id'] );
						/****************************************************
							if we are not setting a defined range
							like a whole month, week , or whatever
							(i.e in overview) lets get first and last day
							we have orders for to be able to calc averages
						****************************************************/
						if($j==1){$datasets['first_date']=$order['order_date'];}


						/****************************************************
							set garnularity (i.e by day, month or year)
						****************************************************/
						$dateResolution=$order['order_date_formatted'];/**set garnularity (i.e by day, month or year)**/

						/****************************************************
							[get/set totals]
						****************************************************/
						$datasets['sales_value_total']+=$order['order_total'];
						$datasets['sales_count_total']++;
						$datasets['sales_order_tax']+=$order['taxes_added']+$order['taxes_included'];
						$datasets['items_value_total']+=$order['total_price_items'];
						$datasets['items_count_total']+=$order['order_items_count'];

						/* per gateway */
						if(!isset($datasets['gateway_sales'][$order['initiator']]['total'])){
							$datasets['gateway_sales'][$order['initiator']]['total_count'] = 1;
							$datasets['gateway_sales'][$order['initiator']]['total'] = $order['order_total'];
						}else{
							$datasets['gateway_sales'][$order['initiator']]['total_count'] ++;
							$datasets['gateway_sales'][$order['initiator']]['total'] += $order['order_total'];
						}
												

						/****************************************************
							[get/set totals this month]
						****************************************************/
						if( $order['order_date_timestamp'] >= $firstDayCurrentMonth ){
							$datasets['sales_this_month_value_total']+=$order['order_total'];
							$datasets['sales_this_month_count_total']++;
							$datasets['items_this_month_value_total']+=$order['total_price_items'];/*items before any charges*/
							$datasets['items_this_month_count_total']+=$order['order_items_count'];
							/* per gateway */
							if(!isset($datasets['gateway_sales'][$order['initiator']]['total_this_month'])){
								$datasets['gateway_sales'][$order['initiator']]['total_this_month'] = $order['order_total'];
							}else{
								$datasets['gateway_sales'][$order['initiator']]['total_this_month'] += $order['order_total'];
							}
						}
						/****************************************************
							[get/set totals last month]
						****************************************************/
						if($order['order_date_timestamp']  < $firstDayCurrentMonth  && $order['order_date_timestamp'] >= $firstDayLastMonth){
							$datasets['sales_last_month_value_total']+=$order['order_total'];
							$datasets['sales_last_month_count_total']++;
							$datasets['items_last_month_value_total']+=$order['total_price_items'];/*items before any charges*/
							$datasets['items_last_month_count_total']+=$order['order_items_count'];
							/* per gateway */
							if(!isset($datasets['gateway_sales'][$order['initiator']]['total_last_month'])){
								$datasets['gateway_sales'][$order['initiator']]['total_last_month'] = $order['order_total'];
							}else{
								$datasets['gateway_sales'][$order['initiator']]['total_last_month'] += $order['order_total'];
							}							
						}

						/****************************************************
							[get/set totals registere users / guests]
						****************************************************/
						/*unique registere users*/
						if(!empty($order['wp_user_id'])){
							if(!isset($datasets['users_registered'][$order['wp_user_id']])){
								$datasets['users_registered_count']++;/*add unique user*/
								/*set user id*/
								$datasets['users_registered'][$order['wp_user_id']]['id'] = $order['wp_user_id'];
							}

								$datasets['users_registered_total_value'] += $order['order_total'];
								$datasets['users_registered_total_items'] += $order['order_items_count'];
						}else{
								/*guest users*/
								$datasets['users_guest_count']=1;/*set guest user*/
								$datasets['users_guest_total_value'] += $order['order_total'];
								$datasets['users_guest_total_items'] += $order['order_items_count'];
						}
						/****************************************************
							[get/set items to sort for bestsellers]
						****************************************************/
						foreach($order['items'] as $iK=>$oItems){
							$uniqueKeyX=explode('|',$iK);
							$category='';
							/**
								if grouped by category is/was set, $uniqueKeyX will have 4 int, concat by a period, where the 3rd denotes the cat id*/
							$kX=explode('.',$uniqueKeyX[0]);
							/*item id*/
							$menuItemId=$kX[0];
							/*size id*/
							$menuItemSize=$kX[1];

							if(count($kX)>3 && $wppizza_options['layout']['items_group_sort_print_by_category']){
								$category = get_term_by( 'id', $kX[2], WPPIZZA_TAXONOMY);
								if(is_object($category)){
									$category=' - <em style="font-size:80%">'.$category->name.'</em>';
								}else{
									$category='';
								}
							}

							/**unset this bought item from the unsold menu items**/
							if(isset($unsoldMenuItems[$menuItemId.'.'.$menuItemSize])){
								unset($unsoldMenuItems[$menuItemId.'.'.$menuItemSize]);
							}

							/**make a unique key by id and name in case an items name was changed */
							/**note, unique keys will be different when grouped/display by category is set*/
							$uKey=MD5($uniqueKeyX[0].$oItems['title'].$oItems['price_label']);
							if(!isset($datasets['bestsellers']['by_volume'][$uKey])){
								/**lets do by volume and by value at the same time**/
								$datasets['bestsellers']['by_value'][$uKey]=array('price'=>$oItems['pricetotal'], 'single_price'=>$oItems['price'], 'quantity'=>$oItems['quantity'], 'title'=>''.$oItems['title'].' ['.$oItems['price_label'].']'.$category.'', 'min_price'=>$oItems['price'], 'max_price'=>$oItems['price']  );
								$datasets['bestsellers']['by_volume'][$uKey]=array('quantity'=>$oItems['quantity'], 'price'=>$oItems['pricetotal'], 'single_price'=>$oItems['price'], 'title'=>''.$oItems['title'].' ['.$oItems['price_label'].']'.$category.'');
							}else{
								/*sum up / set  by value*/
								$datasets['bestsellers']['by_value'][$uKey]['quantity']+=$oItems['quantity'];
								$datasets['bestsellers']['by_value'][$uKey]['price']+=$oItems['pricetotal'];
								/*set min and max price as they may have changed */
								if($oItems['price']>$datasets['bestsellers']['by_value'][$uKey]['max_price']){
									$datasets['bestsellers']['by_value'][$uKey]['max_price']=$oItems['price'];
								}
								if($oItems['price']<$datasets['bestsellers']['by_value'][$uKey]['min_price']){
									$datasets['bestsellers']['by_value'][$uKey]['min_price']=$oItems['price'];
								}

								/*sum up by volume*/
								$datasets['bestsellers']['by_volume'][$uKey]['quantity']+=$oItems['quantity'];
								$datasets['bestsellers']['by_volume'][$uKey]['price']+=$oItems['pricetotal'];
							}
						}

						/****************************************************
							[get/set totals [per granularity]
						****************************************************/
							/**initialize arrays**/
							if(!isset($datasets['sales'][$dateResolution])){
								$datasets['sales'][$dateResolution]['sales_value_total']=0;
								$datasets['sales'][$dateResolution]['sales_count_total']=0;
								$datasets['sales'][$dateResolution]['sales_order_tax']=0;
								$datasets['sales'][$dateResolution]['items_value_total']=0;
								$datasets['sales'][$dateResolution]['items_count_total']=0;
								$datasets['sales'][$dateResolution]['categories'] = array();
							}
							$datasets['sales'][$dateResolution]['sales_value_total']+=$order['order_total'];
							$datasets['sales'][$dateResolution]['sales_count_total']++;
							$datasets['sales'][$dateResolution]['sales_order_tax']+=$order['taxes_added']+$order['taxes_included'];
							$datasets['sales'][$dateResolution]['items_value_total']+=$order['total_price_items'];
							$datasets['sales'][$dateResolution]['items_count_total']+=$order['order_items_count'];
							
							/* per gateway */
							if(!isset($datasets['gateway_sales'][$order['initiator']][$dateResolution])){
								$datasets['gateway_sales'][$order['initiator']][$dateResolution] = $order['order_total'];
							}else{
								$datasets['gateway_sales'][$order['initiator']][$dateResolution] += $order['order_total'];
							}							
							
							foreach($order['items'] as $item_details){
								if(!isset($datasets['sales'][$dateResolution]['categories'][$item_details['category_id']])){
									/* get cat name if we can */
									$cat_name = (!empty($category_names[$item_details['category_id']])) ? $category_names[$item_details['category_id']]['name'] : '';

									$datasets['sales'][$dateResolution]['categories'][$item_details['category_id']] = array('id'=> $item_details['category_id'], 'name' => $cat_name ,'total_sales' => $item_details['pricetotal']);
								}else{
									$datasets['sales'][$dateResolution]['categories'][$item_details['category_id']]['total_sales'] += $item_details['pricetotal'];
								}
							}
					$j++;
				}





				/**************************************
					[sort and slice recent orders - 5 only]
				**************************************/
				if(!empty($recent_orders)){
				rsort($recent_orders);
				$recent_orders = array_slice($recent_orders, 0, apply_filters('wppizza_filter_dashboard_widget_no_of_recent_orders', 5));
				foreach($recent_orders as $roKey=>$roVal){
					// currently unused
					//$recent_orders[$roKey]['timestamp_formatted'] = date($dateformat,$roVal['timestamp']). ' ' . date($timeformat,$roVal['timestamp']);
					/* get user meta */
					$user = (!empty($roVal['wp_user_id'])) ? get_userdata($roVal['wp_user_id']) : false;
					/* add user name etc */
					$recent_orders[$roKey]['user'] = array();
					if($user){
						$recent_orders[$roKey]['user']['first_name'] = $user->first_name;
						$recent_orders[$roKey]['user']['last_name'] = $user->last_name;
						$recent_orders[$roKey]['user']['user_login'] = $user->user_login;
						$recent_orders[$roKey]['user']['user_email'] = $user->user_email;
					}
				}}

				/*******************************
					sort and splice bestsellers
				*******************************/
				arsort($datasets['bestsellers']['by_volume']);
				arsort($datasets['bestsellers']['by_value']);

				/*max display, could be made into a dropdown*/
				if(!isset($_GET['b'])){$bCount=10;}else{$bCount=abs((int)$_GET['b']);}

				/*slice worstsellers - currently not displayed*/
				$worstsellers['by_volume']=array_slice($datasets['bestsellers']['by_volume'],-$bCount);
				asort($worstsellers['by_volume']);
				$worstsellers['by_value']=array_slice($datasets['bestsellers']['by_value'],-$bCount);
				asort($worstsellers['by_value']);

				/*splice bestsellers*/
				array_splice($datasets['bestsellers']['by_volume'],$bCount);
				array_splice($datasets['bestsellers']['by_value'],$bCount);


				/************************************************************
					construct bestsellers html
				*************************************************************/
				$htmlBsVol='<ul id="wppizza-report-top10-volume-ul">';/*by volume*/
				foreach($datasets['bestsellers']['by_volume'] as $bsbv){
					$htmlBsVol.='<li>'.$bsbv['quantity'].' x '.$bsbv['title'].'</li>';
				}
				$htmlBsVol.='</ul>';

				$htmlBsVal='<ul id="wppizza-report-top10-value-ul">';/*by value*/
				foreach($datasets['bestsellers']['by_value'] as $bsbv){
					$priceRange=wppizza_output_format_price($bsbv['single_price']);
					/*show price range if prices were changed */
					if($bsbv['min_price']!=$bsbv['max_price']){
					$priceRange=''.wppizza_output_format_price($bsbv['min_price']).'-'.wppizza_output_format_price($bsbv['max_price']);
					}
					$htmlBsVal.='<li>'.$bsbv['title'].' <span>'.$reportCurrency.''.wppizza_output_format_price($bsbv['price']).'</span><br /> ['.$bsbv['quantity'].' x '.$reportCurrency.''.$priceRange.'] <span>'.round($bsbv['price']/$datasets['items_value_total']*100,2).'%</span></li>';
				}
				$htmlBsVal.='</ul>';


				/************************************************************
					construct worstsellers html - currently not displayed
				*************************************************************/
				$htmlWsVol='<ul id="wppizza-report-bottom10-volume-ul">';/*by volume*/
				foreach($worstsellers['by_volume'] as $bsbv){
					$htmlWsVol.='<li>'.$bsbv['quantity'].' x '.$bsbv['title'].'</li>';
				}
				$htmlWsVol.='</ul>';

				$htmlWsVal='<ul id="wppizza-report-bottom10-value-ul">';/*by value*/
				foreach($worstsellers['by_value'] as $bsbv){
					$priceRange=wppizza_output_format_price($bsbv['single_price']);
					/*show price range if prices were changed */
					if($bsbv['min_price']!=$bsbv['max_price']){
					$priceRange=''.wppizza_output_format_price($bsbv['min_price']).'-'.wppizza_output_format_price($bsbv['max_price']);
					}
					$htmlWsVal.='<li>'.$bsbv['title'].' <span>'.$reportCurrency.''.wppizza_output_format_price($bsbv['price']).'</span><br /> ['.$bsbv['quantity'].' x '.$reportCurrency.''.$priceRange.'] <span>'.round($bsbv['price']/$datasets['items_value_total']*100,2).'%</span></li>';
				}
				$htmlWsVal.='</ul>';

				$htmlNoSellers='<ul id="wppizza-report-nosellers-ul">';/*non sellers*/
				/*add unsold items*/
				foreach($unsoldMenuItems as $usKey=>$usVal){
					$htmlNoSellers.='<li>0 x '.$usVal['title'].' ['.$usVal['price_label'].']</li>';
				}
				$htmlNoSellers.='</ul>';


				/**********************************************************
					get number of months and days in results array
				***********************************************************/
				if($overview && !$customrange){
					/**in case we have an empty results set**/
					if(!isset($datasets['first_date'])){
						$datasets['first_date']="".date('Y',$wpTime)."-".date('m',$wpTime)."-".date('d',$wpTime)." 00:00:00";
					}
					$firstDate = new DateTime($datasets['first_date']);
					$firstDateFormatted = $firstDate->format($dateformat);
					$lastDate = new DateTime("".date('Y',$wpTime)."-".date('m',$wpTime)."-".date('d',$wpTime)." 23:59:59");
					$lastDateFormatted = $lastDate->format($dateformat);
					$dateDifference = $firstDate->diff($lastDate);
					$daysSelected=$dateDifference->days+1;
					$monthAvgDivider=($dateDifference->m)+1;
				}

				/*****************************************************************
					averages
				******************************************************************/
				/*per day*/
				$datasets['sales_count_average']=round($datasets['sales_count_total']/$daysSelected,2);
				$datasets['sales_item_average']=round($datasets['items_count_total']/$daysSelected,2);
				$datasets['sales_value_average']=round($datasets['sales_value_total']/$daysSelected,2);
				/*per month*/
				$datasets['sales_count_average_month']=round($datasets['sales_count_total']/$monthAvgDivider,2);
				$datasets['sales_item_average_month']=round($datasets['items_count_total']/$monthAvgDivider,2);
				$datasets['sales_value_average_month']=round($datasets['sales_value_total']/$monthAvgDivider,2);

			/******************************************************************************************************************************************************
			*
			*
			*	[sidebar boxes]
			*
			*
			******************************************************************************************************************************************************/
			$box=array();
			$boxrt=array();
			if($overview && !$customrange){
				/*boxes left*/
				$box[]=array('id'=>'wppizza-report-val-total', 'class'=>'', 'lbl'=>__('All Sales: Total','wppizza-admin'),'val'=>'<p>'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_total']).'<br /><span class="description">'.__('incl. taxes, charges and discounts','wppizza-admin').'</span></p>');
				$box[]=array('id'=>'wppizza-report-val-avg', 'class'=>'', 'lbl'=>__('All Sales: Averages','wppizza-admin'),'val'=>'<p>'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_average']).' '.__('per day','wppizza-admin').'<br />'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_average_month']).' '.__('per month','wppizza-admin').'</p>');
				$box[]=array('id'=>'wppizza-report-count-total', 'class'=>'', 'lbl'=>__('All Orders/Items: Total','wppizza-admin'),'val'=>'<p>'.$datasets['sales_count_total'].' '.__('Orders','wppizza-admin').': '.$reportCurrency.' '.$datasets['items_value_total'].'<br />('.$datasets['items_count_total'].' '.__('items','wppizza-admin').')<br /><span class="description">'.__('before taxes, charges and discounts','wppizza-admin').'</span></p>');
				$box[]=array('id'=>'wppizza-report-count-avg', 'class'=>'', 'lbl'=>__('All Orders/Items: Averages','wppizza-admin'),'val'=>'<p>'.$datasets['sales_count_average'].' '.__('Orders','wppizza-admin').' ('.$datasets['sales_item_average'].' '.__('items','wppizza-admin').') '.__('per day','wppizza-admin').'<br />'.$datasets['sales_count_average_month'].' '.__('Orders','wppizza-admin').' ('.$datasets['sales_item_average_month'].' items) '.__('per month','wppizza-admin').'</p>');
				$box[]=array('id'=>'wppizza-report-taxes', 'class'=>'', 'lbl'=>__('Total Tax on Orders','wppizza-admin'),'val'=>'<p>'.wppizza_output_format_price($datasets['tax_total']).'</p>');
				$box[]=array('id'=>'wppizza-report-info', 'class'=>'', 'lbl'=>__('Range','wppizza-admin'),'val'=>'<p>'.$firstDateFormatted.' - '.$lastDateFormatted.'<br />'.$daysSelected.' '.__('days','wppizza-admin').' | '.$monthAvgDivider.' '.__('months','wppizza-admin').'</p>');

				/*boxes right*/
				$boxrt[]=array('id'=>'wppizza-report-top10-volume', 'class'=>'', 'lbl'=>__('Best/Worst sellers by Volume - All','wppizza-admin'),'val'=>$htmlBsVol.$htmlWsVol);
				$boxrt[]=array('id'=>'wppizza-report-top10-value', 'class'=>'', 'lbl'=>__('Best/Worst sellers by Value - All (% of order total)','wppizza-admin'),'val'=>$htmlBsVal.$htmlWsVal);
				$boxrt[]=array('id'=>'wppizza-report-nonsellers', 'class'=>'', 'lbl'=>__('Non-Sellers - All','wppizza-admin'),'val'=>$htmlNoSellers);
			}
			if(!$overview || $customrange){
				/*boxes left*/
				$box[]=array('id'=>'wppizza-report-val-total', 'class'=>'', 'lbl'=>__('Sales Total [in range]','wppizza-admin'),'val'=>'<p>'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_total']).'<br /><span class="description">'.__('incl. taxes, charges and discounts','wppizza-admin').'</span></p>');
				$box[]=array('id'=>'wppizza-report-val-avg', 'class'=>'', 'lbl'=>__('Sales Averages [in range]','wppizza-admin'),'val'=>'<p>'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_average']).' '.__('per day','wppizza-admin').'<br />'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_average_month']).' '.__('per month','wppizza-admin').'</p>');
				$box[]=array('id'=>'wppizza-report-count-total', 'class'=>'', 'lbl'=>__('Orders/Items Total [in range]','wppizza-admin'),'val'=>'<p>'.$datasets['sales_count_total'].' '.__('Orders','wppizza-admin').': '.$reportCurrency.' '.$datasets['items_value_total'].'<br /> ('.$datasets['items_count_total'].' '.__('items','wppizza-admin').')<br /><span class="description">'.__('before taxes, charges and discounts','wppizza-admin').'</span></p>');
				$box[]=array('id'=>'wppizza-report-taxes', 'class'=>'', 'lbl'=>__('Total Tax on Orders [in range]','wppizza-admin'),'val'=>'<p>'.wppizza_output_format_price($datasets['tax_total']).'</p>');
				$box[]=array('id'=>'wppizza-report-count-avg', 'class'=>'', 'lbl'=>__('Orders/Items Averages [in range]','wppizza-admin'),'val'=>'<p>'.$datasets['sales_count_average'].' '.__('Orders','wppizza-admin').' ('.$datasets['sales_item_average'].' '.__('items','wppizza-admin').') '.__('per day','wppizza-admin').'<br />'.$datasets['sales_count_average_month'].' '.__('Orders','wppizza-admin').' ('.$datasets['sales_item_average_month'].' items) '.__('per month','wppizza-admin').'</p>');
				$box[]=array('id'=>'wppizza-report-info', 'class'=>'', 'lbl'=>__('Range','wppizza-admin'),'val'=>'<p>'.$firstDateFormatted.' - '.$lastDateFormatted.'<br />'.$daysSelected.' '.__('days','wppizza-admin').'<br />'.$monthAvgDivider.' '.__('months','wppizza-admin').'</p>');

				/*boxes right*/
				$boxrt[]=array('id'=>'wppizza-report-top10-volume', 'class'=>'', 'lbl'=>__('Best/Worst sellers by Volume [in range]','wppizza-admin'),'val'=>$htmlBsVol.$htmlWsVol);
				$boxrt[]=array('id'=>'wppizza-report-top10-value', 'class'=>'', 'lbl'=>__('Best/Worst sellers by Value [% of all orders in range]','wppizza-admin'),'val'=>$htmlBsVal.$htmlWsVal);
				$boxrt[]=array('id'=>'wppizza-report-nonsellers', 'class'=>'', 'lbl'=>__('Non-Sellers [in range]','wppizza-admin'),'val'=>$htmlNoSellers);

			}
			/**allow order change by filter**/
			$box=apply_filters('wppizza_filter_reports_boxes_left',$box);
			$boxrt=apply_filters('wppizza_filter_reports_boxes_right',$boxrt);
			/******************************************************************************************************************************************************
			*
			*
			*	[graph data]
			*
			*
			******************************************************************************************************************************************************/
				/***graph data sales value**/
				$grSalesValue=array();
				foreach($graphDates as $date){
					$str2t=strtotime($date.' 12:00:00');
					$xaxis=date($xaxisFormat,$str2t);
					$val=!empty($datasets['sales'][$date]['sales_value_total']) ? $datasets['sales'][$date]['sales_value_total'] : 0;
					$grSalesValue[]='["'.$xaxis.'",'.$val.']';
				}
				$graph['sales_value']='label:"'.__('sales value','wppizza-admin').'",data:['.implode(',',$grSalesValue).']';

				/***graph data sales count**/
				$grSalesCount=array();
				foreach($graphDates as $date){
					$str2t=strtotime($date.' 12:00:00');
					$xaxis=date($xaxisFormat,$str2t);
					$val=!empty($datasets['sales'][$date]['sales_count_total']) ? $datasets['sales'][$date]['sales_count_total'] : 0;
					$grSalesCount[]='["'.$xaxis.'",'.$val.']';
				}
				$graph['sales_count']='label:"'.__('number of sales','wppizza-admin').'",data:['.implode(',',$grSalesCount).'], yaxis: 2';

				/***graph data items count**/
				$grItemsCount=array();
				foreach($graphDates as $date){
					$str2t=strtotime($date.' 12:00:00');
					$xaxis=date($xaxisFormat,$str2t);
					$val=!empty($datasets['sales'][$date]['items_count_total']) ? $datasets['sales'][$date]['items_count_total'] : 0;
					$grItemsCount[]='["'.$xaxis.'",'.$val.']';
				}
				$graph['items_count']='label:"'.__('items sold','wppizza-admin').'",data:['.implode(',',$grItemsCount).'], yaxis: 3';



		/*
			allow for filtering, perhaps in conjunction with running your own query
			using wppizza_filter_report_query filter
		*/
		$datasets = apply_filters('wppizza_filter_report_datasets', $datasets, $processOrder);

		/************************************
			make array to return
		*************************************/
		$data=array();
		$data['currency']=$reportCurrency;
		$data['counts']=$menu_items_and_categories;
		$data['recent_orders']= $recent_orders ;
		$data['dataset']=$datasets;
		$data['graphs']=array('data'=>$graph,'label'=>$graphLabel,'hoverOffsetTop'=>$hoverOffsetTop,'hoverOffsetLeft'=>$hoverOffsetLeft,'series'=>array('lines'=>$serieslines,'bars'=>$seriesbars,'points'=>$seriespoints));
		$data['boxes']=$box;
		$data['boxesrt']=$boxrt;
		$data['reportTypes']=$reportTypes;
		$data['view']=($overview && !$customrange) ? 'ini' : 'custom';

		/*set transient*/
		if($transient_expiry){
			$data['transient_set_at_'.$transient_expiry.'']=$wpTime;
			set_transient( WPPIZZA_TRANSIENT_REPORTS_NAME.'_'.$transient_expiry.'' , $data, $transient_expiry );/*one hour*/
		}

	return $data;
	}


	/**
		query the data set
	**/
	function wppizza_report_mkquery($wpdbPrefix, $oQuery){
		global $blog_id;


		$ordersQuery='SELECT *, order_date as oDate ,';
		if(defined('WPPIZZA_REPORT_NO_DB_OFFSET')){/* in case accounting for the mysql timezone offset causes issues leave this here for now*/
			$ordersQuery.="UNIX_TIMESTAMP(order_date) ";
		}else{
			$ordersQuery.="UNIX_TIMESTAMP(order_date)-TIMESTAMPDIFF(SECOND, NOW(), UTC_TIMESTAMP()) ";
		}
		$ordersQuery.='as order_date, "'.$blog_id.'" as blog_id FROM '.$wpdbPrefix . WPPIZZA_TABLE_ORDERS .' WHERE payment_status IN ("COMPLETED") ';
		$ordersQuery.= $oQuery;
		$ordersQuery.='ORDER BY order_date ASC';


		/* 
			allow filtering , passing on additionaly where parameters 
			as well as prefix and table to build your own if needs be
		*/
		$ordersQuery = apply_filters('wppizza_filter_report_query', $ordersQuery, $wpdbPrefix, $oQuery);

	return $ordersQuery;
	}


	/*********************
		export
	********************/
	function wppizza_report_export($report_data){
		if(empty($_GET['export'])){
			return;
		}

		/* 
			export your own report if you want 
		*/
		do_action('wppizza_custom_report', $report_data);

		$currency = $report_data['currency'];
		$dataset = $report_data['dataset'];


		$wpTime=current_time('timestamp');
		$filename[]=date('Y.m.d',$wpTime);
		/*add range**/
		if(isset($_GET['from']) && isset($_GET['to'])){
			$filename[]='-[';
			$filename[]=esc_sql(str_replace("-",".",$_GET['from']));
			$filename[]='-';
			$filename[]=esc_sql(str_replace("-",".",$_GET['to']));
			$filename[]=']';
		}else{
			if(isset($_GET['name'])){
				$filename[]='-'.esc_sql(str_replace(" ","_",$_GET['name']));
			}
		}
		/*filter if you want*/
		$filename = apply_filters('wppizza_filter_report_export_title', $filename);
		$filename=implode("",$filename);

		$delimiter=',';
		$encoding='base64';
		$mime='text/csv; charset='.WPPIZZA_CHARSET.'';
		$extension='.csv';

		/**
			get first and last date
			or make upi a range label from get vars
		**/
		$d=0;
		if(!empty($dataset['sales'])){
			foreach($dataset['sales'] as $date => $order){
				if($d==0){
					$startdate=$date;
				}else{
					$enddate=$date;
				}
			$d++;
			}
			/**in case start and end are the same**/
			$enddate=empty($enddate) ? $startdate : $enddate;
			/** range label **/
			$range_label = ''.$startdate.' - '.$enddate.'';
			
		}else{
			$range_label = ''.sanitize_text_field($_GET['name']).'';
		}


		/**************************************************************************
			sales by date
		**************************************************************************/
		$result['sales_by_date']='"Range: '.$range_label.'"'.PHP_EOL.PHP_EOL;
		$result['sales_by_date'].='"'.__('sales by dates','wppizza-admin').'"'.PHP_EOL;
		/*sales*/
		$result['sales_by_date'].='"'.__('date','wppizza-admin').'", "'.__('sales value(incl. taxes, charges and discounts)','wppizza-admin').'", "'.__('items order value','wppizza-admin').'", "'.__('number of sales','wppizza-admin').'", "'.__('number of items sold','wppizza-admin').'"  , "'.__('tax on order','wppizza-admin').'"  '.PHP_EOL;
		$d=0;
		/**sum it up*/
		$sales_value_total=0;
		$items_value_total=0;
		$sales_count_total=0;
		$items_count_total=0;
		$sales_order_tax=0;
		foreach($dataset['sales'] as $date=>$order){
			$result['sales_by_date'].=$date . $delimiter . $order['sales_value_total']  . $delimiter . $order['items_value_total'] . $delimiter . $order['sales_count_total'] . $delimiter . $order['items_count_total'] . $delimiter . $order['sales_order_tax'];
			$result['sales_by_date'].=PHP_EOL;

			/**add it up**/
			$sales_value_total+=$order['sales_value_total'];
			$items_value_total+=$order['items_value_total'];
			$sales_count_total+=$order['sales_count_total'];
			$items_count_total+=$order['items_count_total'];
			$sales_order_tax+=$order['sales_order_tax'];

		$d++;
		}
		/**sums of it all*/
		$result['sales_by_date'].='"", "'.__('total','wppizza-admin').'", "'.__('total','wppizza-admin').'", "'.__('total','wppizza-admin').'", "'.__('total','wppizza-admin').'", "'.__('total','wppizza-admin').'" '.PHP_EOL;
		$result['sales_by_date'].=''. $delimiter  . $sales_value_total  . $delimiter . $items_value_total . $delimiter . $sales_count_total . $delimiter . $items_count_total . $delimiter . $sales_order_tax;


		/**************************************************************************
			sales by item
		**************************************************************************/
		if(is_array($dataset['items_summary']) && count($dataset['items_summary'])>0){

			/*add some empty lines first*/
			$result['sales_by_item']=PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;
			$result['sales_by_item'].='"'.__('sales by item','wppizza-admin').'"'.PHP_EOL;
			/*items*/
			$result['sales_by_item'].='"'.__('quantity','wppizza-admin').'", "'.__('item','wppizza-admin').'", "'.__('total value','wppizza-admin').'"'.PHP_EOL;
			$totalNumberItems=0;
			$totalSalesItems=0;
			foreach($dataset['items_summary'] as $uniqueItem=>$itemDetails){
				$result['sales_by_item'].=$itemDetails['quantity']  . $delimiter . wppizza_decode_entities($itemDetails['title']) . $delimiter . $itemDetails['pricetotal'];
				$result['sales_by_item'].=PHP_EOL;
				/*add it up*/
				$totalNumberItems+=$itemDetails['quantity'];
				$totalSalesItems+=$itemDetails['pricetotal'];
			}

			/*add some empty lines*/
			$result['sales_by_item'].=PHP_EOL;
			//irrelevant as already displayed
			//$result.='"total quantity all items", "",  "total value all items"'.PHP_EOL;
			//$result.=$totalNumberItems  . $delimiter . '' . $delimiter . $totalSalesItems;
		}

		/**************************************************************************
			sales value by gateway
		**************************************************************************/
		if(is_array($dataset['gateways_summary']) && count($dataset['gateways_summary'])>0 && !defined('WPPIZZA_OMIT_REPORT_GATEWAYS_SUMMARY')){
			$result['sales_by_gateway']=PHP_EOL.'"'.__('payment type','wppizza-admin').'"'.PHP_EOL;
			/*items*/
			$result['sales_by_gateway'].='"'.__('type','wppizza-admin').'", "'.__('total value','wppizza-admin').'"'.PHP_EOL;
			foreach($dataset['gateways_summary'] as $uniqueGateway=>$gatewayValue){
				$result['sales_by_gateway'].=$uniqueGateway  . $delimiter . $gatewayValue;
				$result['sales_by_gateway'].=PHP_EOL;
			}
			/*add some empty lines */
			$result['sales_by_gateway'].=PHP_EOL;
		}

		/**************************************************************************
			sales value by order status
		**************************************************************************/
		if(is_array($dataset['order_status_summary']) && count($dataset['order_status_summary'])>0 && !defined('WPPIZZA_OMIT_REPORT_ORDER_STATUS_SUMMARY')){
			$result['sales_by_status']=PHP_EOL.'"'.__('order status','wppizza-admin').'"'.PHP_EOL;
			/*items*/
			$result['sales_by_status'].='"'.__('status','wppizza-admin').'", "'.__('count','wppizza-admin').'", "'.__('total value','wppizza-admin').'"'.PHP_EOL;
			foreach($dataset['order_status_summary'] as $uniqueKey=>$statusValue){
				$result['sales_by_status'].=$uniqueKey  . $delimiter . $statusValue['count'] . $delimiter . $statusValue['value'];
				$result['sales_by_status'].=PHP_EOL;
			}
			/*add some empty lines */
			$result['sales_by_status'].=PHP_EOL;
		}

		/**************************************************************************
			sales value by custom order status
		**************************************************************************/
		if(is_array($dataset['order_custom_status_summary']) && count($dataset['order_custom_status_summary'])>0 && !defined('WPPIZZA_OMIT_REPORT_CUSTOM_ORDER_STATUS_SUMMARY')){
			$result['sales_by_custom_status']=PHP_EOL.'"'.__('custom options','wppizza-admin').'"'.PHP_EOL;
			/*items*/
			$result['sales_by_custom_status'].='"'.__('option','wppizza-admin').'", "'.__('count','wppizza-admin').'", "'.__('total value','wppizza-admin').'"'.PHP_EOL;
			foreach($dataset['order_custom_status_summary'] as $uniqueKey=>$statusValue){
				$result['sales_by_custom_status'].=$uniqueKey  . $delimiter . $statusValue['count'] . $delimiter . $statusValue['value'];
				$result['sales_by_custom_status'].=PHP_EOL;
			}
			/*add some empty lines */
			$result['sales_by_custom_status'].=PHP_EOL;
		}

		/* filter array to be able to delete / add things to the output before imploding if required */
		$result = apply_filters('wppizza_filter_reports_export_results', $result, $report_data);
		$result = implode('',$result);
		/**************************************************************************
			write to file
		**************************************************************************/
		$filename = ''.strtolower(wppizza_validate_alpha_only(WPPIZZA_NAME)).'_report_'.$filename.''.$extension.'';
		header("Content-Encoding: ".WPPIZZA_CHARSET."");
		header("Content-Type: ".$mime."");
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header("Content-Length: " . strlen($result));
		echo $result;
		exit();
	}
}

/***************************************************************
*
*	[ini]
*
***************************************************************/
$WPPIZZA_REPORTS = new WPPIZZA_REPORTS();
?>