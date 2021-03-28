<?php
// =======================================================================
// Author: https://github.com/DOTzX
// Source: https://github.com/DOTzX/PHP-Genshin-WishPityCounter
// =======================================================================

date_default_timezone_set(TIMEZONE_NAME);

$link_wishlist = "https://hk4e-api-os.mihoyo.com/event/gacha_info/api/getConfigList";
$link_gachalog = "https://hk4e-api-os.mihoyo.com/event/gacha_info/api/getGachaLog";
$new_query = "";

function getWishHistory($gachaType=301, $end_id=null, $size=20) {
	global $link_gachalog, $new_query;
	$_nq = [
		"size" => $size,
		"gacha_type" => $gachaType,
	];
	if ($end_id) $_nq["end_id"] = $end_id;
	$_nq = http_build_query($_nq);
	return $link_gachalog . "?" . $new_query . "&" . $_nq;
}

function getWishList() {
	global $link_wishlist, $new_query;
	return $link_wishlist . "?" . $new_query;
}

function proceed($url) {
	global $new_query;

	$query_arr = [];
	parse_str( parse_url($url, PHP_URL_QUERY), $query_arr );

	$new_query = http_build_query([
		"lang" => "en",
		"authkey_ver" => arr_get($query_arr, "authkey_ver", 1),
		"region" => arr_get($query_arr, "region", "os_asia"),
		"game_biz" => arr_get($query_arr, "game_biz", "hk4e_global"),
		"authkey" => arr_get($query_arr, "authkey", ""),
	]);

	$sfo = new SimpleFileOpener("../data/GI_WishData.json");
	$GI_WishData = json_decode($sfo->read(), true);
	$GI_WishData = $GI_WishData ? $GI_WishData : [];

	$player_data = [];
	$isChange = false;

	disable_ob();

	echo "\n<br>[] Accessing getWishList";
	$wish_list = json_decode(http_request(getWishList()), true);
	echo "\n<br>[] ". json_encode($wish_list);

	$uid = null;
	foreach ($wish_list["data"]["gacha_type_list"] as $banner_data) {
		echo "\n<br>[] Getting UID... Banner_Key: " . $banner_data["key"];
		$last_wish_history = json_decode(http_request(getWishHistory($banner_data["key"], null, 1)), true);
		if (isset($last_wish_history["data"]["list"]) && count($last_wish_history["data"]["list"]) > 0) {
			$uid = $last_wish_history["data"]["list"][0]["uid"];
			break;
		}
	}
	echo "\n<br>[] UID: $uid";

	foreach ($wish_list["data"]["gacha_type_list"] as $banner_data) {
		if ($banner_data["key"] == "100") continue;
		
		if (!isset($GI_WishData["BANNER_DATA"])) $GI_WishData["BANNER_DATA"] = [];
		$GI_WishData["BANNER_DATA"][$banner_data["key"]] = $banner_data["name"];

		$isPage = 1;
		$last_id = null;
		if ($uid && isset($GI_WishData[$uid][$banner_data["key"]])) {
			$last_id = $GI_WishData[$uid][$banner_data["key"]][0]["id"];
		}
		echo "\n<br><br>[] Banner_Key: ". $banner_data["key"] ." , Last ID: $last_id";

		$isNextPage = true;
		while ($isNextPage) {
			echo "\n<br>[] Accessing: (". $banner_data["key"] . "," . $last_id . ") | " . $isPage;
			$wish_history = json_decode(http_request(getWishHistory($banner_data["key"], $last_id)), true);

			if (!isset($wish_history["data"]["list"]) || count($wish_history["data"]["list"]) == 0) {
				echo "\n<br>[] No_Data: (". $banner_data["key"] . "," . $last_id . ") | " . $isPage;
				$isNextPage = false;
				break;
			}
			
			if (isset($wish_history["data"]["list"])) {
				$last_id = $wish_history["data"]["list"][count($wish_history["data"]["list"])-1]["id"];

				foreach ($wish_history["data"]["list"] as $wish_data) {
					$_uid = (string) $wish_data["uid"];
					$_gacha_type = (string) $wish_data["gacha_type"];
	
					if (!isset($player_data[$_uid])) $player_data[$_uid] = [];
					if (!isset($player_data[$_uid][$_gacha_type])) $player_data[$_uid][$_gacha_type] = [];
	
					$single_wd = [
						"id" => $wish_data["id"],
						"time" => $wish_data["time"],
						"name" => $wish_data["name"],
						"item_type" => $wish_data["item_type"],
						"rank_type" => (int) $wish_data["rank_type"],
					];
	
					if ( in_array($single_wd, $player_data[$_uid][$_gacha_type]) || 
						(
							isset($GI_WishData[$_uid][$_gacha_type]) && 
							in_array($single_wd, $GI_WishData[$_uid][$_gacha_type])
						) ) {
						echo "\n<br>[] Fetched_Data: (". $banner_data["key"] . "," . $last_id . ") | " . $isPage;
						$isChange = true;
						$isNextPage = false;
						break;
					}
	
					if ($isNextPage) {
						array_push($player_data[$_uid][$_gacha_type], $single_wd);
						$isChange = true;
					}
				}
			}

			$isPage++;
		}
	}

	if ($isChange) {
		foreach ($player_data as $uid => $gacha_type_wddata) {
			if (!isset($GI_WishData[$uid])) $GI_WishData[$uid] = [];

			foreach ($gacha_type_wddata as $gacha_type => $wddata) {
				$GI_WishData[$uid][$gacha_type] = array_merge( $wddata, arr_get($GI_WishData[$uid], $gacha_type, []) );
			}

			$GI_WishData[$uid]["LAST_UPDATE"] = (int) time();
		}

		echo "\n<br>[] Done";
		$sfo->write(json_encode($GI_WishData, 128));
	}
}

function readLog($selected_uid=null) {
	$sfo = new SimpleFileOpener("../data/GI_WishData.json");
	$GI_WishData = json_decode($sfo->read(), true);
	$GI_WishData = $GI_WishData ? $GI_WishData : [];

	if (!$selected_uid) {
		$list_uid = array_keys($GI_WishData);
		sort($list_uid);
		$key = array_search("BANNER_DATA", $list_uid);
		if ($key !== false) unset($list_uid[$key]);
	
		if (count($list_uid) == 1) {
			header("Location: ". INDEX_NAME ."?log_id=" . $list_uid[0]);
		} else {
			echo "Pilih UID:";
			foreach ($list_uid as $value) {
				echo "\n<br>[] <a href='". INDEX_NAME ."?log_id=" . $value . "'>" . $value . "</a>";
			}
		}
		die();
	}

	if (!array_key_exists($selected_uid, $GI_WishData) || !is_numeric($selected_uid)) die("Tidak ada data tersimpan pada UID <b>$selected_uid</b>");

	$utctime = date("Y-m-d H:i:s", $GI_WishData[$selected_uid]["LAST_UPDATE"]);
	echo "<a href='". INDEX_NAME ."'>< Back</a> | Last Update: <b>" . $utctime . " (Timezone: ". TIMEZONE_NAME .")</b><br><br>\n\n";

	foreach ($GI_WishData[$selected_uid] as $banner_key => $wishlist) {
		if (in_array($banner_key, ["LAST_UPDATE"])) continue;
		if (count($wishlist) == 0) continue;

		$b5charcounter = 0;
		$b5charlist = [];

		krsort($wishlist);

		foreach ($wishlist as $wish) {
			$b5charcounter++;

			if ($wish["rank_type"] == 5) {
				array_push($b5charlist, [$wish["time"], $b5charcounter, $wish["name"]]);
				$b5charcounter = 0;
			}
		}

		if ($b5charcounter) array_push($b5charlist, [$wish["time"], $b5charcounter, "(Last Wish Pity)"]);

		krsort($b5charlist);

		$tableGen = new HTMLTableGen();
		$tableGen->tableStyle = [
			"width" => "100%",
			"text-align" => "center",
		];
		$tableGen->tableAttr = [
			"border" => "1",
		];
		$tableGen->th = [
			$tableGen->_arrayCreate("text", "Waktu (GMT+8)"),
			$tableGen->_arrayCreate("text", "Pity"),
			$tableGen->_arrayCreate("text", "*5"),
		];
		foreach ($b5charlist as $b5char) {
			$tableGen->append($tableGen->_arrayCreate("data", [
				$tableGen->_arrayCreate("text", $b5char[0]),
				$tableGen->_arrayCreate("text", $b5char[1]),
				$tableGen->_arrayCreate("text", $b5char[2]),
			]));
		}
		echo "<center><h2>". arr_get($GI_WishData["BANNER_DATA"], $banner_key, "-- No Table Name --") ."</h2></center>";
		echo $tableGen->build();
	}
}
