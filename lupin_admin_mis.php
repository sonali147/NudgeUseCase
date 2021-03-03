<?php

/* $host_name = "172.19.19.41";
$user_name = "lupinlive";
$passwd = "Ep68iwmcH";
$db_name = "lupinlive";  */

/* $host_name = "148.251.6.5";
$user_name = "lupinuat";
$passwd = "dgyailggfalhsa3d4";
$db_name = "lupinuat"; */

require_once("script_config.php");

//OPEN CONNECTION TO SERVER.
$link = mysql_connect($host_name,$user_name,$passwd);
if(!$link) {
    die('Could not connect: ' . mysql_error());
}
else
{
	echo "Database connected";
}

//SELECT DATABASE.
$db_selected = mysql_select_db($db_name, $link);
if (!$db_selected) 
{
    die ('Can\'t use $db_name : ' . mysql_error());
}

// Add After DB Connection. Start
// Change cron name in below query
/*$getCronID = "SELECT cronid FROM cron_script_master WHERE cron_name='lupin_admin_mis';";
$getCronID_res = mysql_query($getCronID);
$cronid = mysql_result($getCronID_res,0,'cronid');

echo "<br>=====CronScriptStartTime=====".$cron_start_time="INSERT INTO cron_script_execution(pid,cronid,`date`,start_time,success,deleted) VALUES('','".$cronid."','".date('Y-m-d')."','".date('Y-m-d h:i:s')."',0,0);";					
$cron_start_time_res=mysql_query($cron_start_time);*/
// Change cron name in below query


echo "<br> Date 1:".$currentDatePar=date('d');
echo "<br> Date 2:".$currentDayPar=date('D');
$cronNamePar='lupin_admin_mis';

$getCronID = "select * from cron_script_master where Active=1 and execution_flag=0 and (frequency_execution='D' or (frequency_execution='M' and FIND_IN_SET(".$currentDatePar.",execution_details) > 0 ) or (frequency_execution='W' and FIND_IN_SET('".$currentDayPar."',execution_details) > 0)) and cron_name='".$cronNamePar."' order by sequence";
$getCronID_res = mysql_query($getCronID);
if(mysql_num_rows($getCronID_res)>0)
{
$cronid = mysql_result($getCronID_res,0,'cronid');
}
else
{
echo "<br>Cron execution not active for ".date('Y-m-d')." Day : ".$currentDayPar;
	exit;
}

echo "<br>=====CronScriptStartTime=====".$cron_start_time="INSERT INTO cron_script_execution(pid,cronid,`date`,start_time,success,deleted) VALUES('','".$cronid."','".date('Y-m-d')."','".date('Y-m-d h:i:s')."',0,0);";					
$cron_start_time_res=mysql_query($cron_start_time);
//End


echo "<br><br>====Started at :'".date('l jS \of F Y h:i:s A')."'";
echo "<b>Admin Mis</B>";

echo"<br><br>===1==".$drop_n7="drop table if exists n7";
$res=mysql_query($drop_n7) or die('Error 1'.mysql_error());

echo"<br><br>===2==".$create_n7="create table n7
select users.id,cast(users.reports_to_id as signed) as report1,00000000 as report2,00000000 as report3,00000000 as report4,00000000 as report5 ,00000000 as report6 ,00000000 as report7 from users,user2role,role2profile,role
where  users.id=user2role.userid
and user2role.roleid =  role2profile.roleid
and cast(users.reports_to_id as signed) > 0
and role2profile.profileid = 5
and users.deleted =0
and role.roleid=user2role.roleid
and users.division = role.division";
$res=mysql_query($create_n7) or die('Error 2'.mysql_error());

echo"<br><br>===3==".$update_n7_1="update n7,(
select users.id as report1,cast(users.reports_to_id as signed) as report2 from users,n7
 where users.id = n7.report1
and users.deleted =0
 group by 1) p1
set n7.report2 = p1.report2
where n7.report1 = p1.report1";
$res=mysql_query($update_n7_1) or die('Error 3'.mysql_error());

echo"<br><br>===4==".$update_n7_2="update n7,(
select users.id as report2,cast(users.reports_to_id as signed) as report3 from users,n7
 where users.id = n7.report2
and users.deleted =0
 group by 1) p1
set n7.report3 = p1.report3
where n7.report2 = p1.report2";
$res=mysql_query($update_n7_2) or die('Error 4'.mysql_error());

echo"<br><br>===5==".$update_n7_3="update n7,(
select users.id as report3,cast(users.reports_to_id as signed) as report4 from users,n7
 where users.id = n7.report3
and users.deleted =0
 group by 1) p1
set n7.report4 = p1.report4
where n7.report3 = p1.report3";
$res=mysql_query($update_n7_3) or die('Error 5'.mysql_error());


echo"<br><br>===6==".$update_n7_4="update n7,(
select users.id as report4,cast(users.reports_to_id as signed) as report5 from users,n7
 where users.id = n7.report4
and users.deleted =0
 group by 1) p1
set n7.report5 = p1.report5
where n7.report4 = p1.report4";
$res=mysql_query($update_n7_4) or die('Error 6'.mysql_error());

echo"<br><br>===7==".$update_n7_5="update n7,(
select users.id as report5,cast(users.reports_to_id as signed) as report6 from users,n7
 where users.id = n7.report5
and users.deleted =0
 group by 1) p1
set n7.report6 = p1.report6
where n7.report5 = p1.report5";
$res=mysql_query($update_n7_5) or die('Error 7'.mysql_error());

echo"<br><br>===8==".$update_n7_6="update n7,(
select users.id as report6,cast(users.reports_to_id as signed) as report7 from users,n7
 where users.id = n7.report6
and users.deleted =0
 group by 1) p1
set n7.report7 = p1.report7
where n7.report6 = p1.report6";
$res=mysql_query($update_n7_6) or die('Error 8'.mysql_error());

$curr_date=date('Y-m-d');
$curr_dt=explode('-',$curr_date);

$prev_date=date('Y-m-d',mktime(0,0,0,$curr_dt[1]-1,'01',$curr_dt[0]));
$prev_dt=explode('-',$prev_date);



for($i=0;$i<2;$i++)
{
  
  $curr_mnt=date('Y-m-d');
  

    if($i==0)
	{
	 $month=$prev_dt[1];
	 $year=$prev_dt[0];
	 $numdays =  date("t",mktime(0,0,0,$month,01,$year));
	 echo "<br><br>startdate===".$startdate=$year."-".$zero.$month."-01";
	 echo "<br><br>enddate===".$enddate=$year."-".$zero.$month."-".$numdays;
	 
     echo"<br><br>===9==".$del_adminmis_prev="delete rpt from adminmis rpt
	 inner join users on users.id=rpt.userid 
	 where month ='".$month."' and year ='".$year."' and users.terr_joining_date <= '".$enddate."'";
	 $res=mysql_query($del_adminmis_prev) or die('Error 9'.mysql_error());
	
	}
	else
	{
	 $month=$curr_dt[1];
	 $year=$curr_dt[0];
   $numdays =  date("t",mktime(0,0,0,$month,01,$year));
   
   echo "<br><br>startdate===".$startdate=$year."-".$zero.$month."-01";
   echo "<br><br>enddate===".$enddate=$year."-".$zero.$month."-".$numdays;
	 
	 echo"<br><br>===10==".$del_adminmis_curr="delete from adminmis where month ='".$month."' and year ='".$year."'";
	 $res=mysql_query($del_adminmis_curr) or die('Error 10'.mysql_error());
	}
	
	$new_date=$year."-".$month."-01";
	echo"<br><br>===11==".$finacialyear_query="select financial_year from financialyear where '".$new_date."' between from_date and to_date";
	$res_fyr=mysql_query($finacialyear_query) or die('Error 11'.mysql_error());
	$fyear=mysql_result($res_fyr,0,'financial_year');
	
/////////////////Insert into adminmis - Master
/*Commentec on 23-06-2020
echo"<br><br>===12==".$insert_adminmis="insert into adminmis
select '' as srno,id as userid,users.division as division,'".$curr_date."' as datetimestamp, '' as cmonth,'".$month."'  as month,
'".$year."' as year,concat_ws( ' ',users.first_name,users.middle_name, last_name) as fieldstaff,
users.user_name as employeesapcode, division.name as division1,users.title as designation,users.headquater as hq,
'0000-00-00' as mtpapprvedon, 31 as daysinmonth,
0 as fieldworkingdays,0 as leaves,0 as lop,0 as adminworkingdays,0 as holi_sun,0 as notfiled,0 as drsinlistspl,0 as drsinlistrpl,
0 as drsinlisttpl,0 as drsinlisttotal,0 as nooddrsmetspl,0 as nooddrsmetrpl,0 as nooddrsmettpl,0 as nooddrsmettotal, 
0 as noofdrcalls,0 as drcallavg, 0 as drmissedspl, 0 as drmissedrpl,0 as drmissedtpl, 0 as drmissedtotal,
0 as missedcalls,0 as cheminlist, 0 as chemcalls, 0 as chemcallavg, 0 as totalpob,curdate() as created_date,
if(month(terr_joining_date)= ".$month." and year(terr_joining_date)=".$year.", 1,0) as flag,terr_joining_date,
0 as spl_dr_met_once, 0 as spl_dr_met_twice, 0 as spl_dr_met_thrice, 0 as spl_dr_met_more_thrice,
0 as rpl_dr_met_once, 0 as rpl_dr_met_twice, 0 as rpl_dr_met_thrice, 0 as rpl_dr_met_more_thrice,
0 as tpl_dr_met_once, 0 as tpl_dr_met_twice, 0 as tpl_dr_met_thrice, 0 as tpl_dr_met_more_thrice,
'' as hq_name,'' as patch_name,0000 as chem_unique_met,0000 as chem_repeat,0000 as chem_cov,
0 as activityworkingdays, 0 as transitworkingdays, 0 as otherworkingdays ,0 as adminworkingdaysnew,
0 as totalclassa,0 as totalclassb,0 as totalclassc,0 as totalclassamet,0 as totalclassbmet,0 as totalclasscmet,
0 as total_coveragea,0 as total_coverageb,0 as total_coveragec,0 as compliance_a,0 as compliance_b,0 as compliance_c,
0 as compliance_suma,0 as compliance_sumb,0 as compliance_sumc,
users.reports_to_id as reports_to_id,
0 as daysinmonth_sum,
0 as holi_sun_sum,
0 as fieldworkingdays_sum,
0 as noofdrcalls_sum,
0 as drcallavg_sum,
0 as chemcalls_sum,
0 as chemcallavg_sum,
0 as nooddrsmettotal_avg,0 as group_id,
patches.patchsapcode as territory_code,
role2profile.profileid as profileid,
0 as spl_freqcoverage,
0 as rpl_freqcoverage,
0 as tpl_freqcoverage
from users, division,user2role ,role2profile,patches
where users.division = division.divisionid
and users.deleted =0 
and users.division <> 0  
and terr_joining_date <= '".$enddate."'
and users.id=user2role.userid
and user2role.roleid=role2profile.roleid
and profileid='5' 
and patches.patchid= users.patch";*/

echo"<br><br>===12==".$insert_adminmis="insert into adminmis
select '' as srno,id as userid,users.division as division,'".$curr_date."' as datetimestamp, '' as cmonth,'".$month."'  as month,
'".$year."' as year,concat_ws( ' ',users.first_name,users.middle_name, last_name) as fieldstaff,
users.user_name as employeesapcode, division.name as division1,users.title as designation,users.headquater as hq,
'0000-00-00' as mtpapprvedon, 31 as daysinmonth,
0 as fieldworkingdays,0 as leaves,0 as lop,0 as adminworkingdays,0 as holi_sun,0 as notfiled,0 as drsinlistspl,0 as drsinlistrpl,
0 as drsinlisttpl,0 as drsinlistqpl,0 as drsinlisttotal,0 as nooddrsmetspl,0 as nooddrsmetrpl,0 as nooddrsmettpl,0 as nooddrsmetqpl,0 as nooddrsmettotal, 
0 as noofdrcalls,0 as drcallavg, 0 as drmissedspl, 0 as drmissedrpl,0 as drmissedtpl,0 as drmissedqpl, 0 as drmissedtotal,
0 as missedcalls,0 as cheminlist, 0 as chemcalls, 0 as chemcallavg, 0 as totalpob,curdate() as created_date,
if(month(terr_joining_date)= ".$month." and year(terr_joining_date)=".$year.", 1,0) as flag,terr_joining_date,
0 as spl_dr_met_once, 0 as spl_dr_met_twice, 0 as spl_dr_met_thrice,0 as spl_dr_met_four, 0 as spl_dr_met_more_four,
0 as rpl_dr_met_once, 0 as rpl_dr_met_twice, 0 as rpl_dr_met_thrice, 0 as rpl_dr_met_four, 0 as rpl_dr_met_more_four,
0 as tpl_dr_met_once, 0 as tpl_dr_met_twice, 0 as tpl_dr_met_thrice, 0 as tpl_dr_met_four, 0 as tpl_dr_met_more_four,
0 as qpl_dr_met_once, 0 as qpl_dr_met_twice, 0 as qpl_dr_met_thrice, 0 as qpl_dr_met_four, 0 as qpl_dr_met_more_four,
'' as hq_name,'' as patch_name,0000 as chem_unique_met,0000 as chem_repeat,0000 as chem_cov,
0 as activityworkingdays, 0 as transitworkingdays, 0 as otherworkingdays ,0 as adminworkingdaysnew,
0 as totalclassa,0 as totalclassb,0 as totalclassc,0 as totalclassamet,0 as totalclassbmet,0 as totalclasscmet,
0 as total_coveragea,0 as total_coverageb,0 as total_coveragec,0 as compliance_a,0 as compliance_b,0 as compliance_c,
0 as compliance_suma,0 as compliance_sumb,0 as compliance_sumc,
users.reports_to_id as reports_to_id,
0 as daysinmonth_sum,
0 as holi_sun_sum,
0 as fieldworkingdays_sum,
0 as noofdrcalls_sum,
0 as drcallavg_sum,
0 as chemcalls_sum,
0 as chemcallavg_sum,
0 as nooddrsmettotal_avg,0 as group_id,
role2profile.profileid as profileid,
patches.patchsapcode as territory_code,
0 as spl_freqcoverage,
0 as rpl_freqcoverage,
0 as tpl_freqcoverage,
0 as qpl_freqcoverage,
0 as drsinlist_hq,
0 as drsinlist_exhq,
0 as drsinlist_os,
0 as nooddrsmet_hq,
0 as nooddrsmet_exhq,
0 as nooddrsmet_os,
0 as drmissed_hq,
0 as drmissed_exhq,
0 as drmissed_os,
0 as noofdrcalls_hq,
0 as noofdrcalls_exhq,
0 as noofdrcalls_os,
0 as fwdays_hq,
0 as fwdays_exhq,
0 as fwdays_os
from users, division,user2role ,role2profile,patches
where users.division = division.divisionid
and users.deleted =0 
and users.division <> 0  
and terr_joining_date <= '".$enddate."'
and users.id=user2role.userid
and user2role.roleid=role2profile.roleid
and profileid='5' 
and patches.patchid= users.patch";
$res=mysql_query($insert_adminmis) or die('Error 12'.mysql_error());


///////////chemist met count

echo"<br><br>===13==".$update_adminmis_1="update adminmis , (
select p1.smownerid,count(p1.contactid) as cnt
                         from (
 select dcrs_main.smownerid,dcrs_main.date,dcrs_contact.contactid
                                  from dcrs_main, dcrs_contact,dcrs,bcntscontactdetails,users,user2role ,role2profile 
                                  where dcrs_main.deleted=0 
                                  and dcrs_main.month=".$month."
                                  and dcrs_main.year=".$year."
								  and users.id=dcrs_main.smownerid
								  and user2role.userid=users.id
								  and role2profile.roleid=user2role.roleid
								  and role2profile.profileid =5
                                  and dcrs_contact.contacttype ='C' 
                                  and dcrs_contact.deleted =0
                                  and dcrs_main.mainid=dcrs.mainid
                                  and dcrs.pid=dcrs_contact.mainid
								  and dcrs.status='Pending'
								  and dcrs_main.status='Pending'
                                  and dcrs_contact.contactid =bcntscontactdetails.contactid
								  and dcrs_main.smownerid=bcntscontactdetails.smownerid  
                                  and (bcntscontactdetails.created_date<='".$enddate."' or bcntscontactdetails.created_date='0000-00-00')
                                  and ((bcntscontactdetails.deleted=1 and bcntscontactdetails.deleted_date>='".$startdate."') or (bcntscontactdetails.deleted=0)) 
                                  group by dcrs_main.smownerid,dcrs_contact.contactid) p1
                      group by p1.smownerid
) p2
set adminmis.chem_unique_met = p2.cnt 
where adminmis.userid= p2.smownerid
and adminmis.month=".$month."
and adminmis.year =".$year."";
$res=mysql_query($update_adminmis_1) or die('Error 13'.mysql_error());


////Admin working days

echo"<br><br>===14==".$update_adminmis_2="update adminmis, (
select p1.smownerid, sum(if(p1.fullday_type<> '0',1,0))+sum(if(p1.fullday_type='0',0.5,0)) as cnt from (
    select dcrs_main.mainid,dcrs.type,dcrs_main.smownerid,dcrs.fullday_type   from  dcrs_main,dcrs,users
                                         where month=".$month."
                                         and year=".$year."
                                         and dcrs_main.deleted=0
                                         and dcrs_main.mainid = dcrs.mainid
										 and users.id=dcrs_main.smownerid
                                         and  dcrs.deleted=0
                                         and dcrs_main.status='Pending'
                                         and dcrs.status='Pending'
										and dcrs.type='ACTIVITY' 
                                         group by dcrs_main.date,dcrs_main.smownerid,dcrs.firsthalf_type,secondhalf_type,fullday_type ) p1
group by p1.smownerid) t2
set adminmis.adminworkingdays = t2.cnt
where adminmis.userid= t2.smownerid
and adminmis.month =".$month."
and adminmis.year=".$year."";
$res=mysql_query($update_adminmis_2) or die('Error 14'.mysql_error());

///Field working days
echo"<br><br>===15==".$update_adminmis_3="update adminmis, (
select p1.smownerid,sum(if(p1.type='DCRS',1,0)) as cnt from (
    select dcrs_main.mainid,dcrs.type,dcrs_main.smownerid   from  dcrs_main,dcrs,users
                                         where month=".$month."
                                         and year=".$year."
                                         and dcrs_main.deleted=0
                                         and dcrs_main.mainid = dcrs.mainid
										 and users.id=dcrs_main.smownerid
                                         and  dcrs.deleted=0
                                         and dcrs_main.status='Pending'
                                         and dcrs.status='Pending'
                                         and ( dcrs.fullday_type is null or dcrs.fullday_type ='0')
										 and dcrs.type='DCRS'
                                         group by dcrs_main.date,dcrs_main.smownerid,dcrs.type ) p1
group by p1.smownerid) t2
set adminmis.fieldworkingdays = t2.cnt
where adminmis.userid= t2.smownerid
and adminmis.month =".$month."
and adminmis.year=".$year."";
$res=mysql_query($update_adminmis_3) or die('Error 15'.mysql_error());


echo"<br><br>===16==".$update_adminmis_4="update adminmis, (
select p1.smownerid, sum(if(p1.fullday_type<> '0',1,0))+sum(if(p1.fullday_type='0',0.5,0)) as cnt from 
  (select dcrs_main.mainid,a.fullday_type,dcrs_main.smownerid   from  dcrs_main,dcrs ,dcrs a ,users
                                         where month=".$month."
                                         and year=".$year."
                                         and dcrs_main.deleted=0
                                         and dcrs_main.mainid = dcrs.mainid
										 and users.id=dcrs_main.smownerid
                                         and  dcrs.deleted=0
										 and dcrs_main.status='Pending' 
					                     and dcrs.status='Pending' 
                                        and dcrs.type='DCRS'
and dcrs.mainid=a.mainid
and a.type='ACTIVITY'
group by dcrs_main.date,dcrs_main.smownerid)p1
group by p1.smownerid) t2
set adminmis.fieldworkingdays = fieldworkingdays-t2.cnt
where adminmis.userid= t2.smownerid
and adminmis.month =".$month."
and adminmis.year=".$year."";
$res=mysql_query($update_adminmis_4) or die('Error 16'.mysql_error());

///To get count of holidays / Leaves /sundays
echo"<br><br>===17==".$drop_xx1="drop table if exists xx1";
$res=mysql_query($drop_xx1) or die('Error 17'.mysql_error());

echo"<br><br>===18==".$create_xx1_1="create table  xx1
select users.id,daymonthyear.date1,space(10) as type1 from users,daymonthyear
where users.deleted =0
and month(daymonthyear.date1) =".$month." and year(daymonthyear.date1) =".$year."";
$res=mysql_query($create_xx1_1) or die('Error 18'.mysql_error());

echo"<br><br>===19==".$create_xx1_2="create index id on xx1 (id)";
$res=mysql_query($create_xx1_2) or die('Error 19'.mysql_error());

echo"<br><br>===20==".$update_xx1_1="update xx1,(
select smownerid,date as date1 from dcrs_main
where deleted =0 and dcrs_main.status='Pending' 
and month=".$month."
and year =".$year."
group by 1,2) p1
set xx1.type1 ='DCR'
where xx1.id = p1.smownerid
and xx1.date1= p1.date1";
$res=mysql_query($update_xx1_1) or die('Error 20'.mysql_error());


echo"<br><br>===21==".$update_xx1_2="update xx1,(
select leaveapplication.emp_id,daymonthyear.date1 from leaveapplication,daymonthyear
 where  daymonthyear.date1 between leaveapplication.frm_date and to_date 
and leaveapplication.leavetype='LWP'
and leaveapplication.deleted=0
 and month(daymonthyear.date1) =".$month." and year(daymonthyear.date1) =".$year.") p1
 set xx1.type1 ='LWP' 
where  xx1.id = p1.emp_id
and xx1.date1= p1.date1
and xx1.type1 =''";
$res=mysql_query($update_xx1_2) or die('Error 21'.mysql_error());

echo"<br><br>===22==".$update_xx1_3="update xx1,(
select leaveapplication.emp_id,daymonthyear.date1 from leaveapplication,daymonthyear
 where  daymonthyear.date1 between leaveapplication.frm_date and to_date 
 and leaveapplication.leavetype <> 'LWP'
 and leaveapplication.deleted=0
 and month(daymonthyear.date1) =".$month." and year(daymonthyear.date1) =".$year.") p1
 set xx1.type1 ='LEAVE' 
where  xx1.id = p1.emp_id
and xx1.date1= p1.date1
and xx1.type1 =''";
$res=mysql_query($update_xx1_3) or die('Error 22'.mysql_error());


echo"<br><br>===23==".$update_xx1_4="update xx1,(
select holidaytemplatedetails.date as date1,user2holidaytemplate.userid from holidaytemplate 
   inner join holidaytemplatedetails on holidaytemplate.id=holidaytemplatedetails.id
   inner join user2holidaytemplate
    on user2holidaytemplate.holidaytemplateid=holidaytemplatedetails.id
   inner join user2role on user2holidaytemplate.userid=user2role.userid
   inner join role2profile on user2role.roleid=role2profile.roleid
   inner join users on users.id=user2role.userid
    where  holidaytemplate.deleted=0
    
    and month(date)=".$month." and year(date)=".$year.") p1
 set xx1.type1 ='HOLIDAY' 
where  xx1.id = p1.userid
and xx1.date1= p1.date1
and xx1.type1 =''";
$res=mysql_query($update_xx1_4) or die('Error 23'.mysql_error());

echo"<br><br>===24==".$update_xx1_5="update xx1,users set xx1.type1 ='WEEK_HOL'
 where xx1.id=users.id and xx1.type1 ='' and dayofweek(xx1.date1) =1 and users.weekstart=0;";
$res=mysql_query($update_xx1_5) or die('Error 24'.mysql_error());


echo"<br><br>===25==".$update_xx1_6="update xx1,users set xx1.type1 ='WEEK_HOL'
 where xx1.id=users.id and xx1.type1 ='' and dayofweek(xx1.date1) =7 and users.weekstart=6;";
$res=mysql_query($update_xx1_6) or die('Error 25'.mysql_error());

echo"<br><br>===26==".$update_adminmis_5="update adminmis,(
select xx1.id, sum(if(xx1.type1='WEEK_HOL',1,0)) as sun_cnt, sum(if(xx1.type1='HOLIDAY',1,0)) as hol_cnt, 
sum(if(xx1.type1='LEAVE',1,0)) as leavel_cnt, sum(if(xx1.type1='LWP',1,0)) as lwp_cnt
 from xx1 where type1 in ('WEEK_HOL','HOLIDAY','LEAVE','LWP')
group by 1) p1
set adminmis.holi_sun = p1.hol_cnt+p1.sun_cnt, adminmis.leaves = p1.leavel_cnt,
adminmis.lop=p1.lwp_cnt
where adminmis.userid = p1.id
and adminmis.month =".$month."
and adminmis.year=".$year."";
$res=mysql_query($update_adminmis_5) or die('Error 26'.mysql_error());

/////Days for which user have not filed
echo"<br><br>===27==".$update_adminmis_6="update adminmis set notfiled =daysinmonth - adminworkingdays - fieldworkingdays - holi_sun - leaves-lop
where month = ".$month." and year =".$year."";
$res=mysql_query($update_adminmis_6) or die('Error 27'.mysql_error());

//////////////////Total number of Drs in list. SPL+RPL+TPL
//Modified on 23-06-2020
echo"<br><br>===28==".$update_adminmis_7="update adminmis,(
select p2.smownerid as id,users.division,sum(if(substring(p2.pid_new,(length(p2.pid_new)),1)=1,1,0)) as cnt1,
sum(if(substring(p2.pid_new,(length(p2.pid_new)),1)=2,1,0)) as cnt2,
sum(if(substring(p2.pid_new,(length(p2.pid_new)),1)=3,1,0)) as cnt3,
sum(if(substring(p2.pid_new,(length(p2.pid_new)),1)=0,1,0)) as cnt4,
sum(if(substring(p2.pid_new,(length(p2.pid_new)),1) < 5,1,0)) as tot_cnt
  from (select p1.smownerid,p1.contactid,max(p1.pid_new) as pid_new from (
select contactdetails.smownerid, contactdetails.contactid,contactdetails.frequency,contact_frequency_changes.frequencyid,
concat(contact_frequency_changes.pid,mod(contact_frequency_changes.frequencyid,4)) as pid_new,
contact_frequency_changes.pid as pid,applicable_from_date,
if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date) as applicable_to_date,
frequencyname,concat(contactdetails.contactid,contact_frequency_changes.frequencyid) as freq,
space(10) as details
from contactdetails,contact_frequency_changes,visitfrequency,users,user2role,role2profile
where  contactdetails.contactid=contact_frequency_changes.contactid 
and contact_frequency_changes.frequencyid=visitfrequency.frequencyid 
and contactdetails.smownerid=users.id
and users.id=user2role.userid
and user2role.roleid=role2profile.roleid
and profileid='5' 
and applicable_from_date<'".$enddate."'
and  (contactdetails.created_date<='".$enddate."'  or contactdetails.created_date='0000-00-00') 
and ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
and cast('".$enddate."' as date) between applicable_from_date and if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date)) p1
group by 1,2) p2,users
where p2.smownerid = users.id 
group by 1) p3
set adminmis.drsinlisttotal = p3.tot_cnt,
adminmis.drsinlistspl=  p3.cnt1,
adminmis.drsinlistrpl=   p3.cnt2,
adminmis.drsinlisttpl=  p3.cnt3,
adminmis.drsinlistqpl=  p3.cnt4
where adminmis.userid = p3.id
and adminmis.month =".$month."
and adminmis.year=".$year."";
$res=mysql_query($update_adminmis_7) or die('Error 28'.mysql_error());
////////////No of doctor met ->SPL+RPL+TPL Modified on 23-06-2020
echo"<br><br>===29==".$update_adminmis_8="update adminmis,(
select p2.smownerid,'".$month."' as month,".$year." as year,
sum(if(p2.pid_new=1,1,0)) as cnt_1, 
sum(if(p2.pid_new=2,1,0)) as cnt_2, 
sum(if(p2.pid_new=3,1,0)) as cnt_3,  
sum(if(p2.pid_new=4,1,0)) as cnt_4,  


 sum(if(p2.pid_new  >0,1,0)) as tot_cnt  from (
select p1.smownerid,p1.contactid,p1.pid_new,count(p1.pid_new) as cnt from (
     select contactdetails.smownerid,contactdetails.frequency,dcrs_contact.contactid,dcrs_main.date,contact_frequency_changes.pid,
      if(mod(contact_frequency_changes.frequencyid,4)=0,4,mod(contact_frequency_changes.frequencyid,4)) as pid_new,
     applicable_from_date,applicable_to_date ,contact_frequency_changes.frequencyid
                                  from dcrs_main, dcrs_contact,dcrs,contactdetails,contact_frequency_changes,users
                                  where dcrs_main.deleted=0 
                                  and dcrs_main.month=".$month."
                                  and dcrs_main.year=".$year."
                                  and dcrs_contact.contacttype ='D' 
                                  and dcrs_main.mainid=dcrs.mainid
                                  and dcrs.pid=dcrs_contact.mainid
								  and users.id=dcrs_main.smownerid
                                  and dcrs_contact.deleted =0
                                  and dcrs.deleted =0
                                  and dcrs_contact.contactid = contactdetails.contactid
								   and dcrs_main.smownerid=contactdetails.smownerid  
                                  and  contactdetails.contactid= contact_frequency_changes.contactid
                                  and contact_frequency_changes.applicable_from_date < '".$enddate."'
                                  and (created_date<='".$enddate."' or contactdetails.created_date='0000-00-00')
                                  and ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
                                  and dcrs.deleted=0 and dcrs.status='Pending' and dcrs_main.status='Pending' and dcrs_contact.deleted=0 
                                  and cast('".$enddate."' as date) between applicable_from_date 
                                        and if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date) 
                                  group by dcrs_main.smownerid,dcrs_contact.contactid, dcrs_main.date) p1
                      group by 1,2,3 ) p2
group by 1 ) p3
set adminmis.nooddrsmettotal = p3.tot_cnt,
adminmis.nooddrsmetspl = p3.cnt_1,
adminmis.nooddrsmetrpl = p3.cnt_2,
adminmis.nooddrsmettpl = p3.cnt_3,
adminmis.nooddrsmetqpl = p3.cnt_4
where adminmis.userid = p3.smownerid
and adminmis.month=".$month."
and adminmis.year = ".$year."";
$res=mysql_query($update_adminmis_8) or die('Error 29'.mysql_error());
/////Dr call average
echo"<br><br>===30==".$update_adminmis_9="update adminmis, (
select p1.smownerid,count(p1.contactid) as cnt
 from (select dcrs_main.smownerid,dcrs_main.date,dcrs_contact.contactid
 from dcrs_main, dcrs_contact,dcrs,users,contactdetails
 where dcrs_main.deleted=0
  and dcrs_main.month=".$month."
  and dcrs_main.year=".$year."
  and dcrs_contact.contacttype ='D'
  and dcrs_main.mainid=dcrs.mainid
  and dcrs.pid=dcrs_contact.mainid
  and users.id=dcrs_main.smownerid
  and dcrs_contact.contactid=contactdetails.contactid 
  and ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
  and dcrs_contact.deleted =0
  and dcrs.deleted =0
  group by dcrs_main.smownerid,dcrs_main.date,dcrs_contact.contactid) p1
  group by p1.smownerid) p2
set adminmis.noofdrcalls = p2.cnt,
adminmis.drcallavg=if(adminmis.fieldworkingdays <> 0, round(p2.cnt/adminmis.fieldworkingdays,2),0)
where  adminmis.userid = p2.smownerid
and adminmis.month=".$month."
and adminmis.year = ".$year."";
$res=mysql_query($update_adminmis_9) or die('Error 30'.mysql_error());

///Chemist call

echo"<br><br>===31==".$update_adminmis_10="update adminmis, (
select p2.smownerid, ".$month." as month, ".$year." as year, sum(p2.cnt) as cnt from(
select p1.smownerid,count(p1.contactid) as cnt,p1.dd,p1.month,p1.year
                         from (select dcrs_main.smownerid,dcrs_contact.contactid,day(dcrs_main.date) as dd,dcrs_main.month,dcrs_main.year
                                  from dcrs_main, dcrs_contact,dcrs,users,bcntscontactdetails
                                  where dcrs_main.deleted=0 
                                  and dcrs_main.month= ".$month."
                                  and dcrs_main.year=".$year."
                                  and dcrs_contact.contacttype ='C' 
                                  and dcrs_main.mainid=dcrs.mainid
                                  and dcrs.pid=dcrs_contact.mainid
								  and users.id=dcrs_main.smownerid
                  and dcrs_contact.contactid=bcntscontactdetails.contactid 
                  and ((bcntscontactdetails.deleted=1 and bcntscontactdetails.deleted_date>='".$startdate."') or (bcntscontactdetails.deleted=0)) 
                                  and dcrs.deleted =0
                                  and dcrs_contact.deleted =0
                                group by dcrs_main.smownerid,dcrs_contact.contactid,day(dcrs_main.date) ) p1
                   group by p1.smownerid,p1.dd) p2
group by 1) p3
set adminmis.chemcalls = p3.cnt
where  adminmis.userid = p3.smownerid
and adminmis.month=".$month."
and adminmis.year = ".$year."";
$res=mysql_query($update_adminmis_10) or die('Error 31'.mysql_error());

/////////// to get unique chem call, chem cov,chemlist

echo"<br><br>===32==".$drop_t1="drop table if exists t1";
$res=mysql_query($drop_t1) or die('Error 32'.mysql_error);


echo"<br><br>===33==".$create_t1="create table t1
select p1.smownerid,count(p1.contactid) as cnt from (
 select bcntscontactdetails.smownerid,bcntscontactdetails.contactid
                                  from bcntscontactdetails
                                  where (bcntscontactdetails.created_date<='".$enddate."' or bcntscontactdetails.created_date='0000-00-00')
                                  and ((bcntscontactdetails.deleted=1 and bcntscontactdetails.deleted_date>='".$startdate."') or (bcntscontactdetails.deleted=0)) 
 group by bcntscontactdetails.smownerid,bcntscontactdetails.contactid) p1
 group by p1.smownerid";
$res=mysql_query($create_t1) or die('Error 33'.mysql_error());

 
echo"<br><br>===34==".$create_index_t1="create index smownerid on t1 (smownerid);";
$res=mysql_query($create_index_t1) or die('Error 34'.mysql_error());

echo"<br><br>===35==".$update_adminmis_11="update adminmis , t1
set adminmis.cheminlist= t1.cnt
where adminmis.userid= t1.smownerid
and adminmis.month=".$month."
and adminmis.year =".$year."";
$res=mysql_query($update_adminmis_11) or die('Error 35'.mysql_error());


echo"<br><br>===36==".$update_adminmis_12="update adminmis , (
select adminmis.userid,chemcalls,chem_unique_met,if(chemcalls>=chem_unique_met,chemcalls-chem_unique_met,0) as c
from adminmis
where 
adminmis.month=".$month."
and adminmis.year =".$year."
) p1
set adminmis.chem_repeat = p1.c 
where adminmis.userid= p1.userid
and adminmis.month=".$month."
and adminmis.year =".$year."";
$res=mysql_query($update_adminmis_12) or die('Error 36'.mysql_error());

echo"<br><br>===37==".$update_adminmis_13="update adminmis , (
select adminmis.userid,cheminlist,chem_unique_met,round((chem_unique_met/cheminlist)*100,2) as c
from adminmis
where 
adminmis.month=".$month."
and adminmis.year =".$year."
) p1
set adminmis.chem_cov = p1.c 
where adminmis.userid= p1.userid
and adminmis.month=".$month."
and adminmis.year =".$year."";
$res=mysql_query($update_adminmis_13) or die('Error 37'.mysql_error());

////Missed data Modified on  23-06-2020
echo"<br><br>===39==".$update_adminmis_14="update adminmis 
set adminmis.drmissedtotal=adminmis.drsinlisttotal - adminmis.nooddrsmettotal,
adminmis.drmissedspl =adminmis.drsinlistspl -adminmis.nooddrsmetspl,
adminmis.drmissedrpl = adminmis.drsinlistrpl - adminmis.nooddrsmetrpl,
adminmis.drmissedtpl = adminmis.drsinlisttpl - adminmis.nooddrsmettpl,
adminmis.drmissedqpl = adminmis.drsinlistqpl - adminmis.nooddrsmetqpl,
adminmis.chemcallavg=if(adminmis.fieldworkingdays <> 0, round(adminmis.chemcalls /adminmis.fieldworkingdays,2),0)
where adminmis.month =".$month." and adminmis.year=".$year."";
$res=mysql_query($update_adminmis_14) or die('Error 39'.mysql_error());

///////////////////////Missed calls
echo"<br><br>===40==".$drop_tempmiss1="drop table if exists tempmiss1";
$res=mysql_query($drop_tempmiss1) or die('Error 40'.mysql_error());

echo"<br><br>===41==".$drop_tempmiss="drop table if exists tempmiss";
$res=mysql_query($drop_tempmiss) or die('Error 41'.mysql_error());


//Modified on 23-06-2020
echo"<br><br>===42==".$create_tempmiss="create table tempmiss
select p3.*,sum(p3.misscall) as missed from  
 (select *,if((pid_new - cnt)<0,0,(pid_new - cnt)) as misscall from 
			(select p1.smownerid,p1.contactid,p1.firstname,p1.pid_new as aa,count(p1.pid_new) as cnt_aa,
					 if(substring(p1.pid_new,(length(p1.pid_new)),1)=0,4,substring(p1.pid_new,(length(p1.pid_new)),1)) as pid_new, 
					 count( if(substring(p1.pid_new,(length(p1.pid_new)),1)=0,4,substring(p1.pid_new,(length(p1.pid_new)),1))) as cnt
				from 			
				(
				 select dcrs_main.smownerid,contactdetails.frequency,contactdetails.firstname,dcrs_contact.contactid,dcrs_main.date,contact_frequency_changes.pid,
				  if(mod(contact_frequency_changes.frequencyid,4)=0,4,mod(contact_frequency_changes.frequencyid,4)) as pid_new_old,
				  cast(if(mod(contact_frequency_changes.frequencyid,4)=0,4,mod(contact_frequency_changes.frequencyid,4)) as signed) as pid_new11_old,
				  cast(concat(contact_frequency_changes.pid,mod(contact_frequency_changes.frequencyid,4)) as signed) as pid_newest_old,
				  max(cast(concat(contact_frequency_changes.pid,mod(contact_frequency_changes.frequencyid,4)) as signed)) as pid_new,
				 applicable_from_date,applicable_to_date ,contact_frequency_changes.frequencyid
											  from dcrs_main, dcrs_contact,dcrs,contactdetails,contact_frequency_changes,users
											  where dcrs_main.deleted=0 
											  and dcrs_main.month=".$month."
											  and dcrs_main.year=".$year."
											  and dcrs_contact.contacttype ='D' 
											  and dcrs_main.mainid=dcrs.mainid
											  and dcrs.pid=dcrs_contact.mainid
											  and users.id=dcrs_main.smownerid
											  and dcrs_contact.contactid = contactdetails.contactid
											  and  contactdetails.contactid= contact_frequency_changes.contactid
											  and contact_frequency_changes.applicable_from_date < '".$enddate."' 
											  and (created_date<='".$enddate."'  or contactdetails.created_date='0000-00-00')
											  and ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
											  and dcrs.deleted=0 and dcrs.status='Pending' and dcrs_main.status='Pending' and dcrs_contact.deleted=0 
											  and cast('".$enddate."'  as date) between applicable_from_date and if(applicable_to_date='0000-00-00','".$enddate."' ,applicable_to_date) 
											  group by dcrs_main.smownerid,dcrs_contact.contactid, dcrs_main.date ) p1
                       
                        group by p1.smownerid,p1.contactid,p1.pid_new)p2)p3
                                               group by p3.smownerid";
$res=mysql_query($create_tempmiss) or die('Error 42-1'.mysql_error());

echo"<br><br>===42-2==".$create_tempmiss1="create table tempmiss1
select userid,if(missed is not null ,missed,0) + drmissedspl + (drmissedrpl * 2) + (drmissedtpl*3)  + (drmissedqpl*4)  as num from adminmis left outer join tempmiss on adminmis.userid=tempmiss.smownerid
where adminmis.month =".$month."  and year = ".$year."";
$res=mysql_query($create_tempmiss1) or die('Error 42-2'.mysql_error());


echo"<br><br>===43==".$update_adminmis_15="update adminmis,tempmiss1
set missedcalls=num 
where adminmis.month =".$month." and adminmis.year = ".$year."
and tempmiss1.userid=adminmis.userid";
$res=mysql_query($update_adminmis_15) or die('Error 43'.mysql_error());

echo"<br><br>===44==".$drop_tempmiss1="drop table if exists tempmiss1";
$res=mysql_query($drop_tempmiss1) or die('Error 44'.mysql_error());

echo"<br><br>===45==".$drop_tempmiss="drop table if exists tempmiss";
$res=mysql_query($drop_tempmiss) or die('Error 45'.mysql_error());

echo"<br><br>===45==".$drop_m7="drop table if exists m7";
$res=mysql_query($drop_m7) or die('Error 45-2'.mysql_error());

///team reports to data
echo"<br><br>===46==".$create_m7="create table m7
select n7.id, n7.report1 as reports_to_id from  n7
union
select n7.id, n7.report2 as reports_to_id from  n7
union 
select n7.id, n7.report3 as reports_to_id from  n7
union 
select n7.id, n7.report4 as reports_to_id from  n7
union 
select n7.id, n7.report5 as reports_to_id from  n7
union 
select n7.id, n7.report6 as reports_to_id from  n7
union 
select n7.id, n7.report7 as reports_to_id from  n7";
$res=mysql_query($create_m7) or die('Error 46'.mysql_error());

echo"<br><br>===19==".$create_index1="create index id on m7 (id)";
$res=mysql_query($create_index1) or die('create_index1 Error '.mysql_error());

echo"<br><br>===19==".$create_index2="create index reports_to_id on m7 (reports_to_id)";
$res=mysql_query($create_index2) or die('create_index2 Error '.mysql_error());

echo"<br><br>===47==".$insert_m7_1="insert into m7
select n7.report1 as id, n7.report2 as reports_to_id from  n7
union 
select n7.report1 as id, n7.report3 as reports_to_id from  n7
union 
select n7.report1 as id, n7.report4 as reports_to_id from  n7
union
select n7.report1 as id, n7.report5 as reports_to_id from  n7
union 
select n7.report1 as id, n7.report6 as reports_to_id from  n7
union 
select n7.report1 as id, n7.report7 as reports_to_id from  n7";
$res=mysql_query($insert_m7_1) or die('Error 47'.mysql_error());

echo"<br><br>===48==".$insert_m7_2="insert into m7
select n7.report2 as id, n7.report3 as reports_to_id from  n7
union 
select n7.report2 as id, n7.report4 as reports_to_id from  n7
union 
select n7.report2 as id, n7.report5 as reports_to_id from  n7
union
select n7.report2 as id, n7.report6 as reports_to_id from  n7
union 
select n7.report2 as id, n7.report7 as reports_to_id from  n7";
$res=mysql_query($insert_m7_2) or die('Error 48'.mysql_error());

echo"<br><br>===49==".$insert_m7_3="insert into m7
select n7.report3 as id, n7.report4 as reports_to_id from  n7
union 
select n7.report3 as id, n7.report5 as reports_to_id from  n7
union
select n7.report3 as id, n7.report6 as reports_to_id from  n7
union 
select n7.report3 as id, n7.report7 as reports_to_id from  n7";
$res=mysql_query($insert_m7_3) or die('Error 49'.mysql_error());

echo"<br><br>===50==".$insert_m7_4="insert into m7
select n7.report4 as id, n7.report5 as reports_to_id from  n7
union
select n7.report4 as id, n7.report6 as reports_to_id from  n7
union 
select n7.report4 as id, n7.report7 as reports_to_id from  n7";
$res=mysql_query($insert_m7_4) or die('Error 50'.mysql_error());

echo"<br><br>===51==".$insert_m7_5="insert into m7
select n7.report5 as id, n7.report6 as reports_to_id from  n7
union 
select n7.report5 as id, n7.report7 as reports_to_id from  n7";
$res=mysql_query($insert_m7_5) or die('Error 51'.mysql_error());

echo"<br><br>===52==".$insert_m7_6="insert into m7
select n7.report6 as id, n7.report7 as reports_to_id from  n7";
$res=mysql_query($insert_m7_6) or die('Error 52'.mysql_error());


//////////////////////met data
echo"<br><br>===53==".$update_adminmis_16="update adminmis ,(
select p2.smownerid,'".$month."' as month,".$year." as year,sum(if(p2.cnt=1,1,0)) as cnt_1, sum(if(p2.cnt=2,1,0)) as cnt_2, sum(if(p2.cnt=3,1,0)) as cnt_3, sum(if(p2.cnt=4,1,0)) as cnt_4, 
 sum(if(p2.cnt >4,1,0)) as cn_more_4  from (
select p1.smownerid,p1.contactid,p1.pid_new,count(p1.pid_new) as cnt from (
select dcrs_main.smownerid,contactdetails.frequency,dcrs_contact.contactid,dcrs_main.date,contact_frequency_changes.pid,
      if(mod(contact_frequency_changes.frequencyid,4)=0,4,mod(contact_frequency_changes.frequencyid,4)) as pid_new,
     applicable_from_date,applicable_to_date ,contact_frequency_changes.frequencyid
                                  from dcrs_main, dcrs_contact,dcrs,contactdetails,contact_frequency_changes,users
                                  where dcrs_main.deleted=0 
                                  and dcrs_main.month=".$month."
                                  and dcrs_main.year=".$year."
                                  and dcrs_contact.contacttype ='D' 
                                  and dcrs_main.mainid=dcrs.mainid
                                  and dcrs.pid=dcrs_contact.mainid
								  and users.id=dcrs_main.smownerid
                                 and mod(contact_frequency_changes.frequencyid,4)=1
                                  and dcrs_contact.contactid = contactdetails.contactid
								  and dcrs_main.smownerid=contactdetails.smownerid  
                                  and  contactdetails.contactid= contact_frequency_changes.contactid
                                  and contact_frequency_changes.applicable_from_date < '".$enddate."'
                                  and (created_date<='".$enddate."' or contactdetails.created_date='0000-00-00')
                                  and ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
                                  and dcrs.deleted=0 and dcrs.status='Pending' and dcrs_main.status='Pending' and dcrs_contact.deleted=0 
                                  and cast( '".$enddate."' as date) between applicable_from_date and 
                                        if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date) 
                                  group by dcrs_main.smownerid,dcrs_contact.contactid, dcrs_main.date) p1
                      group by 1,2,3 ) p2
group by 1 ) p3
set adminmis.spl_dr_met_once =p3.cnt_1,
adminmis.spl_dr_met_twice =p3.cnt_2,
adminmis.spl_dr_met_thrice=p3.cnt_3,
adminmis.spl_dr_met_four=p3.cnt_4,
adminmis.spl_dr_met_more_four =p3.cn_more_4
 where adminmis.userid = p3.smownerid
 and adminmis.month=p3.month
 and adminmis.year = p3.year";
 $res=mysql_query($update_adminmis_16) or die('Error 53'.mysql_error());
 
 
 
echo"<br><br>===54==".$update_adminmis_17="update adminmis ,(
select p2.smownerid,'".$month."' as month,".$year." as year,sum(if(p2.cnt=1,1,0)) as cnt_1, sum(if(p2.cnt=2,1,0)) as cnt_2, sum(if(p2.cnt=3,1,0)) as cnt_3, sum(if(p2.cnt=4,1,0)) as cnt_4, 
 sum(if(p2.cnt >4,1,0)) as cn_more_4 from (
select p1.smownerid,p1.contactid,p1.pid_new,count(p1.pid_new) as cnt from (
select dcrs_main.smownerid,contactdetails.frequency,dcrs_contact.contactid,dcrs_main.date,contact_frequency_changes.pid,
      if(mod(contact_frequency_changes.frequencyid,4)=0,4,mod(contact_frequency_changes.frequencyid,4)) as pid_new,
     applicable_from_date,applicable_to_date ,contact_frequency_changes.frequencyid
                                  from dcrs_main, dcrs_contact,dcrs,contactdetails,contact_frequency_changes,users
                                  where dcrs_main.deleted=0 
                                  and dcrs_main.month=".$month."
                                  and dcrs_main.year=".$year."
                                  and dcrs_contact.contacttype ='D' 
                                  and dcrs_main.mainid=dcrs.mainid
                                  and dcrs.pid=dcrs_contact.mainid
								  and users.id=dcrs_main.smownerid
                                  and mod(contact_frequency_changes.frequencyid,4)=2
                                  and dcrs_contact.contactid = contactdetails.contactid
								  and dcrs_main.smownerid=contactdetails.smownerid  
                                  and  contactdetails.contactid= contact_frequency_changes.contactid
                                  and contact_frequency_changes.applicable_from_date < '".$enddate."'
                                  and (created_date<='".$enddate."' or contactdetails.created_date='0000-00-00')
                                  and ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
                                  and dcrs.deleted=0 and dcrs.status='Pending' and dcrs_main.status='Pending' and dcrs_contact.deleted=0 
                                  and cast( '".$enddate."' as date) between applicable_from_date and 
                                        if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date) 
                                  group by dcrs_main.smownerid,dcrs_contact.contactid, dcrs_main.date) p1
                      group by 1,2,3 ) p2
group by 1 ) p3
set adminmis.rpl_dr_met_once =p3.cnt_1,
adminmis.rpl_dr_met_twice =p3.cnt_2,
adminmis.rpl_dr_met_thrice=p3.cnt_3,
adminmis.rpl_dr_met_more_four =p3.cn_more_4,
adminmis.rpl_dr_met_more_four =p3.cn_more_4
 where adminmis.userid = p3.smownerid
 and adminmis.month=p3.month
 and adminmis.year = p3.year";
  $res=mysql_query($update_adminmis_17) or die('Error 54'.mysql_error());
 
echo"<br><br>===55==".$update_adminmis_18="update adminmis ,(
select p2.smownerid,'".$month."' as month,".$year." as year,sum(if(p2.cnt=1,1,0)) as cnt_1, sum(if(p2.cnt=2,1,0)) as cnt_2, sum(if(p2.cnt=3,1,0)) as cnt_3, sum(if(p2.cnt=4,1,0)) as cnt_4, 
 sum(if(p2.cnt >4,1,0)) as cn_more_4  from (
select p1.smownerid,p1.contactid,p1.pid_new,count(p1.pid_new) as cnt from (
select dcrs_main.smownerid,contactdetails.frequency,dcrs_contact.contactid,dcrs_main.date,contact_frequency_changes.pid,
      if(mod(contact_frequency_changes.frequencyid,4)=0,4,mod(contact_frequency_changes.frequencyid,4)) as pid_new,
     applicable_from_date,applicable_to_date ,contact_frequency_changes.frequencyid
                                  from dcrs_main, dcrs_contact,dcrs,contactdetails,contact_frequency_changes,users
                                  where dcrs_main.deleted=0 
                                  and dcrs_main.month=".$month."
                                  and dcrs_main.year=".$year."
                                  and dcrs_contact.contacttype ='D' 
                                  and dcrs_main.mainid=dcrs.mainid
                                  and dcrs.pid=dcrs_contact.mainid
								  and users.id=dcrs_main.smownerid
                                 and mod(contact_frequency_changes.frequencyid,3)=0
                                  and dcrs_contact.contactid = contactdetails.contactid
								  and dcrs_main.smownerid=contactdetails.smownerid  
                                  and  contactdetails.contactid= contact_frequency_changes.contactid
                                  and contact_frequency_changes.applicable_from_date < '".$enddate."'
                                  and (created_date<='".$enddate."' or contactdetails.created_date='0000-00-00')
                                  and  ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
                                  and dcrs.deleted=0 and dcrs.status='Pending' and dcrs_main.status='Pending' and dcrs_contact.deleted=0 
                                  and cast( '".$enddate."' as date) between applicable_from_date and 
                                        if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date) 
                                  group by dcrs_main.smownerid,dcrs_contact.contactid, dcrs_main.date) p1
                      group by 1,2,3 ) p2
group by 1 ) p3
set adminmis.tpl_dr_met_once =p3.cnt_1,
adminmis.tpl_dr_met_twice =p3.cnt_2,
adminmis.tpl_dr_met_thrice=p3.cnt_3,
adminmis.tpl_dr_met_four=p3.cnt_4,
adminmis.tpl_dr_met_more_four =p3.cn_more_4
 where adminmis.userid = p3.smownerid
 and adminmis.month=p3.month
 and adminmis.year = p3.year";
   $res=mysql_query($update_adminmis_18) or die('Error 55'.mysql_error());
 
echo"<br><br>===55==".$update_adminmis_19="update adminmis ,(
select p2.smownerid,'".$month."' as month,".$year." as year,sum(if(p2.cnt=1,1,0)) as cnt_1, sum(if(p2.cnt=2,1,0)) as cnt_2, sum(if(p2.cnt=3,1,0)) as cnt_3, sum(if(p2.cnt=4,1,0)) as cnt_4, 
 sum(if(p2.cnt >4,1,0)) as cn_more_4  from (
select p1.smownerid,p1.contactid,p1.pid_new,count(p1.pid_new) as cnt from (
select dcrs_main.smownerid,contactdetails.frequency,dcrs_contact.contactid,dcrs_main.date,contact_frequency_changes.pid,
      if(mod(contact_frequency_changes.frequencyid,4)=0,4,mod(contact_frequency_changes.frequencyid,4)) as pid_new,
     applicable_from_date,applicable_to_date ,contact_frequency_changes.frequencyid
                                  from dcrs_main, dcrs_contact,dcrs,contactdetails,contact_frequency_changes,users
                                  where dcrs_main.deleted=0 
                                  and dcrs_main.month=".$month."
                                  and dcrs_main.year=".$year."
                                  and dcrs_contact.contacttype ='D' 
                                  and dcrs_main.mainid=dcrs.mainid
                                  and dcrs.pid=dcrs_contact.mainid
								  and users.id=dcrs_main.smownerid
                                 and mod(contact_frequency_changes.frequencyid,4)=0
                                  and dcrs_contact.contactid = contactdetails.contactid
								  and dcrs_main.smownerid=contactdetails.smownerid  
                                  and  contactdetails.contactid= contact_frequency_changes.contactid
                                  and contact_frequency_changes.applicable_from_date < '".$enddate."'
                                  and (created_date<='".$enddate."' or contactdetails.created_date='0000-00-00')
                                  and  ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
                                  and dcrs.deleted=0 and dcrs.status='Pending' and dcrs_main.status='Pending' and dcrs_contact.deleted=0 
                                  and cast( '".$enddate."' as date) between applicable_from_date and 
                                        if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date) 
                                  group by dcrs_main.smownerid,dcrs_contact.contactid, dcrs_main.date) p1
                      group by 1,2,3 ) p2
group by 1 ) p3
set adminmis.qpl_dr_met_once =p3.cnt_1,
adminmis.qpl_dr_met_twice =p3.cnt_2,
adminmis.qpl_dr_met_thrice=p3.cnt_3,
adminmis.qpl_dr_met_four=p3.cnt_4,
adminmis.qpl_dr_met_more_four =p3.cn_more_4
 where adminmis.userid = p3.smownerid
 and adminmis.month=p3.month
 and adminmis.year = p3.year";
   $res=mysql_query($update_adminmis_19) or die('Error 56'.mysql_error());
   
   /*Updated on 2020-07-17 by Ganesh Start*/
   

   
   echo"<br><br>===101===".$update101="
update adminmis,(
select p2.smownerid as id,users.division,sum(if(p2.station='HQ',1,0)) as hq,
sum(if(p2.station='EX-HQ',1,0)) as exhq,
sum(if(p2.station='OS',1,0)) as os
  from (
select p1.smownerid,p1.contactid,p1.townid,max(p1.pid_new) as pid_new,p1.station from (
select contactdetails.smownerid, contactdetails.contactid,contactdetails.frequency,contact_frequency_changes.frequencyid,
concat(contact_frequency_changes.pid,mod(contact_frequency_changes.frequencyid,4)) as pid_new,
contact_frequency_changes.pid as pid,applicable_from_date,
if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date) as applicable_to_date,
frequencyname,concat(contactdetails.contactid,contact_frequency_changes.frequencyid) as freq,
space(10) as details,towns.station,towns.townid
from contactdetails,contact_wise_bricks,contact_frequency_changes,visitfrequency,users,user2role,role2profile,townbrickassociation,towns
where  contactdetails.contactid=contact_frequency_changes.contactid and contact_wise_bricks.contact_id = contactdetails.contactid and contact_wise_bricks.deleted=0
and contact_wise_bricks.brick_id = townbrickassociation.brickid and townbrickassociation.townid = towns.townid
and contact_frequency_changes.frequencyid=visitfrequency.frequencyid 
and contactdetails.smownerid=users.id
and users.id=user2role.userid 
and user2role.roleid=role2profile.roleid
and profileid='5' 
and applicable_from_date<'".$enddate."'
and  (contactdetails.created_date<='".$enddate."'  or contactdetails.created_date='0000-00-00') 
and ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
and cast('".$enddate."' as date) between applicable_from_date and if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date)
) p1
group by 1,2

) p2,users
where p2.smownerid = users.id 
group by 1) p3
set adminmis.drsinlist_hq = p3.hq,
adminmis.drsinlist_exhq=  p3.exhq,
adminmis.drsinlist_os=   p3.os
where adminmis.userid = p3.id
and adminmis.month =".$month."
and adminmis.year=".$year.";";
$res=mysql_query($update101) or die('Error 101'.mysql_error());

   
   
   
   echo"<br><br>===102===".$update102="update adminmis,(
select p2.smownerid,".$month." as month,".$year." as year,
sum(if(p2.station='HQ',1,0)) as hq,
sum(if(p2.station='EX-HQ',1,0)) as exhq,
sum(if(p2.station='OS',1,0)) as os  from (
select p1.smownerid,p1.contactid,p1.pid_new,count(p1.pid_new) as cnt,p1.station from (
     select contactdetails.smownerid,contactdetails.frequency,dcrs_contact.contactid,dcrs_main.date,contact_frequency_changes.pid,
      if(mod(contact_frequency_changes.frequencyid,4)=0,4,mod(contact_frequency_changes.frequencyid,4)) as pid_new,
     applicable_from_date,applicable_to_date ,contact_frequency_changes.frequencyid,towns.station
                                  from dcrs_main, dcrs_contact,dcrs,contactdetails,contact_wise_bricks,contact_frequency_changes,users,townbrickassociation,towns
                                  where dcrs_main.deleted=0 
                                  and dcrs_main.month=".$month."
                                  and dcrs_main.year=".$year."
                                  and dcrs_contact.contacttype ='D' 
                                  and dcrs_main.mainid=dcrs.mainid
                                  and dcrs.pid=dcrs_contact.mainid
								  and users.id=dcrs_main.smownerid 
                                  and dcrs_contact.deleted =0
                                  and dcrs.deleted =0
                                  and dcrs_contact.contactid = contactdetails.contactid
and contact_wise_bricks.contact_id = contactdetails.contactid and contact_wise_bricks.deleted=0
and contact_wise_bricks.brick_id = townbrickassociation.brickid and townbrickassociation.townid = towns.townid
								   and dcrs_main.smownerid=contactdetails.smownerid  
                                  and  contactdetails.contactid= contact_frequency_changes.contactid
                                  and contact_frequency_changes.applicable_from_date < '".$enddate."'
                                  and (contactdetails.created_date<='".$enddate."' or contactdetails.created_date='0000-00-00')
                                  and ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
                                  and dcrs.deleted=0 and dcrs.status='Pending' and dcrs_main.status='Pending' and dcrs_contact.deleted=0 
                                  and cast('".$enddate."' as date) between applicable_from_date 
                                        and if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date) 
                                  group by dcrs_main.smownerid,dcrs_contact.contactid, dcrs_main.date

) p1
                      group by 1,2 ) p2
group by 1 ) p3
set adminmis.nooddrsmet_hq = p3.hq,
adminmis.nooddrsmet_exhq = p3.exhq,
adminmis.nooddrsmet_os = p3.os
where adminmis.userid = p3.smownerid
and adminmis.month=".$month."
and adminmis.year = ".$year.";";
$res=mysql_query($update102) or die('Error 102'.mysql_error());

   

   echo"<br><br>===103===".$update103="update adminmis,(
select p2.smownerid,".$month." as month,".$year." as year,
sum(if(p2.station='HQ',p2.cnt,0)) as hq,
sum(if(p2.station='EX-HQ',p2.cnt,0)) as exhq,
sum(if(p2.station='OS',p2.cnt,0)) as os  from (
select p1.smownerid,p1.contactid,p1.pid_new,count(p1.pid_new) as cnt,p1.station from (
     select contactdetails.smownerid,contactdetails.frequency,dcrs_contact.contactid,dcrs_main.date,contact_frequency_changes.pid,
      if(mod(contact_frequency_changes.frequencyid,4)=0,4,mod(contact_frequency_changes.frequencyid,4)) as pid_new,
     applicable_from_date,applicable_to_date ,contact_frequency_changes.frequencyid,towns.station,towns.townid
                                  from dcrs_main, dcrs_contact,dcrs,contactdetails,contact_wise_bricks,contact_frequency_changes,users,townbrickassociation,towns
                                  where dcrs_main.deleted=0 
                                  and dcrs_main.month=".$month."
                                  and dcrs_main.year=".$year."
                                  and dcrs_contact.contacttype ='D' 
                                  and dcrs_main.mainid=dcrs.mainid
                                  and dcrs.pid=dcrs_contact.mainid
								  and users.id=dcrs_main.smownerid 
                                  and dcrs_contact.deleted =0
                                  and dcrs.deleted =0
                                  and dcrs_contact.contactid = contactdetails.contactid
and contact_wise_bricks.contact_id = contactdetails.contactid and contact_wise_bricks.deleted=0
and contact_wise_bricks.brick_id = townbrickassociation.brickid and townbrickassociation.townid = towns.townid
								   and dcrs_main.smownerid=contactdetails.smownerid  
                                  and  contactdetails.contactid= contact_frequency_changes.contactid
                                  and contact_frequency_changes.applicable_from_date < '".$enddate."'
                                  and (contactdetails.created_date<='".$enddate."' or contactdetails.created_date='0000-00-00')
                                  and ((contactdetails.deleted=1 and contactdetails.deleted_date>='".$startdate."') or (contactdetails.deleted=0)) 
                                  and dcrs.deleted=0 and dcrs.status='Pending' and dcrs_main.status='Pending' and dcrs_contact.deleted=0 
                                  and cast('".$enddate."' as date) between applicable_from_date 
                                        and if(applicable_to_date='0000-00-00','".$enddate."',applicable_to_date) 
                                  group by dcrs_main.smownerid,dcrs_contact.contactid, dcrs_main.date

) p1
                      group by 1,2 ) p2
group by 1 ) p3
set adminmis.noofdrcalls_hq = p3.hq,
adminmis.noofdrcalls_exhq = p3.exhq,
adminmis.noofdrcalls_os = p3.os
where adminmis.userid = p3.smownerid
and adminmis.month=".$month."
and adminmis.year = ".$year.";";
$res=mysql_query($update103) or die('Error 103'.mysql_error());

   
   /*
   echo"<br><br>===104===".$update104="DROP TABLE IF EXISTS tb_fw;";
$res=mysql_query($update104) or die('Error 104'.mysql_error());

   
   
   echo"<br><br>===105===".$update105="CREATE TABLE tb_fw
select dcrs_main.mainid,dcrs_main.date,dcrs.type,dcrs_main.smownerid ,towns.station from  dcrs_main,dcrs,dcrs_contact,contactdetails,contact_wise_bricks,users,townbrickassociation,towns
                                         where month=".$month."
                                         and year=".$year."
                                         and dcrs_main.deleted=0
                                         and dcrs_main.mainid = dcrs.mainid
                                         and dcrs.pid = dcrs_contact.mainid
                                  and dcrs_contact.contactid = contactdetails.contactid and dcrs_contact.contacttype='D'
and contact_wise_bricks.contact_id = contactdetails.contactid and contact_wise_bricks.deleted=0
and contact_wise_bricks.brick_id = townbrickassociation.brickid and townbrickassociation.townid = towns.townid
										 and users.id=dcrs_main.smownerid 
                                         and  dcrs.deleted=0
                                  and dcrs_contact.deleted =0
                                         and dcrs_main.status='Pending'
                                         and dcrs.status='Pending'
                                         and ( dcrs.fullday_type is null or dcrs.fullday_type ='0')
										 and dcrs.type='DCRS'
                                         group by dcrs_main.date,dcrs_main.smownerid,dcrs.type,towns.station ;";
$res=mysql_query($update105) or die('Error 105'.mysql_error());

   
   
   echo"<br><br>===106===".$create106="CREATE INDEX mainid on tb_fw(mainid);";
$res=mysql_query($create106) or die('create 106'.mysql_error());

   echo"<br><br>===106===".$create107="CREATE INDEX station on tb_fw(station);";
$res=mysql_query($create107) or die('create 107'.mysql_error());


   echo"<br><br>===106===".$insert107="INSERT INTO tb_fw
SELECT p1.* FROM (
select dcrs_main.mainid,dcrs_main.date,dcrs.type,dcrs_main.smownerid ,towns.station from  dcrs_main,dcrs,dcrs_contact,bcntscontactdetails,users,townbrickassociation,towns
                                         where month=".$month."
                                         and year=".$year."
                                         and dcrs_main.deleted=0
                                         and dcrs_main.mainid = dcrs.mainid
                                         and dcrs.pid = dcrs_contact.mainid
                                  and dcrs_contact.contactid = bcntscontactdetails.contactid and dcrs_contact.contacttype='C'
and bcntscontactdetails.brick = townbrickassociation.brickid and townbrickassociation.townid = towns.townid
										 and users.id=dcrs_main.smownerid  
                                         and  dcrs.deleted=0
                                  and dcrs_contact.deleted =0
                                         and dcrs_main.status='Pending'
                                         and dcrs.status='Pending'
                                         and ( dcrs.fullday_type is null or dcrs.fullday_type ='0')
										 and dcrs.type='DCRS'
                                         group by dcrs_main.date,dcrs_main.smownerid,dcrs.type,towns.station)p1
LEFT OUTER JOIN tb_fw on tb_fw.mainid = p1.mainid and tb_fw.station = p1.station 
WHERE  tb_fw.mainid is null;";
$res=mysql_query($insert107) or die('insert107'.mysql_error());


   echo"<br><br>===106===".$update106="ALTER TABLE tb_fw add column activity DECIMAL(10,2) not null;";
$res=mysql_query($update106) or die('Error 106'.mysql_error());

   
   echo"<br><br>===107===".$update107="ALTER TABLE tb_fw add column fw DECIMAL(10,2) not null;";
$res=mysql_query($update107) or die('Error 107'.mysql_error());

   
   echo"<br><br>===108===".$update108="UPDATE tb_fw,(
select dcrs_main.mainid,dcrs_main.date,a.fullday_type,dcrs_main.smownerid,if(a.fullday_type<> '0',1,0.5) as activity   from  dcrs_main,dcrs ,dcrs a ,users
                                         where month=".$month."
                                         and year=".$year."
                                         and dcrs_main.deleted=0
                                         and dcrs_main.mainid = dcrs.mainid
										 and users.id=dcrs_main.smownerid 
                                         and  dcrs.deleted=0
										 and dcrs_main.status='Pending' 
					                     and dcrs.status='Pending' 
                                        and dcrs.type='DCRS'
and dcrs.mainid=a.mainid
and a.type='ACTIVITY'
group by dcrs_main.date,dcrs_main.smownerid
)p1
SET tb_fw.activity = p1.activity
WHERE tb_fw.smownerid = p1.smownerid and  tb_fw.date = p1.date ";
$res=mysql_query($update108) or die('Error 108'.mysql_error());

   
   
   
   echo"<br><br>===109===".$update109="update tb_fw,(
select smownerid,date,count(station) as cnt,dcrday,(dcrday/count(station)) as fw FROM (
select smownerid,date,station,activity,(1- activity) as dcrday FROM tb_fw
)p1
GROUP BY smownerid,date
)p2
SET tb_fw.fw = p2.fw
WHERE tb_fw.smownerid = p2.smownerid and tb_fw.date = p2.date";
$res=mysql_query($update109) or die('Error 109'.mysql_error());

   
   
   
   echo"<br><br>===110===".$update110="update adminmis, (
select smownerid,".$month." as month,".$year." as year,
sum(if(station='HQ',fw,0)) as hq,
sum(if(station='EX-HQ',fw,0)) as exhq,
sum(if(station='OS',fw,0)) as os FROM tb_fw
GROUP BY smownerid) t2
set adminmis.fwdays_hq = t2.hq,adminmis.fwdays_exhq = t2.exhq,adminmis.fwdays_os = t2.os
where adminmis.userid= t2.smownerid
and adminmis.month =".$month."
and adminmis.year=".$year."";
$res=mysql_query($update110) or die('Error 110'.mysql_error());*/
 $query="drop table if exists ixx1;";
echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());

$query="create table  ixx1
select users.id,daymonthyear.date1,space(10) as type1,space(10) as type2,space(10) as wday,'1.00' as numberofdays from users,daymonthyear
where users.deleted =0
and month(daymonthyear.date1) =".$month." and year(daymonthyear.date1) =".$year.";";
echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());


$query="create index id on ixx1 (id)";
echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());


$query="update ixx1,(
select smownerid,date as date1 from dcrs_main
where dcrs_main.deleted =0 and dcrs_main.status='Pending' 
and month=".$month."
and year =".$year."
group by 1,2) p1
set ixx1.type1 ='DCR'
where ixx1.id = p1.smownerid
and ixx1.date1= p1.date1";
echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());


$query="update ixx1,(
select smownerid,date as date1 from dcrs_main,dcrs
where dcrs_main.deleted =0 and dcrs_main.status='Pending' and dcrs_main.mainid=dcrs.mainid
and dcrs.type='DCRS' and dcrs.deleted=0
and month=".$month."
and year =".$year."
group by 1,2) p1
set ixx1.type2 ='DCR'
where ixx1.id = p1.smownerid
and ixx1.date1= p1.date1";
echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());


$query="update ixx1,(
select leaveapplication.emp_id,daymonthyear.date1 from leaveapplication,daymonthyear
 where  daymonthyear.date1 between leaveapplication.frm_date and to_date 
and leaveapplication.leavetype='LWP'
and leaveapplication.deleted=0
 and month(daymonthyear.date1) =".$month." and year(daymonthyear.date1) =".$year.") p1
 set ixx1.type1 ='LWP' 
where  ixx1.id = p1.emp_id
and ixx1.date1= p1.date1
and ixx1.type1 =''";
echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());


$query="update ixx1,(
select leaveapplication.emp_id,daymonthyear.date1 from leaveapplication,daymonthyear
 where  daymonthyear.date1 between leaveapplication.frm_date and to_date 
 and leaveapplication.leavetype <> 'LWP'
 and leaveapplication.deleted=0
 and month(daymonthyear.date1) =".$month." and year(daymonthyear.date1) =".$year.") p1
 set ixx1.type1 ='LEAVE' 
where  ixx1.id = p1.emp_id
and ixx1.date1= p1.date1
and ixx1.type1 =''";
echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());


$query="update ixx1,(
select holidaytemplatedetails.date as date1,user2holidaytemplate.userid from holidaytemplate 
   inner join holidaytemplatedetails on holidaytemplate.id=holidaytemplatedetails.id
   inner join user2holidaytemplate
    on user2holidaytemplate.holidaytemplateid=holidaytemplatedetails.id
   inner join user2role on user2holidaytemplate.userid=user2role.userid
   inner join role2profile on user2role.roleid=role2profile.roleid
   inner join users on users.id=user2role.userid
    where  holidaytemplate.deleted=0
    and month(date)=".$month." and year(date)=".$year.") p1
 set ixx1.type1 ='HOLIDAY' 
where  ixx1.id = p1.userid
and ixx1.date1= p1.date1
and ixx1.type1 =''";
echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());


 $query="update ixx1,users set ixx1.type1 ='WEEK_HOL'
 where ixx1.id=users.id and ixx1.type1 ='' and dayofweek(ixx1.date1) =1 and users.weekstart=0";
 echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());


 $query="update ixx1,(
select smownerid,date as date1,station from Travelplan
where Travelplan.travel_delete =0 
and month(date)=".$month."
and year(date)=".$year."
group by 1,2) p1
set ixx1.wday =p1.station
where ixx1.id = p1.smownerid
and ixx1.date1= p1.date1";
echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());



$query="UPDATE ixx1,(
SELECT smownerid,date,COUNT(pid) as cnt FROM (
SELECT dcrs_main.smownerid,dcrs_main.date,dcrs.pid FROM dcrs_main
INNER JOIN dcrs on dcrs.mainid = dcrs_main.mainid
where dcrs_main.deleted=0 and dcrs_main.`status`='Pending' and dcrs.deleted=0 and dcrs.type='ACTIVITY' and  (dcrs.firsthalf_type >0 or dcrs.secondhalf_type>0) and dcrs_main.month=".$month." and dcrs_main.year=".$year.")p1
GROUP BY smownerid,date
HAVING cnt =1)p2
SET ixx1.numberofdays='0.50'
WHERE ixx1.id = p2.smownerid and  ixx1.date1 = p2.date;";
echo $query."<br><br>" ;
mysql_query($query) or die(mysql_error());



$query="update adminmis,(
select ixx1.id, sum(if(ixx1.wday='HQ',numberofdays,0)) as hq_cnt, sum(if(ixx1.wday='EX-HQ',numberofdays,0)) as exhq_cnt, 
sum(if(ixx1.wday='OS',numberofdays,0)) as os_cnt
 from ixx1 where wday in ('HQ','EX-HQ','OS') and type2='DCR'
group by 1)p2
set adminmis.fwdays_hq=p2.hq_cnt,
 adminmis.fwdays_exhq=p2.exhq_cnt,
 adminmis.fwdays_os=p2.os_cnt
where adminmis.userid= p2.id
				and adminmis.month=".$month."
				and adminmis.year =".$year."";
				echo $query."<br><br>" ;
 mysql_query($query) or die(mysql_error());
   
   echo"<br><br>===111===".$update111="update adminmis 
set adminmis.drmissed_hq =adminmis.drsinlist_hq -adminmis.nooddrsmet_hq,
adminmis.drmissed_exhq = adminmis.drsinlist_exhq - adminmis.nooddrsmet_exhq,
adminmis.drmissed_os = adminmis.drsinlist_os - adminmis.nooddrsmet_os
where adminmis.month =".$month." and adminmis.year=".$year.";";
$res=mysql_query($update111) or die('Error 111'.mysql_error());

   
 
   
   
     /*Updated on 2020-07-17 by Ganesh End*/
	 
	 
	 
	 
 /////////////////Higher up data
 
/* Query Changed by soniya on 2020-02-10 for 
 round(avg(drcallavg),2) as drcallavg to round((avg(noofdrcalls)/avg(fieldworkingdays)),2) as drcallavg
 round(avg(chemcallavg),2) as chemcallavg to round((avg(chemcalls)/avg(fieldworkingdays)),2) as chemcallavg
echo"<br><br>===56==".$insert_adminmis_2="insert into adminmis
select '' as srno,users.id as userid,users.division,adminmis.datetimestamp,adminmis.cmonth,adminmis.month,
adminmis.year,concat_ws('-',users.first_name,users.middle_name,users.last_name) as fieldstaff,users.user_name as employeesapcode,
division.name as division1,users.title as designation,users.headquater as hq,
'0000-00-00' as mtpapprvedon,daysinmonth,round(avg(fieldworkingdays),2) as fieldworkingdays,round(avg(leaves),2) as leaves,
round(avg(lop),2) as lop,round(avg(adminworkingdays),2) as adminworkingdays,round(avg(holi_sun),2) as holi_sun,
round(avg(notfiled),2) as notfiled,
sum(drsinlistspl) as drsinlistspl,sum(drsinlistrpl) as drsinlistrpl,sum(drsinlisttpl) as drsinlisttpl,sum(drsinlisttotal) as drsinlisttotal,
sum(nooddrsmetspl) as nooddrsmetspl,sum(nooddrsmetrpl) as nooddrsmetrpl,sum(nooddrsmettpl) as nooddrsmettpl,
sum(nooddrsmettotal) as nooddrsmettotal,round(avg(noofdrcalls),2) as noofdrcalls,
round(avg(drcallavg),2) as drcallavg,
sum(drmissedspl) as drmissedspl,sum(drmissedrpl) as drmissedrpl,sum(drmissedtpl) as drmissedtpl,sum(drmissedtotal) as drmissedtotal,
sum(missedcalls) as missedcalls,sum(cheminlist) as cheminlist,round(avg(chemcalls),2) as chemcalls,
round(avg(chemcallavg),2) as chemcallavg,
sum(totalpob) as totalpob,curdate() as created_date, 0 as flag, '0000-00-00' as terr_joining_date,
sum(spl_dr_met_once) as spl_dr_met_once,sum(spl_dr_met_twice) as spl_dr_met_twice,
sum(spl_dr_met_thrice) as spl_dr_met_thrice,sum(spl_dr_met_more_thrice) as spl_dr_met_more_thrice,
sum(rpl_dr_met_once) as rpl_dr_met_once,sum(rpl_dr_met_twice) as rpl_dr_met_twice,
sum(rpl_dr_met_thrice) as rpl_dr_met_thrice,sum(rpl_dr_met_more_thrice) as rpl_dr_met_more_thrice,
sum(tpl_dr_met_once) as tpl_dr_met_once,sum(tpl_dr_met_twice) as tpl_dr_met_twice,
sum(tpl_dr_met_thrice) as tpl_dr_met_thrice,sum(tpl_dr_met_more_thrice) as tpl_dr_met_more_thrice,
'' as hq_name,'' as patch_name,sum(chem_unique_met) as chem_unique_met,round(avg(chem_repeat),2) as chem_repeat,round(avg(chem_cov),2) as chem_cov ,
round(avg(activityworkingdays),2) as activityworkingdays, round(avg(transitworkingdays),2) as transitworkingdays, round(avg(otherworkingdays),2) as otherworkingdays ,round(avg(adminworkingdaysnew),2) as adminworkingdaysnew,
sum(totalclassa) as totalclassa,sum(totalclassb) as totalclassb,sum(totalclassc) as totalclassc,
sum(totalclassamet) as totalclassamet,sum(totalclassbmet) as totalclassbmet,sum(totalclasscmet) as totalclasscmet,
round(avg(total_coveragea) ,2) as total_coveragea,
round(avg(total_coverageb) ,2) as total_coverageb,
round(avg(total_coveragec) ,2) as total_coveragec,

round(avg(compliance_a),2) as compliance_a,
round(avg(compliance_b),2) as compliance_b,
round(avg(compliance_c),2) as compliance_c,
sum(compliance_suma) as compliance_suma,sum(compliance_sumb) as compliance_sumb,sum(compliance_sumc) as compliance_sumc,
users.reports_to_id as reports_to_id,
sum(daysinmonth) as daysinmonth_sum,
sum(holi_sun) as holi_sun_sum,
sum(fieldworkingdays) as fieldworkingdays_sum,
sum(noofdrcalls) as noofdrcalls_sum,
sum(drcallavg) as drcallavg_sum,
sum(chemcalls) as chemcalls_sum,
sum(chemcallavg) as chemcallavg_sum,
round(avg(nooddrsmettotal),2) as nooddrsmettotal_avg,0 as group_id,'' as patchsapcode,0 as profileid,
0 as spl_freqcoverage,
0 as rpl_freqcoverage,
0 as tpl_freqcoverage
from adminmis,m7,division,users,users u1
where adminmis.userid=m7.id
and u1.id=m7.id
and users.id=m7.reports_to_id
and users.division = division.divisionid
and adminmis.month =".$month."
and adminmis.year =".$year."
and adminmis.division=users.division
and users.terr_joining_date <= '".$enddate."'
and u1.rx_type='Regular'
group by 2";*/
/* commented by Ganesh on date 2020-04-28 to add 4 visit frequency

echo"<br><br>===56==".$insert_adminmis_2="insert into adminmis
select '' as srno,users.id as userid,users.division,adminmis.datetimestamp,adminmis.cmonth,adminmis.month,
adminmis.year,concat_ws('-',users.first_name,users.middle_name,users.last_name) as fieldstaff,users.user_name as employeesapcode,
division.name as division1,users.title as designation,users.headquater as hq,
'0000-00-00' as mtpapprvedon,daysinmonth,round(avg(fieldworkingdays),2) as fieldworkingdays,round(avg(leaves),2) as leaves,
round(avg(lop),2) as lop,round(avg(adminworkingdays),2) as adminworkingdays,round(avg(holi_sun),2) as holi_sun,
round(avg(notfiled),2) as notfiled,
sum(drsinlistspl) as drsinlistspl,sum(drsinlistrpl) as drsinlistrpl,sum(drsinlisttpl) as drsinlisttpl,sum(drsinlisttotal) as drsinlisttotal,
sum(nooddrsmetspl) as nooddrsmetspl,sum(nooddrsmetrpl) as nooddrsmetrpl,sum(nooddrsmettpl) as nooddrsmettpl,
sum(nooddrsmettotal) as nooddrsmettotal,round(avg(noofdrcalls),2) as noofdrcalls,
round((avg(noofdrcalls)/avg(fieldworkingdays)),2) as drcallavg,
sum(drmissedspl) as drmissedspl,sum(drmissedrpl) as drmissedrpl,sum(drmissedtpl) as drmissedtpl,sum(drmissedtotal) as drmissedtotal,
sum(missedcalls) as missedcalls,sum(cheminlist) as cheminlist,round(avg(chemcalls),2) as chemcalls,
round((avg(chemcalls)/avg(fieldworkingdays)),2) as chemcallavg,
sum(totalpob) as totalpob,curdate() as created_date, 0 as flag, '0000-00-00' as terr_joining_date,
sum(spl_dr_met_once) as spl_dr_met_once,sum(spl_dr_met_twice) as spl_dr_met_twice,
sum(spl_dr_met_thrice) as spl_dr_met_thrice,sum(spl_dr_met_more_thrice) as spl_dr_met_more_thrice,
sum(rpl_dr_met_once) as rpl_dr_met_once,sum(rpl_dr_met_twice) as rpl_dr_met_twice,
sum(rpl_dr_met_thrice) as rpl_dr_met_thrice,sum(rpl_dr_met_more_thrice) as rpl_dr_met_more_thrice,
sum(tpl_dr_met_once) as tpl_dr_met_once,sum(tpl_dr_met_twice) as tpl_dr_met_twice,
sum(tpl_dr_met_thrice) as tpl_dr_met_thrice,sum(tpl_dr_met_more_thrice) as tpl_dr_met_more_thrice,
'' as hq_name,'' as patch_name,sum(chem_unique_met) as chem_unique_met,round(avg(chem_repeat),2) as chem_repeat,round(avg(chem_cov),2) as chem_cov ,
round(avg(activityworkingdays),2) as activityworkingdays, round(avg(transitworkingdays),2) as transitworkingdays, round(avg(otherworkingdays),2) as otherworkingdays ,round(avg(adminworkingdaysnew),2) as adminworkingdaysnew,
sum(totalclassa) as totalclassa,sum(totalclassb) as totalclassb,sum(totalclassc) as totalclassc,
sum(totalclassamet) as totalclassamet,sum(totalclassbmet) as totalclassbmet,sum(totalclasscmet) as totalclasscmet,
round(avg(total_coveragea) ,2) as total_coveragea,
round(avg(total_coverageb) ,2) as total_coverageb,
round(avg(total_coveragec) ,2) as total_coveragec,

round(avg(compliance_a),2) as compliance_a,
round(avg(compliance_b),2) as compliance_b,
round(avg(compliance_c),2) as compliance_c,
sum(compliance_suma) as compliance_suma,sum(compliance_sumb) as compliance_sumb,sum(compliance_sumc) as compliance_sumc,
users.reports_to_id as reports_to_id,
sum(daysinmonth) as daysinmonth_sum,
sum(holi_sun) as holi_sun_sum,
sum(fieldworkingdays) as fieldworkingdays_sum,
sum(noofdrcalls) as noofdrcalls_sum,
sum(drcallavg) as drcallavg_sum,
sum(chemcalls) as chemcalls_sum,
sum(chemcallavg) as chemcallavg_sum,
round(avg(nooddrsmettotal),2) as nooddrsmettotal_avg,0 as group_id,'' as patchsapcode,0 as profileid,
0 as spl_freqcoverage,
0 as rpl_freqcoverage,
0 as tpl_freqcoverage
from adminmis,m7,division,users,users u1
where adminmis.userid=m7.id
and u1.id=m7.id
and users.id=m7.reports_to_id
and users.division = division.divisionid
and adminmis.month =".$month."
and adminmis.year =".$year."
and adminmis.division=users.division
and users.terr_joining_date <= '".$enddate."'
and u1.rx_type='Regular'
group by 2";
 */
echo"<br><br>===56==".$insert_adminmis_2="insert into adminmis
select '' as srno,users.id as userid,users.division,adminmis.datetimestamp,adminmis.cmonth,adminmis.month,
adminmis.year,concat_ws('-',users.first_name,users.middle_name,users.last_name) as fieldstaff,users.user_name as employeesapcode,
division.name as division1,users.title as designation,users.headquater as hq,
'0000-00-00' as mtpapprvedon,daysinmonth,round(avg(fieldworkingdays),2) as fieldworkingdays,round(avg(leaves),2) as leaves,
round(avg(lop),2) as lop,round(avg(adminworkingdays),2) as adminworkingdays,round(avg(holi_sun),2) as holi_sun,
round(avg(notfiled),2) as notfiled,
sum(drsinlistspl) as drsinlistspl,sum(drsinlistrpl) as drsinlistrpl,sum(drsinlisttpl) as drsinlisttpl,sum(drsinlistqpl) as drsinlistqpl,sum(drsinlisttotal) as drsinlisttotal,
sum(nooddrsmetspl) as nooddrsmetspl,sum(nooddrsmetrpl) as nooddrsmetrpl,sum(nooddrsmettpl) as nooddrsmettpl,sum(nooddrsmetqpl) as nooddrsmetqpl,
sum(nooddrsmettotal) as nooddrsmettotal,round(avg(noofdrcalls),2) as noofdrcalls,
round((avg(noofdrcalls)/avg(fieldworkingdays)),2) as drcallavg,
sum(drmissedspl) as drmissedspl,sum(drmissedrpl) as drmissedrpl,sum(drmissedtpl) as drmissedtpl,sum(drmissedqpl) as drmissedqpl,sum(drmissedtotal) as drmissedtotal,
sum(missedcalls) as missedcalls,sum(cheminlist) as cheminlist,round(avg(chemcalls),2) as chemcalls,
round((avg(chemcalls)/avg(fieldworkingdays)),2) as chemcallavg,
sum(totalpob) as totalpob,curdate() as created_date, 0 as flag, '0000-00-00' as terr_joining_date,
sum(spl_dr_met_once) as spl_dr_met_once,sum(spl_dr_met_twice) as spl_dr_met_twice,
sum(spl_dr_met_thrice) as spl_dr_met_thrice,sum(spl_dr_met_four) as spl_dr_met_four,sum(spl_dr_met_more_four) as spl_dr_met_more_four,
sum(rpl_dr_met_once) as rpl_dr_met_once,sum(rpl_dr_met_twice) as rpl_dr_met_twice,
sum(rpl_dr_met_thrice) as rpl_dr_met_thrice,sum(rpl_dr_met_four) as rpl_dr_met_four,sum(rpl_dr_met_more_four) as rpl_dr_met_more_four,
sum(tpl_dr_met_once) as tpl_dr_met_once,sum(tpl_dr_met_twice) as tpl_dr_met_twice,
sum(tpl_dr_met_thrice) as tpl_dr_met_thrice,sum(tpl_dr_met_four) as tpl_dr_met_four,sum(tpl_dr_met_more_four) as tpl_dr_met_more_four,
sum(qpl_dr_met_once) as qpl_dr_met_once,sum(qpl_dr_met_twice) as qpl_dr_met_twice,
sum(qpl_dr_met_thrice) as qpl_dr_met_thrice,sum(qpl_dr_met_four) as qpl_dr_met_four,sum(qpl_dr_met_more_four) as qpl_dr_met_more_four,
'' as hq_name,'' as patch_name,sum(chem_unique_met) as chem_unique_met,round(avg(chem_repeat),2) as chem_repeat,round(avg(chem_cov),2) as chem_cov ,
round(avg(activityworkingdays),2) as activityworkingdays, round(avg(transitworkingdays),2) as transitworkingdays, round(avg(otherworkingdays),2) as otherworkingdays ,round(avg(adminworkingdaysnew),2) as adminworkingdaysnew,
sum(totalclassa) as totalclassa,sum(totalclassb) as totalclassb,sum(totalclassc) as totalclassc,
sum(totalclassamet) as totalclassamet,sum(totalclassbmet) as totalclassbmet,sum(totalclasscmet) as totalclasscmet,
round(avg(total_coveragea) ,2) as total_coveragea,
round(avg(total_coverageb) ,2) as total_coverageb,
round(avg(total_coveragec) ,2) as total_coveragec,
round(avg(compliance_a),2) as compliance_a,
round(avg(compliance_b),2) as compliance_b,
round(avg(compliance_c),2) as compliance_c,
sum(compliance_suma) as compliance_suma,sum(compliance_sumb) as compliance_sumb,sum(compliance_sumc) as compliance_sumc,
users.reports_to_id as reports_to_id,
sum(daysinmonth) as daysinmonth_sum,
sum(holi_sun) as holi_sun_sum,
sum(fieldworkingdays) as fieldworkingdays_sum,
sum(noofdrcalls) as noofdrcalls_sum,
sum(drcallavg) as drcallavg_sum,
sum(chemcalls) as chemcalls_sum,
sum(chemcallavg) as chemcallavg_sum,
round(avg(nooddrsmettotal),2) as nooddrsmettotal_avg,0 as group_id,0 as profileid,'' as patchsapcode,
0 as spl_freqcoverage,
0 as rpl_freqcoverage,
0 as tpl_freqcoverage,
0 as qpl_freqcoverage,

sum(drsinlist_hq) as drsinlist_hq,
sum(drsinlist_exhq) as drsinlist_exhq,
sum(drsinlist_os) as drsinlist_os,
sum(nooddrsmet_hq) as nooddrsmet_hq,
sum(nooddrsmet_exhq) as nooddrsmet_exhq,
sum(nooddrsmet_os) as nooddrsmet_os,
sum(drmissed_hq) as drmissed_hq,
sum(drmissed_exhq) as drmissed_exhq,
sum(drmissed_os) as drmissed_os,
round(avg(noofdrcalls_hq),2)  as noofdrcalls_hq,
round(avg(noofdrcalls_exhq),2)  as noofdrcalls_exhq,
round(avg(noofdrcalls_os),2)  as noofdrcalls_os,
round(avg(fwdays_hq),2) as fwdays_hq,
round(avg(fwdays_exhq),2) as fwdays_exhq,
round(avg(fwdays_os),2) as fwdays_os
from adminmis,m7,division,users,users u1
where adminmis.userid=m7.id
and u1.id=m7.id
and users.id=m7.reports_to_id
and users.division = division.divisionid
and adminmis.month =".$month."
and adminmis.year =".$year."
and adminmis.division=users.division
and users.terr_joining_date <= '".$enddate."'
and u1.rx_type='Regular'
group by 2";

$res=mysql_query($insert_adminmis_2) or die('Error 56'.mysql_error());


echo"<br><br>===97===".$update97="update `adminmis` 
SET `adminmis`.spl_freqcoverage=ROUND((nooddrsmetspl/drsinlistspl)*100,2)
where `month`=".$month." and `year`=".$year." ";
$res=mysql_query($update97) or die('Error 97'.mysql_error());

echo"<br><br>===98===".$update98="update `adminmis` 
SET `adminmis`.rpl_freqcoverage=ROUND(( ( (rpl_dr_met_once*1) + (rpl_dr_met_twice*2) + (rpl_dr_met_thrice*2) + (rpl_dr_met_four*2) + (rpl_dr_met_more_four*2) )/(drsinlistrpl*2))*100,2)
where `month`=".$month." and `year`=".$year." ";
$res=mysql_query($update98) or die('Error 98'.mysql_error());

echo"<br><br>===99===".$update99="update `adminmis` 
SET `adminmis`.tpl_freqcoverage=ROUND(( ( (tpl_dr_met_once*1) + (tpl_dr_met_twice*2) + (tpl_dr_met_thrice*3) + (tpl_dr_met_four*3) + (tpl_dr_met_more_four*3) )/(drsinlisttpl*3))*100,2)
where `month`=".$month." and `year`=".$year." ";
$res=mysql_query($update99) or die('Error 99'.mysql_error());

echo"<br><br>===100===".$update100="update `adminmis` 
SET `adminmis`.qpl_freqcoverage=ROUND(( ( (qpl_dr_met_once*1) + (qpl_dr_met_twice*2) + (qpl_dr_met_thrice*3) + (qpl_dr_met_four*4) + (qpl_dr_met_more_four*4) )/(drsinlisttpl*3))*100,2)
where `month`=".$month." and `year`=".$year." ";
$res=mysql_query($update100) or die('Error 100'.mysql_error());


echo "<br><br>====Ended at :'".date('l jS \of F Y h:i:s A')."'";

echo "<br><br> Summary populated for month-".$month." and year-".$year."";
}

//Add at the end of Script. Start						
echo "<br>=====CronScriptEndTime=====".$cron_end_time = "UPDATE cron_script_execution SET end_time='".date('Y-m-d h:i:s')."' WHERE cronid='".$cronid."' and date='".date('Y-m-d')."';";	
$cron_end_time_res = mysql_query($cron_end_time); 
//End

exit();

?>