import pdb
import json
import time
import math
from datetime import datetime
from pymongo import MongoClient
from flask import Flask, jsonify, request

app = Flask(__name__)

class FlaskApp():
    
    def __init__(self):
       
        
        self.mongo_client = MongoClient('mongodb://localhost:27017/')
        self.mongo_db = self.mongo_client['Nudge']
        
        
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
    
    def getUserData(self, user_id):
        collection = self.mongo_db["user_data"]
        data = collection.find_one({"id": user_id})
        if "_id" in data:
            del data["_id"]
        return data

    def get_leaderboard_data(self, user_id, filter_type="all_india"):
        result = []
        collection = self.mongo_db["user_data"]
        now = datetime.now()
        if filter_type == "all_india":
            resp = collection.find({"year":now.year, "month":now.month},{"_id":0, "id":1, "score":1}).sort("score",-1)
        elif filter_type == "zone":
            zone_id = self.mongo_db["user_data"].find_one({"id":user_id}, {"_id":0, "zoneid":1})["zoneid"]
            resp = collection.find({"zoneid":zone_id, "year":now.year, "month":now.month},{"_id":0, "id":1, "zoneid":1, "score":1, "user_name":1, "zonename":1}).sort("score",-1)
        elif filter_type == "region":
            region_id = self.mongo_db["user_data"].find_one({"id":user_id}, {"_id":0, "regionid":1})["regionid"]
            resp = collection.find({"regionid":region_id, "year":now.year, "month":now.month},{"_id":0, "id":1, "regionid":1, "score":1, "user_name":1, "regionname":1}).sort("score",-1)
        elif filter_type == "area":
            area_id = self.mongo_db["user_data"].find_one({"id":user_id}, {"_id":0, "areaid":1})["areaid"]
            resp = collection.find({"areaid":area_id, "year":now.year, "month":now.month},{"_id":0, "id":1, "areaid":1, "score":1, "user_name":1, "areaname":1}).sort("score",-1)
        rank = 0
        for i,row in enumerate(resp):
            print(row)
            if row['id'] == data['user_id']:
                rank = i+1
            result.append(row)
        return {"data":resp, "rank":rank}
    
    def get_stats_data(self, user_id):
        collection = self.mongo_db["statistics"]
        now = datetime.now()
        resp = collection.find_one({"id":user_id}, {"_id":0, "id":1, "2V":1, "dcr":1, "tp_dev":1, "call_avg":1, "score":1})
        resp["coverage"] = resp["2V"][-1]["coverage"]
        resp["tp_deviation"] = round(resp["tp_dev"][-1]["tp_dev"],2)
        field_days_till_date = self.mongo_db["user_data"].find_one({"id":user_id, "year":now.year, "month":now.month}, {"_id":0,"field_days_till_date":1})["field_days_till_date"]
        #field_days_till_date = self.mongo_db["user_data"].find_one({"id":user_id, "year":now.year, "month":3}, {"_id":0,"field_days_till_date":1})["field_days_till_date"]
        if field_days_till_date:
            resp["dcr_percent"] = round((len(list(filter(lambda x:x["year"]==now.year and x["month"]==now.month, resp["dcr"])))/field_days_till_date)*100,2)
        else:
            resp["dcr_percent"] = 0
        dcr = self.mongo_db["statistics"].aggregate([{"$match":{"id":user_id}}, {"$project":{"_id":0,"dcr":1}},{"$unwind":{"path":"$dcr"}},{"$group":{"_id":{"year":"$dcr.year","month":"$dcr.month"}, "count":{"$sum":"$dcr.no_of_days"}}}])
        dcr_stat = []
        for e in dcr:
            temp = {}
            temp["year"] = e["_id"]["year"]
            temp["month"] = e["_id"]["month"]
            temp["count"] = e["count"]
            dcr_stat.append(temp)
        resp["dcr"] = sorted(dcr_stat, key=lambda x:(x["year"],x["month"]))
        return resp

    def main(self, user_id):
        data = self.getUserData(user_id)
        nudge = []
        call_avg_flag = True
        call_avg_diff = 0
        
        coverage_2v_flag = True
        
        if data["avg_dr_calls"] >= 12.0:
            call_avg_flag = False
        else:
            call_avg_diff = 12 - data["avg_dr_calls"]
            call_avg_diff = math.ceil(call_avg_diff)
        if call_avg_flag:
            text = "Hey " + data["user_name"] + " you need to do an extra " + str(call_avg_diff) + " doctor vist to maintain your call average, you can edit your tour plan to cover more grounds!!"
            nudge.append(text)
        
        if coverage_2v_flag:
            ratio = data["nooddrsmetrpl"] / data["drsinlistrpl"]
            percent = int(math.floor(ratio*100))
            calls_left = math.ceil(data["noofdrcalls"])
            text = "Hi, your 2v coverage is " + str(percent) + "%, you have " + str(calls_left) + " doctors calls left to cover you 2v compliance, you can edit your tour plan to stay on track!!"
            nudge.append(text)
        if not nudge:
            text = "You are doing amazing work!!"
            nudge.append(text)
        return nudge

obj = FlaskApp()

@app.route("/get_nudge_count", methods=["GET"]) #user id
@app.route("/get_nudge", methods=["GET"]) #user id - nudge and todo
@app.route("/completed_nudge", methods=["GET"]) #user id, nudge id

@app.route("/add_todo", methods=["POST"]) #user id, nudge id/blank
@app.route("/completed_todo", methods=["POST"]) #user id, todo id 

@app.route("/get_leaderboard", methods=["GET"]) #user id, area filter
def get_leaderboard_data():
    data = json.loads(request.data)
    print(data)
    resp = obj.get_leaderboard_data(data["user_id"], filter_type=data["filter"])
    return json.dumps(resp)


@app.route("/get_stats", methods=["GET"]) #user id, type, filter
def get_stats_data():
    data = json.loads(request.data)
    print(data)
    resp = obj.get_stats_data(data['user_id'])
    return json.dumps(resp)


@app.route("/get_nudge", methods=["GET"])
def api_call():   
    data = json.loads(request.data)
    print(data)
    nudge = obj.main(data["user_id"])
    print (nudge)
    return json.dumps(nudge)

if __name__ == '__main__':
    print ("working...")
    app.run(host='0.0.0.0', port=5000)
    
    # obj.gm_worker.work()
    