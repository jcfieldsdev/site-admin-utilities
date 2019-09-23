<?php

/******************************************************************************
 * Database Editor                                                            *
 *                                                                            *
 * Copyright (C) 2009 J.C. Fields (jcfields@jcfields.dev).                    *
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

// directory containing databases (relative to script)
const DB_DIR = 'db';
// extension for database files
const DB_EXT = '.sqlite';

// name of log file ('' to disable logging)
const LOG_NAME = 'login';
// maximum length of log (0 to disable log cropping)
const LOG_LINES = 100;

// directory containing log files
const LOG_DIR = 'logs';
// extension for log files
const LOG_EXT = '.log';

const THEMES = [
	'Light' => [
		'bg'          => '#eab4b4 0%, #fff 20%, #fff 80%, #d4d4d4 100%',
		'fg'          => '#000',
		'uiBg'        => 'rgba(192, 192, 192, 0.8)',
		'uiBdr'       => '#808080',
		'buttonBg'    => '#c0c0c0',
		'inputBg'     => 'linear-gradient(#fff, #d4d4d4)',
		'inputFoc'    => '#f00',
		'tblBdr'      => '#c0c0c0',
		'tblRowBg'    => '#fff',
		'tblRowBgAlt' => '#f0f0f0'
	],
	'Dark' => [
		'bg'          => '#656464, #2b2a2a, #181717, #8d0000',
		'fg'          => '#fff',
		'uiBg'        => 'rgba(64, 64, 64, 0.8)',
		'uiBdr'       => '#404040',
		'buttonBg'    => '#808080',
		'inputBg'     => 'linear-gradient(#606060, #404040)',
		'inputFoc'    => '#f00',
		'tblBdr'      => '#202020',
		'tblRowBg'    => '#404040',
		'tblRowBgAlt' => '#606060'
	],
	'Red' => [
		'bg'          => '#db4e4e, #6b3f9a, #cc4e74',
		'fg'          => '#fff',
		'uiBg'        => 'rgba(107, 63, 154, 0.8)',
		'uiBdr'       => '#cc4e74',
		'buttonBg'    => '#db4e4e',
		'inputBg'     => 'linear-gradient(#9f4a8e, #6b3f9a)',
		'inputFoc'    => '#db4e4e',
		'tblBdr'      => '#9f4a8e',
		'tblRowBg'    => '#d24459',
		'tblRowBgAlt' => '#cc4e74'
	],
	'Green' => [
		'bg'          => '#398564, #52bf90, #fcd667',
		'fg'          => '#fff',
		'uiBg'        => 'rgba(49, 114, 86, 0.8)',
		'uiBdr'       => '#398564',
		'buttonBg'    => '#317256',
		'inputBg'     => 'linear-gradient(#398564, #317256)',
		'inputFoc'    => '#fcd667',
		'tblBdr'      => '#49ab81',
		'tblRowBg'    => '#317256',
		'tblRowBgAlt' => '#398564'
	],
	'Blue' => [
		'bg'          => '#8dbdd8, #fff, #d8e9f4',
		'fg'          => '#000',
		'uiBg'        => 'rgba(64, 144, 192, 0.8)',
		'uiBdr'       => '#b2d4e6',
		'buttonBg'    => '#4090c0',
		'inputBg'     => 'linear-gradient(#d8e9f4, #8dbdd8)',
		'inputFoc'    => '#fff',
		'tblBdr'      => '#68a8ca',
		'tblRowBg'    => '#8dbdd8',
		'tblRowBgAlt' => '#b2d4e6'
	]
];
const DEFAULT_THEME = 'Light';

/*
 * handles logins
 */

$dbEdit = new DbEdit();
$form = $dbEdit->getForm();

/*
 * prints everything
 */

$theme = $dbEdit->getTheme();
$colors = THEMES[$theme];

echo <<<"HTML"
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en"><head><title>Database Editor</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="mailto:jcfields@jcfields.dev" rel="author" title="J.C. Fields">
<style type="text/css">
body {
	background: linear-gradient(to bottom right, {$colors['bg']});
	color: {$colors['fg']};
	font: 10pt Helvetica, Arial, sans-serif;
}

body, html {
	height: 100%;
	margin: 0;
	padding: 0;
}

button {
	background: {$colors['buttonBg']};
	border: 0;
	border-radius: 1em;
	box-shadow: inset 0 -2px 2px rgba(0, 0, 0, 0.5);
	color: inherit;
	font-family: inherit;
	font-size: 100%;
	padding: 0.25em;
}

div#out {
	height: 100%;
	overflow: scroll;
}

div#ui {
	background: {$colors['uiBg']};
	border: 1px solid {$colors['uiBdr']};
	border-radius: 1em;
	box-shadow: 2px 2px 4px #000;
	color: inherit;
	left: 50%;
	margin: 1em 0;
	padding: 0.5em 1em;
	position: fixed;
	transform: translate(-50%, 0);
	white-space: nowrap;
}

input {
	background: {$colors['inputBg']};
	border: 0;
	color: {$colors['fg']};
	font-size: 100%;
	outline: 2px solid transparent;
	width: 50em;
}

input, pre {
	font-family: "Lucida Console", Monaco, monospace;
}

input:focus {
	outline-color: {$colors['inputFoc']};
}

p {
	margin: 10em 2em 1em;
}

pre {
	border-left: 2px solid {$colors['uiBdr']};
	margin: 1em 2em;
	padding-left: 1em;
}

span.error {
	font-weight: bold;
}

span.hotkey {
	text-decoration: underline;
}

table {
	border-collapse: collapse;
}

table.db {
	margin: 10em auto 4em;
	width: 95%;
}

table.db td {
	background: {$colors['tblRowBg']};
	border: 1px solid {$colors['tblBdr']};
	color: inherit;
}

table.db th {
	text-align: center;
}

table.db tr:nth-child(2n) td {
	background: {$colors['tblRowBgAlt']};
	color: inherit;
}

table.form {
	margin: auto;
}

table.form th {
	text-align: right;
}

td {
	vertical-align: top;
}

td, th {
	padding: 0.25em;
}

th {
	font-weight: bold;
	text-align: left;
	vertical-align: center;
}</style></head>
<body>$form</body></html>
HTML;

class DbEdit {
	private $theme, $db, $loggedIn, $doQuery, $selQuery;

	/*
	 * constructor
	 */
	public function __construct() {
		$this->theme    = $_POST['theme']    ?? DEFAULT_THEME;
		$this->db       = $_POST['db']       ?? '';
		$this->loggedIn = $_POST['loggedin'] ?? '';
		$this->doQuery  = $_POST['doquery']  ?? '';
		$this->selQuery = $_POST['selquery'] ?? '';

		$dbDir = __DIR__ . '/' . DB_DIR;

		if (!file_exists($dbDir)) {
			mkdir($dbDir);
		}
	}

	/*
	 * database editor
	 */
	public function getForm() {
		if (!$this->db) {
			return $this->getLoginPrompt();
		}

		$log = new Log(LOG_NAME, LOG_LINES);

		// logs first page load of session
		if (!$this->loggedIn && !$log->writeLog($db)) {
			return self::getError('Could not write log file "%s".', LOG_NAME);
		}

		try {
			$dsn = 'sqlite:' . implode('/', [
				__DIR__,
				DB_DIR,
				$this->db . DB_EXT
			]);
			$link = new PDO($dsn);
			$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (Exception $err) {
			return self::getError('%s', $err->getMessage());
		}

		$output = $error = '';
		$doAffected = $selAffected = 0;

		try {
			if (!empty($this->doQuery)) {
				$doResult = $link->query($this->doQuery);

				if ($doResult) {
					$doAffected = $doResult->rowCount();
				}
			}
		} catch (PDOException $err) {
			$error .= sprintf(
				"<p><strong>Do error:</strong></p>\n<pre>%s</pre>\n",
				$err->getMessage()
			);
		}

		try {
			if (empty($this->selQuery)) {
				$this->selQuery = 'SELECT name FROM sqlite_master '
				                . 'WHERE type = "table" '
				                . 'AND name NOT LIKE "sqlite_%"';
			}

			$selResult = $link->query($this->selQuery);

			// get column names
			$columns = [];
			$output .= '<table class="db"><tr><th>#</th>';

			for ($i = 0; $i < $selResult->columnCount(); $i++) {
				$meta = $selResult->getColumnMeta($i);
				$columns[] = $meta['name'];
				$output .= "<th>{$meta['name']}</th>";
			}

			$output .= '</tr>';

			// get rows
			foreach ($selResult as $row) {
				$selAffected++;
				$output .= "\n<tr><th>$selAffected</th>";

				foreach ($columns as $column) {
					$text = self::formatString($row[$column]);
					$output .= "<td>$text</td>";
				}

				$output .= '</tr>';
			}

			$output .= '</table>';
		} catch (PDOException $err) {
			$error .= sprintf(
				"<p><strong>Select error:</strong></p>\n<pre>%s</pre>",
				$err->getMessage()
			);
		}

		$doQuery = htmlspecialchars($this->doQuery, ENT_QUOTES);
		$selQuery = htmlspecialchars($this->selQuery, ENT_QUOTES);
		$db = htmlspecialchars($this->db, ENT_QUOTES);
		$theme = htmlspecialchars($this->theme, ENT_QUOTES);

		if ($error) {
			$output = $error;
		}

		return <<<"HTML"
		<div id="ui"><form action="{$_SERVER['SCRIPT_NAME']}" method="post" enctype="application/x-www-form-urlencoded">
		<div><input type="hidden" name="loggedin" value="loggedin">
		<input type="hidden" name="db" value="$db">
		<input type="hidden" name="theme" value="$theme"></div>
		<table class="form"><tr><th><label for="doquery"><span class="hotkey">D</span>o:</label></th><td><input type="text" value="$doQuery" accesskey="d" spellcheck="false" name="doquery" id="doquery"> ($doAffected)</td></tr>
		<tr><th><label for="selquery"><span class="hotkey">S</span>elect:</label></th><td><input type="text" value="$selQuery" accesskey="s" spellcheck="false" name="selquery" id="selquery"> ($selAffected)
		<button type="submit" accesskey="t">Submi<span class="hotkey">t</span></button></td></tr></table></form></div>
		<div id="out">$output</div>
		HTML;
	}

	/*
	 * gets login prompt
	 */
	public function getLoginPrompt() {
		$themes = '';

		foreach (array_keys(THEMES) as $theme) {
			$selected = $this->theme === $theme ? ' selected="selected"' : '';
			$theme = htmlspecialchars($theme, ENT_QUOTES);
			$themes .= "<option value=\"$theme\"$selected>$theme</option>";
		}

		$databases = implode('</option><option>', $this->getDatabases());

		return <<<"HTML"
		<div id="ui"><form action="{$_SERVER['SCRIPT_NAME']}" method="post" enctype="application/x-www-form-urlencoded">
		<table class="form"><tr><th><label for="db"><span class="hotkey">D</span>atabase:</label></th><td>
		<select accesskey="d" name="db" id="db"><option>$databases</option></select></td></tr>
		<tr><th><label for="theme"><span class="hotkey">T</span>heme</label></th>
		<td><select accesskey="t" name="theme" id="theme">$themes</select>
		<button type="submit" accesskey="t">Submi<span class="hotkey">t</span></button></td></tr></table></form></div>
		HTML;
	}

	/*
	 * reads databases from directory
	 */
	private function getDatabases() {
		$databases = [];

		foreach (new DirectoryIterator(__DIR__ . '/' . DB_DIR) as $file) {
			if ('.' . $file->getExtension() === DB_EXT) {
				$databases[] = $file->getBasename(DB_EXT);
			}
		}

		sort($databases);
		return $databases;
	}

	/*
	 * formats error messages
	 */
	private function getError($message, ...$args) {
		$error = htmlspecialchars(vsprintf($message, $args));
		return "<p><span class=\"error\">Error:</span> $error</p>";
	}

	/*
	 * returns currently selected theme
	 */
	public function getTheme() {
		return $this->theme;
	}

	/*
	 * treats string for display
	 */
	private static function formatString($text) {
		// escapes < & >
		$text = htmlspecialchars($text);
		// converts line breaks to <br>
		$text = preg_replace("/\r\n/", "\n", $text);
		$text = preg_replace("/[\r\n]/", '<br>', $text);
		// removes unprintable characters
		// leaves \n, \t, and \r alone
		$text = preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/', '?', $text);

		return $text;
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
	public function writeLog($db) {
		if (empty($this->logName)) { // logging disabled
			return true;
		}

		$line = implode("\t", [
			date('d M Y H:i:s O'),
			$_SERVER['REMOTE_ADDR'],
			'dbedit',
			$db
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