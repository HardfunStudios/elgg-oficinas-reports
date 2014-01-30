<?php 

$dbprefix = elgg_get_config("dbprefix");
$options = array(
	"type" => "user",
	"limit" => 0,
	"offset" => 0,
	"relationship" => "member_of_site",
	"relationship_guid" => elgg_get_site_entity()->getGUID(),
	"inverse_relationship" => true,
	"site_guids" => false,
	"joins" => array("JOIN " . $dbprefix . "users_entity ue ON e.guid = ue.guid"),
  // "wheres" => array("ue.last_login <= " . $last_login),
	"order_by" => "ue.last_login DESC"
	);

$users = elgg_get_entities_from_relationship($options);

$custom_profile_fields_custom_profile_type = elgg_get_plugin_setting("default_profile_type", "profile_manager");
$profile_type = get_entity($custom_profile_fields_custom_profile_type); 
$profile_type_name = $profile_type->getTitle();


$saved = 0;
$temp = array();
foreach($users as $user) {
  $temp[] = (int)$user->custom_profile_type;
  if(empty($user->custom_profile_type)) {
    // $user->custom_profile_type = ;
    if(create_metadata($user->guid, 'custom_profile_type', $custom_profile_fields_custom_profile_type, 'integer', $user->guid, get_default_access($user)) ) {
      $saved++;
    } 
  }
}


system_message($saved." usuários com o perfil alterado para ".$profile_type_name);


forward(REFERER);