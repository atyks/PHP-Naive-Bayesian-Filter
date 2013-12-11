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

	# Classe de manipulation de recordSet, compos� d'un tableau de la forme

	class recordset {
		var $arry_data; # tableau contenant les donn�es
		var $int_index; # index pour parcourir les enregistrements
		# les enregistrements commencent � l'index 0

		var $int_row_count; # nombre d'enregistrements
		var $int_col_count; # nombre de colonnes

		function recordSet($data) {
			$this->int_index = 0;

			if (is_array($data)) {
				$this->arry_data = $data;

				$this->int_row_count = count($this->arry_data);

				if ($this->int_row_count == 0) {
					$this->int_col_count = 0;
				}
				else {
					$this->int_col_count = count($this->arry_data[0]);
				}
			}
		}

		function field($c) {
			if (!empty($this->arry_data)) {
				if (is_integer($c)) {
					$T = array_values($this->arry_data[$this->int_index]);

					return (isset($T[($c)])) ? $T[($c)] : false;
				}
				else {
					$c = strtolower($c);
					if (isset($this->arry_data[$this->int_index][$c])) {
						if (!is_array($this->arry_data[$this->int_index][$c])) {
							return trim($this->arry_data[$this->int_index][$c]);
						}
						else {
							return $this->arry_data[$this->int_index][$c];
						}
					}
					else {
						return false;
					}
				}
			}
		}

		function f($c) {
			return $this->field($c);
		}

		function setField($c, $v) {
			$c = strtolower($c);
			$this->arry_data[$this->int_index][$c] = $v;
		}

		function moveStart() {
			$this->int_index = 0;

			return true;
		}

		function moveEnd() {
			$this->int_index = ($this->int_row_count - 1);

			return true;
		}

		function moveNext() {
			if (!empty($this->arry_data) && !$this->EOF()) {
				$this->int_index++;

				return true;
			}
			else {
				return false;
			}
		}

		function movePrev() {
			if (!empty($this->arry_data) && $this->int_index > 0) {
				$this->int_index--;

				return true;
			}
			else {
				return false;
			}
		}

		function move($index) {
			if (!empty($this->arry_data) && $this->int_index >= 0 && $index < $this->int_row_count) {
				$this->int_index = $index;

				return true;
			}
			else {
				return false;
			}
		}

		function BOF() {
			return ($this->int_index == - 1 || $this->int_row_count == 0);
		}

		function EOF() {
			return ($this->int_index == $this->int_row_count);
		}

		function isEmpty() {
			return ($this->int_row_count == 0);
		}

		# Donner le tableau de donn�es
		function getData() {
			return $this->arry_data;
		}

		# Nombre de lignes
		function nbRow() {
			return $this->int_row_count;
		}
	}
