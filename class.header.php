<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author mitke_r
 */
include_once("class.database.php");
class HeaderData extends Database {
//put your code here
    public function getUserInfo() {
        $user_id = $_SESSION['user_id'];
        $arr = self::getRow(array('field'=> '*', 'condition'=>"AND user_id ='". $user_id. "'"));
        $profile_logo = $arr['profile_logo_path'];
        $last_login = $arr['last_login'];

		$getinfo = array("profile_logo" => $profile_logo, "last_login" => $last_login);
		return $getinfo;
    }

    public function getRow($params) {
        $query = "SELECT {$params['field']} FROM user_registration WHERE 1=1 {$params['condition']} ";
		//echo $query;die();
        return $this->db->get_row($query, array_a);

    }

	public function getUserDetails($user_id) {
        $query = "SELECT * FROM user_registration WHERE user_id = '$user_id'";
		//echo $query;die();
        return $this->db->get_row($query, array_a);

    }

	public function getUserDiasporaPatient($user_id) {
        $query = "select count(*) cnt from diaspora_registrations WHERE patient_id = '$user_id'";
		//echo $query;die();
        return $this->db->get_row($query, array_a);

    }

	public function getUserDiasporaRegistrations($user_id) {
        $query = "select count(*) cnt from diaspora_registrations WHERE user_id = '$user_id'";
		//echo $query;die();
        return $this->db->get_row($query, array_a);

    }

	public function getUserSettingValue($user_id, $setting_id) {

		$query = "select * from user_settings where user_id = '$user_id' and setting_id = '$setting_id'";
		//echo $query;die();
		$getinfo = $this->db->get_row($query, array_a);

		return $getinfo["setting_value"];

	}

	public function getMsgNotificationCount()
	{
		session_start();
		$user_id = $_SESSION['user_id'];

		$query = "SELECT IFNULL(SUM(ct),0) AS total_count
			FROM(
				SELECT a.uniqueid, a.from_user, a.to_user, a.sentdate
				, (CASE WHEN IFNULL(b.user_id,'')='' THEN a.from_user ELSE b.user_id END) AS user_id
				, (CASE WHEN IFNULL(b.user_id,'')='' THEN a.from_user ELSE CONCAT(b.first_name ,' ', b.last_name) END) AS fullname
				, a.read, b.profile_logo_path, COUNT(*) AS ct
				FROM live_chat a
				LEFT JOIN user_registration b ON(a.from_user = b.user_id)
				WHERE a.to_user='".$user_id."'
				AND a.read IN(0,1)
				GROUP BY a.from_user
			)q
		";
		//echo $query;
		$getinfo = $this->db->get_row($query, array_a);
		echo $getinfo["total_count"];
	}

	public function getMsgNotificationList()
	{
		$user_id = $_SESSION['user_id'];

		$query = "SELECT a.uniqueid, a.from_user, a.to_user, a.sentdate
					, (CASE WHEN IFNULL(b.user_id,'')='' THEN a.from_user ELSE b.user_id END) AS user_id
					, (CASE WHEN IFNULL(b.user_id,'')='' THEN a.from_user ELSE CONCAT(b.first_name ,' ', b.last_name) END) AS	fullname, a.read, b.profile_logo_path, COUNT(*) AS ct FROM live_chat a
					LEFT JOIN user_registration b ON(a.from_user = b.user_id)
					WHERE a.to_user='".$user_id."' AND a.read IN(0,1)
					GROUP BY a.from_user
					ORDER BY a.sentdate DESC";

		//echo $query;
		$getinfo = $this->db->get_results($query, array_a);
		$html = "
			<table width='100%' style='font-family:Verdana;font-size:10px;font-weight: normal;background-color:#EDEFF5;' border='0' cellspacing='0' cellpadding='0'>";
			if(!empty($getinfo) && is_array($getinfo))
			{
				foreach($getinfo as $uinfo)
				{
					$profilePic = $uinfo['profile_logo_path'];
					if($profilePic == "" || $profilePic == null)
					{
						$profilePic = "images/default-profile.png";
					}
					$html .= "<tr user_id='".$uinfo['from_user']."' style='cursor:pointer;' onclick='showUInTouchWeb(this);'>
									<td width='20%' style='border-bottom: solid 1px #B4B5B0;line-height:20px'>
										<img src='".$profilePic."' alt='profile' class='userimg' width='25px' height='25px'/>
									</td>

									<td width='50%' style='color: #505050;font-family:Verdana;font-size:10px;font-weight: normal;border-bottom: solid 1px #B4B5B0;line-height:20px' align='left'>".$uinfo['fullname']."</td>


									<td width='30%' align='center' style='border-bottom: solid 1px #B4B5B0;line-height:20px'>
											<div id='cart-hdr' class='bc-cart'>
												<a href='javascript:void(0);'>
												<div id='cart-count' class='bc-number' style='display: block;'>".$uinfo['ct']."</div>
											</div>
									</td>
							 </tr>

							 ";
				}
			}
			else
			{
				$html .= "<tr>
								<td width='100%' align='center'><b>No new notification</b></td>
						 </tr>";
			}
		$html.="</table>";
		echo $html;
	}

     public function __construct() {
         parent::__construct();
    }

	public function getVideoMeetingCount(){
		session_start();
		$user_id = $_SESSION['user_id'];

		$query = "SELECT count(*) as ct from video_meeting as a where a.created_by = '" . $user_id . "' AND IFNULL(a.attended, '') <> 'Y'";
		//echo $query;
		$getinfo = $this->db->get_row($query, array_a);
		echo $getinfo["ct"];
	}

	public function getVideoMeetingList(){
		
		$user_id = $_SESSION['user_id'];

		$query = "SELECT a.id, a.type, a.triage_type, a.meeting_id, a.start_url, a.join_url, a.user_id, a.is_host, a.attended, a.topic
				  FROM video_meeting AS a
				  WHERE a.created_by = '" . $user_id . "' AND IFNULL(a.attended, '') <> 'Y' ";

		//echo $query;
		$getinfo = $this->db->get_results($query, array_a);
		$html = "
			<table width='100%' style='font-family:Verdana;font-size:10px;font-weight: normal;background-color:#EDEFF5;' border='0' cellspacing='0' cellpadding='0'>";
			if(!empty($getinfo) && is_array($getinfo))
			{
				foreach($getinfo as $uinfo)
				{
					$is_host = "";
					if($uinfo['is_host'] == "Y"){
						$is_host = "<span style='font-style: italic;color:#FF0000;'>(Host)</span>";
					}

					$html .= "<tr meeting_id='".$uinfo['meeting_id']."' user_id='".$uinfo['user_id']."'										   is_host='".$uinfo['is_host']."' start_url='".$uinfo['start_url']."' join_url='".$uinfo['join_url']."'			   primary_id='".$uinfo['id']."' style='cursor:pointer;' onclick='videoMeetingAttended(this);'>
									<td width='85%' style='color: #505050;font-family:Verdana;font-size:10px;font-weight: normal;border-bottom: solid 1px #B4B5B0;line-height:20px;overflow:hidden;white-space:nowrap;text-overflow: ellipsis;' align='left'>". $uinfo['topic'] . $is_host . "</td>

									<td style='color: #505050;font-family:Verdana;font-size:10px;font-weight: normal;border-bottom: solid 1px #B4B5B0;line-height:20px;overflow:hidden;white-space:nowrap;text-overflow: ellipsis;'>
										<a style='color: #0000FF;' value='Attended' onclick='event.stopPropagation();setVideoMeetingAttendStatus(this)'    is_host='".$uinfo['is_host']."' primary_id='".$uinfo['id']."'>Attended</a> 
									</td>
							 </tr>
							 ";
				}
			}
			else
			{
				$html .= "<tr>
								<td width='100%' align='center'><b>No new video meeting</b></td>
						 </tr>";
			}
		$html.="</table>";
		echo $html;
	}

	public function setVideoMeetingAttendStatus(){
		$primary_id = $_POST['primary_id'];
		$user_id = $_SESSION['user_id'];

		$query = "UPDATE video_meeting 
				  SET attended = 'Y',
				  modified_by = '" . $user_id . "',
				  modified_dt = now() 
				  WHERE id = '" . $primary_id . "'";
		$this->db->query($query, array_a);
		echo $query;
		echo "SUCCESSFUL";
	}
}
?>
