# phpMyAdmin MySQL-Dump
# version 2.2.6
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Nov 01, 2003 at 11:47 PM
# Server version: 3.23.49
# PHP Version: 4.2.0
# Database : `nb`
# --------------------------------------------------------

#
# Table structure for table `nb_categories`
#

CREATE TABLE nb_categories (
  category_id varchar(250) NOT NULL default '',
  probability double NOT NULL default '0',
  word_count bigint(20) NOT NULL default '0',
  PRIMARY KEY  (category_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `nb_references`
#

CREATE TABLE nb_references (
  id varchar(250) NOT NULL default '',
  category_id varchar(250) NOT NULL default '',
  content text NOT NULL,
  PRIMARY KEY  (id),
  KEY category_id (category_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `nb_wordfreqs`
#

CREATE TABLE nb_wordfreqs (
  word varchar(250) NOT NULL default '',
  category_id varchar(250) NOT NULL default '',
  count bigint(20) NOT NULL default '0',
  PRIMARY KEY  (word,category_id)
) TYPE=MyISAM;
