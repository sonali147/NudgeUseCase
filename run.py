#4202,4291,3965,3967
import regex
import MySQLdb.cursors
from copy import deepcopy
from pymongo import MongoClient
from datetime import datetime, date, timedelta
import time,math

try:
    db = MySQLdb.connect(
        host="XXX.XXX.XXX.XXX",
        user="XXXX",
        passwd="XXX",
        db="XXXXX",
        connect_timeout=10,
        cursorclass=MySQLdb.cursors.DictCursor)
    print("SQL db connection done")
except Exception as e:
    db = None
    print("SQL db connection failed")
    print(e)

try:
    mongo_client = MongoClient('mongodb://localhost:27017/')
    print("Mongodb connection done")
except Exception as e:
    mongo_client = None
    mongo_db = None
    print("Mongodb connection failed")
    print(e)
if mongo_client is not None:
    mongo_db = mongo_client['Nudge']
    
suffix = "_nudge_app"

now = datetime.now()
year = now.year
#year = 2017
month = now.month
month = 3 
last_date = date(year, month+1,1) - timedelta(days=1)
total_days_in_month = last_date.strftime("%d")
month_date_list = [date(year,month,each).strftime("%Y-%m-%d") for each in range(1,int(total_days_in_month)+1)]
#print(month_date_list)
today_date = now.strftime("%Y-%m-%d")
day_subtract_dict = {"Mon" : -5, "Tue" : -4, "Wed" : -3, "Thur" : -2, "Fri" : -1, "Sat" : 0, "Sun" : 1}

print(year)
print(month)
print(today_date)

today_date = "2021-03-31"

def main():
    division_wise_off = division_holidays()
    #print(division_wise_off)
    summary = individual_summary(division_wise_off)
    create_nudges()
    #print(summary)
    return True

def create_db_conn():
    try:
        conn = db.cursor()
    except Exception as e:
        conn = None
        print("Db cursor couldn't initialise")
        print(e)
    return conn

def division_holidays():
    field_days = {}
    conn = create_db_conn()
    if conn is not None:
        query = "SELECT division,week_holiday,additionaloff FROM week_holidaymaster{} WHERE deleted=0".format(suffix)
        conn.execute(query)
        result = conn.fetchall()
        #division - 0 means rest of the division
        for row in result:
            field_days[row["division"]] = {}
            field_days[row["division"]]["holiday_dates"] = get_dates([row["week_holiday"], row["additionaloff"]])
        conn.close()
    return field_days

def user_holiday(user_id):
    dates = []
    conn = create_db_conn()
    if conn is not None:
        query = "select holidaytemplateid from user2holidaytemplate{append} where userid = {user_id} and yearid = {year}".format(user_id=user_id, year=year,append=suffix)
        conn.execute(query)
        result = conn.fetchall()
        #result = ({"holidaytemplateid" : 57},)
        conn.close()
        dates = holiday_template_dates(result)
        print("user holiday : ",user_id, dates,sep="\n")
    return dates

def mongo_dump(data, collection, many=0):
    if mongo_db is not None:
        col = mongo_db[collection]
        if many:
            col.insert_many(data)
        else:
            col.insert_one(data)
        print("Data dumped in ",collection)

def holiday_template_dates(result):
    dates = []
    conn = create_db_conn()
    col = mongo_db["holiday_template"]
    for each in result:
        holiday_template_id = each["holidaytemplateid"]
        if col.find_one({"holiday_template_id" : holiday_template_id, "year" : year}):
            dates = col.find_one({"holiday_template_id" : holiday_template_id, "year" : year},{"_id": 0,"dates": 1})["dates"]
        else:
            if conn is not None:
                query = "select date from holidaytemplatedetails{append} where id={holiday_template_id}".format(holiday_template_id=holiday_template_id, append=suffix)
                conn.execute(query)
                res = conn.fetchall()
                for e in res:
                    dates.append(e["date"].strftime("%Y-%m-%d"))
                mongo_dump({"holiday_template_id":holiday_template_id, "year":year, "dates":dates}, "holiday_template")
    conn.close()
    return dates

def user_leaves(user_id):
    dates = []
    conn = create_db_conn()
    if conn is not None:
        query = """SELECT * FROM `leaveapplication{append}` 
        left outer join leavestatus{append} on leaveapplication{append}.applno=leavestatus{append}.applno 
        where leaveapplication{append}.emp_id={user_id} and leavestatus{append}.approved!='Rejected' 
        and leavestatus{append}.approved!='Withdrawn' and year={year} and month={month}""".format(user_id=user_id, year=year, month=month, append=suffix)
        #for testing dates extraction if no of days leaves is more than 1 
        #query = "SELECT * FROM `leaveapplication` left outer join leavestatus on leaveapplication.applno=leavestatus.applno where leaveapplication.emp_id={user_id} and no_of_days>1".format(user_id=user_id)
        conn.execute(query)
        result = conn.fetchall()
        for each in result:
            from_dt = each["frm_date"]
            to_dt = each["to_date"]
            num_days = each["no_of_days"]
            next_dt = from_dt
            for i in range(int(num_days)):
                dates.append(next_dt.strftime("%Y-%m-%d"))
                next_dt = next_dt + timedelta(days=1) 
        #print("user leaves", dates, sep="\n")
        dates = list(set(dates))
        conn.close()
    return dates

def get_dates(day_names):
    dates = []
    day_num_list = []
    first_day = date(year, month, 1).isoweekday()

    def find_date(day_num):
        final_date = 7*int(day_num) - first_day + sub
        try:
            dt = date(year,month,final_date)
            final_date = dt.strftime("%Y-%m-%d")
        except ValueError:
            final_date = None
        return final_date

    for each in day_names:
        if regex.findall("\d", each):
            day = each[0:-1]
            day_num = each[-1]
            day_num_list = []
        else:
            day = each
            day_num_list = [1,2,3,4,5]
        sub = day_subtract_dict[day]
        if day_num_list:
            for day_num in day_num_list:
                output = find_date(day_num)
                if output:
                    dates.append(output)
        else:
            output = find_date(day_num)
            if output:
                dates.append(output)
        
    return dates

def individual_summary(division_wise_off):
    result = ()
    data = []
    conn = create_db_conn()
    if conn is not None:
        #remove limit 3 in prod
        query = "select id, division, user_name, employeecode, monthdays, leaves, field_days as field_days_till_date ,tot_dr_calls , avg_dr_calls as call_avg , tot_dr_met , tot_chem_calls, avg_chem_calls, deviation_days from individual_mis_summary{append} where month={month} and year={year} and user_name!='Vacant' limit 5".format(month=month, year=year, append=suffix)
        #print(query)
        conn.execute(query)
        result = conn.fetchall()
        #print(result) #tuple
        for row in result:
            score = 0
            temp = deepcopy(row)
            temp["year"] = year
            temp["month"] = month 
            for k,v in temp.items():
                if k in ["leaves", "field_days_till_date","tot_dr_calls","call_avg", "tot_dr_met", "tot_chem_calls",
                "avg_chem_calls", "deviation_days"]:
                    temp[k] = float(v)
            #tp deviation
            temp["tp_deviation"] = round(temp["deviation_days"]/temp["field_days_till_date"] * 100,2) if temp["field_days_till_date"] else 0
            if temp["tp_deviation"] < 10:
                score += 10
            print(row["id"])
            territory_info = user_area(row["id"]) 
            temp["patchid"] = territory_info.get("patchid", None)
            temp["areaid"] = territory_info.get("areaid", None)
            temp["regionid"] = territory_info.get("regionid", None)
            temp["zoneid"] = territory_info.get("zoneid", None)
            temp["patchname"] = territory_info.get("patchname", None)
            temp["areaname"] = territory_info.get("areaname", None)
            temp["regionname"] = territory_info.get("regionname", None)
            temp["zonename"] = territory_info.get("zonename", None)
            div = row["division"]
            if div in division_wise_off:
                week_off = division_wise_off[div]
            else:
                week_off = division_wise_off[0]
            holidays = user_holiday(row["id"])
            leaves = user_leaves(row["id"])
            temp["leaves"] = leaves
            temp["holidays"] = holidays
            temp["field_days"] = sorted(list(set(month_date_list) - set(week_off.get("holiday_dates",[])) - set(holidays) - set(leaves)))
            temp["no_of_field_days"] = len(temp["field_days"])
            temp = adminmis(temp)
            if temp.get("coverage", None):
                if temp["coverage"] > 90:
                    score += 10
                if temp["coverage"] > 95:
                    score += 10
            tp = tour_plan(row["id"])
            dcr_report = mongo_db["dcr"].find_one({"id": row["id"], "year": year, "month": month}, {"_id": 0});
            if dcr_report:
                dcr(dcr_report)
            else:
                dcr_report = {}
                dcr_report["id"] = row["id"]
                dcr_report["year"] = year
                dcr_report["month"] = month
                dcr_report["dcr_open"] = []
                dcr_report["dcr_completed"] = []
                for dt in temp["field_days"]:
                    dcr_report["dcr_open"].append({"date": dt, "status": "Open", "completed": None, "filedWithin": None})
                dcr(dcr_report)
            lock = dcr_lock(row["id"])
            if lock:
                no_lock = True
                for e in lock:
                    if e["year"] == year and e["month"] == month:
                        no_lock = False
                        break
                if no_lock:
                    score += 10
            else:
                score += 10
            stats_temp = {"id":row["id"], "2V":two_v_coverage(row["id"]), "call_avg":call_avg(row["id"]),  
            "tp_dev":tp_dev(row["id"]), "dcr":lock, "score":score}
            resp_stat = mongo_db["statistics"].find_one({"id":row["id"]})
            if resp_stat:
                mongo_db["statistics"].replace_one({"id":row["id"]},stats_temp)
            else:
                mongo_db["statistics"].insert(stats_temp)
            temp["score"] = score
            resp_user_data = mongo_db["user_data"].find_one({"id":row["id"], "year":year, "month":month})
            if resp_user_data:
                mongo_db["user_data"].replace_one({"id":row["id"]}, temp)
            else:
                mongo_db["user_data"].insert(temp)
            data.append(temp)
        #mongo_dump(data, "user_data", many=1)
        conn.close()
    # print (data)
    return data

def adminmis(temp):
    result = {}
    conn = create_db_conn()
    user_id = temp["id"]
    temp["spl"] = {}
    temp["rpl"] = {}
    temp["tpl"] = {}
    temp["qpl"] = {}
    avg = 0
    count = 0
    if conn is not None:
        query = """select drsinlistspl,drsinlistrpl,drsinlisttpl,drsinlistqpl,drsinlisttotal,
        spl_dr_met_once, spl_dr_met_twice, spl_dr_met_thrice, spl_dr_met_four, spl_dr_met_more_four,   
        rpl_dr_met_once, rpl_dr_met_twice, rpl_dr_met_thrice, rpl_dr_met_four, rpl_dr_met_more_four,   
        tpl_dr_met_once, tpl_dr_met_twice, tpl_dr_met_thrice, tpl_dr_met_four, tpl_dr_met_more_four,   
        qpl_dr_met_once, qpl_dr_met_twice, qpl_dr_met_thrice, qpl_dr_met_four, qpl_dr_met_more_four,   
        spl_freqcoverage,rpl_freqcoverage,tpl_freqcoverage,qpl_freqcoverage,nooddrsmettotal, 
        reports_to_id from adminmis{append} where userid = {user_id} and month = {month} and year = {year}""".format(user_id=user_id, month=month, year=year, append=suffix)
        conn.execute(query)
        result = conn.fetchone()
        if not result:
            result = {}
        #print(result)
        temp["manager_id"] = result.get("reports_to_id", None)
        temp["spl"]["tot_drs"] = result.get("drsinlistspl", 0)
        temp["rpl"]["tot_drs"] = result.get("drsinlistrpl", 0)
        temp["tpl"]["tot_drs"] = result.get("drsinlisttpl", 0)
        temp["qpl"]["tot_drs"] = result.get("drsinlistqpl", 0)
        temp["spl"]["dr_met_1"] = result.get("spl_dr_met_once", 0)
        temp["rpl"]["dr_met_1"] = result.get("rpl_dr_met_once", 0)
        temp["tpl"]["dr_met_1"] = result.get("tpl_dr_met_once", 0)
        temp["qpl"]["dr_met_1"] = result.get("qpl_dr_met_once", 0)
        temp["spl"]["dr_met_2"] = result.get("spl_dr_met_twice", 0)
        temp["rpl"]["dr_met_2"] = result.get("rpl_dr_met_twice", 0)
        temp["tpl"]["dr_met_2"] = result.get("tpl_dr_met_twice", 0)
        temp["qpl"]["dr_met_2"] = result.get("qpl_dr_met_twice", 0)
        temp["spl"]["dr_met_3"] = result.get("spl_dr_met_thrice", 0)
        temp["rpl"]["dr_met_3"] = result.get("rpl_dr_met_thrice", 0)
        temp["tpl"]["dr_met_3"] = result.get("tpl_dr_met_thrice", 0)
        temp["qpl"]["dr_met_3"] = result.get("qpl_dr_met_thrice", 0)
        temp["spl"]["dr_met_4"] = result.get("spl_dr_met_four", 0)
        temp["rpl"]["dr_met_4"] = result.get("rpl_dr_met_four", 0)
        temp["tpl"]["dr_met_4"] = result.get("tpl_dr_met_four", 0)
        temp["qpl"]["dr_met_4"] = result.get("qpl_dr_met_four", 0)
        temp["spl"]["dr_met_more_4"] = result.get("spl_dr_met_more_four", 0)
        temp["rpl"]["dr_met_more_4"] = result.get("rpl_dr_met_more_four", 0)
        temp["tpl"]["dr_met_more_4"] = result.get("tpl_dr_met_more_four", 0)
        temp["qpl"]["dr_met_more_4"] = result.get("qpl_dr_met_more_four", 0)
        temp["spl"]["coverage"] = float(result.get("spl_freqcoverage", 0))
        temp["rpl"]["coverage"] = float(result.get("rpl_freqcoverage", 0))
        temp["tpl"]["coverage"] = float(result.get("tpl_freqcoverage", 0))
        temp["qpl"]["coverage"] = float(result.get("qpl_freqcoverage", 0))
        if temp["spl"]["tot_drs"] > 0 :
            avg += temp["spl"]["coverage"]
            count += 1
        if temp["rpl"]["tot_drs"] > 0 :
            avg += temp["rpl"]["coverage"]
            count += 1
        if temp["tpl"]["tot_drs"] > 0 :
            avg += temp["tpl"]["coverage"]
            count += 1
        if temp["qpl"]["tot_drs"] > 0 :
            avg += temp["qpl"]["coverage"]
            count += 1
        if count != 0:
            avg = round(avg/count,2)
        temp["coverage"] = avg
    return temp

def dcr(dcr_report):
    result = ()
    user_id = dcr_report["id"]
    #dt_completed = [e["date"] for e in dcr_report["dcr_completed"]]
    dt_open = [e["date"] for e in dcr_report["dcr_open"]]
    conn = create_db_conn()
    if conn is not None:
        # considering only current month
        query = """select date, status, creation_date from dcrs_main{append} 
        where deleted = 0 and smownerid = {user_id} and year = {year} and month = {month}""".format(user_id=user_id, year=year, month=month, append=suffix)
        conn.execute(query)
        result = conn.fetchall()
        for each in result:
            dt = each["date"]
            dt_str = dt.strftime("%Y-%m-%d")
            status = each["status"]
            if status == "Pending":
                if dt_str in dt_open:
                    dcr_report["dcr_open"] = [each for each in dcr_report["dcr_open"] if each["date"] != dt_str ]
                    filedWithin = (each["creation_date"] - dt).days
                    dcr_report["dcr_completed"].append({"date":dt_str, "status":status, "completed_date":each["creation_date"].strftime("%Y-%m-%d"), "filedWithin":filedWithin})
            
                    
    resp_dcr = mongo_db["dcr"].find_one({"id":user_id, "year":year, "month":month})
    if resp_dcr:
        mongo_db["dcr"].replace_one({"id":user_id, "year":year, "month":month}, dcr_report)
    else:
        mongo_db["dcr"].insert(dcr_report)
    #mongo_dump(dcr_report, "dcr")

def tour_plan(user_id):
    result = ()
    dr_details = {"id":user_id, "year": year, "month":month, "data":[]}
    #checkIfInDb
    response = mongo_db["tour_plan"].find_one({"id":user_id, "year":year, "month":month},{"_id":0})
    if response and response.get("data",[]):
        return response
    conn = create_db_conn()
    if conn is not None:
        query1 = "Select crmid from touringyearmonth{append} where year={year} and month={month} and smownerid={user_id}".format(year=year, month=month, user_id=user_id, append=suffix) 
        conn.execute(query1)
        result = conn.fetchone()
        print(result)
        if result and result.get("crmid", None):
            crm_id = result["crmid"]
            query2 = """SELECT touringdetails{append}.patch_brick AS brickId, bricks{append}.brickname AS brickName, 
                        touringday{append}.`day` AS `day`, contactdetails{append}.contactid AS contactId, 
                        trim(concat(trim(contactdetails{append}.firstname),' ',trim(contactdetails{append}.lastname))) AS contactName, 
                        towns{append}.station as station, towns{append}.townid as townId,towns{append}.townname as townName FROM touringday{append} 
                        INNER JOIN touringdetails{append} ON touringdetails{append}.id = touringday{append}.daykey 
                        inner join contact_wise_bricks{append} on touringdetails{append}.patch_brick=contact_wise_bricks{append}.brick_id
                        INNER JOIN touringcontacts{append} ON touringcontacts{append}.id = touringdetails{append}.pid 
                        INNER JOIN contactdetails{append} ON contactdetails{append}.contactid = touringcontacts{append}.contactid
                        inner join bricks{append} on contact_wise_bricks{append}.brick_id=bricks{append}.brickid 
                        INNER JOIN townbrickassociation{append} ON contactdetails{append}.brick = townbrickassociation{append}.brickid 
                        INNER JOIN towns{append} ON townbrickassociation{append}.townid = towns{append}.townid 
                        INNER JOIN touringyearmonth{append} ON touringday{append}.crmid = touringyearmonth{append}.crmid 
                        WHERE touringcontacts{append}.contacttype = 'DR' and contactdetails{append}.deleted=0 and bricks{append}.deleted=0 
                        and contactdetails{append}.brick=touringdetails{append}.patch_brick and contact_wise_bricks{append}.deleted=0 AND touringyearmonth{append}.crmid = {crm_id}  
                        GROUP BY contactdetails{append}.contactid""".format(crm_id=crm_id, append=suffix)
            conn.execute(query2)
            drs_in_tp = conn.fetchall()
            if drs_in_tp:
                data = []
                for row in drs_in_tp:
                    data.append(row)
                dr_details["data"]= data
                resp_tp = mongo_db["tour_plan"].find_one({"id":user_id, "year":year, "month":month})
                if resp_tp:
                    mongo_db["tour_plan"].replace_one({"id":user_id, "year":year, "month":month}, dr_details)
                else:
                    mongo_dump(dr_details, "tour_plan")
            conn.close()
    return dr_details

def user_area(user_id):
    result = {}
    conn = create_db_conn()
    if conn is not None:
        query = """select users{append}.id, patches{append}.patchid, patches{append}.patchname, areas{append}.areaid, areas{append}.areaname,
         regions{append}.regionid, regions{append}.regionname, zones{append}.zoneid, zones{append}.zonename from patches{append} 
         inner join areas{append} on patches{append}.areaid = areas{append}.areaid 
         inner join regions{append} on patches{append}.regionid=regions{append}.regionid 
         inner join zones{append} on patches{append}.zoneid=zones{append}.zoneid 
         inner join users{append} on users{append}.patch=patches{append}.patchid where users{append}.id={user_id}""".format(user_id=user_id, append=suffix)
        conn.execute(query)
        result = conn.fetchone()
        if not result:
            result = {}
    return result
    
def ach_percent():
    query = ""

def two_v_coverage(user_id):
    data = []
    conn = create_db_conn()
    if conn is not None:
        query = "select year, month, drsinlistspl, drsinlistrpl, drsinlisttpl, drsinlistqpl, spl_freqcoverage,rpl_freqcoverage,tpl_freqcoverage,qpl_freqcoverage from adminmis{append} where userid = {user_id} order by year,month".format(user_id=user_id, append=suffix)
        conn.execute(query)
        result = conn.fetchall()
        for row in result:
            avg = 0
            count = 0
            temp = deepcopy(row)
            del temp["drsinlistspl"]
            del temp["drsinlistrpl"]
            del temp["drsinlisttpl"]
            del temp["drsinlistqpl"]
            temp["spl_freqcoverage"] = float(row.get("spl_freqcoverage", 0))
            temp["rpl_freqcoverage"] = float(row.get("rpl_freqcoverage", 0))
            temp["tpl_freqcoverage"] = float(row.get("tpl_freqcoverage", 0))
            temp["qpl_freqcoverage"] = float(row.get("qpl_freqcoverage", 0))
            if row["drsinlistspl"] > 0 :
                avg += temp["spl_freqcoverage"]
                count += 1
            if row["drsinlistrpl"] > 0 :
                avg += temp["rpl_freqcoverage"]
                count += 1
            if row["drsinlisttpl"] > 0 :
                avg += temp["tpl_freqcoverage"]
                count += 1
            if row["drsinlistqpl"] > 0 :
                avg += temp["qpl_freqcoverage"]
                count += 1
            if count != 0:
                avg = round(avg/count,2)
            temp["coverage"] = avg
            data.append(temp)
        conn.close()
    return data

def tp_dev(user_id):
    data = []
    conn = create_db_conn()
    if conn is not None:
        query = "select year, month, deviation_days, field_days, (deviation_days/field_days)*100 as tp_dev from individual_mis_summary{append} where id={user_id} order by year,month".format(user_id=user_id, append=suffix)
        conn.execute(query)
        result = conn.fetchall()
        for row in result:
            temp = deepcopy(row)
            #print(row)
            temp["deviation_days"] = float(row.get("deviation_days",0))
            temp["field_days"] = float(row.get("field_days",0))
            temp["tp_dev"] = float(row.get("tp_dev",0)) if row.get("tp_dev") else 0
            data.append(temp)
        conn.close()
    return data

def call_avg(user_id):
    data = []
    conn = create_db_conn()
    if conn is not None:
        query = "SELECT year, month, avg_dr_calls FROM `individual_mis_summary{append}` where id={user_id} order by year,month".format(user_id=user_id, append=suffix)
        conn.execute(query)
        result = conn.fetchall()
        for row in result:
            temp = deepcopy(row)
            temp["avg_dr_calls"] = float(row.get("avg_dr_calls", 0))
            data.append(temp)
        conn.close()
    return data

def dcr_lock(user_id):
    data = []
    conn = create_db_conn()
    if conn is not None:
        query = "SELECT year, month, leavetype, frm_date, to_date, no_of_days, reason FROM `leaveapplication{append}` where emp_id={user_id} and leavetype='LWP' and reason like '%DAR BLOCKED BY SYSTEM%' order by year,month".format(user_id=user_id, append=suffix)
        conn.execute(query)
        result = conn.fetchall()
        for row in result:
            temp = deepcopy(row)
            temp["frm_date"] = row.get("frm_date").strftime("%Y-%m-%d")
            temp["to_date"] = row.get("to_date").strftime("%Y-%m-%d")
            temp["no_of_days"] = float(row.get("no_of_days", 0))
            data.append(temp)
        conn.close()
    return data

def create_nudges():
    print ("in create_nudges")
    prev_nudges_list = mongo_db["notifications"].find({ "year": year, "month": month, "action_taken": False}, {"_id": 0})
    prev_nudges_dict = {}
    for row in prev_nudges_list:
        if row["id"] in prev_nudges_dict:
            prev_nudges_dict[row["id"]].append(row)
        else:
            prev_nudges_dict[row["id"]] = [row]
    all_user_data = mongo_db["user_data"].find({ "year": year, "month": month}, {"_id": 0})
    counter = 0
    for user_data in all_user_data:
        user_id = user_data["id"]
        
        prev_nudges = prev_nudges_dict[user_id] if prev_nudges_dict.get(user_id, None) else []

        ##############call average################
        call_avg_flag = True
        maintain_call_avg = 12
        
        #call_avg from current data, get from previous nudge is any
        current_call_avg = user_data.get("call_avg", 0)
        call_avg_condition = {
                "current_avg" : current_call_avg,
                "maintain_avg" : maintain_call_avg,
            }
        prev_call_avg = list(filter(lambda x:x['type'] == "call_avg", prev_nudges)) 
        if prev_call_avg:
            if list(filter(lambda x:x["condition"]["current_avg"] == call_avg_condition["current_avg"] and x["condition"]["maintain_avg"] == call_avg_condition["maintain_avg"], prev_call_avg)):
                call_avg_flag = False       
        if current_call_avg >= 12.0:
            text = "Hey you gained 10 points for maintaining a good call average, keep up the good work!!"
            call_avg_flag = False
        else:
            call_avg_diff = 12 - current_call_avg
            call_avg_diff = math.ceil(call_avg_diff)
            text = "Your call average is low. To maintain the call average, please add " + str(call_avg_diff) + " doctor visits to your tour plan."
        
        # write if call avg nudge to be sent or not or previously sent and is any improvement
        if call_avg_flag :
            nudge_dict = {
                "nudge_id":  str(int(time.time()))+ "_call_" + str(counter),
                "text" : text,
                "type": "call_avg",
                "created_date" : today_date,
                "action_taken" : False,
                "sent": False,
                "condition" : call_avg_condition,
                "previous_nudge_id" :"",
                "moved_todo" : False,
                "new": True,
                "month" : month,
                "year" : year
            }
            mongo_dump(nudge_dict, "notifications")
            counter +=1
        
        
        ##############tour plan deviation################
        tp_deviation = user_data.get("tp_deviation", 0)
        tp_dev_flag = True       
        tp_dev_condition = {
            "current_deviation" : tp_deviation
            }
        prev_tp = list(filter(lambda x:x['type'] == "tp_dev", prev_nudges)) 
        if prev_tp:
            if list(filter(lambda x:x["condition"]["current_deviation"] == tp_dev_condition["current_deviation"], prev_tp)):
                tp_dev_flag = False       
        ## check towards month end to send notification
        if tp_dev_flag and tp_deviation > 10:
            text = "You have deviated from your Tour Plan by " +str(tp_deviation) +"%. Please plan your Monthly Tour Plan properly to avoid loosing points"
            nudge_dict = {
                "nudge_id":  str(int(time.time()))+ "_tp_dev_" + str(counter),
                "text" : text,
                "type": "tp_dev",
                "created_date" : today_date,
                "action_taken" : False,
                "sent": False,
                "condition" : tp_dev_condition,
                "previous_nudge_id" :"",
                "moved_todo" : False,
                "new": True,
                "month" : month,
                "year" : year
            }
            mongo_dump(nudge_dict, "notifications")
            counter +=1
        
        
        ##############Coverage################
        coverage = user_data.get("coverage", 0)
        coverage_flag = True
        expected_coverage = 90
        if coverage >= 90:
            expected_coverage = 95
        coverage_condition = {
            "current_coverage":coverage,
            "expected_coverage": expected_coverage,
            }
        prev_coverage = list(filter(lambda x:x['type'] == "coverage", prev_nudges)) 
        if prev_coverage:
            if list(filter(lambda x:x["condition"]["current_coverage"] == coverage_condition["current_coverage"] and x["condition"]["expected_coverage"] == coverage_condition["expected_coverage"], prev_coverage)):
                coverage_flag = False
        
        if coverage <90:
            text = "You have visited "+str(coverage) + "% of doctors according to the number of visits planned, please try to cover more than 90% to gain points"
        elif coverage >=90 and coverage < 95 :
            text = "Congratulations on hitting 90% of your planned visits. On achieving 95%, you will be awarded extra 10 points."
        elif coverage >= 95:
            text = "well done"
            coverage_flag = False
            
        if coverage_flag:
            nudge_dict = {
                "nudge_id":  str(int(time.time()))+ "_coverage_" + str(counter),
                "text" : text,
                "type": "coverage",
                "created_date" : today_date,
                "action_taken" : False,
                "sent": False,
                "condition" : coverage_condition,
                "previous_nudge_id" :"",
                "moved_todo" : False,
                "new": True,
                "month" : month,
                "year" : year
            }
            mongo_dump(nudge_dict, "notifications")
            counter +=1
        
        ##############DCR filing################
        dcr_data = mongo_db["dcr"].find_one({ "id": user_id,"year": year, "month": month}, {"_id": 0})
        dcr_flag = False
        # today_date = "2021-03-31"
        dcr_condition = {
            "pending_days" : 5,
            "lock" : False,
            "date" : str(today_date)
            }

        prev_dcr = list(filter(lambda x:x['type'] == "dcr", prev_nudges)) 
        if prev_dcr:
            pending_dcr = list(filter(lambda x:x["condition"]["lock"] == False and x["condition"]["pending_days"] > 0, prev_dcr))
            dcr_open_dates = [each["date"] for each in dcr_data["dcr_open"]]
            dcr_pending = []
            for each in pending_dcr:
                if each["created_date"] in dcr_open_dates:
                    temp = deepcopy(pending_dcr[each])
                    temp["condition"]["pending_days"] = pending_dcr[each]["condition"]["pending_days"] - 1
                    if temp["condition"]["pending_days"] > 0:
                        temp["text"] = "A gentle reminder to file {} DCR report to avoid dcr lock.".format(each["created_date"])
                    else:
                        temp["text"] = "Sorry your DCR report for {} is locked. Please visit your manager.".format(each["created_date"])
                        temp["condition"]["lock"] = True
                    mongo_db["notifications"].replace_one({"id":user_id, "type":"dcr", "created_date": each["date"]}, temp)
                    dcr_pending.append(temp)

        
        for doc in dcr_data.get("dcr_open",[]):
            if doc.get("date","") == str(today_date) and doc.get("status","") == "Open":
                text = "A reminder to file the today's DCR report, to avoid DCR lock."
                dcr_flag = True
        
        if dcr_flag:
            nudge_dict = {
                "id" : user_id,
                "nudge_id":  str(int(time.time()))+ "_dcr_" + str(counter),
                "text" : text,
                "type": "dcr",
                "created_date" : today_date,
                "action_taken" : False,
                "sent": False,
                "condition" : dcr_condition,
                "previous_nudge_id" :"",
                "moved_todo" : False,
                "new": True,
                "month" : month,
                "year" : year
            }
            mongo_dump(nudge_dict, "notifications")
            counter +=1
            
        # check other old filings and pending details

        #################adding tour plan to TODO list#############
        day = int(now.strftime("%d"))
        tour_data = mongo_db["tour_plan"].find_one({ "id": user_id,"year": year, "month": month}, {"_id": 0});
        
        for tour in tour_data.get("data",[]):
            if tour["day"] == day and tour.get("contactName", ""):
                
                text = "Visit " + tour.get("contactName", "")
                todo_dict = {
                    "id" : user_id,
                    "todo_id":  str(int(time.time()))+ "_todo_" + str(counter),
                    "linked_nudge_id" : "",
                    "text": text,
                    "created_date" : today_date,
                    "action_taken" : False,
                    "sent": False,
                    "new": True,
                    "month" : month,
                    "year" : year
                }
                mongo_dump(todo_dict, "todo")
                counter +=1
        
        
        

if __name__=="__main__":
    print("Working...")
    main()
    # print(division_holidays())
    # print(get_dates(["Fri2", "Mon3", "Sun"]))
    # print(user_holiday(864))
    # print(user_leaves(864))
    # print(tour_plan(6035))

