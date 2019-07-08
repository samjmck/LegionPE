<?php

namespace LegionPE\Iota\base;

class Utils{
	/**
	 * @param BaseSession $session
	 * @return int
	 */
	public static function getSessionMaxFriends(BaseSession $session): int{
		if($session->getRank() & Constants::USER_RANK_VIP){
			return $session->getRank() & Constants::USER_RANK_TYPE_PLUS ? Constants::MAX_FRIENDS_VIP_PLUS : Constants::MAX_FRIENDS_VIP;
		}elseif($session->getRank() & Constants::USER_RANK_DONATOR){
			return $session->getRank() & Constants::USER_RANK_TYPE_PLUS ? Constants::MAX_FRIENDS_DONATOR_PLUS : Constants::MAX_FRIENDS_DONATOR;
		}
		return Constants::MAX_FRIENDS_DEFAULT;
	}
	/**
	 * @param BaseSession $session
	 * @return int
	 */
	public static function getSessionMaxTeamPlayers(BaseSession $session): int{
		if($session->getRank() & Constants::USER_RANK_VIP){
			return $session->getRank() & Constants::USER_RANK_TYPE_PLUS ? Constants::MAX_TEAM_PLAYERS_VIP_PLUS : Constants::MAX_TEAM_PLAYERS_VIP;
		}elseif($session->getRank() & Constants::USER_RANK_DONATOR){
			if($session->getRank() & Constants::USER_RANK_TYPE_PLUS){
				return Constants::MAX_TEAM_PLAYERS_DONATOR_PLUS;
			}
		}
		return 0;
	}
	/**
	 * @param BaseSession $session
	 * @return int
	 */
	public static function getSessionMaxPartyPlayers(BaseSession $session): int{
		if($session->getRank() & Constants::USER_RANK_VIP){
			if($session->getRank() & Constants::USER_RANK_TYPE_PLUS){
				return Constants::MAX_PARTY_PLAYERS_VIP_PLUS;
			}else{
				return Constants::MAX_TEAM_PLAYERS_VIP;
			}
		}elseif($session->getRank() & Constants::USER_RANK_DONATOR){
			return $session->getRank() & Constants::USER_RANK_TYPE_PLUS ? Constants::MAX_TEAM_PLAYERS_DONATOR_PLUS : Constants::MAX_PARTY_PLAYERS_DONATOR;
		}
		return 0;
	}
	/**
	 * @return \mysqli
	 */
	public static function getMySQLiConnection(): \mysqli{
		return (new \mysqli(Constants::MYSQL_HOST, Constants::MYSQL_USER, Constants::MYSQL_PASS, Constants::MYSQL_DB, Constants::MYSQL_PORT));
	}
	/**
	 * @param int $seconds
	 * @return string
	 */
	public static function secondsToTime(int $seconds): string{
		$periods = array(
			'day' => 86400,
			'hour' => 3600,
			'minute' => 60,
			'second' => 1
		);
		$parts = array();
		foreach($periods as $name => $dur){
			$div = floor($seconds / $dur);

			if($div == 0){
				continue;
			}else{
				if($div == 1){
					$parts[] = $div . " " . $name;
				}else{
					$parts[] = $div . " " . $name . "s";
				}
				$seconds %= $dur;
			}
		}
		$last = array_pop($parts);
		if(empty($parts)){
			return $last;
		}else{
			return join(', ', $parts) . " and " . $last;
		}
	}
	/**
	 * @param string $ip
	 * @return array
	 */
	public static function getIpData(string $ip){
		$data = json_decode(file_get_contents('http://geoplugin.net/json.gp?ip=' . $ip), true);
		return [
			'countrycode' => $data['geo_plugin_countryCode'],
			'latitude' => $data['geoplugin_latitude'],
			'longitude' => $data['geoplugin_longitude'],
			'currencycode' => $data['geoplugin_currencyCode']
		];
	}
	/**
	 * @param int $constant
	 * @return string
	 */
	public static function languageConstantToString(int $constant): string{
		switch($constant){
			case Constants::LANG_CODE_EN:
				return 'en';
				break;
		}
	}
	/**
	 * @param string $countryCode
	 * @return string
	 */
	public static function getCountryCodeLanguageCode(string $countryCode): string{
		switch($countryCode){
			case "AF": // afghanistan

				break;
			case "AL": // albania

				break;
			case "AS": // american samoa

				break;
			case "AD": // andorra

				break;
			case "AO": // angola

				break;
			case "AQ": // antarctica

				break;
			case "AG": // antigua and barbuda

				break;
			case "AR": // argentina

				break;
			case "AM": // armenia

				break;
			case "AW": // aruba

				break;
			case "AU": // australia

				break;
			case "AT": // austria

				break;
			case "AZ": // azerbaijan

				break;
			case "BS": // bahamas

				break;
			case "BH": // bahrain

				break;
			case "BD": // bangladesh

				break;
			case "BB": // barbados

				break;
			case "BY": // belarus

				break;
			case "BE": // belgium

				break;
			case "BZ": // belize

				break;
			case "BJ": // benin

				break;
			case "BM": // bermuda

				break;
			case "BT": // bhutan

				break;
			case "BO": // bolivia

				break;
			case "BA": // bosnia and herzegovina

				break;
			case "BW": // botswana

				break;
			case "BV": // bouvet island

				break;
			case "BR": // brazil

				break;
			case "IO": // british indian ocean territory

				break;
			case "BN": // brunei darussalam

				break;
			case "BG": // bulgaria

				break;
			case "BF": // burkina faso

				break;
			case "BI": // burundi

				break;
			case "KH": // cambodia

				break;
			case "CM": // cameroon

				break;
			case "CA": // canada

				break;
			case "CV": // cape verde

				break;
			case "KY": // cayman islands

				break;
			case "CF": // central african republic

				break;
			case "TD": // chad

				break;
			case "CL": // chile

				break;
			case "CN": // china

				break;
			case "CX": // christmas island

				break;
			case "CC": // cocos keeling islands

				break;
			case "CO": // colombia

				break;
			case "KM": // comoros

				break;
			case "CG": // congo

				break;
			case "CD": // the democratic republic of congo

				break;
			case "CK": // cook islansd

				break;
			case "CR": // costa rica

				break;
			case "CI": // cote d'ivoire

				break;
			case "HR": // croatia

				break;
			case "CU": // cuba

				break;
			case "CY": // cyprus

				break;
			case "CZ": // czech republic

				break;
			case "DK": // denmark

				break;
			case "DJ": // djibouti

				break;
			case "DM": // dominica

				break;
			case "DO": // dominican republic

				break;
			case "EC": // ecuador

				break;
			case "EG": // egypt

				break;
			case "SV": // el salvador

				break;
			case "GQ": // equatorial guinea

				break;
			case "ER": // eritrea

				break;
			case "EE": // estonia

				break;
			case "ET": // ethiopia

				break;
			case "FK": // falkland islands

				break;
			case "FO": // faroe islands

				break;
			case "FJ": // fiji

				break;
			case "FI": // finland

				break;
			case "FR": // france

				break;
			case "GF": // french guiana

				break;
			case "PF": // french polynesia

				break;
			case "TF": // french southern territories

				break;
			case "GA": // gabon

				break;
			case "GM": // gambia

				break;
			case "GE": // georgia

				break;
			case "DE": // germany

				break;
			case "GH": // ghana

				break;
			case "GI": // gibraltar

				break;
			case "GR": // greece

				break;
			case "GL": // greenland

				break;
			case "GD": // grenada

				break;
			case "GP": // goadeloupe

				break;
		}
	}
}
