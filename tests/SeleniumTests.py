#  Copyright 2011 Martin Nordholts <martin@chromecode.com>
#
#  Licensed under the Apache License, Version 2.0 (the "License");
#  you may not use this file except in compliance with the License.
#  You may obtain a copy of the License at
#
#      http://www.apache.org/licenses/LICENSE-2.0
#
#  Unless required by applicable law or agreed to in writing, software
#  distributed under the License is distributed on an "AS IS" BASIS,
#  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#  See the License for the specific language governing permissions and
#  limitations under the License.

# System tests using Selenium

from selenium import selenium
import unittest, time, re

class SeleniumTests(unittest.TestCase):
    def setUp(self):
        self.verificationErrors = []
        self.selenium = selenium("localhost", 4444, "*firefox /Applications/Firefox.app/Contents/MacOS/firefox", "http://localhost/")
        self.selenium.start()

        self.first_user = "SeleniumFirstUser";
        self.second_user = "SeleniumSecondUser";
        self.third_user = "SeleniumThirdUser";
        self.password = "password";
        self.second_logged_in_user_name = "SeleniumSecondUser";
        self.project_name = "SeleniumTestProject";
        self.project_url_name = "seleniumtestproject";
        self.dashboard_suffix = " dashboard - Task Taste";
        self.edited_first_task = "Edited first task"
        self.edited_first_task_again = "Edited first task again"
        self.edited_project_url = "/projects/" + self.third_user + "/" + self.project_url_name;
        self.edited_second_task_size = "42";
        self.edited_project_name = "SeleniumTestProject";
        self.new_task_name = "New task 1, click to edit";
        self.named_task_name = "Named new task";
        self.second_new_task_name = "New task 1, click to edit"; # Same, beause we refresh the page
        self.edited_new_task = "Rename first task so we can create a new one";
        self.second_edited_new_task = "Edited new task";
        self.first_task_name = "First task, click to edit";
        self.second_task_name = "Second task, click to edit";
        self.zero_size = '0';
        self.zeroed_task_name = self.edited_new_task;
        self.default_project_description = "Project description. Click here to edit if you are a logged in project administrator.";
        self.new_project_description = "This is an edited project description";
        self.cancelled_task_name = "Canceled task name text";
        

    # Should probably be split up into separate test_-methods
    def test_everything(self):
        sel = self.selenium

        print "Create account"
        self.open("/")
        sel.type("newusername", self.first_user)
        sel.type("newpassword", self.password)
        sel.type("newemail", "selenium@chromecode.com")
        sel.click("//input[@value='Create account']")
        sel.wait_for_page_to_load("30000")
        self.assertEquals(sel.get_title(), self.first_user + self.dashboard_suffix);

        print "Logout"
        self.open("/")
        sel.click("logout")
        sel.wait_for_page_to_load("30000")
        self.assertTrue(sel.is_text_present("Create Account"));

        print "Login"
        self.open("/")
        sel.type("username", self.first_user)
        sel.type("password", self.password)
        sel.click("//input[@value='Log in']")
        sel.wait_for_page_to_load("30000")
        self.assertEquals(sel.get_title(), self.first_user + self.dashboard_suffix);

        print "Logout again..."
        self.open("/")
        sel.click("logout")
        sel.wait_for_page_to_load("30000")
        self.assertTrue(sel.is_text_present("Create Account"));

        print "Create account that exists"
        self.open("/")
        sel.type("newusername", self.first_user)
        sel.type("newpassword", self.password)
        sel.click("//input[@value='Create account']");
        self.fixme_sleep();
        self.assertEquals(sel.get_alert(), "Failed to create account, try a different username.");

        print "Create account, no mail case"
        self.open("/")
        sel.type("newusername", self.second_user)
        sel.type("newpassword", self.password)
        sel.click("//input[@value='Create account']")
        sel.wait_for_page_to_load("30000")
        self.assertEquals(sel.get_title(), self.second_user + self.dashboard_suffix);

        print "Create project"
        self.open("/")
        sel.type("new-project-name", self.project_name)
        sel.click("new-project-submit")
        sel.wait_for_page_to_load("30000")
        self.assertEquals(sel.get_location()[len("http://localhost"):], "/projects/" + self.second_user + "/" + self.project_url_name);
        self.assertEquals(sel.get_title(), self.second_user + "/" + self.project_name + " - Task Taste Project Schedule");
        self.assertTrue(sel.is_text_present("Tasks left"));
        self.assertTrue(sel.is_text_present(self.first_task_name));
        self.assertTrue(sel.is_text_present("Size"));
        self.assertTrue(sel.is_text_present("5"));
        self.assertTrue(sel.is_text_present("Worked per week"));

        print "Logout from project page"
        self.open("/projects/" + self.second_user + "/" + self.project_url_name)
        sel.click("logout")
        sel.wait_for_page_to_load("30000")
        self.assertEquals(sel.get_location()[len("http://localhost"):], "/projects/" + self.second_user + "/" + self.project_url_name);
        # We have logged out and shall not see this text
        self.assertTrue(not sel.is_text_present("Worked per week"));

        print "Create account, no mail case (again)"
        self.open("/")
        sel.type("newusername", self.third_user)
        sel.type("newpassword", self.password)
        sel.click("//input[@value='Create account']")
        sel.wait_for_page_to_load("30000")
        self.assertEquals(sel.get_title(), self.third_user + self.dashboard_suffix);

        print "Create project, other user same project name"
        self.open("/")
        sel.type("new-project-name", self.project_name)
        sel.click("new-project-submit")
        sel.wait_for_page_to_load("30000")
        self.assertEquals(sel.get_location()[len("http://localhost"):], self.edited_project_url);
        self.assertEquals(sel.get_title(), self.third_user + "/" + self.project_name + " - Task Taste Project Schedule");

        print "Projects listed in dashboard"
        self.open("/");
        self.assertEquals(sel.get_title(), self.third_user + self.dashboard_suffix);
        self.assertTrue(sel.is_text_present(self.project_name));

        print "Projects in dashboard clickable"
        self.open("/");
        sel.click("link=" + self.project_name);
        sel.wait_for_page_to_load("30000");
        self.assertEquals(sel.get_location()[len("http://localhost"):], self.edited_project_url);

        print "Change first task name"
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.edited_first_task));
        sel.click("//h3[text()='" + self.first_task_name + "']");
        self.fixme_sleep();
        sel.type("input-field", self.edited_first_task)
        sel.click("save-button")
        self.fixme_sleep();
        # Reopen so we don't assert on the DHTML but on the static HTML
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.edited_first_task));

        print "Change first task size"
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.edited_second_task_size));
        sel.click("//h3[text()='5']");
        sel.type("input-field", self.edited_second_task_size);
        sel.click("save-button");
        self.fixme_sleep();
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.edited_second_task_size));

        print "Create new task"
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.new_task_name));
        sel.click("add-task-button");
        self.fixme_sleep();
        self.assertTrue(sel.is_text_present(self.new_task_name));
        # Make sure the task is in place also when we refresh the page
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.new_task_name));

        print "Create named new task"
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.named_task_name));
        sel.type("add-task-input-field", self.named_task_name);
        sel.click("add-task-button");
        self.fixme_sleep();
        self.assertTrue(sel.is_text_present(self.named_task_name));
        # Make sure the task is in place also when we refresh the page
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.named_task_name));

        print "Change new task name"
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.edited_new_task));
        sel.click("//h3[text()='" + self.new_task_name + "']");
        self.fixme_sleep();
        sel.type("input-field", self.edited_new_task)
        sel.click("save-button")
        self.fixme_sleep();
        # Reopen so we don't assert on the DHTML but on the static HTML
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.edited_new_task));

        print "Edit newly created task"
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.second_new_task_name));
        sel.click("add-task-button");
        self.fixme_sleep();
        sel.click("//h3[text()='" + self.second_new_task_name + "']");
        self.fixme_sleep();
        # Make sure we can edit the task without refreshing the page
        self.assertFalse(sel.is_text_present(self.second_edited_new_task));
        sel.type("input-field", self.second_edited_new_task)
        sel.click("save-button")
        self.fixme_sleep();
        # Reopen so we don't assert on the DHTML but on the static HTML
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.second_edited_new_task));

        print "Edit project description"
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.new_project_description));
        sel.click("//div[text()='" + self.default_project_description + "']");
        self.fixme_sleep();
        sel.type("input-field", self.new_project_description)
        sel.click("save-button")
        self.fixme_sleep();
        # Assert both on the DHTML and on the static HTML
        self.assertTrue(sel.is_text_present(self.new_project_description));
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.new_project_description));

        print "Set task size to 0"
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.zeroed_task_name));
        sel.click("//h3[text()='1']");
        sel.type("input-field", '0');
        sel.click("save-button");
        self.fixme_sleep();

        print "Cancel button can be clicked"
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.cancelled_task_name));
        sel.click("//h3[text()='" + self.edited_first_task + "']");
        self.fixme_sleep();
        self.assertTrue(sel.is_element_present("cancel-button"));
        self.assertTrue(sel.is_element_present("save-button"));
        sel.type("input-field", self.cancelled_task_name)
        sel.click("cancel-button")
        self.fixme_sleep();
        self.assertFalse(sel.is_element_present("cancel-button"));
        self.assertFalse(sel.is_element_present("save-button"));
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.cancelled_task_name));

        print "Escape can be used for Cancel"
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.cancelled_task_name));
        sel.click("//h3[text()='" + self.edited_first_task + "']");
        self.fixme_sleep();
        self.assertTrue(sel.is_element_present("cancel-button"));
        self.assertTrue(sel.is_element_present("save-button"));
        sel.type("input-field", self.cancelled_task_name)
        sel.key_press("input-field", '27');
        self.fixme_sleep();
        self.assertFalse(sel.is_element_present("cancel-button"));
        self.assertFalse(sel.is_element_present("save-button"));
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.cancelled_task_name));

        print "Return can be used for Save"
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.edited_first_task_again));
        sel.click("//h3[text()='" + self.edited_first_task + "']");
        self.fixme_sleep();
        self.assertTrue(sel.is_element_present("cancel-button"));
        self.assertTrue(sel.is_element_present("save-button"));
        sel.type("input-field", self.edited_first_task_again);
        sel.key_press("input-field", '13');
        self.fixme_sleep();
        self.assertFalse(sel.is_element_present("cancel-button"));
        self.assertFalse(sel.is_element_present("save-button"));
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.edited_first_task_again));

        print "Can't set zero-lengthed text"
        self.open(self.edited_project_url);
        sel.click("//h3[text()='" + self.edited_first_task_again + "']");
        self.fixme_sleep();
        sel.type("input-field", "");
        sel.click("save-button");
        self.assertEquals(sel.get_alert(), "Sorry, but you can not set text of zero length.");
        # Make sure we can use the input controls again, i.e. make
        # sure they are not still disabled
        sel.type("input-field", "");
        sel.click("save-button");
        self.assertEquals(sel.get_alert(), "Sorry, but you can not set text of zero length.");

        print "Delete task"
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.edited_first_task_again));
        sel.click("//div[@class='task']/div/a");
        self.fixme_sleep();
        self.assertFalse(sel.is_text_present(self.edited_first_task_again));
        self.open(self.edited_project_url);
        self.assertFalse(sel.is_text_present(self.edited_first_task_again));

        print "Can update worked per week"
        self.open(self.edited_project_url);
        sel.click("//div[@id='worked-per-week-setting']/h3[@class='size']");
        self.fixme_sleep();
        sel.type("input-field", "11");
        sel.click("save-button");
        self.fixme_sleep();
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present("11"));

        print "Zero sized tasks not shown when logged out"
        self.open(self.edited_project_url);
        self.assertTrue(sel.is_text_present(self.zeroed_task_name));
        sel.click("logout");
        sel.wait_for_page_to_load("30000");
        self.assertTrue(sel.is_text_present(self.edited_project_name));
        self.assertFalse(sel.is_text_present(self.zeroed_task_name));

    def open(self, url):
        self.selenium.open(url, ignoreResponseCode=False);

    def fixme_sleep(self):
        # We shouldn't use sleep, but rather get a callback when we
        # may move on
        time.sleep(1);
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
