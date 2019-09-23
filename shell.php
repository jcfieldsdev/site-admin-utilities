<?php

/******************************************************************************
 * Shell                                                                      *
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

const THEMES = [
	'Light' => [
		'bg'     => '#fff',
		'bgui'   => '#ccc',
		'fglo'   => '#666',
		'fghi'   => '#000',
		'foclo'  => '#600',
		'fochi'  => '#f00'
	],
	'Dark' => [
		'bg'     => '#000',
		'bgui'   => '#333',
		'fglo'   => '#ccc',
		'fghi'   => '#fff',
		'foclo'  => '#060',
		'fochi'  => '#0f0'
	],
	'Red' => [
		'bg'     => '#300',
		'bgui'   => '#600',
		'fglo'   => '#ccc',
		'fghi'   => '#fff',
		'foclo'  => '#990',
		'fochi'  => '#ff0'
	],
	'Green' => [
		'bg'     => '#030',
		'bgui'   => '#060',
		'fglo'   => '#ccc',
		'fghi'   => '#fff',
		'foclo'  => '#909',
		'fochi'  => '#f6f'
	],
	'Blue' => [
		'bg'     => '#006',
		'bgui'   => '#009',
		'fglo'   => '#ccc',
		'fghi'   => '#fff',
		'foclo'  => '#960',
		'fochi'  => '#f90'
	]
];
const DEFAULT_THEME = 'Dark';

/*
 * gets form and executes commands
 */

$shell = new Shell();

$output = $shell->getOutput();
$form = $shell->getForm();

/*
 * prints everything
 */

$theme = $shell->getTheme();
$colors = THEMES[$theme];

echo <<<"HTML"
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en"><head><title>Shell</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="mailto:jcfields@jcfields.dev" rel="author" title="J.C. Fields">
<style type="text/css">
body, html {
	height: 100%;
	margin: 0;
	padding: 0;
}

button, html, input {
	background: {$colors['bg']};
	color: {$colors['fglo']};
}

button, html, input, pre {
	font: 9pt/150% "Lucida Console", Monaco, monospace;
}

button, input {
	border: none;
	outline: 2px solid transparent;
}

button:focus, button:hover, input:focus, input:hover {
	background: {$colors['bg']};
	color: {$colors['fghi']};
	outline-color: {$colors['fochi']};
}

button:hover {
	cursor: pointer;
}

button:hover:not(:focus), input:hover:not(:focus) {
	outline-style: dashed;
}

div#out {
	box-sizing: border-box;
	height: 100%;
	overflow: scroll;
	padding: 1em;
	white-space: pre;
}

div#ui {
	background: {$colors['bgui']};
	border-radius: 2em;
	bottom: 1em;
	color: inherit;
	padding: 0.25em 1em;
	position: absolute;
	right: 1em;
	width: 50%;
}

input[type="password"], input[type="text"] {
	width: 100%;
}

span.hotkey {
	text-decoration: underline;
}

span#last, span.error, span.highlight, span.notice, span.prompt, th {
	background: none;
	color: {$colors['fghi']};
}

span.cwd {
	background: none;
	color: {$colors['fochi']};
}

span.host {
	background: none;
	color: {$colors['foclo']};
}

span.prompt {
	font-weight: bold;
}

table {
	padding-right: 4em;
	width: 100%;
}

td, th {
	white-space: nowrap;
}

th {
	font-style: normal;
	font-weight: normal;
	text-align: right;
}</style>
<script type="text/javascript">
window.addEventListener("load", function() {
	let history = [], line = [], matches = [], changed = false, match = 0;

	const list = JSON.parse(document.getElementsByName("list")[0].value);
	const command = document.getElementsByName("command")[0].value;

	try {
		history = JSON.parse(sessionStorage.getItem("shell")) || [];
	} catch (error) {
		console.error(error);
	}

	if (command !== "") {
		history.push(command);
	}

	let index = history.length || 0;

	const prompt = document.getElementById("prompt");
	prompt.addEventListener("keydown", function(event) {
		if (event.keyCode === 9) { // tab
			event.preventDefault();
			this.value = tabComplete(this.value, event);
		}

		if (event.keyCode === 38) { // up arrow
			document.getElementById("prompt").value = getLastCommand();
		}

		if (event.keyCode === 40) { // down arrow
			document.getElementById("prompt").value = getNextCommand();
		}
	});
	prompt.addEventListener("input", function(event) {
		changed = true;
	});
	prompt.focus();

	const elements = Array.from(document.getElementById("dir"));
	elements.push(prompt);

	for (const element of elements) {
		element.setAttribute("autocomplete", "off");
		element.setAttribute("autocorrect", "off");
		element.setAttribute("autocapitalize", "off");
		element.setAttribute("spellcheck", "false");
	}

	document.getElementById("recall").addEventListener("click", function() {
		document.getElementById("prompt").value = getLastCommand();
	});
	document.getElementById("setcwd").addEventListener("click", function() {
		const cwd = document.getElementsByName("cwd")[0].value;
		document.getElementById("dir").value = cwd;
	});

	window.addEventListener("beforeunload", function() {
		sessionStorage.setItem("shell", JSON.stringify(history));
	});

	function getLastCommand() {
		if (index < 1) {
			index = 0;
		} else {
			index--;
		}

		if (history[index] === undefined) {
			return "";
		}

		return history[index];
	}

	function getNextCommand() {
		if (index >= history.length - 1) {
			index = history.length;
			return "";
		}

		index++;
		return history[index];
	}

	function tabComplete(value, event) {
		line = value.split(" ");

		if (changed) {
			const str = line[line.length - 1];

			matches = [];

			for (const file in list) {
				if (list[file].startsWith(str)) {
					matches.push(list[file]);
				}
			}

			match = 0;
			changed = false;
		} else {
			if (event.shiftKey) {
				if (match > 0) {
					match--;
				}
			} else if (match < matches.length - 1) {
				match++;
			}
		}

		if (matches.length !== 0) {
			line[line.length - 1] = matches[match];
		}

		return line.join(" ");
	}
});</script></head>
<body><div id="out">$output</div>
<div id="ui"><form action="{$_SERVER['SCRIPT_NAME']}#last" method="post" enctype="multipart/form-data">
<div><input type="hidden" value="session" name="session">
<input type="hidden" value="{$form['cwd']}" name="cwd">
<input type="hidden" value="{$form['command']}" name="command">
<input type="hidden" value="{$form['list']}" name="list">
<input type="hidden" value="{$form['history']}" name="history"></div>
<table><tr><th><label for="prompt">E<span class="hotkey">x</span>ecute command:</label></th>
<td><input type="text" accesskey="x" tabindex="1" name="prompt" id="prompt">
<button type="button" accesskey="l" title="Recalls the last command entered." id="recall">&uArr;</button></td></tr>
<tr><th><label for="file"><span class="hotkey">U</span>pload file:</label></th>
<td><input type="file" accesskey="u" tabindex="2" name="file" id="file">
<input type="checkbox" value="unzip" accesskey="z" tabindex="3" name="unzip" id="unzip">
<label for="unzip">Un<span class="hotkey">z</span>ip</label></td></tr>
<tr><th><label for="dir">Working <span class="hotkey">d</span>irectory:</label></th>
<td><input type="text" value="{$form['cwd']}" accesskey="o" tabindex="4" name="dir" id="dir">
<button type="button" title="Sets field to the current working directory." id="setcwd">.</button></td></tr>
<tr><th></th><td><p><button type="submit" accesskey="s" tabindex="5">&crarr; <span class="hotkey">S</span>ubmit</button>
<input type="checkbox" value="clear" accesskey="c" tabindex="6" name="clear" id="clear">
<label for="clear"><span class="hotkey">C</span>lear</label>
&nbsp;<select accesskey="t" tabindex="7" name="theme" id="theme">{$form['themes']}</select></p></td></tr></table></form></div></body></html>
HTML;

/*
 * Shell class
 */
class Shell {
	private $cwd, $session, $clear, $history, $command, $unzip, $theme;

	/*
	 * constructor
	 */
	public function __construct() {
		$this->theme   = $_POST['theme']   ?? DEFAULT_THEME;
		$this->session = $_POST['session'] ?? '';
		$this->clear   = $_POST['clear']   ?? false;
		$this->cwd     = $_POST['dir']     ?? $this->pwd();
		$this->command = $_POST['prompt']  ?? '';
		$this->history = $_POST['history'] ?? '';
		$this->unzip   = $_POST['unzip']   ?? false;

		$this->history = base64_decode($this->history);
	}

	/*
	 * formats output buffer and executes input
	 */
	public function getOutput() {
		$log = new Log(LOG_NAME, LOG_LINES);

		// logs first page load of session
		if (!$this->session && !$log->writeLog()) {
			return $this->getError(sprintf(
				'Could not write to log file "%s".',
				LOG_NAME
			));
		}

		$html = '';

		if ($this->clear) {
			$this->history = '';
		} else {
			// shows history if available, else current directory listing
			if (empty($this->history) && empty($this->command)) {
				$this->history = $this->ls();
				$html .= sprintf(
					'<span id="last">%s</span>',
					$this->history
				);
			} else {
				$html .= $this->history;
			}
		}

		if ($this->command) {
			$output = "\n<span class=\"prompt\">";

			// writes prompt
			if (PHP_OS === 'WINNT') {
				$output .= sprintf(
					'<span class="cwd"></span> &gt;',
					htmlspecialchars($this->cwd)
				);
			} else {
				$output .= sprintf(
					'[<span class="host">%s</span>:'
					. '<span class="cwd">%s</span>]%% ',
					htmlspecialchars($_SERVER['HTTP_HOST']),
					htmlspecialchars($this->cwd)
				);
			}

			$output .= '</span>' . htmlspecialchars($this->command) . "\n";
			$output .= htmlspecialchars(shell_exec(sprintf(
				'(cd %s && %s) 2>&1',
				escapeshellarg($this->cwd),
				$this->command
			)));
			$output = self::unprintable($output);

			$this->history .= $output;
			$html .= sprintf("\n<span id=\"last\">%s</span>", trim($output));
		}

		if ($_FILES) {
			// removes terminal slashes
			$this->cwd = rtrim($this->cwd, '\\/');

			// uses unzip function if enabled
			if ($upload = ($this->unzip ? $this->unzip() : $this->upload())) {
				$this->history .= "\n" . $upload;
				$html .= "<span id=\"last\">\n$upload</span>";
			}
		}

		return $html;
	}

	/*
	 * formats form
	 */
	public function getForm() {
		$themes = '';

		foreach (array_keys(THEMES) as $theme) {
			$selected = $this->theme === $theme ? ' selected="selected"' : '';
			$theme = htmlspecialchars($theme, ENT_QUOTES);
			$themes .= "<option value=\"$theme\"$selected>$theme</option>";
		}

		// gets directory listing, removes . and ..
		$list = array_values(array_diff(scandir($this->cwd), ['.', '..']));

		return [
			'cwd'     => htmlspecialchars($this->cwd, ENT_QUOTES),
			'command' => htmlspecialchars($this->command, ENT_QUOTES),
			'list'    => htmlspecialchars(json_encode($list)),
			'history' => base64_encode($this->history),
			'themes'  => $themes
		];
	}

	/*
	 * formats error messages
	 */
	private function getError($error) {
		$error = htmlspecialchars($error);
		return "<p><span class=\"error\">Error:</span> $error</p>";
	}

	/*
	 * returns currently selected theme
	 */
	public function getTheme() {
		return $this->theme;
	}

	/*
	 * uploads files to server
	 */
	private function upload() {
		if (!$name = $_FILES['file']['name']) {
			return;
		}

		if (is_uploaded_file($_FILES['file']['tmp_name'])) {
			$destination = $this->cwd . '/' . $_FILES['file']['name'];

			if (copy($_FILES['file']['tmp_name'], $destination)) {
				$message = sprintf(
					"The file %s was uploaded to the directory %s.",
					$name,
					$this->cwd
				);
			} else {
				$message = "The file $name did not upload correctly.";
			}
		} else {
			switch ($_FILES['file']['error']) {
				case 0:
					$message = "The file $name is not an uploaded file.";
					break;
				case 1: // file exceeds upload_max_filesize directive in php.ini
				case 2: // file exceeds MAX_FILE_SIZE directive in HTML form
					$message = "The file $name exceeds the allowed file size.";
					break;
				case 3:
					$message = "The file $name was only partially uploaded.";
					break;
				case 4:
					$message = 'A file must be specified for uploading.';
					break;
				default:
					$message = "The file $name could not be uploaded.";
			}
		}

		if (!$message) {
			$message = 'An unknown error occurred when uploading a file.';
		}

		return 'shell.php: ' . $message;
	}

	/*
	 * handles uploads if unzip option true
	 */
	private function unzip() {
		// checks if zip extension is loaded
		if (!extension_loaded('zip')) {
			return;
		}

		$zip = new ZipArchive();

		// calls upload function if not a zip file
		if ($zip->open($_FILES['file']['tmp_name']) !== true) {
			return upload();
		}

		$zip->extractTo($this->cwd . '/');
		$zip->close();

		return sprintf(
			"shell.php: The file %s was unzipped to the directory %s.\n\n%s",
			$_FILES['file']['name'],
			$this->cwd,
			$this->ls()
		);
	}

	/*
	 * return current working directory
	 */
	private function pwd() {
		return rtrim(shell_exec(PHP_OS === 'WINNT' ? 'cd' : 'pwd'));
	}

	/*
	 * returns current working directory listing
	 */
	private function ls() {
		return shell_exec(sprintf(
			'cd %s && %s',
			escapeshellarg($this->cwd),
			PHP_OS === 'WINNT' ? 'dir /a' : 'ls -Alp'
		));
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
	public function writeLog() {
		if (empty($this->logName)) { // logging disabled
			return true;
		}

		$line = implode("\t", [
			date('d M Y H:i:s O'),
			$_SERVER['REMOTE_ADDR'],
			'shell'
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