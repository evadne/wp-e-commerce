<?php
global $wpdb;
?>
<div class="wrap">
<?php
include('updates/database_template.php');

$path = "/var/www/apps.instinct.co.nz/wp_2.7/wp-content/plugins/trunk/*";

echo "<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif;', font-size:12px;>";

//$search_command = 'grep -RIsE --exclude-dir=\.svn \'(".[:space:]*|\{)\$wpdb->prefix(\}|[:space:]*.")_TABLENAME_\' *  ' ;
//  $search_command = 'grep -RIsEl --exclude-dir=\.svn --exclude=database_template.php \'("\.[:space:]*|\{)\$wpdb->prefix(\}|[:space:]*\.")_TABLENAME_\' '.$path.'  ' ;
//  $replace_command = 'sed -ri \'s/("\.[:space:]*|\{)\$wpdb->prefix(\}|[:space:]*\.")_TABLENAME_/"._CONSTANTNAME_."/g\' ' ;
 
 
 $search_command = 'grep -RIsEl --exclude-dir=\.svn --exclude=database_template.php \'`(\{)\$wpdb->prefix(\})_TABLENAME_`\' '.$path.'  ' ;
 $replace_command = 'sed -ri \'s/`(\{)\$wpdb->prefix(\})_TABLENAME_`/`"._CONSTANTNAME_."`/g\' ' ;

// $search_command = 'grep -RIsEl --exclude-dir=\.svn --exclude=database_template.php \'WPSC_TABLE\' '.$path.'  ' ;
// $replace_command = 'sed -ri \'s/WPSC_TABLE/WPSC_TABLE/g\' ' ;
// 
// //echo $constant_name."\n";
// //echo str_replace("_TABLENAME_", $table_name, $search_command)."\n";
// $file_list = explode("\n", shell_exec($search_command));
// foreach($file_list as $file) {
// 	if($file != '') {
// 		$sed_command = $replace_command.trim($file);
// 		echo $sed_command. "\n";
// 	//	shell_exec($sed_command);
// 	}
// }
// print_r($file_list);
/*

foreach($wpsc_database_template as $key => $table_data) {
  
	$table_name = $key;
  if($table_data['previous_names'] != null) {
    $table_name = $table_data['previous_names'];
  }
  
  $table_name = preg_replace("/^wp_/", "", $table_name);
  //echo $table_name." \n";
  
  $constant_name = strtoupper(str_replace("wp_wpsc_", "wpsc_table_", $key));
  
  //echo $constant_name."\n";
  echo str_replace("_TABLENAME_", $table_name, $search_command)."\n";
  $file_list = explode("\n", shell_exec(str_replace("_TABLENAME_", $table_name, $search_command)));
  foreach($file_list as $file) {
    if($file != '') {
			$sed_command = str_replace(array("_TABLENAME_", "_CONSTANTNAME_"), array($table_name, $constant_name), $replace_command).trim($file);
			echo $sed_command."\n";
			shell_exec($sed_command);
    }
  }
  print_r($file_list);
  
  //break;  
}
// */
echo "</pre>";


/*
echo "<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif;', font-size:12px;>";
foreach($wpsc_database_template as $key => $table_data) {
  $patterns = array("/^{$wpdb->prefix}(?!wpsc)/", "/^{$wpdb->prefix}/", "/associations/", "/priceandstock/", "/also_bought_product/", "/collect_data_forms/");
	$replacements = array("{$wpdb->prefix}wpsc_","{\$wpdb->prefix}", "assoc", "properties", "also_bought", "checkout_forms");
  $old_table_name = str_replace("wp_","{\$wpdb->prefix}",$key);
  $new_table_name = preg_replace($patterns,$replacements,$key);
  $constant_name = strtoupper(str_replace("{\$wpdb->prefix}wpsc_", "wpsc_table_", $new_table_name));
  
  echo "// code to create or update the $new_table_name table\n";
  echo "\$table_name = $constant_name;\n";
  foreach($table_data as $data_type => $data_group) {
   if(is_array($data_group)) {
		foreach($data_group as $data_row_name => $data_row) {
			echo "\$wpsc_database_template[\$table_name]['$data_type']['$data_row_name'] = \"$data_row\";\n";
		}
   } else {
		$data_group = preg_replace("/^wp_/", "{\$wpdb->prefix}", $data_group);
		echo "\$wpsc_database_template[\$table_name]['$data_type'] = \"$data_group\";\n";
   }
  }
  
  echo "\n\r";
}
echo "</pre>";
*/


?>
</div>