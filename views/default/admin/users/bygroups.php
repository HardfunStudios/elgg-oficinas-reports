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

if(!empty($users)){
	$content = "<table class='elgg-table'>";
	$content .= "<tr>";
	$content .= "<th>" . elgg_echo("user") . "</th>";
	$content .= "<th>" . elgg_echo("usersettings:statistics:label:lastlogin") . "</th>";
	$content .= "<th>" . elgg_echo("banned") . "</th>";
	$content .= "</tr>";
	
	foreach($users as $user){
		$content .= "<tr>";
		$content .= "<td width='40%'>" . elgg_view("output/url", array("text" => $user->name, "href" => $user->getURL())) . "</td>";
		$last_login = $user->last_login;
		if(empty($last_login)){
			$content .= "<td>" . elgg_echo("profile_manager:admin:users:inactive:never") . "</td>";
		} else {
			$content .= "<td>" . elgg_view_friendly_time($last_login) . "</td>";
		}
		$groups = $user->getGroups();
		$content .= "<td width='40%'> <nl>";
		foreach($groups as $key=>$group) {
      // if($key!=0) $content.=", ";
		  $content .= "<li>$group->name</li>";
		}
		$content .= "</nl></td>";
		$content .= "</tr>";
	}
	
	$content .= "</table>";
	
	$options["count"] = true;
	$count = elgg_get_entities_from_relationship($options);
	
	$content .= elgg_view("navigation/pagination", array("offset" => $offset, "limit" => $limit, "count" => $count));
	
	$download_link = elgg_add_action_tokens_to_url("/action/profile_manager/users/export_inactive?last_login=" . $last_login);
	
	$content .= "<br />" . elgg_view("input/button", array("value" => elgg_echo("profile_manager:admin:users:inactive:download"), "onclick" => "document.location.href='" . $download_link . "'", "class" => "elgg-button-action"));
	
} else {
	$content = elgg_echo("notfound");
}

echo elgg_view_module("inline", elgg_echo("oficinas_reports:admin:users:bygroups"), $content);