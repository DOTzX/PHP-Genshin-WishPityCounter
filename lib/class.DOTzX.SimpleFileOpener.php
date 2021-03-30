<?php
// =======================================================================
// Author: https://gist.github.com/DOTzX/
// Source: https://gist.github.com/DOTzX/26afe5ab070acf09e4f055db37a0ad97
// =======================================================================

if (!defined("WORKING_DIR")) define("WORKING_DIR", __DIR__);

class SimpleFileOpener {
	function __construct($file_name, $edit_mode=["r", "w"]) {
		$this->filename = WORKING_DIR . "/" . $file_name;
		$this->edit_mode = $edit_mode;
		if (!file_exists(dirname($this->filename))) {
			mkdir(dirname($this->filename), 0777, true);
		} else {
			chmod(dirname($this->filename), 0777);
		}
		if (file_exists($this->filename)) {
			chmod($this->filename, 0777);
		}
	}

	function read() {
		$content = "";
		if (file_exists($this->filename) && filesize($this->filename)) {
			$handle = fopen($this->filename, $this->edit_mode[0]);
			$content = fread($handle, filesize($this->filename));
			fclose($handle);
		}
		return $content;
	}

	function write($content) {
		$handle = fopen($this->filename, $this->edit_mode[1]);
		$status = fwrite($handle, $content);
		fclose($handle);
		return $status;
	}
}

// usage:
// $file_path = "data.json";
// $sfo = new SimpleFileOpener($file_path);
// $data = json_decode($sfo->read(), true);
// $data = $data ? $data : [];
//
// array_push($data, "test");
//
// $sfo->write(json_encode($data));