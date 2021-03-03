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
last_date = date(year, month+1,1) - timedelta(days=1)
total_days_in_month = last_date.strftime("%d")
month_date_list = [date(year,month,each).strftime("%Y-%m-%d") for each in range(1,int(total_days_in_month)+1)]
#print(month_date_list)
month = 4
today_date = now.strftime("%Y-%m-%d")
day_subtract_dict = {"Mon" : -5, "Tue" : -4, "Wed" : -3, "Thur" : -2, "Fri" : -1, "Sat" : 0, "Sun" : 1}

print(year)
print(month)
print(today_date)

def main():
    division_wise_off = division_holidays()
    #print(division_wise_off)
    id_list, summary = individual_summary(division_wise_off)
    print(summary)
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
        query = "SELECT * FROM `leaveapplication` left outer join leavestatus on leaveapplication.applno=leavestatus.applno where leaveapplication.emp_id={user_id} and leavestatus.approved!='Rejected' and leavestatus.approved!='Withdrawn' and year={year} and month={month}".format(user_id=user_id, year=year, month=month)
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
        conn.close()
    return dates

def get_dates(day_names):
    dates = []
    day_num_list = []
    first_day = date(year, month, 1).isoweekday()

    def find_date(day_num):
            final_date = 7*int(day_num) - first_day + sub
            try:
                date(year,month,final_date)
                final_date = "{year}-{month}-{date}".format(year=year, month=month, date=final_date)
            except ValueError:
                final_date = None
            return final_date

    for each in day_names:
        if regex.findall("\d", each):
            day = each[0:-1]
            day_num = each[-1]
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
        query = "select id, division, user_name, employeecode, monthdays, leaves, field_days as field_days_till_date ,tot_dr_calls , avg_dr_calls as call_avg , tot_dr_met , tot_chem_calls, avg_chem_calls from individual_mis_summary where month={month} and year={year} limit 3".format(month=month, year=year)
        #print(query)
        conn.execute(query)
        result = conn.fetchall()
        #print(result) #tuple
        id_list = []
        for row in result:
            temp = deepcopy(row)
            for k,v in temp.items():
                if k in ["leaves", "field_days_till_date","tot_dr_calls","call_avg", "tot_dr_met", "tot_chem_calls",
                "avg_chem_calls"]:
                    temp[k] = float(v)
            id_list.append(row["id"])
            div = row["division"]
            if div in division_wise_off:
                week_off = division_wise_off[div]
            else:
                week_off = division_wise_off[0]
            holidays = user_holiday(row["id"])
            leaves = user_leaves(row["id"])
            temp["leaves"] = leaves
            temp["holidays"] = holidays
            temp["field_days"] = sorted(list(set(month_date_list) - set(week_off) - set(holidays) - set(leaves)))
            temp["no_of_field_days"] = len(temp["field_days"])
            temp = adminmis(temp)
            data.append(temp)
        conn.close()
    return id_list, data

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

def dcr():
    query = ""

def ach_percent():
    query = ""

def two_v_coverage():
    query = ""

if __name__=="__main__":
    print("Working...")
    main()
    #print(division_holidays())
    #print(get_dates(["Fri2", "Mon3", "Sun"]))
    #print(user_holiday(864))
    #print(user_leaves(864))

