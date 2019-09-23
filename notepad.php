<?php

/******************************************************************************
 * Notepad                                                                    *
 *                                                                            *
 * Copyright (C) 2006 J.C. Fields (jcfields@jcfields.dev).                    *
 *                                                                            *
 * Permission is hereby granted, free of charge, to any person obtaining a    *
 * copy of this software and associated documentation files (the "Software"), *
 * to deal in the Software without restriction, including without limitation  *
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,   *
 * and/or sell copies of the Software, and to permit persons to whom the      *
 * Software is furnished to do so, subject to the following conditions:       *
 *                                                                            *
 * The above copyright notice and this permission notice shall be included in *
 * all copies or substantial portions of the Software.                        *
 *                                                                            *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR *
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,   *
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL    *
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER *
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING    *
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER        *
 * DEALINGS IN THE SOFTWARE.                                                  *
 ******************************************************************************/

/*
 * configuration
 */

// name of log file ('' to disable logging)
const LOG_NAME = 'login';
// maximum length of log (0 to disable log cropping)
const LOG_LINES = 100;

// directory containing log files
const LOG_DIR = 'logs';
// extension for log files
const LOG_EXT = '.log';

// defines supported character encodings
const ENCODINGS = [
	'7bit',	        '8bit',        'ASCII',
	'BASE64',       'BIG-5',       'byte2be',
	'byte2le',      'byte4be',     'byte4le',
	'CP866',        'CP936',       'CP950',
	'EUC-CN',       'EUC-JP',      'EUC-KR',
	'EUC-TW',       'eucJP-win',   'HTML-ENTITIES',
	'HZ',           'ISO-2022-JP', 'ISO-2022-KR',
	'ISO-8859-1',   'ISO-8859-2',  'ISO-8859-3',
	'ISO-8859-4',   'ISO-8859-5',  'ISO-8859-6',
	'ISO-8859-7',   'ISO-8859-8',  'ISO-8859-9',
	'ISO-8859-10',  'ISO-8859-13', 'ISO-8859-14',
	'ISO-8859-15',  'JIS',         'KOI8-R',
	'SJIS-win',     'SJIS',        'UCS-2',
	'UCS-2BE',      'UCS-2LE',     'UCS-4',
	'UCS-4BE',      'UCS-4LE',     'UHC',
	'UTF-7',        'UTF-8',       'UTF-16',
	'UTF-16BE',     'UTF-16LE',    'UTF-32',
	'UTF-32BE',     'UTF-32LE',    'UTF7-IMAP',
	'Windows-1251', 'Windows-1252'
];
const DEFAULT_ENCODING = 'UTF-8';

/*
 * gets editor
 */

$notepad = new Notepad();
$notepad->open();
$form = $notepad->getForm();

/*
 * prints everything
 */

echo <<<"HTML"
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en"><head><title>Notepad</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="mailto:jcfields@jcfields.dev" rel="author" title="J.C. Fields">
<style type="text/css">
body {
	background: #6aa;
	margin: 0;
	padding: 10px;
}

body, button {
	color: #fff;
}

body, input#file, textarea {
	border: 2px dotted #9cc;
}

button {
	background: #099;
	border: 1px outset #0cc;
	height: 2em;
}

button, html, input, select {
	font-family: Verdana, Helvetica, sans-serif;
}

button, html, input, select, textarea {
	font-size: 10pt;
}

button, label {
	font-weight: bold;
}

button:active {
	border-style: inset;
}

button:hover {
	background: #0aa;
	border-color: #0ff;
	cursor: pointer;
}

html {
	padding: 10px 10%;
}

html, input, select, textarea {
	background: #fff;
	color: #000;
	line-height: 150%;
}

input, select {
	border: 1px solid #000;
}

input#file, textarea {
	width: 100%;
}

span.hotkey {
	text-decoration: underline;
}

textarea {
	font-family: "Lucida Console", Monaco, monospace;
	height: 45em;
}

ul {
	list-style: none;
	margin: 0;
	padding: 0;
}</style></head>
<body><form action="{$_SERVER['SCRIPT_NAME']}" method="post" enctype="application/x-www-form-urlencoded">
<p><label for="file"><span class="hotkey">F</span>ile or directory:</label></p>
<p><input type="text" value="{$form['file']}" accesskey="f" spellcheck="false" name="file" id="file"></p>
<p><button type="submit" value="open" accesskey="o" name="open"><span class="hotkey">O</span>pen</button></p>
<p><label for="buffer"><span class="hotkey">C</span>urrent document:</label></p>
<p><textarea rows="30" cols="80" accesskey="c" name="buffer" id="buffer">{$form['buffer']}</textarea></p>
<p>{$form['encodeSel']}</p>
<p>{$form['breakCheck']}</p>
<p>{$form['unprintCheck']}</p>
<p><button type="submit" value="save" accesskey="s" name="save"><span class="hotkey">S</span>ave</button></p></form></body></html>
HTML;

/*
 * Notepad class
 */
class Notepad {
	private $file, $buffer, $mode, $encode, $breaks, $unprint;

	/*
	 * constructor
	 */
	public function __construct() {
		$this->file   = $_POST['file']   ?? '';
		$this->buffer = $_POST['buffer'] ?? '';
		$this->mode   = $_POST['save']   ?? false;

		$this->encode  = $_POST['encode']  ?? DEFAULT_ENCODING;
		$this->breaks  = $_POST['breaks']  ?? true;
		$this->unprint = $_POST['unprint'] ?? true;
	}

	/*
	 * opens or saves file given form data
	 */
	public function open() {
		if ($_POST) {
			$file = new File($this->file);

			if (empty($this->mode)) {
				$buffer = $file->read(
					$this->encode,
					$this->breaks,
					$this->unprint
				);
			} else {
				$buffer = $file->save(
					$this->buffer,
					$this->encode,
					$this->breaks,
					$this->unprint
				);
			}
		} else {
			// returns directory listing for working dir if no file specified
			$this->file = self::pwd();
			$buffer = self::ls($this->file);
		}

		$this->buffer = $buffer;
	}

	/*
	 * formats form with correct values
	 */
	public function getForm() {
		$file = htmlspecialchars($this->file, ENT_QUOTES);
		$buffer = htmlspecialchars($this->buffer);

		$encodeSel = self::formSelect(
			ENCODINGS,
			'encode',
			'&Encoding:',
			'i',
			$this->encode
		);
		$breakCheck = self::formCheckbox(
			'breaks',
			'Convert Windows &line breaks',
			'l',
			$this->breaks
		);
		$unprintCheck = self::formCheckbox(
			'unprint',
			'Convert &unprintable characters.',
			'u',
			$this->unprint
		);

		return [
			'file'         => $file,
			'buffer'       => $buffer,
			'encodeSel'    => $encodeSel,
			'breakCheck'   => $breakCheck,
			'unprintCheck' => $unprintCheck
		];
	}

	/*
	 * formats select field
	 */
	private static function formSelect($options, $name, $label, $accesskey, $selected) {
		$html = '<label>' . self::formatLabel($label) . '&nbsp;';
		$html .= "<select accesskey=\"$accesskey\" name=\"$name\" id=\"$name\">";

		foreach ($options as $option) {
			if ($option === $selected) {
				$html .= "<option selected=\"selected\">$option</option>";
			} else {
				$html .= "<option>$option</option>";
			}
		}

		$html .= '</select></label>';

		return $html;
	}

	/*
	 * formats checkbox
	 */
	private static function formCheckbox($name, $label, $accesskey, $value) {
		$checked = $value ? ' checked="checked"' : '';
		$html = "<label><input type=\"checkbox\" value=\"$name\"$checked accesskey=\"$accesskey\" name=\"$name\" id=\"$name\">";
		$html .= '&nbsp;' . self::formatLabel($label) . '</label>';

		return $html;
	}

	/*
	 * formats input label
	 */
	private static function formatLabel($text) {
		return preg_replace('/&(\w)/', '<span class="hotkey">$1</span>', $text);
	}

	/*
	 * return current working directory
	 */
	private static function pwd() {
		return rtrim(shell_exec(PHP_OS === 'WINNT' ? 'cd' : 'pwd'));
	}

	/*
	 * return directory listing
	 */
	private static function ls($dir) {
		$dir = escapeshellarg($dir);
		return shell_exec(PHP_OS === 'WINNT' ? "dir $dir" : "ls -Alp $dir");
	}
}

/*
 * File class
 */
class File {
	private $file;

	/*
	 * constructor
	 */
	public function __construct($file) {
		$this->file = $file;
	}

	/*
	 * reads requested file or directory
	 */
	public function read($encode, $breaks, $unprint) {
		if (is_dir($this->file)) {
			$buffer = self::ls($this->file);
		} elseif (is_file($this->file)) {
			$buffer = file_get_contents($this->file);
		} else {
			$buffer = 'Read error: Invalid file.';
		}

		if ($breaks) {
			$buffer = self::win2unix($buffer);
		}

		if ($encode) {
			$buffer = mb_convert_encoding($buffer, DEFAULT_ENCODING, $encode);
		}

		if ($unprint) {
			$buffer = self::unprintable($buffer);
		}

		return $buffer;
	}

	/*
	 * saves submitted text
	 */
	public function save($buffer, $encode, $breaks, $unprint) {
		if ($breaks) {
			$buffer = self::win2unix($buffer);
		}

		if ($encode) {
			$buffer = mb_convert_encoding($buffer, $encode, DEFAULT_ENCODING);
		}

		if ($unprint) {
			$buffer = self::unprintable($buffer);
		}

		$log = new Log(LOG_NAME, LOG_LINES);

		if ($log->writeLog($this->file)) {
			if (file_put_contents($this->file, $buffer) !== false) {
				$buffer = $this->read();
			} else {
				$buffer = "Write error: Could not open \"{$this->file}\".";
			}
		} else {
			$buffer = sprintf(
				'Write error: Could not write to log file "%s".',
				LOG_NAME
			);
		}

		return $buffer;
	}

	/*
	 * converts Windows line breaks to Unix
	 */
	private static function win2unix($text) {
		return str_replace("\r\n", "\n", $text);
	}

	/*
	 * strips unprintable characters out of a string
	 */
	private static function unprintable($text) {
		// leaves \n, \t, and \r alone
		return preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/', '?', $text);
	}
}

/*
 * Log class
 */
class Log {
	private $logFile, $logName, $logLines;

	/*
	 * constructor
	 */
	public function __construct($logName, $logLines) {
		$logDir = __DIR__ . '/' . LOG_DIR;

		if (!file_exists($logDir)) {
			mkdir($logDir);
		}

		$this->logFile = $logDir . '/' . $logName . LOG_EXT;
		$this->logName = $logName;
		$this->logLines = $logLines;
	}

	/*
	 * writes log file
	 */
	public function writeLog($file) {
		if (empty($this->logName)) { // logging disabled
			return true;
		}

		$line = implode("\t", [
			date('d M Y H:i:s O'),
			$_SERVER['REMOTE_ADDR'],
			'notepad',
			$file
		]);

		$result = file_put_contents($this->logFile, "$line\n", FILE_APPEND);

		if ($result !== false) {
			$this->cropLog();
			return true;
		}
	}

	/*
	 * crops log
	 */
	private function cropLog() {
		if (empty($this->logLines)) { // log cropping disabled
			return true;
		}

		$counter = 0;
		$contents = file($this->logFile);

		if (count($contents) > $this->logLines) {
			$contents = array_slice($contents, -$this->logLines);
			return file_put_contents($this->logFile, $contents) !== false;
		}
	}
}