<?php

$dbprefix = elgg_get_config("dbprefix");

$offset = sanitise_int(get_input("offset", 0), false);

$options = array(
	"type" => "user",
	"relationship" => "member_of_site",
	"relationship_guid" => elgg_get_site_entity()->getGUID(),
	"inverse_relationship" => true,
	"site_guids" => false,
	"joins" => array("JOIN " . $dbprefix . "users_entity ue ON e.guid = ue.guid"),
  // "wheres" => array("ue.last_login <= " . $last_login),
	"order_by" => "ue.name"
);

$count_options = $options;
$count_options["count"] = "TRUE";



$ia = elgg_set_ignore_access(true);
$num_users = elgg_get_entities_from_relationship($count_options);


$groups = elgg_get_entities(
	array(
		'type' => 'group',
		"site_guids" => false,
		"limit"=> 0,
	));

$ia = elgg_set_ignore_access(false);

$unique_users_approved = 0;
$total_users_approvals = 0;
$total_users_returns = 0;
$users_approvals_by_mode = array();
$users_approvals_by_round = array();

$exclude_mode = get_input("exclude_mode");
$exclude_round = get_input("exclude_round");

if($num_users>0){

  foreach($groups as $group) {
    $filter_mode[$group->mode] = 1;
    $filter_round[$group->round] = 1;
	}
  
  $__elgg_ts = time();
  $__elgg_token = generate_action_token($__elgg_ts);
  $content = "<p><h3>Excluir Modos e Rodadas da Contagem</h3></p>";
  $content .= "<form action='/admin/oficinas/approved' method='post'>";
  $content .= "<input type=hidden name=__elgg_token value='$__elgg_token'>";
  $content .= "<input type=hidden name=__elgg_ts value='$__elgg_ts'>";
	
 	foreach($filter_mode as $mode=>$m) {
 	  $chked = "";
 	  if(in_array($mode,$exclude_mode)) $chked = "checked";
 	  if(!empty($mode)) $content .= "<input type=checkbox name='exclude_mode[]' value='$mode' $chked>$mode";
 	}
 	
 	$content .= "<br/>";
 	foreach($filter_round as $round=>$v) {
 	  $chked = "";
 	  if(in_array($round,$exclude_round)) $chked = "checked";
 	  if(!empty($round)) $content .= "<input type=checkbox name='exclude_round[]' value='$round' $chked>$round";
 	}
  
  $content .= "<p><input type=submit value='Filtar'></p>";
  $content .= "</form>";
  
	$content .= "<table class='elgg-table'>";
	$content .= "<tr>";
	$content .= "<th>" . elgg_echo("user") . "</th>";
	$content .= "<th>Cidade</th>";
	$content .= "<th>Estado</th>";
	$content .= "<th>" . elgg_echo("approved") . "</th>";
	$content .= "</tr>";
	
	$limit = 100;
	$user_counter = 0;
	$user_cursor = $limit;
	
	$options['limit'] = $limit;
	
	while($user_counter<$num_users) {
	  
	  if($user_cursor==$limit) {
	    $options['offset'] = $user_counter;
	    unset($users);
	    $users = elgg_get_entities_from_relationship($options);
	    $user_cursor=0;
	  }
	  
	  $user = $users[$user_cursor];
	  $user_cursor++;
	  $user_counter++;
	 
	  

    $approved = 0;
    $groups_approved = "";
    
    foreach($groups as $group) {
      $group_guid = $group->guid;
      
      if(in_array($group->mode,$exclude_mode)) continue;
      if(in_array($group->round,$exclude_round)) continue;

      if(!eval("return \$user->approved_{$group_guid};")) continue;
		  $groups_approved .= "<li>$group->name</li>";
      $approved++;
      $total_users_approvals++;
      
      $users_approvals_by_round[$group->round]++;
      $users_approvals_by_mode[$group->mode]++;
		}
		
		if(!$approved)  {
		  continue;
		} elseif($approved>=2) {
		  $total_users_returns++;
		}
		
		$content .= "<tr>";
		$content .= "<td width='40%'>" . elgg_view("output/url", array("text" => $user->name, "href" => $user->getURL())) . "</td>";
  	$content .= "<td>".$user->cidade."</td>";
  	$content .= "<td>".$user->estado."</td>";
		$content .= "<td width='40%'> <nl>$groups_approved</nl></td>";
		$content .= "</tr>";
		
		$unique_users_approved ++;
	}

	$content .= "</table>";
	
	$header = "<ul>";
	$header .= "<li>".elgg_echo('oficinas_reports:admin:users:number_approved_user').":  $unique_users_approved</li>";
	$header .= "<li>".elgg_echo('oficinas_reports:admin:users:number_approvals').":  $total_users_approvals</li>";
	$header .= "<li>".elgg_echo('oficinas_reports:admin:users:number_returns').":  $total_users_returns</li>";
	

	$header .= "</ul><br/>";

	$header .= "<h3>Conclusões por Modalidade</h3> <ul class='second_level'>";
  foreach($users_approvals_by_mode as $mode=>$count) {
    $header .= "<li>$mode: $count</li>";
  }
  $header .= "</ul>";

	$header .= "<p><h3>Conclusões por Rodada</h3> <ul class='second_level'>";
  foreach($users_approvals_by_round as $round=>$count) {

    $header .= "<li>$round: $count</li>";
  }
  $header .= "</ul></p>";
		
} else {
	$content = elgg_echo("notfound");
}

echo elgg_view_module("inline", '', $header.$content);