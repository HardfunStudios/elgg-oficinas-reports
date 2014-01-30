<?php 

$dbprefix = elgg_get_config("dbprefix");

$users = get_input('approved');
$comments = get_input('comment');
$group_guid = get_input('group_guid');

$ia = elgg_set_ignore_access(true);
if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup) && ($group->canEdit())){
  $metadata_name = "approved_$group_guid";
  $comment_name = "{$metadata_name}_comment";
  $saved=0;
  $approved=0;

  foreach($users as $user_guid=>$user_approved) {
    if($user_approved) $approved++;
    create_metadata($user_guid, $metadata_name, $user_approved,'','',ACCESS_LOGGED_IN);

    create_metadata($user_guid, $comment_name, $comments[$user_guid],'','',ACCESS_LOGGED_IN);
    $saved++;
  }
  
  create_metadata($group_guid, 'students_approved', $approved);
  
  system_messages($saved." usuários salvos",'success');
}
$ia = elgg_set_ignore_access(false);
forward(REFERER);