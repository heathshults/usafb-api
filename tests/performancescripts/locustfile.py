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

class LoginTaskSet(TaskSet):

# PerfTest Login  End Point Url /login
    @task(1)
    def login(self):
        userDataList=read_csv(os.path.dirname(os.path.realpath(__file__))+"/_Data/User_Data.csv")
        userName = [d.get('column_0') for d in userDataList]
        password = [d.get('column_1') for d in userDataList]
        response = self.client.post("/login", data=json.dumps({"email":userName[0],"password":password[0]}),headers={'Content-Type': 'application/json', 'Accept': 'application/json','Accept-Encoding':'gzip'})
        print(response.json())

class Auth_Login(HttpLocust):
     min_wait=5000
     max_wait=15000
     users = [{'login_name': "autouser@gmail.com", 'password': "password123"}, {'login_name': "lolo@gmail.com", 'password': "lolo"}]
     task_set = LoginTaskSet

