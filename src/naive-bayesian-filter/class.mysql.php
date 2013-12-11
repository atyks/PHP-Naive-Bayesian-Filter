<?php
	# ***** BEGIN LICENSE BLOCK *****
	# Version: MPL 1.1/GPL 2.0/LGPL 2.1
	#
	# The contents of this file are subject to the Mozilla Public License Version
	# 1.1 (the "License"); you may not use this file except in compliance with
	# the License. You may obtain a copy of the License at
	# http://www.mozilla.org/MPL/
	#
	# Software distributed under the License is distributed on an "AS IS" basis,
	# WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	# for the specific language governing rights and limitations under the
	# License.
	#
	# The Original Code is DotClear Weblog.
	#
	# The Initial Developer of the Original Code is
	# Olivier Meunier.
	# Portions created by the Initial Developer are Copyright (C) 2003
	# the Initial Developer. All Rights Reserved.
	#
	# Contributor(s):
	#
	# Alternatively, the contents of this file may be used under the terms of
	# either the GNU General Public License Version 2 or later (the "GPL"), or
	# the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
	# in which case the provisions of the GPL or the LGPL are applicable instead
	# of those above. If you wish to allow use of your version of this file only
	# under the terms of either the GPL or the LGPL, and not to allow others to
	# use your version of this file under the terms of the MPL, indicate your
	# decision by deleting the provisions above and replace them with the notice
	# and other provisions required by the GPL or the LGPL. If you do not delete
	# the provisions above, a recipient may use your version of this file under
	# the terms of any one of the MPL, the GPL or the LGPL.
	#
	# ***** END LICENSE BLOCK *****

	# Classe de connexion MySQL

	require_once dirname(__FILE__) . '/class.recordset.php';

	class Connection {
		var $con_id;
		var $error;
		var $errno;

		function Connection($user, $pwd, $alias = '', $dbname) {
			$this->error = '';

			$this->con_id = @mysql_connect($alias, $user, $pwd);

			if (!$this->con_id) {
				$this->setError();
			}
			else {
				$this->database($dbname);
			}
		}

		function database($dbname) {
			$db = @mysql_select_db($dbname);
			if (!$db) {
				$this->setError();

				return false;
			}
			else {
				return true;
			}
		}

		function close() {
			if ($this->con_id) {
				mysql_close($this->con_id);

				return true;
			}
			else {
				return false;
			}
		}

		function select($query, $class = 'recordset') {
			if (!$this->con_id) {
				return false;
			}

			if ($class == '' || !class_exists($class)) {
				$class = 'recordset';
			}

			$cur = mysql_unbuffered_query($query, $this->con_id);

			if ($cur) {
				# Insertion dans le reccordset
				$i = 0;
				$arryRes = array();

				while ($res = mysql_fetch_row($cur)) {
					for ($j = 0; $j < count($res); $j++) {
						$arryRes[$i][strtolower(mysql_field_name($cur, $j))] = $res[$j];
					}
					$i++;
				}

				return new $class($arryRes);
			}
			else {
				$this->setError();

				return false;
			}
		}

		function execute($query) {
			if (!$this->con_id) {
				return false;
			}

			$cur = mysql_query($query, $this->con_id);

			if (!$cur) {
				$this->setError();

				return false;
			}
			else {
				return true;
			}

		}

		function getLastID() {
			if ($this->con_id) {
				return mysql_insert_id($this->con_id);
			}
			else {
				return false;
			}
		}

		function setError() {
			if ($this->con_id) {
				$this->error = mysql_error($this->con_id);
				$this->errno = mysql_errno($this->con_id);
			}
			else {
				$this->error = mysql_error();
				$this->errno = mysql_errno();
			}
		}

		function error() {
			if ($this->error != '') {
				return $this->errno . ' - ' . $this->error;
			}
			else {
				return false;
			}
		}

		function escapeStr($str) {
			return mysql_escape_string($str);
		}
	}
