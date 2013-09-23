-- Loan application form db schema

create table `applications` (
	`id` int(11) not null auto_increment,
	`token` char(32) not null,
	`time_commenced` timestamp not null,
	`time_submitted` datetime default null,
	`loan_amount` int(11) default null,
	`australian_resident` tinyint(1) default null,
	`medicare` tinyint(1) default null,
	`loan_term_weeks` int(11) default null,
	`accepted_warning` tinyint(1) default null,
	`accepted_terms` tinyint(1) default null,
	`accepted_marketing` tinyint(1) default null,
	primary key (`id`)
);

create table `personal_details` (
	`id` int(11) not null auto_increment,
	`first_name` varchar(255) default null,
	`middle_name` varchar(255) default null,
	`last_name` varchar(255) default null,
	`date_of_birth` date default null,
	`email` varchar(255) default null,
	`phone_primary` char(10) default null,
	`phone_secondary` char(10) default null,
	primary key (`id`)
);

create table `application_personal_details` (
	`id` int(11) not null auto_increment,
	`application_id` int(11) not null,
	`personal_details_id` int(11) not null,
	primary key (`id`),
	foreign key (`application_id`) references `applications` (`id`),
	foreign key (`personal_details_id`) references `personal_details` (`id`)
);

create table `address_details` (
	`id` int(11) not null auto_increment,
	`address_line1` varchar(255) default null,
	`address_line2` varchar(255) default null,
	`suburb` varchar(64) default null,
	`postcode` char(4) default null,
	`state` char(3) default null, -- "WA ", "SA ", "NT ", "QLD", "NSW", "VIC", "TAS", "ACT"
	`duration_months` int(11) default null,
	primary key (`id`)
);

create table `application_address_details` (
	`id` int(11) not null auto_increment,
	`application_id` int(11) not null,
	`address_details_id` int(11) not null,
	primary key (`id`),
	foreign key (`application_id`) references `applications` (`id`),
	foreign key (`address_details_id`) references `address_details` (`id`)
);

create table `income_details` (
	`id` int(11) not null auto_increment,
	`type` char(1) default null, -- C = Contractor, T = Temp, P = Permanent, S = Self Employed, B = Benefits / Pension
	`description` varchar(255) default null,
	`amount` int(11) default null,
	`period` char(1) default null, -- W = Weekly, F = Fortnightly, M = Monthly
	`duration_months` int(11) default null,
	primary key (`id`)
);

create table `application_income_details` (
	`id` int(11) not null auto_increment,
	`application_id` int(11) not null,
	`income_details_id` int(11) not null,
	primary key (`id`),
	foreign key (`application_id`) references `applications` (`id`),
	foreign key (`income_details_id`) references `income_details` (`id`)
);
