<?php

require_once(dirname(__FILE__) . "/Mailinglist.class.php");

class Mailman {
	private $lists = array();

	private $user;

	function __construct($user) {
		$this->user = $user;
		$this->group = $group;
		$this->loadLists();
	}

	function loadLists() {
		global $config;

		$this->lists = array();
		exec('newgrp ' . MAILMAN_EXEC_GROUP . ' <<< "' . $config['site']['path'] . '/lib/ucp_mailman.py ' . implode(" ", $this->user->getMails()) . '"', $lists);
		foreach($lists as $value) {
			list($listname, $listdesc, $archiveurl, $policy, $members) = explode(",", $value);
			if ($policy == 1) { // Oeffentliche Liste?
				if (trim($members) == "") {
					$members = array();
				} else {
					$members = explode(" ", $members);
				}
				$this->addList(new Mailinglist($this, $listname, $listdesc, $archiveurl, $policy, $members));
			}
		}
	}

	function setLists($lists) {
		$this->lists = $lists;
	}

	function addList($list) {
		$this->lists[] = $list;
	}

	function &getLists() {
		return $this->lists;
	}

	function &getList($listname) {
		foreach ($this->lists as $value) {
			if ($value->getName() == $listname) return ($value);
		}
	}

	function hasList($list) {
		foreach ($this->getLists() as $value) {
			if ($value == $list) {
				return true;
			}
		}
		return false;
	}

	function addListMember($list, $member) {
		exec('newgrp ' . MAILMAN_EXEC_GROUP . ' <<< "' . MAILMAN_BIN_PATH . 'add_members --welcome-msg=n --admin-notify=n --regular-members-file=- ' . $list . ' <<< ' . $member . '"');
	}
	
	function removeListMember($list, $member) {
		exec('newgrp ' . MAILMAN_EXEC_GROUP . ' <<< "' . MAILMAN_BIN_PATH . 'remove_members --nouserack --noadminack --file=- ' . $list . ' <<< ' . $member . '"');
	}
}

?>
