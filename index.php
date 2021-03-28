<?php
// =======================================================================
// Author: https://github.com/DOTzX
// Source: https://github.com/DOTzX/PHP-Genshin-WishPityCounter
// =======================================================================

define("INDEX_NAME", basename(__FILE__)); // DON'T CHANGE THIS LINE !
define("TIMEZONE_NAME", "Asia/Jakarta");

include "lib/class.DOTzX.HTMLTableGen.php"; // Find new update here: https://gist.github.com/DOTzX/3ecab71817e8461b308a1addf06eec03
include "lib/class.DOTzX.SimpleFileOpener.php"; // Find new update here: https://gist.github.com/DOTzX/26afe5ab070acf09e4f055db37a0ad97
include "lib/general.php";
include "lib/game.php";

// =======================================================================

if (!file_exists("data/GI_WishData.json") && file_exists("GI_WishData.json")) {
	if (!file_exists("data/")) mkdir("data/");
	rename("GI_WishData.json", "data/GI_WishData.json");
}

// =======================================================================

if ( isset($_GET["read_log"]) || isset($_GET["log_id"]) ) {
	if (!file_exists("data/GI_WishData.json")) die("Tidak ada data tersimpan");
	readLog(isset($_GET["log_id"]) ? $_GET["log_id"] : null);
} else if (isset($_GET["url"])) {
	$url = base64_decode($_GET["url"]);

	if (!str_starts_with($url, "https://webstatic-sea.mihoyo.com/") && !str_starts_with($url, "https://genshin.mihoyo.com/")) {
		die("Invalid url");
	}
	if (!str_contains($url, "authkey=")) die("No authkey");

	proceed($url);

	if (file_exists("data/GI_WishData.json")) {
?>

<!-- oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo -->
<br><br>
<button onclick="readLog()">Baca data yang telah dibuat</button>
<!-- oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo -->

<?php
	}
?>

<!-- oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo -->
<script type="text/javascript">
alert("Done");

function readLog() {
	window.location = "<?=basename(__FILE__)?>?read_log";
}
</script>
<!-- oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo -->

<?php
// ==============================================================================
} else {
// ==============================================================================

	disable_ob();

	if (file_exists("data/GI_WishData.json")) {
?>

<!-- oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo -->
<button onclick="readLog()">Baca data yang telah ada</button>
<br><br>
<div>Perbarui data ? Ikuti cara berikut:</div>
<!-- oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo -->

<?php
	}
?>

<!-- oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo -->
<div>1. Buka menu <b>Wish</b></div>
<div>2. Klik <b>History</b></div>
<div>3. Buka <b>Command Prompt (CMD)</b> pada windows anda</div>
<div>4. Copy paste ke CMD: <b>explorer "AppData\LocalLow\miHoYo\Genshin Impact"</b></div>
<div>5. Drag-n-drop file <b>output_log.txt</b> pada folder tersebut ke halaman ini</div>

<script type="text/javascript">
var isAllow = true;

function handleFileSelect(evt) {
	evt.stopPropagation();
	evt.preventDefault();

	if (!isAllow) return alert("Harap gunakan update-an terbaru !");
	
	var files = evt.dataTransfer.files;
	var reader = new FileReader();  
	reader.onload = function(event) {
		var mystr = event.target.result;

		var res = [...mystr.matchAll(/OnGetWebViewPageFinish:(http.*mihoyo.*authkey.*#)/g)];

		if (res.length) {
			if (confirm("Klik OK utk memulai")) {
				window.location = "<?=basename(__FILE__)?>?url=" + btoa(res[res.length-1][1]);
			}
		}
		else alert("Bukan file log GI");
	}
	reader.readAsText(files[0], "UTF-8");
}

function handleDragOver(evt) {
	evt.stopPropagation();
	evt.preventDefault();
	evt.dataTransfer.dropEffect = 'copy';
}

function readLog() {
	window.location = "<?=basename(__FILE__)?>?read_log";
}

document.body.addEventListener('dragover', handleDragOver, false);
document.body.addEventListener('drop', handleFileSelect, false);
</script>
<!-- oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo -->

<?php
	$fcontent = "";
	if (file_exists("REPOSITORY_LAST_UPDATE")) {
		$sfo = new SimpleFileOpener("REPOSITORY_LAST_UPDATE", ["r", "w"], false);
		$fcontent = $sfo->read();
	}
	$rlu_check = http_request("https://raw.githubusercontent.com/DOTzX/PHP-Genshin-WishPityCounter/master/REPOSITORY_LAST_UPDATE", 5);
	if ($rlu_check) {
		$new_update = false;
		if ($rlu_check == "404: Not Found") {
			$new_update = "https://github.com/DOTzX/PHP-Genshin-WishPityCounter";
		} else if ($fcontent != $rlu_check) {
			$_exp = explode("|", $rlu_check, 2);
			if (count($_exp) == 2) {
				$new_update = $_exp[1];
			} else {
				$new_update = "https://github.com/DOTzX/PHP-Genshin-WishPityCounter";
			}
		}
		if ($new_update) {
?>

<!-- oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo -->
<h1>New Update <a href='<?=$new_update?>'>here</a></h1>
<script type="text/javascript">
var isAllow = false;
</script>
<!-- oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo -->

<?php
		}
	}
}
