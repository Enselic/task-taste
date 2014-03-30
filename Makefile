#
# Copyright 2011 Martin Nordholts <martin@chromecode.com>
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#


# Manual configuration:
#

# HTML and PHP roots
APACHE_HTML_ROOT = /Library/WebServer/Documents/
PHP_INCLUDE_ROOT = /php/includes

# Paths to source of libraries we use
JQPLOT_SRC_ROOT = /Users/martin/Downloads

# Absolute path to phpunit
# Download with curl -O https://phar.phpunit.de/phpunit.phar and chmod +x it
PHPUNIT = /Users/martin/source/task-taste/phpunit.phar

#
# End of manual configuration

install-and-remove-config-php: install
	rm -r $(PHP_INCLUDE_ROOT)/TaskTasteConfig

install: install-dependencies
	@echo "Installing files served by httpd:"
	cp -r html/* $(APACHE_HTML_ROOT)
	@echo "Installing files on PHP include path:"
	cp -r includes/* $(PHP_INCLUDE_ROOT)

install-dependencies:
	@echo "Installing dependencies:"
	mkdir -p $(APACHE_HTML_ROOT)/externaljs $(APACHE_HTML_ROOT)/externalcss
	cp $(JQPLOT_SRC_ROOT)/dist/*.js $(APACHE_HTML_ROOT)/externaljs
	cp $(JQPLOT_SRC_ROOT)/dist/plugins/*.js $(APACHE_HTML_ROOT)/externaljs
	cp $(JQPLOT_SRC_ROOT)/dist/jquery.jqplot.css $(APACHE_HTML_ROOT)/externalcss

install-tests:
	@echo "Installing tests served by httpd:"
	cp tests/QUnitTests.html $(APACHE_HTML_ROOT)

tests: purge-database phpunit-tests selenium-tests

purge-database:
	@echo "Purging test database:"
	@php tests/purge-database.php

phpunit-tests:
	@echo "PHPUnit tests:"
	$(PHPUNIT) tests/UtilsTests.php
	$(PHPUNIT) tests/ProjectTests.php

selenium-tests: purge-database

	@echo "Selenium tests:"
	python tests/SeleniumTests.py


.PHONY: tests install phpunit-tests selenium-tests purge-database
