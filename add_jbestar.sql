-- jbestar / startend1@ 로그인 가능하도록 회원 추가
-- 총판(control) 로그인: mb_level=8, mb_state_delete=0 필요

-- 1) 이미 있으면 비밀번호·레벨·상태만 갱신
UPDATE `member`
SET `mb_pwd` = 'startend1@', `mb_level` = 8, `mb_state_delete` = 0, `mb_state_active` = 1
WHERE `mb_uid` = 'jbestar';

-- 2) 없으면 새로 삽입
INSERT INTO `member` (
  `mb_uid`, `mb_pwd`, `mb_level`, `mb_emp_fid`, `mb_nickname`, `mb_user`,
  `mb_money`, `mb_point`, `mb_time_join`, `mb_time_last`, `mb_ip_last`,
  `mb_game_pb_ratio`, `mb_state_active`, `mb_state_delete`, `mb_state_print`,
  `mb_limit_round`, `mb_limit_single`, `mb_limit_mix`
)
SELECT 'jbestar', 'startend1@', 8, 0, 'jbestar', '', 0, 0, NOW(), NOW(), '', '0', 1, 0, 0, 0, 0, 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `member` WHERE `mb_uid` = 'jbestar');
