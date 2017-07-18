from locust import Locust
from locust import TaskSet
from locust import task
from locust import HttpLocust
import os


class LoginTaskSet(TaskSet):


    @task(1)
    def login(self):
        response = self.client.post("/login", {"username":"autouser@gmail.com","password":"password123"},headers={'Content-Type': 'application/json', 'Accept': 'application/json','Accept-Encoding':'gzip'})


class auth/Login(HttpLocust):
     min_wait=5000
     max_wait=15000
     users = [{'login_name': "autouser@gmail.com", 'password': "password123"}, {'login_name': "lolo@gmail.com", 'password': "lolo"}]
     task_set = LoginTaskSet

