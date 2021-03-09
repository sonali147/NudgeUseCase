import regex
import MySQLdb.cursors
from copy import deepcopy
from pymongo import MongoClient
from datetime import datetime, date, timedelta

try:
    db = MySQLdb.connect(
        host="144.76.139.246",
        user="sonali_gupta",
        passwd="Si8D%4tG1P%J",
        db="lupinsales",
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
    

now = datetime.now()
year = now.year
year = 2017
month = now.month
#month = 4
last_date = date(year, month+1,1) - timedelta(days=1)
total_days_in_month = last_date.strftime("%d")
month_date_list = [date(year,month,each).strftime("%Y-%m-%d") for each in range(1,int(total_days_in_month)+1)]
#print(month_date_list)
today_date = now.strftime("%Y-%m-%d")
day_subtract_dict = {"Mon" : -5, "Tue" : -4, "Wed" : -3, "Thur" : -2, "Fri" : -1, "Sat" : 0, "Sun" : 1}

print(year)
print(month)
print(today_date)

def main():
    division_wise_off = division_holidays()
    #print(division_wise_off)
    summary = individual_summary(division_wise_off)
    print(summary)
    #mongo_dump(summary, "user_data", many=1)
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
        query = "SELECT division,week_holiday,additionaloff FROM week_holidaymaster WHERE deleted=0"
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
        query = "select holidaytemplateid from user2holidaytemplate where userid = {user_id} and yearid = {year}".format(user_id=user_id, year=year)
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
                query = "select date from holidaytemplatedetails where id={holiday_template_id}".format(holiday_template_id=holiday_template_id)
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
        query = """SELECT * FROM `leaveapplication` 
        left outer join leavestatus on leaveapplication.applno=leavestatus.applno 
        where leaveapplication.emp_id={user_id} and leavestatus.approved!='Rejected' 
        and leavestatus.approved!='Withdrawn' and year={year} and month={month}""".format(user_id=user_id, year=year, month=month)
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
    id_list = []
    data = []
    conn = create_db_conn()
    if conn is not None:
        #remove limit 3 in prod
        query = "select id, division, user_name, employeecode, monthdays, leaves, field_days as field_days_till_date ,tot_dr_calls , avg_dr_calls as call_avg , tot_dr_met , tot_chem_calls, avg_chem_calls from individual_mis_summary where month={month} and year={year} and id=6035 limit 3".format(month=month, year=year)
        #print(query)
        conn.execute(query)
        result = conn.fetchall()
        #print(result) #tuple
        for row in result:
            temp = deepcopy(row)
            temp["year"] = year
            temp["month"] = month 
            for k,v in temp.items():
                if k in ["leaves", "field_days_till_date","tot_dr_calls","call_avg", "tot_dr_met", "tot_chem_calls",
                "avg_chem_calls"]:
                    temp[k] = float(v)
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
            tp = tour_plan(row["id"])
            dcr_report = mongo_db["dcr"].find_one({"id": row["id"]}, {"_id": 0});
            if dcr_report:
                dcr(dcr_report)
            else:
                dcr_report = {}
                dcr_report["id"] = row["id"]
                dcr_report["dcr_open"] = []
                dcr_report["dcr_completed"] = []
                for dt in temp["field_days"]:
                    dcr_report["dcr_open"].append({"date": dt, "status": "Open", "completed": None, "filedWithin": None})
                dcr(dcr_report)
            data.append(temp)
            mongo_dump(data, "user_data", many=1)
        conn.close()
    return data

def adminmis(temp):
    result = {}
    conn = create_db_conn()
    user_id = temp["id"]
    temp["spl"] = {}
    temp["rpl"] = {}
    temp["tpl"] = {}
    temp["qpl"] = {}
    if conn is not None:
        query = """select drsinlistspl,drsinlistrpl,drsinlisttpl,drsinlistqpl,drsinlisttotal,
        spl_dr_met_once, spl_dr_met_twice, spl_dr_met_thrice, spl_dr_met_four, spl_dr_met_more_four,   
        rpl_dr_met_once, rpl_dr_met_twice, rpl_dr_met_thrice, rpl_dr_met_four, rpl_dr_met_more_four,   
        tpl_dr_met_once, tpl_dr_met_twice, tpl_dr_met_thrice, tpl_dr_met_four, tpl_dr_met_more_four,   
        qpl_dr_met_once, qpl_dr_met_twice, qpl_dr_met_thrice, qpl_dr_met_four, qpl_dr_met_more_four,   
spl_freqcoverage,rpl_freqcoverage,tpl_freqcoverage,qpl_freqcoverage,nooddrsmettotal, reports_to_id from adminmis               
where userid = {user_id} and month = {month} and year = {year}""".format(user_id=user_id, month=month, year=year)
        conn.execute(query)
        result = conn.fetchone()
        print(result)
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
    return temp

def tp_deviation():
    query = ""

def dcr(dcr_report):
    result = ()
    user_id = dcr_report["id"]
    dt_completed = [e["date"] for e in dcr_report["dcr_completed"]]
    dt_open = [e["date"] for e in dcr_report["dcr_open"]]
    conn = create_db_conn()
    if conn is not None:
        # considering only current month
        query = """select date, status, creation_date from dcrs_main 
        where deleted = 0 and smownerid = {user_id} and year = {year} and month = {month}""".format(user_id=user_id, year=year, month=month)
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
    mongo_dump(dcr_report, "dcr")

def tour_plan(user_id):
    result = ()
    dr_details = {"id":user_id, "year": year, "month":month, "data":[]}
    #checkIfInDb
    response = mongo_db["tour_plan"].find_one({"id":user_id, "year":year, "month":month},{"_id":0})
    if response and response.get("data",[]):
        return response
    conn = create_db_conn()
    if conn is not None:
        query1 = "Select crmid from touringyearmonth where year={year} and month={month} and smownerid={user_id}".format(year=year, month=month, user_id=user_id) 
        conn.execute(query1)
        result = conn.fetchone()
        print(result)
        if result.get("crmid", None):
            crm_id = result["crmid"]
            query2 = """SELECT touringdetails.patch_brick AS brickId, bricks.brickname AS brickName, 
                        touringday.`day` AS `day`, contactdetails.contactid AS contactId, 
                        trim(concat(trim(contactdetails.firstname),' ',trim(contactdetails.lastname))) AS contactName, 
                        towns.station as station, towns.townid as townId,towns.townname as townName FROM touringday 
                        INNER JOIN touringdetails ON touringdetails.id = touringday.daykey 
                        inner join contact_wise_bricks on touringdetails.patch_brick=contact_wise_bricks.brick_id
                        INNER JOIN touringcontacts ON touringcontacts.id = touringdetails.pid 
                        INNER JOIN contactdetails ON contactdetails.contactid = touringcontacts.contactid
                        inner join bricks on contact_wise_bricks.brick_id=bricks.brickid 
                        INNER JOIN townbrickassociation ON contactdetails.brick = townbrickassociation.brickid 
                        INNER JOIN towns ON townbrickassociation.townid = towns.townid 
                        INNER JOIN touringyearmonth ON touringday.crmid = touringyearmonth.crmid 
                        WHERE touringcontacts.contacttype = 'DR' and contactdetails.deleted=0 and bricks.deleted=0 
                        and contactdetails.brick=touringdetails.patch_brick and contact_wise_bricks.deleted=0 AND touringyearmonth.crmid = {crm_id}  
                        GROUP BY contactdetails.contactid""".format(crm_id=crm_id)
            conn.execute(query2)
            drs_in_tp = conn.fetchall()
            if drs_in_tp:
                data = []
                for row in drs_in_tp:
                    data.append(row)
                dr_details["data"]= data
                mongo_dump(dr_details, "tour_plan")
            conn.close()
    return dr_details

def ach_percent():
    query = ""


if __name__=="__main__":
    print("Working...")
    main()
    #print(division_holidays())
    #print(get_dates(["Fri2", "Mon3", "Sun"]))
    #print(user_holiday(864))
    #print(user_leaves(864))
    #print(tour_plan(6035))

