<?php
// VARIABLES GENERALES.
$game_timestamp = file_get_contents($tmp_path . '/game_timestamp.txt');
if ($game_timestamp == FALSE) {
  $game_timestamp = time();
  file_put_contents($tmp_path . '/game_timestamp.txt', $game_timestamp);
}
$settings_timestamp = file_get_contents($tmp_path . '/settings_timestamp.txt');
if ($settings_timestamp == FALSE) {
  $settings_timestamp = time();
  file_put_contents($tmp_path . '/settings_timestamp.txt', $settings_timestamp);
}

if (
  !isset($_SESSION['settings']) ||
  !isset($_SESSION['current_timestamp']) ||
  $settings_timestamp > $_SESSION['current_timestamp']
) {
  $default_settings_set = [
    'carac1_name' => 'esprit',
    'carac2_name' => 'corps',
    'carac3_name' => '',
    'carac1_group' => 'malin',
    'carac2_group' => 'fort',
    'carac3_group' => '',
    'adventure_name' => 'Notre Aventure',
    'adventure_guide' => "Rejoindre l'Aventure : ...'",
    'role_leader' => 'leader',
    'role_traitre' => 'traître',
    'same_stats_all' => 0,
    'random_tags' => 1,
    'willpower_on' => 0
  ];
  if (file_exists($tmp_path . '/settings.txt')) {
    $settings_data = file_get_contents($tmp_path . '/settings.txt');
    $settings = unserialize($settings_data);
  }
  else {
    $settings = [];
  }

  foreach ($default_settings_set as $setting_key => $setting_value) {
    if (!isset($settings[$setting_key]) || $settings[$setting_key] === "") {
      $settings[$setting_key] = $setting_value;
    }
  }
  $_SESSION['settings'] = $settings;
  if (isset($_SESSION['id']) && $_SESSION['id'] == 1) {
    $_SESSION['current_timestamp'] = $settings_timestamp;
  }
}
else {
  $settings = $_SESSION['settings'];
}

