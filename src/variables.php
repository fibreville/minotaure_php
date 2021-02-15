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
$default_settings_set = [
  'carac1_name' => 'esprit',
  'carac2_name' => 'corps',
  'carac1_group' => 'malin',
  'carac2_group' => 'fort',
  'adventure_name' => 'Notre Aventure',
  'adventure_guide' => "Rejoindre l'Aventure : maspero.blue/rpg/ ou taper !aventure'",
  'image_url' => './img/logo.png'
];

if (
  !isset($settings) ||
  !isset($_SESSION['current_timestamp']) ||
  $settings_timestamp > $_SESSION['current_timestamp']
) {
  $settings_data = file_get_contents($tmp_path . '/settings.txt');
  $settings = unserialize($settings_data);

  foreach ($default_settings_set as $setting_key => $setting_value) {
    if (!isset($settings[$setting_key]) || $settings[$setting_key] == '') {
      $settings[$setting_key] = $setting_value;
    }
  }
  $_SESSION['settings'] = $settings;
}