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
	"order_by" => "guid"
));

$ia = elgg_set_ignore_access(false);

$unique_users_approved = 0;
$total_users_approvals = 0;
$total_users_returns = 0;
$total_users_registrations = 0;
$users_approvals_by_mode = array();
$users_approvals_by_round = array();



if($num_users>0){

	$groups_approvals = array();

	foreach($groups as $group) {
		$filter_mode[$group->mode] = 1;
		$filter_round[$group->round] = 1;
		
		
		#quick-fix for the array issue. Need to find problem later
		$students = $group->students_registered;
		if(is_array($students)) $students = $students[0];
			
		
		$groups_approvals[$group->guid]['students_registered'] = $students;
		
		$total_users_registrations += (int) $groups_approvals[$group->guid]['students_registered'] ;
	}

	$limit = 100;
	$user_counter = 0;
	$user_cursor = $limit;
	
	$options['limit'] = $limit;
	
	$user_approved = array();

	
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
    
		foreach($groups as $group) {
			$group_guid = $group->guid;
      

			if(!eval("return \$user->approved_{$group_guid};")) continue;
      
			$groups_approvals[$group_guid]['total']++;
			if(!$user_approved[$user->guid]) $groups_approvals[$group_guid]['new']++;
			$user_approved[$user->guid] = true;
		}
		
		if(!$approved)  {
			continue;
		} elseif($approved>=2) {
			$total_users_returns++;
		}
		
	}


	$__elgg_ts = time();
	$__elgg_token = generate_action_token($__elgg_ts);
	$content = "<p><h3>Excluir Modos e Rodadas da Contagem</h3></p>";
	$content .= "<form action='/admin/oficinas/approved_by_round' method='post'>";
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
	$content .= "<tr><th>Nome</th><th>Rodada</th><th>Modalidade</th><th>Inscritos</th><th>Formados</th><th>Novos</th><th>Tx Aprovação</th>";
  
	$total_approvals = 0;
	$unique_students = 0;
	$exclude_mode = get_input("exclude_mode");
	$exclude_round = get_input("exclude_round");
	foreach($groups as $group) {
    
		if(!($exclude_mode==null || $group->mode==null)) if(in_array($group->mode,$exclude_mode)) continue;
		if(!($exclude_round==null || $group->round==null)) if(in_array($group->round,$exclude_round)) continue;
    
		$registered = $groups_approvals[$group->guid]['students_registered'];
		
		if($registered>0) {
			$temp = $groups_approvals[$group->guid]['total'];
			$tx = ($temp/$registered)*100;
		} else $tx = "";
		
		if(!empty($tx)) $tx = number_format((float)$tx,1,",","")."%";
    
		$content .= "<tr><td><a href='".$group->getURL()."'>$group->name</a></td>";
		$content .= "<td>$group->round</td>";
		$content .= "<td>$group->mode</td>";
		$content .= "<td>".$registered."</td>";
		$content .= "<td>".$groups_approvals[$group->guid]['total']."</td>";
		$content .= "<td>".$groups_approvals[$group->guid]['new']."</td>";
		$content .= "<td>".$tx."</td></tr>";
    
		$total_approvals += $groups_approvals[$group->guid]['total'];
		$unique_students += $groups_approvals[$group->guid]['new'];
	}

	$global_approval_rate =  ($total_approvals/$total_users_registrations)*100;

	$content .= "<tr><td colspan=3>TOTALS</td>";
	$content .= "<td> $total_users_registrations </td>";
	$content .= "<td>".$total_approvals."</td>";
	$content .= "<td>".$unique_students."</td>"; 
	
	$content .= "<td>".number_format((float)$global_approval_rate,1,",","")."%</td></tr>";  

	$content .= "</table>";
	
		
} else {
	$content = elgg_echo("notfound");
}

echo elgg_view_module("inline", '', $header.$content);