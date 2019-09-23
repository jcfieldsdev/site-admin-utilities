#!/usr/bin/perl -T
################################################################################
# Log Viewer                                                                   #
#                                                                              #
# Copyright (C) 2006 J.C. Fields (jcfields@jcfields.dev).                      #
#                                                                              #
# Permission is hereby granted, free of charge, to any person obtaining a copy #
# of this software and associated documentation files (the "Software"), to     #
# deal in the Software without restriction, including without limitation the   #
# rights to use, copy, modify, merge, publish, distribute, sublicense, and/or  #
# sell copies of the Software, and to permit persons to whom the Software is   #
# furnished to do so, subject to the following conditions:                     #
#                                                                              #
# The above copyright notice and this permission notice shall be included in   #
# all copies or substantial portions of the Software.                          #
#                                                                              #
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR   #
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,     #
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE  #
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER       #
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING      #
# FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS #
# IN THE SOFTWARE.                                                             #
################################################################################

use strict;
use warnings;

use CGI;
use File::Basename;
use POSIX qw(strftime);

use constant {
	# directory containing logs (no trailing slash)
	LOG_DIR => 'logs',
	# pattern for file names in log directory
	FILTER  => qr/\.(log|txt)$/
};

################################################################################
# prints everything                                                            #
################################################################################

my $q = CGI->new();
$q->charset('utf-8');

my $log = $q->param('log') || '';
my $log_dir = dirname(__FILE__) . '/' . LOG_DIR;
my $log_file = $log_dir . '/' . $log;

my ($log_info, $log_text);

if (-e $log_file) {
	if ($log) {
		$log_text = get_log($log_file);
		$log_info = get_log_info($log_file);
	} else {
		$log_text = '<p>No log file loaded.</p>';
		$log_info = '';
	}
} else {
	$log_text = '<p>The specified log does not exist.</p>';
	$log_info = '';
}

my $client_info = get_client_info();
my $select = get_select($log_dir, $log);

print $q->header('application/xhtml+xml');
print <<"HTML";
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><head><title>Log Viewer</title>
<link href="mailto:jcfields\@jcfields.dev" rel="author" title="J.C. Fields"/>
<style type="text/css">
body
{margin: 1%; padding: 0;}

button
{background: #f90; border: 1px outset #f90; color: #fff; font-size: 125%;}

button, html
{font-family: Helvetica, Arial, sans-serif;}

button, label, th
{font-weight: bold;}

html
{background: #fff; color: #000; font-size: 10pt; line-height: 150%; padding: 0;}

table
{font-family: Monaco, "Lucida Console", monospace;}

td, th
{vertical-align: top; white-space: nowrap;}

td:nth-child(2n)
{background: #f0f0f0; color: inherit;}

th
{background: none; color: #f90; font-style: normal; padding-right: 1em; text-align: right;}

tr:hover td
{background: #ffc;}

tr:hover td:nth-child(2n)
{background: #f0f0c6;}</style></head>
<body><form action="$ENV{'SCRIPT_NAME'}" method="get">
<p><label for="log">Log file:</label> $select&nbsp;<button type="submit">Load</button></p></form>
<div>$client_info$log_info$log_text</div></body></html>
HTML

################################################################################
# subroutines                                                                  #
################################################################################

sub get_log {
	my ($path) = @_;

	open my $handle, '<', $path
		or return '<p>The specified log could not be read.</p>';
	my @log = <$handle>;
	close $handle;

	my $html = '';

	if ($path =~ /\.log$/) { # sorts logs with most recent entries on top
		for (reverse 0 .. @log) {
			$html .= process_line($log[$_], $_);
		}
	} else {
		for (0 .. @log) {
			$html .= process_line($log[$_], $_);
		}
	}

	chop $html;

	return "<table>$html</table>";
}

sub get_client_info {
	my $html = '';

	if (exists $ENV{'REMOTE_ADDR'}) {
		$html .= "<p>Your IP address is $ENV{'REMOTE_ADDR'}.</p>\n";
	}

	if (exists $ENV{'HTTP_USER_AGENT'}) {
		$html .= sprintf(
			"<p>Your user agent is &ldquo;%s.&rdquo;</p>\n",
			CGI::escapeHTML($ENV{'HTTP_USER_AGENT'})
		);
	}

	return $html;
}

sub get_log_info {
	my ($path) = @_;
	my ($last_mod, $size);

	if (-f $path) {
		my @stat;

		@stat = stat $path;
		$size = $stat[7];
		$last_mod = $stat[9];
	} else {
		$size = 0;
		$last_mod = time;
	}

	return sprintf(
		"<p>This log is %s. It was last modified %s.</p>\n",
		format_file_size($size),
		format_date($last_mod)
	);
}

sub get_select {
	my ($path, $selected) = @_;

	opendir my $handle, $path
		or return;
	my @dir = readdir $handle;
	closedir $handle;

	@dir = sort @dir;

	my $select = '<select name="log" id="log">';
	$selected = CGI::escapeHTML($selected);

	for my $file (@dir) {
		next if ($file =~ /^\./);
		next if ($file !~ FILTER);

		$file = CGI::escapeHTML($file);

		if ($file eq $selected) {
			$select .= qq{<option selected="selected">$file</option>};
		} else {
			$select .= qq{<option>$file</option>};
		}
	}

	$select .= '</select>';

	return $select;
}

sub process_line {
	my ($row, $n) = @_;

	return '' if (!$row);

	# removes non-ASCII characters
	$row =~ s/[^\x00-\x7f]/?/g;
	$row = CGI::escapeHTML($row);
	$row =~ s|\t|</td><td>|g;
	chomp $row;

	return sprintf("<tr><th>%d</th><td>%s</td></tr>\n", $n + 1, $row);
}

sub format_date {
	# RFC 822 date
	return strftime('%a, %d %b %Y %T +0000', gmtime shift);
}

sub format_file_size {
	my ($file_size) = @_;

	return sprintf('%.2f GB', $file_size / 1024**3) if ($file_size > 1024**3);
	return sprintf('%.1f MB', $file_size / 1024**2) if ($file_size > 1024**2);
	return sprintf('%.0f KB', $file_size / 1024)    if ($file_size > 1024);

	return $file_size == 1 ? '1 byte' : "$file_size bytes";
}