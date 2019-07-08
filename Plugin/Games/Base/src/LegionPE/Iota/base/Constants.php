<?php

namespace LegionPE\Iota\base;

class Constants{
	const TCP_AUTH_TOKEN    = "7aa7f59a7933161fcfde942f7fb1a15ba129d91f077f1a3bb204c0389a5e4aba";
	const TCP_SERVER_IP     = '0.0.0.0';
	const TCP_SERVER_PORT   = 1276;


	const PLAYER_MODE_PLAYING       = 0;
	const PLAYER_MODE_SPECTATING    = 1;


	const MAX_FRIENDS_DEFAULT       = 2;
	const MAX_FRIENDS_DONATOR       = 6;
	const MAX_FRIENDS_DONATOR_PLUS  = 10;
	const MAX_FRIENDS_VIP           = 15;
	const MAX_FRIENDS_VIP_PLUS      = 20;

	const MAX_TEAM_PLAYERS_DONATOR_PLUS = 8;
	const MAX_TEAM_PLAYERS_VIP          = 15;
	const MAX_TEAM_PLAYERS_VIP_PLUS     = 20;

	const MAX_PARTY_PLAYERS_DONATOR         = 3;
	const MAX_PARTY_PLAYERS_DONATOR_PLUS    = 3;
	const MAX_PARTY_PLAYERS_VIP             = 6;
	const MAX_PARTY_PLAYERS_VIP_PLUS        = 8;


	const BAN_DURATION_BUG_EXPLOITING   = 5184000;
	const BAN_DURATION_IMPERSONATION    = 2592000;
	const BAN_DURATION_HACKING          = 62208000;

	const MUTE_DURATION_SWEARING    = 604800;
	const MUTE_DURATION_SPAM        = 864000;
	const MUTE_DURATION_ADVERTISING = 2419200;


	const OS_TYPE_UNKNOWN       = 0;
	const OS_TYPE_ANDROID       = 1;
	const OS_TYPE_IOS           = 2;
	const OS_TYPE_MACOS         = 3;
	const OS_TYPE_FIREOS        = 4;
	const OS_TYPE_GEARVR        = 5;
	const OS_TYPE_HOLOLENS      = 6;
	const OS_TYPE_WINDOWS_10    = 7;
	const OS_TYPE_WINDOWS       = 8;
	const OS_TYPE_DEDICATED     = 9;

	public static $osTypes = [
		self::OS_TYPE_UNKNOWN       => "Unknown",
		self::OS_TYPE_ANDROID       => "Android",
		self::OS_TYPE_IOS           => "iOS",
		self::OS_TYPE_MACOS         => "macOS",
		self::OS_TYPE_FIREOS        => "FireOS",
		self::OS_TYPE_GEARVR        => "GearVR",
		self::OS_TYPE_HOLOLENS      => "HoloLens",
		self::OS_TYPE_WINDOWS_10    => "Windows 10",
		self::OS_TYPE_WINDOWS       => "Windows",
		self::OS_TYPE_DEDICATED     => "Dedicated"
	];

	const INPUT_TYPE_UNKNOWN     = 0;
	const INPUT_TYPE_MOUSE       = 1;
	const INPUT_TYPE_TOUCH       = 2;
	const INPUT_TYPE_CONTROLLER  = 3;

	public static $controlTypes = [
		self::INPUT_TYPE_UNKNOWN      => "Unknown",
		self::INPUT_TYPE_MOUSE        => "Mouse",
		self::INPUT_TYPE_TOUCH        => "Touch",
		self::INPUT_TYPE_CONTROLLER   => "Controller"
	];


	const ROUTER_EVENT_TEAM_ADDED                       = "TeamAddedEvent";
	const ROUTER_EVENT_TEAM_REMOVED                     = "TeamRemovedEvent";
	const ROUTER_EVENT_TEAM_CREATE                      = "TeamCreateEvent";
	const ROUTER_EVENT_TEAM_DISBAND                     = "TeamDisbandEvent";
	const ROUTER_EVENT_TEAM_LEAVE                       = "TeamLeaveEvent";
	const ROUTER_EVENT_TEAM_CHAT                        = "TeamChatEvent";
	const ROUTER_EVENT_TEAM_ACCEPT_INVITE               = "TeamAcceptInviteEvent";
	const ROUTER_EVENT_TEAM_DENY_INVITE                 = "TeamDenyInviteEvent";
	const ROUTER_EVENT_TEAM_PROMOTE                     = "TeamPromoteEvent";
	const ROUTER_EVENT_TEAM_DEMOTE                      = "TeamDemoteEvent";
	const ROUTER_EVENT_TEAM_INVITE                      = "TeamInviteEvent";
	const ROUTER_EVENT_TEAM_BROADCAST                   = "TeamBroadcastEvent";
	const ROUTER_EVENT_TEAM_BROADCAST_PRESET_MESSAGE    = "TeamBroadcastPresetMessageEvent";
	const ROUTER_EVENT_TEAM_KICK                        = "TeamKickEvent";

	const ROUTER_EVENT_PARTY_CREATE                     = "PartyCreateEvent";
	const ROUTER_EVENT_PARTY_ACCEPT_INVITE              = "PartyAcceptInviteEvent";
	const ROUTER_EVENT_PARTY_DENY_INVITE                = "PartyDenyInviteEvent";
	const ROUTER_EVENT_PARTY_INVITE                     = "PartyInviteEvent";
	const ROUTER_EVENT_PARTY_KICK                       = "PartyKickEvent";
	const ROUTER_EVENT_PARTY_LEAVE                      = "PartyLeaveEvent";
	const ROUTER_EVENT_PARTY_SUMMON                     = "PartySummonEvent";
	const ROUTER_EVENT_PARTY_BROADCAST                  = "PartyBroadcastEvent";
	const ROUTER_EVENT_PARTY_BROADCAST_PRESET_MESSAGE   = "PartyBroadcastPresetMessageEvent";
	const ROUTER_EVENT_PARTY_USER_MESSAGE               = "PartyUserMessageEvent";
	const ROUTER_EVENT_PARTY_USER_PRESET_MESSAGE        = "PartyUserPresetMessageEvent";
	const ROUTER_EVENT_PARTY_DISBAND                    = "PartyDisbandEvent";

	const ROUTER_EVENT_FRIEND_ACCEPT_REQUEST    = "FriendAcceptRequestEvent";
	const ROUTER_EVENT_FRIEND_DENY_REQUEST      = "FriendDenyRequestEvent";
	const ROUTER_EVENT_FRIEND_REQUEST           = "FriendRequestEvent";
	const ROUTER_EVENT_FRIEND_REMOVE            = "FriendRemoveEvent";
	const ROUTER_EVENT_FRIEND_ONLINE_CHANGE     = "FriendOnlineChangeEvent";

	const ROUTER_EVENT_USER_JOIN            = "UserJoinEvent";
	const ROUTER_EVENT_USER_CREATE          = "UserCreateEvent";
	const ROUTER_EVENT_USER_MESSAGE         = "UserMessageEvent";
	const ROUTER_EVENT_USER_PRESET_MESSAGE  = "UserPresetMessageEvent";

	const ROUTER_EVENT_CHAT_ROOM_BROADCAST                  = "ChatRoomBroadcastEvent";
	const ROUTER_EVENT_CHAT_ROOM_BROADCAST_PRESET_MESSAGE   = "ChatRoomBroadcastPresetMessageEvent";

	const ROUTER_EVENT_ADMIN_LOG_CREATE     = "AdminLogCreateEvent";
	const ROUTER_EVENT_ADMIN_LOG_REMOVE     = "AdminLogRemoveEvent";

	const ROUTER_EVENT_PUNISH   = "PunishEvent";


	const REGEX_NUMBERS_LETTERS_SYMBOLS = '/^[a-zA-Z0-9\+\_\-]+$/';
	const REGEX_NUMBERS_LETTERS = '/^[a-zA-Z0-9]+$/';
	const REGEX_NUMBERS = '/^[0-9]+$/';
	const REGEX_LETTERS = '/^[A-zA-Z]+$/';


	const DEFAULT_VALUE_COINS = 100.00;
	const DEFAULT_VALUE_ON_TIME = 0;
	const DEFAULT_VALUE_CHAT_LANGUAGE_CODE = 0;
	const DEFAULT_VALUE_SERVER_LANGUAGE_CODE = 0;
	const DEFAULT_VALUE_RANK = 0;
	const DEFAULT_VALUE_WARN_POINTS = 0;
	const DEFAULT_VALUE_MUTE_UNTIL = -1;
	const DEFAULT_VALUE_BAN_UNTIL = -1;
	const DEFAULT_VALUE_PVP_KILLS = 0;
	const DEFAULT_VALUE_PVP_DEATHS = 0;
	const DEFAULT_VALUE_PVP_MAXSTREAK = 0;
	const DEFAULT_VALUE_BATTLE_KILLS = 0;
	const DEFAULT_VALUE_BATTLE_DEATHS = 0;
	const DEFAULT_VALUE_BATTLE_LOSSES = 0;
	const DEFAULT_VALUE_BATTLE_WINS = 0;
	const DEFAULT_VALUE_BATTLE_PLAYED = 0;
	const DEFAULT_VALUE_SERVER_TIMES_JOINED = 0;
	const DEFAULT_VALUE_PURCHASED_RANK = -1;
	const DEFAULT_VALUE_PURCHASED_RANK_DURATION = -1;
	const DEFAULT_VALUE_TEAM_ID = -1;
	const DEFAULT_VALUE_BATTLE_TIME_PLAYED = 0;
	const DEFAULT_VALUE_PARTY_LEADER_UID = -1;
	const DEFAULT_VALUE_HAS_PARTY_INVITES = 0;
	const DEFAULT_VALUE_HAS_FRIENDS = 0;
	const DEFAULT_VALUE_HAS_TEAM_INVITES = 0;
	const DEFAULT_VALUE_HAS_ADMIN_LOGS = 0;
	const DEFAULT_VALUE_HAS_IGNORED = 0;
	const DEFAULT_VALUE_EMAIL = null;
	const DEFAULT_VALUE_HASH = null;


	const PARTY_STATUS_REQUESTED = 0;
	const PARTY_STATUS_ACCEPTED = 1;


	const STATS_BATTLE_PLAYED = 'battle_played';
	const STATS_BATTLE_TIME_PLAYED = 'battle_time_played';
	const STATS_BATTLE_KILLS = 'battle_kills';
	const STATS_BATTLE_DEATHS = 'battle_deaths';
	const STATS_BATTLE_WINS = 'battle_wins';
	const STATS_BATTLE_LOSSES = 'battle_losses';
	const STATS_PVP_KILLS = 'pvp_kills';
	const STATS_PVP_DEATHS = 'pvp_deaths';
	const STATS_PVP_MAX_STREAK = 'pvp_max_streak';
	const STATS_SERVER_TIMES_JOINED = 'server_times_joined';


	const MAX_PASSWORD_ATTEMPTS = 3;

	const MAX_PLAYERS = 60;
	const MAX_PRIORITY_PLAYERS = 15;

	const MAX_IGNORED_PLAYERS = 20;


	const NICK_ERROR_TAKEN          = 0;
	const NICK_ERROR_CURRENT_NICK   = 1;


	const NICK_STATUS_REQUESTED = 0;
	const NICK_STATUS_ACCEPTED  = 1;
	const NICK_STATUS_DENIED    = 2;


	const FRIEND_STATUS_REQUESTED   = 0;
	const FRIEND_STATUS_ACCEPTED    = 1;
	const FRIEND_STATUS_DENIED      = 2;
	const FRIEND_STATUS_REMOVED     = 3;


	const ADMIN_LOG_TYPE_WARNING    = 0;
	const ADMIN_LOG_TYPE_BAN        = 1;
	const ADMIN_LOG_TYPE_MUTE       = 2;


	const USER_RANK_DEFAULT      = 0;
	const USER_RANK_DONATOR      = 1;
	const USER_RANK_VIP          = 2;
	const USER_RANK_MOD          = 4;
	const USER_RANK_ADMIN        = 8;
	const USER_RANK_OWNER        = 16;
	const USER_RANK_DEVELOPER    = 32;
	const USER_RANK_BUILDER      = 64;
	const USER_RANK_TYPE_PLUS    = 8192;
	const USER_RANK_TYPE_TRIAL   = 16384;
	const USER_RANK_TYPE_HEAD    = 32768;
	const USER_RANK_TYPE_CO      = 65536;


	const TEAM_STATUS_REQUESTED = 0;
	const TEAM_STATUS_ACCEPTED  = 1;
	const TEAM_STATUS_DENIED    = 2;


	const TEAM_RANK_MEMBER      = 0;
	const TEAM_RANK_MOD         = 8;
	const TEAM_RANK_ADMIN       = 16;
	const TEAM_RANK_OWNER       = 32;
	const TEAM_RANK_TYPE_PLUS   = 8192;
	const TEAM_RANK_TYPE_TRIAL  = 16384;
	const TEAM_RANK_TYPE_HEAD   = 32768;
	const TEAM_RANK_TYPE_CO     = 65536;


	const ERROR_CODE_TEAM_CREATE_CREATOR_IN_TEAM             = 0;
	const ERROR_CODE_TEAM_CREATE_NAME_TOO_SHORT              = 1;
	const ERROR_CODE_TEAM_CREATE_NAME_TOO_LONG               = 2;
	const ERROR_CODE_TEAM_CREATE_NAME_DISALLOWED_CHARACTERS  = 3;
	const ERROR_CODE_TEAM_CREATE_ACRONYM_WRONG_LENGTH        = 4;
	const ERROR_CODE_TEAM_CREATE_ACRONYM_NO_SYMBOLS          = 5;

	const ERROR_CODE_PARTY_CREATE_IN_PARTY  = 0;

	const ERROR_CODE_CHAT_ROOM_CREATE_KEY_TOO_LONG  = 0;


	const LANG_CODE_EN = 0;

	public static $languageCodes = [
		self::LANG_CODE_EN => 'en'
	];


	const MYSQL_HOST    = '127.0.0.1';
	const MYSQL_USER    = 'root';
	const MYSQL_PASS    = 'testing12345';
	const MYSQL_PORT    = 3306;
	const MYSQL_DB      = 'LegionPE';

	const CHAT_ROOM_KEY_MAX_LENGTH = 128;
}
