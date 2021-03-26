<?php
// =======================================================================
// Author: https://github.com/DOTzX
// Source: https://github.com/DOTzX/PHP-Genshin-WishPityCounter
// =======================================================================

date_default_timezone_set("Asia/Jakarta");
include "lib/class.DOTzX.HTMLTableGen.php"; // Find new update here: https://gist.github.com/DOTzX/3ecab71817e8461b308a1addf06eec03
include "lib/class.DOTzX.SimpleFileOpener.php"; // Find new update here: https://gist.github.com/DOTzX/26afe5ab070acf09e4f055db37a0ad97
include "lib/general.php";
include "lib/game.php";

// =======================================================================

if ( isset($_GET["read_log"]) || isset($_GET["log_id"]) ) {
	if (!file_exists("GI_WishData.json")) die("Tidak ada data tersimpan");
	readLog(isset($_GET["log_id"]) ? $_GET["log_id"] : null);
} else if (isset($_GET["url"])) {
	$url = base64_decode($_GET["url"]);

	if (!str_starts_with($url, "https://webstatic-sea.mihoyo.com/") && !str_starts_with($url, "https://genshin.mihoyo.com/")) {
		die("Invalid url");
	}

	proceed($url);

	if (file_exists("GI_WishData.json")) {
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

if (file_exists("GI_WishData.json")) {
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
function handleFileSelect(evt) {
	evt.stopPropagation();
	evt.preventDefault();

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
}
