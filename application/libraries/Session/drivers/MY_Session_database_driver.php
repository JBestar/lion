<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DB 세션 GET_LOCK 대기 상한 단축.
 * 기본 CI 는 300초 — 락을 못 받으면 Apache/PHP 스레드가 수분 점유되어 전역 대기로 번짐.
 * 정상 요청은 생성자에서 즉시 session_write_close 하므로 락 보유는 수~수십 ms 수준.
 */
class MY_Session_database_driver extends CI_Session_database_driver {

	/** @var int MySQL GET_LOCK timeout (seconds) */
	protected $_lion_get_lock_timeout = 5;

	protected function _get_lock($session_id)
	{
		if ($this->_platform === 'mysql')
		{
			$arg = md5($session_id.($this->_config['match_ip'] ? '_'.$_SERVER['REMOTE_ADDR'] : ''));
			$timeout = (int) $this->_lion_get_lock_timeout;
			if ($timeout < 1) {
				$timeout = 5;
			}
			$row = $this->_db->query("SELECT GET_LOCK('".$arg."', ".$timeout.") AS ci_session_lock")->row();
			if ($row && (int) $row->ci_session_lock === 1)
			{
				$this->_lock = $arg;
				return TRUE;
			}

			log_message('error', 'Session: GET_LOCK timeout after '.$timeout.'s for session hash '.$arg);
			return FALSE;
		}

		return parent::_get_lock($session_id);
	}
}
