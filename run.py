import regex
import MySQLdb
import MySQLdb.cursors
from copy import deepcopy
from pymongo import MongoClient
from datetime import datetime, date,timedelta

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
#year = 2017
month = now.month
last_date = date(year, month+1,1) - timedelta(days=1)
total_days_in_month = last_date.strftime("%d")
month_date_list = [date(year,month,each).strftime("%Y-%m-%d") for each in range(1,int(total_days_in_month)+1)]
#print(month_date_list)
#month = 4
today_date = now.strftime("%Y-%m-%d")
day_subtract_dict = {"Mon" : -5, "Tue" : -4, "Wed" : -3, "Thur" : -2, "Fri" : -1, "Sat" : 0, "Sun" : 1}

print(year)
print(month)
print(today_date)

def main():
    division_wise_off = division_holidays()
    summary, id_list = individual_summary()

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
        year = 2018
        query = "select holidaytemplateid from user2holidaytemplate where userid = {user_id} and yearid = {year}".format(user_id=user_id, year=year)
        #conn.execute(query)
        #result = conn.fetchall()
        result = ({"holidaytemplateid" : 57},)
        conn.close()
        print(result)
        dates = holiday_template_dates(result)
    return dates

def holiday_template_dates(result):
    dates = []
    conn = create_db_conn()
    for each in result:
        holiday_template_id = each["holidaytemplateid"]
        if conn is not None:
            print("here")
            query = "select date from holidaytemplatedetails where id={holiday_template_id}".format(holiday_template_id=holiday_template_id)
            conn.execute(query)
            res = conn.fetchall()
            for e in res:
                dates.append(e["date"].strftime("%Y-%m-%d"))
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

def individual_summary():
    result = ()
    id_list = []
    data = []
    conn = create_db_conn()
    if conn is not None:
        query = "select id, division, user_name, employeecode, monthdays, leaves, field_days as field_days_till_date ,tot_dr_calls , avg_dr_calls as call_avg , tot_dr_met , tot_chem_calls, avg_chem_calls from individual_mis_summary where month={month} and year={year} limit 3".format(month=month, year=year)
        #print(query)
        conn.execute(query)
        result = conn.fetchall()
        print(result) #tuple
        id_list = []
        for row in result:
            temp = deepcopy(row)
            id_list.append(row["id"])
            div = row["division"]
            if div in division_wise_off:
                week_off = division_holidays[div]
            else:
                week_off = division_holidays["0"]
            holidays = user_holiday(row["id"])
            leaves = user_leaves(row["id"])
            temp["field_days"] = list(set(month_date_list) - set(week_off) - set(holidays) - set(leaves))
            temp["no_of_field_days"] = len(temp["field_days"])
            data.append(row)  
        conn.close()
    return result, id_list, data


def tp_deviation():
    query = ""

def dcr():
    query = ""

def ach_percent():
    query = ""

def two_v_coverage():
    query = ""

if __name__=="__main__":
    #main()
    #print(division_holidays())
    #print(get_dates(["Fri2", "Mon3", "Sun"]))
    #print(user_holiday(864))
    #print(user_leaves(864))
    print("Working...")