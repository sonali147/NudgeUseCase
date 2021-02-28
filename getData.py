import pdb
import json
import time
import MySQLdb
import MySQLdb.cursors

from pymongo import MongoClient

class SyncDB():
    
    def __init__(self):
        self.db=MySQLdb.connect(
            host="144.76.139.246",
            user="sonali_gupta",
            passwd="Si8D%4tG1P%J",
            db="lupinsales",
            cursorclass=MySQLdb.cursors.DictCursor)
        
        self.mongo_client = MongoClient('mongodb://localhost:27017/')
        self.mongo_db = self.mongo_client['Nudge']
        print("mongo connect done")
        self.main()
        
    def mongoDump(self, data, collection_name, many=0):
        # pdb.set_trace()
        collection = self.mongo_db[collection_name]
        if many:
            collection.insert_many(data)
            for doc in data:
                if "_id" in doc:
                    del doc["_id"]
        else:
            collection.insert_one(data)
            if "_id" in data:
                del data["_id"]
        print("data dumped into : ", collection_name)
    
    def getAllUsers(self):
        c = self.db.cursor()
        query = "SELECT * FROM `individual_mis_summary` WHERE month = 12 and year = 2020 and tot_dr_calls != 0 limit 10"
        c.execute(query)
        result = c.fetchall()
        c.close()
        print("getAllUsers done")
        return result
    
    def getUserDataAdminMIS(self, user_id):
        c = self.db.cursor()
        query = "SELECT userid,reports_to_id, division, month, year, employeesapcode, division1, designation,hq,hq_name, patch_name, fieldworkingdays, drsinlistrpl,nooddrsmetrpl, noofdrcalls, drcallavg, drmissedrpl, rpl_dr_met_once, rpl_dr_met_twice, rpl_dr_met_thrice, rpl_dr_met_four, rpl_dr_met_more_four FROM `adminmis` WHERE drsinlistrpl !=0 and userid = "+ str(user_id) +" and year = 2020 and month = 12"
        c.execute(query)
        result = c.fetchone()
        c.close()
        print("getUserDataAdminMIS done")
        return result
    
    def main(self):
        print("in main...")
        data = self.getAllUsers()
        for doc in data:
            user_data = self.getUserDataAdminMIS(doc["id"])
            print (type(doc))
            print (type(user_data))
            res = {**doc, **user_data}
            for k,v in res.items():
                if not isinstance(v, str):
                    if k in ["id", "division","monthdays", "month", "year", "userid", "reports_to_id"]:
                        res[k] = int(v)
                    else:
                        res[k] = float(v)
                
            # print("\n\n\n++++++++++++++++++++++++++++++++++")
            # print(res)
            # print("++++++++++++++++++++++++++++++++++")
            self.mongoDump(res, "user_data")
            time.sleep(1.0)
    
if __name__ == '__main__':
    print ("working...")
    SyncDB()
    # obj.gm_worker.work()
    