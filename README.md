Carbon14
========

[![Latest Stable Version](https://poser.pugx.org/smalot/carbon14/v/stable)](https://packagist.org/packages/smalot/carbon14)
[![Total Downloads](https://poser.pugx.org/smalot/carbon14/downloads)](https://packagist.org/packages/smalot/carbon14)
[![Latest Unstable Version](https://poser.pugx.org/smalot/carbon14/v/unstable)](https://packagist.org/packages/smalot/carbon14)
[![License](https://poser.pugx.org/smalot/carbon14/license)](https://packagist.org/packages/smalot/carbon14)
[![composer.lock](https://poser.pugx.org/smalot/carbon14/composerlock)](https://packagist.org/packages/smalot/carbon14)

**Table of Contents**



# Install

## Download


````sh
wget -O- https://github.com/smalot/carbon14/releases/download/v0.3.0/carbon14.phar > carbon14
chmod +x carbon14
````

*Note update url with the latest release number.*


## Initialize

Retrieve your private access token here https://console.online.net/en/api/access

Then, run the following command line

````sh
carbon14 init
````

Settings will be stored in this file

````
$HOME/.carbon14.yml
````

# Commands

````
Available commands:
  cron                Cron process
  help                Displays help for a command
  init                Init Carbon14
  list                Lists commands
  self-update         Updates Carbon14 to the latest version
 archive
  archive:freeze      Archive files from temporary storage
  archive:job:list    List all jobs of an archive
  archive:key:delete  Delete an archive's encryption key
  archive:key:get     Get an archive's encryption key
  archive:key:set     Set an archive's encryption key
  archive:list        List all archives
  archive:restore     Unarchive files into temporary storage
 job
  job:list            Get a list of jobs
  job:run             Run a job
 safe
  safe:create         Create a safe
  safe:delete         Delete a safe (archives included)
  safe:list           Get a list of the user's safes
````


# Jobs

A job is a sequence of basic tasks:
- selection of safe (*using config file or forced in command line*)
- selection (*eventually creation*) of an archive
- use of a source which provides one or more files
- file upload to the archive (*resume available using `FTP` protocol*)
- eventually a final cleanup in the archive

# Sources

## Direct

The `direct` source supports basic method provided by the `symfony/finder` files.

Sample file for a direct file transfer. (`$HOME/.carbon14/redmine.yml`)

````yaml
name: 'Redmine'
description: 'Transfer the 2 lastest backup files'
status: active
last_execution: "2016-12-31 12:51:00"
source:
  type: direct
  settings:
    finder:
      in: ['/data/redmine/backup']
      depth: '== 0'
      name: '*.tar'
      not_name: ~
      size: ~
      follow_links: false
      sort: modified_time
      reverse: true
      count: 2
````

## MySQL

@todo

## Postgresql

@todo

## Tarball

@todo

## Docker

@todo


