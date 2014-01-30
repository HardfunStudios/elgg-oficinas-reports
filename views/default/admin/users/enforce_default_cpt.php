<?php

$dbprefix = elgg_get_config("dbprefix");

$limit =  max((int) get_input("limit", 50), 0);
$offset = sanitise_int(get_input("offset", 0), false);

$options = array(
	"type" => "user",
	"limit" => $limit,
	"offset" => $offset,
	"relationship" => "member_of_site",
	"relationship_guid" => elgg_get_site_entity()->getGUID(),
	"inverse_relationship" => true,
	"site_guids" => false,
	"joins" => array("JOIN " . $dbprefix . "users_entity ue ON e.guid = ue.guid"),
  // "wheres" => array("ue.last_login <= " . $last_login),
	"order_by" => "ue.last_login DESC"
	);

$users = elgg_get_entities_from_relationship($options);
$content = '';
if(!empty($users)){
  

	$content .= "<table class='elgg-table'>";
	$content .= "<tr>";
	$content .= "<th>" . elgg_echo("user") . "</th>";
	$content .= "<th>" . elgg_echo("custom_profile_type") . "</th>";
	$content .= "</tr>";
	
	foreach($users as $user){

    $link = elgg_view('output/url', array(
			'href' => $user->getURL() ,
			'text' => $icon . $user->name,
      // 'class' => 'mentions-user-link'
		));

		$content .= "<td >" . $link . "</td>";

	  
	  $profile_type_guid = $user->custom_profile_type;
		$profile_type_name = "";
		if(!empty($profile_type_guid)){
			$profile_type = get_entity($profile_type_guid); 
			$profile_type_name = $profile_type->getTitle();
      // var_dump($profile_type );
	  } 
		$content .= "<td >" . $profile_type_name . "</td>";

		$content .= "</tr>";
	}
	
	$content .= "</table>";
	
	$options["count"] = true;
	$count = elgg_get_entities_from_relationship($options);
	
	$content .= elgg_view("navigation/pagination", array("offset" => $offset, "limit" => $limit, "count" => $count));
	
	$enforce_link = elgg_add_action_tokens_to_url('/action/oficinas_reports/enforce_dpf');
	$content .= "<br />" . elgg_view("input/button", 
	          array("value" => elgg_echo("enforce_default_cpt"), 
	                "onclick" => "document.location.href='" . $enforce_link . "'", 
	                "class" => "elgg-button-action"));
  
  $content .= "<br/>" ;
	
} else {
	$content = elgg_echo("notfound");
}

echo elgg_view_module("inline", elgg_echo("oficinas_reports:admin:users:bygroups"), $content);