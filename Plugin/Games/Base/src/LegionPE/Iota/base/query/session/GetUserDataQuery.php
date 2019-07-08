<?php

/*
	SELECT DISTINCT users.*, user_stats.*,
	CASE
		WHEN users.has_friends = 1 THEN
			GROUP_CONCAT(DISTINCT(
			CASE
				WHEN friend_data.requester_uid = users.uid THEN CONCAT(CAST(friend_data.requested_uid AS CHAR), '::', CAST(friend_names.name AS CHAR), '::', CAST(friend_data.status AS CHAR))
				ELSE CONCAT(CAST(friend_data.requester_uid AS CHAR), '::', CAST(friend_names.name AS CHAR), '::', CAST(friend_data.status AS CHAR))
			END
			))
		ELSE NULL
	END AS friends,
	CASE
		WHEN users.has_ignored = 1 THEN
			GROUP_CONCAT(
				DISTINCT(ignored.ignored_uid)
			)
		ELSE NULL
	END AS ignored_names,
	CASE
		WHEN users.has_team_invites = 1 THEN
			GROUP_CONCAT(
				DISTINCT(
					CONCAT(
						CAST(team_invites.id AS CHAR), '::', CAST(team.name AS CHAR), '::', CAST(team_invites.creation_time AS CHAR), '::', CAST(team_invites.invite_duration AS CHAR), '::', CAST(team_invites.inviter_uid AS CHAR)
					)
				)
			)
		ELSE NULL
	END AS team_invites,
	CASE
		WHEN users.has_party_invites = 1 THEN
			GROUP_CONCAT(
				DISTINCT(
					CONCAT(
						CAST(party_invites.leader_uid AS CHAR), '::', CAST(party_invites.invite_duration AS CHAR), '::', CAST(party_names.name AS CHAR)
					)
				)
			)
		ELSE NULL
	END AS party_invites_leader_uids FROM users
	LEFT JOIN users_stats AS user_stats ON user_stats.uid = users.uid
	LEFT JOIN friends AS friend_data ON users.has_friends = 1 AND (friend_data.requester_uid = users.uid OR friend_data.requested_uid = users.uid)
	LEFT JOIN users AS friend_names ON users.has_friends = 1 AND friend_names.uid IN (CASE WHEN friend_data.requester_uid = users.uid THEN friend_data.requested_uid ELSE friend_data.requester_uid END)
	LEFT JOIN ignored_users AS ignored ON users.has_ignored = 1 AND users.uid = ignored.uid
	LEFT JOIN teams_players AS team_invites ON users.has_team_invites = 1 AND users.uid = team_invites.invited_uid AND team_invites.status = 0
	LEFT JOIN teams AS team ON users.has_team_invites = 1 AND team_invites.id = team.id
	LEFT JOIN parties_players AS party_invites ON users.has_party_invites = 1 AND users.uid = party_invites.uid AND party_invites.status = 0
	LEFT JOIN users AS party_names ON users.has_party_invites = 1 AND party_invites.leader_uid = party_names.uid
	WHERE users.uid = 49
*/

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetUserDataQuery extends Query{
	private $name;
	public function __construct(BasePlugin $plugin, callable $callback, string $name){
		parent::__construct($plugin, $callback);
		$this->name = $name;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("SELECT *, INET_NTOA(lastip) AS last_ip_string, users_stats.pvp_kills, users_stats.pvp_deaths, users_stats.battle_kills, users_stats.battle_deaths, users_stats.battle_wins, users_stats.battle_losses, users_stats.battle_played, users_stats.battle_time_played, users_stats.server_times_joined, GROUP_CONCACT(ignored_users.ignored_uid) FROM users INNER JOIN users_stats ON users.uid = users_stats.uid AND users.name = '" . $mysql->escape_string($this->name) . "'");
		//$this->setQueryResult(($query instanceof \mysqli_result ? $this->processRow($query->fetch_assoc()) : []));
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_SELECT;
	}
	public function getColumnTypes(): array{
		return [
			'uid' => self::TYPE_INT,
			'name' => self::TYPE_STRING,
			'last_ip' => self::TYPE_STRING,
			'online' => self::TYPE_INT,
			'authenticated' => self::TYPE_INT,
			'uuid' => self::TYPE_STRING,
			'coins' => self::TYPE_FLOAT,
			'hash' => self::TYPE_STRING,
			'team_id' => self::TYPE_INT,
			'party_leader_uid' => self::TYPE_INT,
			'chat_language_code' => self::TYPE_INT,
			'server_language_code' => self::TYPE_INT,
			'registration_time' => self::TYPE_INT,
			'last_on' => self::TYPE_INT,
			'on_time' => self::TYPE_INT,
			'rank' => self::TYPE_INT,
			'purchased_rank' => self::TYPE_INT,
			'purchased_rank_duration' => self::TYPE_INT,
			'warn_points' => self::TYPE_INT,
			'mute_time' => self::TYPE_INT,
			'ban_time' => self::TYPE_INT,
			'pvp_kills' => self::TYPE_INT,
			'pvp_deaths' => self::TYPE_INT,
			'pvp_maxstreak' => self::TYPE_INT,
			'battle_kills' => self::TYPE_INT,
			'battle_deaths' => self::TYPE_INT,
			'battle_losses' => self::TYPE_INT,
			'battle_wins' => self::TYPE_INT,
			'battle_played' => self::TYPE_INT,
			'battle_time_played' => self::TYPE_INT,
			'server_times_joined' => self::TYPE_INT,
			'last_ip_string' => self::TYPE_STRING,
			'has_party_invites' => self::TYPE_INT,
			'has_friends' => self::TYPE_INT,
			'has_team_invites' => self::TYPE_INT,
			'has_admin_logs' => self::TYPE_INT,
			'has_ignored' => self::TYPE_INT
		];
	}
}
