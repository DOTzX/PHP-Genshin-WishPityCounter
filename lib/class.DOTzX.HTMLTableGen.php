<?php
// =======================================================================
// Author: https://gist.github.com/DOTzX/
// Source: https://gist.github.com/DOTzX/3ecab71817e8461b308a1addf06eec03
// =======================================================================

class HTMLTableGen {
	/*
		$th = array(
			array(
				"class" => array(""),
				"style" => array("" => ""),
				"attr" => array("" => ""),
				"text" => ""
			)
		);
		$tableClass = array("");
		$tableStyle = array("" => "");
		$tableAttr = array("" => "");
	*/
	function __construct($th = array(), $tableClass = array(), $tableStyle = array(), $tableAttr = array()) {
		$this->tableClass = $tableClass;
		$this->tableStyle = $tableStyle;
		$this->tableAttr = $tableAttr;
		$this->th = $th;
		$this->data = array();
	}

	/*
		$arr = array(
			"class" => array(""),
			"style" => array("" => ""),
			"attr" => array("" => ""),
			"data" => array(
				array(
					"class" => array(""),
					"style" => array("" => ""),
					"attr" => array("" => ""),
					"text" => ""
				)
			)
		);
	*/
	function append($arr) {
		array_push($this->data, $arr);
	}

	function build() {
		$txt = $this->_tagCreate("table", $this->tableClass, $this->tableStyle, $this->tableAttr);
		$txt .= "\n\t<thead>\n\t\t<tr>\n";
		foreach($this->th as $headRow) $txt .= "\t\t\t" . $this->_tagCreate("th", $headRow["class"], $headRow["style"], $headRow["attr"]) . $headRow["text"] . "</th>\n";
		$txt = trim($txt, "\n");
		$txt .= "\n\t\t</tr>\n\t</thead>\n\t<tbody>\n";
		foreach($this->data as $dataRow) {
			$txt .= "\t\t" . $this->_tagCreate("tr", $dataRow["class"], $dataRow["style"], $dataRow["attr"]) . "\n";
			foreach($dataRow["data"] as $data) $txt .= "\t\t\t" . $this->_tagCreate("td", $data["class"], $data["style"], $data["attr"]) . $data["text"] . "</td>\n";
			$txt .= "\t\t</tr>\n";
		}
		$txt .= "\t</tbody>\n</table>\n";
		return $txt;
	}

	/*
		$classList = array("");
		$styleDict = array("" => "");
		$attrDict = array("" => "");
	*/
	function _arrayCreate($name, $data, $classList = array(), $styleDict = array(), $attrDict = array()) {
		return array(
			"class" => $classList,
			"style" => $styleDict,
			"attr" => $attrDict,
			$name => $data
		);
	}

	/*
		$classList = array("");
		$styleDict = array("" => "");
		$attrDict = array("" => "");
	*/
	function _tagCreate($tagText, $classList = array(), $styleDict = array(), $attrDict = array()) {
		$txt = "<" . $tagText;
		if (count($classList) != 0) $txt .= ' class="' . join(" ", $classList) . '"';
		if (count($attrDict) != 0) {
			$sText = "";
			foreach ($attrDict as $key => $value) $sText .= ' '.$key.'="'.$value.'"';
			$txt .= $sText;
		}
		if (count($styleDict) != 0) {
			$sText = "";
			foreach ($styleDict as $key => $value) $sText .= "$key:$value; ";
			$txt .= ' style="' . trim($sText) . '"';
		}
		return $txt . ">";
	}
}

// usage:
// $tableGen = new HTMLTableGen();
// $tableGen->tableAttr = [
//     "border" => "1",
// ];
// $tableGen->th = [
//     $tableGen->_arrayCreate("text", "Column A"),
//     $tableGen->_arrayCreate("text", "Column B"),
//     $tableGen->_arrayCreate("text", "Column C"),
// ];
// $tableGen->append($tableGen->_arrayCreate("data", [
//     $tableGen->_arrayCreate("text", "1st row col A"),
//     $tableGen->_arrayCreate("text", "1st row col B"),
//     $tableGen->_arrayCreate("text", "1st row col C"),
// ]));
// $tableGen->append($tableGen->_arrayCreate("data", [
//     $tableGen->_arrayCreate("text", "2nd row col A"),
//     $tableGen->_arrayCreate("text", "2nd row col B"),
//     $tableGen->_arrayCreate("text", "2nd row col C"),
// ]));
// echo $tableGen->build();