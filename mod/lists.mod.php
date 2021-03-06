<?php

require_once(dirname(__FILE__) . "/../class/Mailman.class.php");

class lists {
	private $options;
	
	public function __construct($options) {
		$this->options = $options;
	}

	private function overview() {
		global $user, $smarty;

		$mails = array();
		foreach ($user->getMails() as $mail) {
			if ($user->isVerified($mail)) {
				$mails[] = $mail;
			}
		}
		$smarty->assign("mails", $mails);

		$mailman = new Mailman($user, $this->options["mailman_group"], $this->options["mailman_binpath"]);

		if (isset($_POST["save"])) {
			foreach ($mailman->getLists() as $list) {
				$mail = stripslashes($_POST["mail"][$list->getName()]);
				if ( (empty($mail) && $list->hasMember() )
				  || (!empty($mail) && !$list->hasMember($mail) ) ) {
					foreach ($list->getMembers() as $member) {
						if ($user->hasMail($member)) {
							$list->removeMember($member);
						}
					}
				}
				if (!empty($mail) && !$list->hasMember($mail)) {
					$list->addMember($mail);
				}
			}
		}

		$lists = array();
		foreach ($mailman->getLists() as $list) {
			$members = array();
			$has = false;
			foreach ($mails as $mail) {
				if ($list->hasMember($mail)) {
					$members[] = $mail;
					$has = true;
				}
			}
			$lists[] = array($list->getName(), $list->getSendAddress(), $list->getDescription(), $list->getArchiveURL(), $has, $members);
		}
		$smarty->assign("lists", $lists);

		return $smarty->fetch("lists.tpl");
	}

	public function main() {
		global $user;

		if (!$user->isVerified()) {
			echo "Bevor du deine Mailinglisten verwalten kannst, muss mindestens eine E-Mail Adresse durch eine Best&auml;tigungsmail verifiziert werden.";
		} else {
			$do = isset($_REQUEST["do"]) ? stripslashes($_REQUEST["do"]) : "";
			switch ($do) {
				case "overview":
				default:
					return $this->overview();
			}
		}
	}

}

?>
