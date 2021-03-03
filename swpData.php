<?php
	require_once 'config.php';
	require_once 'db_functions.php';
	require_once 'AD/LDAP_authentication.php';
	// error_reporting(E_ALL); 
	// ini_set('display_errors', '1'); 

	$_SERVER['REQUEST_METHOD']='POST';
	

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		
		
	$userid=$_REQUEST['userid'];
	
			////added by aslesha
	$app_access=array();
		$access_name='';
		$sql="SELECT app_access
		FROM users 
		inner join division_app_access on division_app_access.divisionid=users.division
		where users.id='$userid' 
		and user_name <> '' and users.`status`='Active' and (now() between start_date and end_date || end_date='0000-00-00 00:00:00')and division_app_access.deleted=0 ";
		$result=mysql_query($sql) or die("user division access query".mysql_error());
		mysql_num_rows($result);
		if(mysql_num_rows($result))
		{
			while($access = mysql_fetch_assoc($result))
			{
				array_push($app_access,$access['app_access']);
				if($access_name=='')
				{
					$access_name=$access['app_access'];
				}
				else
				{
					$access_name.='/'.$access['app_access'];
				}
				
			}
		}
		else
		{
			$sql="SELECT app_access
				FROM app_master where (now() between start_date and end_date || end_date='0000-00-00 00:00:00') and app_master.deleted=0";
			$result=mysql_query($sql) or die("user master access query".mysql_error());
			while($access = mysql_fetch_assoc($result))
			{
				array_push($app_access,$access['app_access']);
				if($access_name=='')
				{
					$access_name=$access['app_access'];
				}
				else
				{
					$access_name.='/'.$access['app_access'];
				}
			}
		
		}
		
		/* print_r($app_access);
        exit(); */
		$user_skip_cnt=0;
		$q_appversion="SELECT count(id)as user_skip_cnt from user_skip_appversion where userid='".$userid."' and deleted=0";
					
			$exe_appversion=mysql_query($q_appversion) or die("app version query failed ".mysql_error());
			$user_skip_cnt=mysql_result($exe_appversion, 0,'user_skip_cnt');
		
		if (!in_array($_REQUEST['appVersion'],$app_access) && $user_skip_cnt==0)
		{	
	
		//nikhil20161105
		
		$json['message']['message'] ="You are rolled on iPad version '.$access_name.', Kindly login in that version"; 
		$json['code']['code'] =0;
		echo json_encode($json);
		exit();

		}
		
	
	

		function esc($s){
$r = str_replace("'","`",$s);
$r = utf8_encode($r);
$r = stripslashes($r);
return $r;
}

	
	$json=array();
	
	
	
	$data=array();
	$i=0;
	
	$m=$_REQUEST['month'];
	$y=$_REQUEST['year'];
	
	$monthyear = $y."-".$m;
	
	
		$auth=1;
		$l=0;
		
		$select=array('crmid'=>'');
		$from='touringyearmonth';
		//Format key=columnname,value=fieldvalue
		$where=array("smownerid='$userid'","year='$y'","month='$m'","deleted=0","authorise=2","submitted=1");
		$limit=1;
		$data =select($select,$from,$where,$limit);
		$crmid=$data[0]['crmid'];
		
		if($data[0]==0){
			$code=1;
		} else{
			$code=0;
		}

		$l=0;$docfoundflag=0;
		for($i=1;$i<=31;$i++){
			
			//prathmesh added bricks.deleted=0 in below query ticket 0389381 16-4-2018
			
			  $query="SELECT touringdetails.patch_brick AS brickId, bricks.brickname AS brickName, touringday.`day` AS `day`,contactdetails.contactid AS contactId, trim(concat(trim(contactdetails.firstname),' ',trim(contactdetails.lastname))) AS contactName, towns.station as station, towns.townid as townId,towns.townname as townName FROM touringday 
			 	INNER JOIN touringdetails ON touringdetails.id = touringday.daykey 
			 	inner join contact_wise_bricks on touringdetails.patch_brick=contact_wise_bricks.brick_id 
				inner join bricks on contact_wise_bricks.brick_id=bricks.brickid 
				inner join contactdetails on contact_wise_bricks.contact_id=contactdetails.contactid
			 	INNER JOIN townbrickassociation ON contactdetails.brick = townbrickassociation.brickid 
			 	INNER JOIN towns ON townbrickassociation.townid = towns.townid 
			 	INNER JOIN touringyearmonth ON touringday.crmid = touringyearmonth.crmid 
			 	WHERE  touringday.`day` =$i and contactdetails.deleted=0 /*and contactdetails.brick=touringdetails.patch_brick*/ AND touringyearmonth.crmid = '$crmid' and bricks.deleted=0 and contact_wise_bricks.deleted=0 GROUP BY contactId"; 


			$result = mysql_query($query) or die('query 1'.mysql_error());
			$rows = mysql_num_rows($result);
			$found = 0;
			
			$k=0;
			
			if($rows > 0){
				$docfoundflag=1;
				while($d = mysql_fetch_assoc($result)){
					$json['swpData']['doctorDetails'][$l]['day'] = $d['day'];
					$json['swpData']['doctorDetails'][$l]['date'] = $monthyear."-".$i;
					$json['swpData']['doctorDetails'][$l]['doctorData'][$k]['brickId'] = $d['brickId'];
					$json['swpData']['doctorDetails'][$l]['doctorData'][$k]['brickName'] = $d['brickName'];
					$json['swpData']['doctorDetails'][$l]['doctorData'][$k]['contactId'] = $d['contactId'];
					$json['swpData']['doctorDetails'][$l]['doctorData'][$k]['contactName'] = strtoupper(esc($d['contactName']));
					$json['swpData']['doctorDetails'][$l]['doctorData'][$k]['station'] = $d['station'];
					$json['swpData']['doctorDetails'][$l]['doctorData'][$k]['townId'] = $d['townId'];
					$json['swpData']['doctorDetails'][$l]['doctorData'][$k]['townName'] = $d['townName'];
					$k++;
				}
             $l++;
			} 
		}

		if($docfoundflag==0)
		{
			$json['swpData']['doctorDetails']=array();
		}

		$l=0;$chemfoundflag=0;
		for($i=1;$i<=31;$i++){
			
			//prathmesh added bricks.deleted=0 in below query ticket 0389381 16-4-2018
			
			$query="SELECT touringdetails.patch_brick AS brickId,  bricks.brickname AS brickName, touringday.`day` AS `day`,bcntscontactdetails.contactid AS chemistId,bcntscontactdetails.chemistname AS chemistName, towns.station as station, towns.townid as townId,towns.townname 
				FROM touringday 
				INNER JOIN touringdetails ON touringdetails.id = touringday.daykey 
				inner join bricks on touringdetails.patch_brick = bricks.brickid 
				inner join bcntscontactdetails on touringdetails.patch_brick=bcntscontactdetails.brick
				INNER JOIN townbrickassociation ON bcntscontactdetails.brick = townbrickassociation.brickid 
				INNER JOIN towns ON townbrickassociation.townid = towns.townid 
				INNER JOIN touringyearmonth ON touringday.crmid = touringyearmonth.crmid 
				WHERE  touringday.`day`=$i and bcntscontactdetails.deleted=0 and bcntscontactdetails.brick=touringdetails.patch_brick AND touringyearmonth.crmid = '$crmid' and bricks.deleted=0 GROUP BY chemistId";
			$result = mysql_query($query) or die('query 2'.mysql_error());
			$rows = mysql_num_rows($result);
			$found = 0;
			$k=0;
			
			if($rows > 0){
				$chemfoundflag=1;
				while($d = mysql_fetch_assoc($result)){
					$json['swpData']['chemistDetails'][$l]['day'] = $d['day'];
					$json['swpData']['chemistDetails'][$l]['date'] = $monthyear."-".$i;
					$json['swpData']['chemistDetails'][$l]['chemistData'][$k]['brickId'] = $d['brickId'];
					$json['swpData']['chemistDetails'][$l]['chemistData'][$k]['brickName'] = $d['brickName'];
					$json['swpData']['chemistDetails'][$l]['chemistData'][$k]['chemistId'] = $d['chemistId'];
					$json['swpData']['chemistDetails'][$l]['chemistData'][$k]['chemistName'] = strtoupper(esc($d['chemistName']));
					$json['swpData']['chemistDetails'][$l]['chemistData'][$k]['station'] = $d['station'];
					$json['swpData']['chemistDetails'][$l]['chemistData'][$k]['townId'] = $d['townId'];
					$json['swpData']['chemistDetails'][$l]['chemistData'][$k]['townName'] = $d['townname'];
					$k++;
				}
               $l++;
			} 
		}

		if($chemfoundflag==0)
		{
			$json['swpData']['chemistDetails']=array();
		}

		$l=0;$foundflag=0;
		for($i=1;$i<=31;$i++){
			
			//prathmesh added bricks.deleted=0 in below query ticket 0389381 16-4-2018
			
			$query="SELECT touringdetails.patch_brick AS brickId,bricks.brickname AS brickName,touringday.`day` AS `day`,stckcontactdetails.contactid AS stockistId,stckcontactdetails.stockistname AS stockistName, towns.station as station, towns.townid as townId,towns.townname FROM touringday INNER JOIN touringdetails ON touringdetails.id = touringday.daykey inner join bricks on touringdetails.patch_brick = bricks.brickid inner join stckcontactdetails on touringdetails.patch_brick=stckcontactdetails.brick INNER JOIN townbrickassociation ON stckcontactdetails.brick = townbrickassociation.brickid INNER JOIN towns ON townbrickassociation.townid = towns.townid INNER JOIN touringyearmonth ON touringday.crmid = touringyearmonth.crmid WHERE  stckcontactdetails.deleted=0 and stckcontactdetails.brick=touringdetails.patch_brick AND touringday.`day`=$i AND touringyearmonth.crmid ='$crmid' and bricks.deleted=0 GROUP BY stockistId";
			$result = mysql_query($query) or die('query 3'.mysql_error());
			$rows = mysql_num_rows($result);
			$found = 0;
			$k=0;
			
			if($rows > 0){
				$foundflag=1;
				while($d = mysql_fetch_assoc($result)){
					$json['swpData']['stockistDetails'][$l]['day'] = $d['day'];
					$json['swpData']['stockistDetails'][$l]['date'] = $monthyear."-".$i;
					$json['swpData']['stockistDetails'][$l]['stockistData'][$k]['brickId'] = $d['brickId'];
					$json['swpData']['stockistDetails'][$l]['stockistData'][$k]['brickName'] = "";
					$json['swpData']['stockistDetails'][$l]['stockistData'][$k]['stockistId'] = $d['stockistId'];
					$json['swpData']['stockistDetails'][$l]['stockistData'][$k]['stockistName'] = strtoupper(esc($d['stockistName']));
					$json['swpData']['stockistDetails'][$l]['stockistData'][$k]['station'] = $d['station'];
					$json['swpData']['stockistDetails'][$l]['stockistData'][$k]['townId'] = $d['townId'];
					$json['swpData']['stockistDetails'][$l]['stockistData'][$k]['townName'] = $d['townname'];
					$k++;
				}
                   $l++;
			}
			 // else
    //          {
    //              $json['swpData']['stockistDetails'][$l]['day'] = $i;
				// $json['swpData']['stockistDetails'][$l]['date'] = $monthyear."-".$i;
				// $json['swpData']['stockistDetails'][$l]['stockistData'] = array();
    //              $l++;
    //          }
		}

		if($foundflag==0)
		{
			$json['swpData']['stockistDetails']=array();
		}


				$l=0;$insdfoundflag=0;
		for($i=1;$i<=31;$i++){
			
			
			 $query="SELECT touringdetails.patch_brick AS brickId,touringday.`day` AS `day`,contactdetails.contactid AS contactId, trim(concat(trim(contactdetails.firstname),' ',trim(contactdetails.lastname))) AS contactName, towns.station as station, towns.townid as townId FROM touringday INNER JOIN touringdetails ON touringdetails.id = touringday.daykey INNER JOIN touringcontacts ON touringcontacts.id = touringdetails.pid INNER JOIN contactdetails ON contactdetails.contactid = touringcontacts.contactid INNER JOIN townbrickassociation ON contactdetails.brick = townbrickassociation.brickid INNER JOIN towns ON townbrickassociation.townid = towns.townid INNER JOIN touringyearmonth ON touringday.crmid = touringyearmonth.crmid WHERE touringcontacts.contacttype = 'DR' AND touringday.`day` =$i and contactdetails.deleted=0 and contactdetails.brick=touringdetails.patch_brick AND touringyearmonth.crmid = '$crmid'  GROUP BY contactId";
			$result = mysql_query($query) or die('query insd'.mysql_error());
			$rows = mysql_num_rows($result);
			$found = 0;

			$k=0;
			
			if($rows > 0){

				$insdfoundflag=1;

				while($d = mysql_fetch_assoc($result)){
					$json['swpData']['insdDetails'][$l]['day'] = $d['day'];
					$json['swpData']['insdDetails'][$l]['date'] = $monthyear."-".$i;
					$json['swpData']['insdDetails'][$l]['insdData'][$k]['brickId'] = $d['brickId'];
					$json['swpData']['insdDetails'][$l]['insdData'][$k]['brickName'] = "";
					$json['swpData']['insdDetails'][$l]['insdData'][$k]['insdId'] = $d['contactId'];
					$json['swpData']['insdDetails'][$l]['insdData'][$k]['insdName'] = strtoupper(esc($d['contactName']));
					$json['swpData']['insdDetails'][$l]['insdData'][$k]['station'] = $d['station'];
					$json['swpData']['insdDetails'][$l]['insdData'][$k]['townId'] = $d['townId'];
					$json['swpData']['insdDetails'][$l]['insdData'][$k]['townName'] = "";
					$k++;
				}
                   $l++;
			}
		}

		if($insdfoundflag==0)
		{
			$json['swpData']['insdDetails']=array();
		}

		$l=0;$actfoundflag==0;
		for($i=1;$i<=31;$i++){
		 	$query="SELECT stdactivity.activity_name,stdactivity.id as activityid,touringday.`day` AS `day`  FROM touringday INNER JOIN touringdetails ON touringdetails.id = touringday.daykey   inner join stdactivity on touringdetails.activity = stdactivity.id INNER JOIN touringyearmonth ON touringday.crmid = touringyearmonth.crmid WHERE  touringday.`day`=$i  AND touringyearmonth.crmid = '$crmid' group by stdactivity.id";
			
			//echo"<br/><br/>";
			$result = mysql_query($query) or die('query 4'.mysql_error());
			$result1 = mysql_query($query);
			$rows = mysql_num_rows($result);
			$found = 0;
			$k=0;
			$act_array = array();
			$p = 0;
			if($rows > 0){
			
			while($act = mysql_fetch_assoc($result1)){
			$act_array[$p] = $act['activityid'];
			$p++;
			}
			}
			$flag_act = 0;
			
		//	echo count($act_array);
			if(count($act_array) == 1 && !(in_array('1',$act_array)))
			{
			  $flag_act=1; 
			}
			if(count($act_array) > 1 && in_array('1',$act_array))
			{
			$flag_act=2; 
			}
			if(count($act_array)> 1 && !(in_array('1',$act_array)))
			{
			$flag_act=3; 
			}
			
			//echo "<br>---".$flag_act."====".$rows;
			if($rows > 0 && $flag_act >0){
				$actfoundflag=1;	
				while($d = mysql_fetch_assoc($result)){
					$json['swpData']['activityDetails'][$l]['day'] = $d['day'];
					$json['swpData']['activityDetails'][$l]['activityName'] = strtoupper($d['activity_name']);
					$json['swpData']['activityDetails'][$l]['date'] = $monthyear."-".$i;

					if($d['activityid'] != 2){
						$finalActId = $d['activityid'];
					}

					$json['swpData']['activityDetails'][$l]['activityId'] = $finalActId;

					$json['swpData']['activityDetails'][$l]['activityData'][$k]['activityId'] = $d['activityid'];
					$json['swpData']['activityDetails'][$l]['activityData'][$k]['activityFlag'] = $flag_act;

					

					$k++;
				}
                   $l++;
			} 
		}

		if($actfoundflag==0)
		{
			$json['swpData']['activityDetails']=array();
		}







		$l=0;
		$p=0;
		$leave_presence=0; $leaveDetailsArray=array();
		for($i=1;$i<=31;$i++){
			$tempdate = $y."-".$m."-".$i;

			$Leave_query="select leaveapplication.reason as reason, 
			leaveapplication.leavetype as leavetype
			from leaveapplication 
			inner join leavetype on leaveapplication.leavetype=leavetype.leaveid 
			left outer join leavestatus on leaveapplication.applno=leavestatus.applno 
			where ('".$tempdate."' between frm_date and to_date) 
			and emp_id='".$userid."' and leaveapplication.deleted=0 
			and leaveapplication.app_del=0  
			and leavestatus.approved!='Rejected'";
			//echo "<br><br>====".$Leave_query;
			$leaveres = mysql_query($Leave_query);
			$leaverows = mysql_num_rows($leaveres);
			
			if($leaverows>0)
			{
				while($x = mysql_fetch_assoc($leaveres)){
					$leaveDetailsArray[$p]['day'] = date('Y-n-j',strtotime($tempdate));
					$leaveDetailsArray[$p]['reason'] = esc($x['reason']);
					$leaveDetailsArray[$p]['leavetype'] = $x['leavetype'];
					$p++;
					$leave_presence = 1;
				}
				$l++;
			}

		}	

		if($leave_presence==0){
				$json['swpData']['leaveDetails'] = array();			
			}
			else{
				$json['swpData']['leaveDetails'] = array_merge($leaveDetailsArray);
			}



		$l=0;
		$p=0;
		$holiday_presence = 0;
		$holidayDetailsArray=array();
			$Holiday_query="select holidaytemplatedetails.name, holidaytemplatedetails.date from holidaytemplatedetails 
			inner join user2holidaytemplate on user2holidaytemplate.holidaytemplateid=holidaytemplatedetails.id 
			where user2holidaytemplate.userid='".$userid."' and MONTH(holidaytemplatedetails.date)='".$m."' 
			and yearid='".$y."' and YEAR(holidaytemplatedetails.date)='".$y."' ";
			//echo $Holiday_query;exit();
			$holresult = mysql_query($Holiday_query);
			$holrows = mysql_num_rows($holresult);

			if($holrows>0){
				while($x = mysql_fetch_assoc($holresult)){
				$holidayDetailsArray[$p]['day'] = date('Y-n-j',strtotime(esc($x['date'])));
				$holidayDetailsArray[$p]['holidaytype'] = "PUBLIC HOLIDAY";
				$holidayDetailsArray[$p]['holiday'] = esc($x['name']);
				$holiday_presence = 1;
				$p++;
				}
			}	
			if($holiday_presence==0){
				$json['swpData']['holidayDetails'] = array();			
			}
			else{
				$json['swpData']['holidayDetails'] = array_merge($holidayDetailsArray);
			}




		switch($code){
			case 1:
				$message="No MTP data available";$code=0;
				break;
			
			case 0:	$message="Master Tour Plan downloaded successfully";
				break;
	}

	$json['message']['message']=$message;
	$json['code']['code']=$code;
	 echo json_encode($json);
	 }






?>