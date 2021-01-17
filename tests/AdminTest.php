<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
include 'src/admin_tools.php';

final class AdminTest extends TestCase
{
	public function generateUser($db): void
	{
		$db->query(
				"INSERT INTO `hrpg`"
				." (`nom`, `mdp`, `carac2`, `carac1`, `hp`, `leader`, `traitre`, `vote`, `tag1`, `tag2`, `tag3`, `log`, `lastlog`)"
				." VALUES ('".substr(md5(microtime()),rand(0,26),5)."', '', '1', '1', '1', '0', '0', '0', '', '', '', NULL, NULL)"
			);
	}
	public static function getSQLCount($db, $of)
	{
		$r = $db->query('SELECT count(*) as total FROM '.$of);
		$lines = $r->fetchAll();
		$r->closeCursor();
		return $lines[0]['total'];
	}
	public function testMakeElection(): void
	{
		$_SESSION = array();
		include 'src/connexion.php';
		$this->generateUser($db);

		$post = [
			'election'=> 'leader',
			'random_choice' => 'random',
		];
		make_election($db,$post);

		$this->assertEquals($this->getSQLCount($db, 'hrpg WHERE leader=1'),1);
	}
	public function testUpdateAdventureSurvey(): void
	{
		$_SESSION = array();
		include 'src/connexion.php';

		$post = [
			'choix'=> 'ghn',
			'c1'=> '',
			'c2'=> 'zrth',
			'c3'=> '',
			'c4'=> 'rth',
			'c5'=> '',
			'c6'=> '',
			'c7'=> '',
			'c8'=> '',
			'c9'=> '',
			'c10'=> '',
			'choixtag'=> '',
		];
		survey_update($db, $post);

		$r = $db->query('SELECT * FROM sondage LIMIT 1');
		$lines = $r->fetchAll();
		$r->closeCursor();

		$this->assertNotEquals($lines[0],$post);
	}
	public function testAddAdventureLoots(): void
	{
		$_SESSION = array();
		include 'src/connexion.php';
		$this->generateUser($db);

		$previous_add_count_loots = $this->getSQLCount($db, 'loot');
		$previous_add_count_logs = $this->getSQLCount($db, 'hrpg WHERE log <> ""');

		$post = [
			'loot'=>'srtjhrth',
			'propriete'=>'hp',
			'bonus'=>2,
			'qui'=>'*',
			'qui_multiple'=>''
		];
		updateLoot($db, $post);

		$after_add_count_loots = $this->getSQLCount($db, 'loot');
		$after_add_count_logs = $this->getSQLCount($db, 'hrpg WHERE log <> ""');

		$this->assertNotEquals($previous_add_count_logs,$after_add_count_logs);
		$this->assertNotEquals($previous_add_count_loots,$after_add_count_loots);
	}
	public function testAddAdventureEvents(): void
	{
		$_SESSION = array();
		include 'src/connexion.php';

		$post = [
			'type'=>'carac1',
			'difficulte'=>0,
			'penalite_type'=>'hp',
			'penalite'=>'',
			'victime'=>'*',
			'victime_multiple'=>'',
			'victimetag'=>''
		];
		ob_start();
		$sanction = update_events($db, $post);
		ob_end_clean();
		$this->assertNotEquals($sanction,null);
	}
	public function testAddAdventureTags(): void
	{
		$_SESSION = array();
		include 'src/connexion.php';
		$this->generateUser($db);

		$previous_add_count = $this->getSQLCount($db, 'hrpg WHERE log <> ""');

		$post = [
			'tag1' => 'azegerg,sre,sr',
			'tag2' => 'azegerg,srt,tdy,dty,u',
			'tag3' => 'azegerg',
		];
		add_new_tags($db, $post);

		$after_add_count = $this->getSQLCount($db, 'hrpg WHERE log <> ""');

		$this->assertNotEquals($previous_add_count,$after_add_count);
	}
	public function testEditAdventureSettings(): void
	{
		$_SESSION = array();
		include 'src/connexion.php';
		$post = [
			'adventure_name' => 'azegerg',
			'adventure_guide' => 'azegerg',
			'image_url' => 'azegerg',
			'carac1_name' => 'azegerg',
			'carac1_group' => 'azegerg',
			'carac2_name' => 'azegerg',
			'carac2_group' => 'azegerg',
		];
		save_new_settings($post, $tmp_path);
		$this->assertTrue(file_exists($tmp_path . '/settings.txt'));
		$this->assertTrue(file_exists($tmp_path . '/settings_timestamp.txt'));
	}
	public function testDeleteAdventureInitSondage(): void
	{
		$_SESSION = array();
		include 'src/connexion.php';
		delete_adventure($db, $tmp_path);

		$r = $db->query('SELECT * FROM sondage');
		$lines = $r->fetchAll();
		$r->closeCursor();
		$this->assertEquals( count($lines), 1, 'Not exactly one sondage found after delete adventure' );
		foreach ($lines[0] as $field => $value) {
			$this->assertEquals( $value, '', 'Not empty field found in default sondage after delete adventure (field='.$field.', value='.$value.')' );
		}
	}
	public function testDeleteAdventureInitUsers(): void
	{
		$_SESSION = array();
		include 'src/connexion.php';
		delete_adventure($db, $tmp_path);

		$r = $db->query('SELECT * FROM hrpg');
		$lines = $r->fetchAll();
		$r->closeCursor();

		if (count($lines) < 1)
			return;

		$this->assertEquals( count($lines), 1, 'Not exactly one user found after delete adventure' );
		$this->assertEquals( $lines[0]['id'], 1, 'User keep is not admin' );
	}
	public function testDeleteAdventureInitLoot(): void
	{
		$_SESSION = array();
		include 'src/connexion.php';
		delete_adventure($db, $tmp_path);

		$this->assertEquals( $this->getSQLCount($db, 'loot'), 0, 'Loot table not empty' );
	}
	public function testCleanAdventureInitLoot(): void
	{
		$_SESSION = array();
		include 'src/connexion.php';
		clean_adventure($db, $tmp_path);

		$this->assertEquals( $this->getSQLCount($db, 'hrpg WHERE vote!="0"'), 0, 'Votes not reset' );
	}
}