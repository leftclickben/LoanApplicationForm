#!/bin/bash

rootPassword=$1

yes | mysqladmin -u root -p$rootPassword drop loanapps
mysqladmin -u root -p$rootPassword create loanapps
mysql -u loanapps -p"wee15gzQs3C69yf" loanapps < ../schema/create.sql
