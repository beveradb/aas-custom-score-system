<?php
/*
Plugin Name: Match Score Listing
*/
function d($array){
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class My_Example_List_Table extends WP_List_Table {

  function __construct(){
    global $status, $page;

        parent::__construct( array(
            'singular'  => __( 'book', 'mylisttable' ),     //singular name of the listed records
            'plural'    => __( 'books', 'mylisttable' ),   //plural name of the listed records
            'ajax'      => false        //does this table support ajax?

    ) );

    add_action( 'admin_head', array( &$this, 'admin_header' ) );            

    }

  function admin_header() {
    $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
    if( 'my_list_test' != $page )
    return;
    echo '<style type="text/css">';
    echo '.wp-list-table .column-cb { width: 5%; }';
    echo '.wp-list-table .column-vanue_name { width: 15%; }';
    echo '.wp-list-table .column-home_team_name { width: 15%; }';
    echo '.wp-list-table .column-away_team_name { width: 15%;}';
    echo '.wp-list-table .column-home_team_points { width: 5%;}';
    echo '.wp-list-table .column-away_team_points { width: 5%;}';
    echo '.wp-list-table .column-week_number { width: 5%;}';
    echo '.wp-list-table .column-date { width: 10%;}';
    echo '.wp-list-table .column-display_name { width: 20%;}';
    echo '</style>';
  }

  function no_items() {
    _e( 'No Score found, dude.' );
  }
        
 function get_sortable_columns() {
  $sortable_columns = array(
    'booktitle'  => array('booktitle',false),
    'author' => array('author',false),
    'isbn'   => array('isbn',false)
  );
  return $sortable_columns;
}

function column_default( $item, $column_name ) {
    switch( $column_name ) { 
        case 'vanue_name':
        case 'home_team_name':
        case 'away_team_name':            
        case 'home_team_points':
        case 'away_team_points':
        case 'week_number':
        case 'date':
        case 'display_name':
             return $item[ $column_name ];
        default:
            return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
  }
function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'vanue_name' => __( 'Venue Name', 'mylisttable' ),
            'home_team_name' => __( 'Home Team Name', 'mylisttable' ),
            'away_team_name'    => __( 'Away Team Name', 'mylisttable' ),
            'home_team_points' => __( 'Home Team Points', 'mylisttable' ),
            'away_team_points' => __( 'Away Team Points', 'mylisttable' ),
            'week_number' => __( 'Week Number', 'mylisttable' ),
            'date' => __( 'Date', 'mylisttable' ),
            'display_name' => __( 'User Name', 'mylisttable' ),
        
        );
         return $columns;
    }

function usort_reorder( $a, $b ) {
  // If no sort, default to title
  $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'booktitle';
  // If no order, default to asc
  $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
  // Determine sort order
  $result = strcmp( $a[$orderby], $b[$orderby] );
  // Send final sort direction to usort
  return ( $order === 'asc' ) ? $result : -$result;
}

function column_vanue_name($item){
  $actions = array(
             'delete'    => sprintf('<a onclick="return confirm(\'Are you sure want to delete this match score ?\')" href="?page=%s&action=%s&match_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
        );

  return sprintf('%1$s %2$s', $item['vanue_name'], $this->row_actions($actions) );
}

function get_bulk_actions() {
  $actions = array(
    'delete'    => 'Delete'
  );
  return $actions;
}

function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="match[]" value="%s" />', $item['id']
        );    
    }

function prepare_items() {
  global $wpdb;  
        
  $action = $this->current_action();
   if($action=='delete'){
      $ids=$_POST['match'];
      $wpdb->query("DELETE FROM `ss_matches` WHERE `id` IN (".implode(",",$ids).")");
  }
  
  if(isset($_GET['action']) && $_GET['action']=='delete'){
      if(isset($_GET['match_id']))
        $wpdb->query("DELETE FROM `ss_matches` WHERE `id` =".$_GET['match_id']);
  }
  
  $columns  = $this->get_columns();
  $hidden   = array();
  $sortable = $this->get_sortable_columns();
  $this->_column_headers = array( $columns, $hidden, $sortable );
        
   $per_page = 10;
  $current_page = $this->get_pagenum();
        
  //$total_items = count( $this->example_data );    
  $total_items = $wpdb->get_var("SELECT COUNT(m.`id`) FROM `ss_matches` AS m 
            JOIN `ss_teams` AS ht ON (m.`home_team_id`=ht.`id`)
            JOIN  `ss_teams` AS `at`  ON (m.`away_team_id`=at.`id`)
            JOIN  `ss_venues` AS v  ON (m.`venue_id`=v.`id`)
            JOIN  `wp_users` AS u  ON (m.`user_id`=u.`ID`)");
  $offset=( $current_page-1 )* $per_page;

  // only ncessary because we have sample data
  $this->found_data = array_slice( $this->example_data,$offset, $per_page );

$sql="SELECT m.`id`,ht.`name` as home_team_name ,v.`name` AS vanue_name,
    at.`name` as away_team_name,m.`home_team_points`,m.`away_team_points`,m.`date`,m.`week_number`,
    m.`import_message`,m.`archived`,u.`display_name` FROM `ss_matches` AS m 
            JOIN `ss_teams` AS ht ON (m.`home_team_id`=ht.`id`)
            JOIN  `ss_teams` AS `at`  ON (m.`away_team_id`=at.`id`)
            JOIN  `ss_venues` AS v  ON (m.`venue_id`=v.`id`)
            JOIN  `wp_users` AS u  ON (m.`user_id`=u.`ID`)
            ORDER BY m.`id` LIMIT $offset,$per_page";  
  $records = $wpdb->get_results($sql,ARRAY_A);
        
  $this->set_pagination_args( array(
    'total_items' => $total_items,                  //WE have to calculate the total number of items
    'per_page'    => $per_page                     //WE have to determine how many items to show on a page
  ) );
  $this->items = $records;
}

} //class



function my_add_menu_items(){
  $hook = add_menu_page( 'Match Score', 'Match Score', 'activate_plugins', 'match_score_list', 'my_render_list_page' );
  add_action( "load-$hook", 'add_options' );
}

function add_options() {
  global $myListTable;
  $option = 'per_page';
  $args = array(
         'label' => 'Books',
         'default' => 10,
         'option' => 'books_per_page'
         );
  add_screen_option( $option, $args );
  $myListTable = new My_Example_List_Table();
}
add_action( 'admin_menu', 'my_add_menu_items' );



function my_render_list_page(){
  global $myListTable;
  echo '</pre><div class="wrap"><h2>Match Score List  </h2>'; 
  $myListTable->prepare_items(); 
?>
  <form method="post">
    <input type="hidden" name="page" value="match_score_list">
    <?php
    //$myListTable->search_box( 'search', 'search_id' );

  $myListTable->display(); 
  echo '</form></div>'; 
}

        