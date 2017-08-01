from locust import Locust
from locust import TaskSet
from locust import task
from locust import HttpLocust
import os
import csv
import random
import json

#Method to read CSV data for data driven
def read_csv(filename):
    csvfile = list(csv.reader(open(filename)))
    csvdics = []
    for row in csvfile:
        row_dict = {}
        for i in range(len(row)):
            row_dict['column_%s' % i] = row[i]
        csvdics.append(row_dict)
    csvdics.pop(0)
    return csvdics

class ApiTaskSet(TaskSet):

    request_list = []
    bearer_token=""

# PerfTest Login  End Point Url /login
    @task(3)
    def login(self):
        userDataList=read_csv(os.path.dirname(os.path.realpath(__file__))+"/_Data/User_Data.csv")
        userName = [d.get('column_0') for d in userDataList]
        password = [d.get('column_1') for d in userDataList]
        response = self.client.post("/login", data=json.dumps({"email":userName[0],"password":password[0]}),headers={'Content-Type': 'application/json', 'Accept': 'application/json','Accept-Encoding':'gzip'})
        json_response_dict = response.json()
        print(json_response_dict)
        request_id = json_response_dict['access_token']
        self.request_list.append(request_id)

# Intial Execution Script to Get Token to be passed to UploadPlayer
    def on_start(self):
       self.login()

 # PerfTest UploadPlayer  End Point Url registrants/import
    @task(6)
    def uploadplayer(self):
        if len(self.request_list) > 1:
          self.bearer_token = self.request_list.pop(0)
        files = {'csv_file':  open(os.path.dirname(os.path.realpath(__file__))+"/_Data/uploadplayers/Sample_PlayerUpload.csv", 'rb')}
        response = self.client.post("/registrants/import",files=files,headers={'Accept-Encoding':'gzip','Authorization':'Bearer ' + self.bearer_token })
        print(response.status_code)
        print(response.content)
        if response.status_code != 200:
            raise self.client.ConnectionError(response.content)

# class to call the  TaskSet for all the API's
class APILocust(HttpLocust):
     min_wait=5000
     max_wait=15000
     task_set = ApiTaskSet
