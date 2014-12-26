Welcome to LETS-Software
====

A content management system catering to a local LETS. A full-featured suite of tools to transact, communicate and meet with your community. Submit articles and events, make comments, create auctions and much more!



System Requirement: 
- A Web server supporting PHP5
- PHP 5.2 or above
- MySQL 5.0.7 or above

Installation:
- Copy the files to your webhost making sure the index.php file is in the url root directory.
   For example /home/your_account or /home/your_friends_account/your_account
- Change the user name, password of the database user and give the name of the database in /includes/config.php

  IMPORTANT: The scripts will immediately be in an installation-ready state.

- Open your browser to the url you placed index.php in and follow the prompts.
   For example http://www.your-domain.com/ 
- This is the required configuration:
   - /images 777
   - /logs 777
   - .htaccess 777
   - /logs/<Your Site Name>.log 666
   - /logs/<Your Site Name>_Errors.log 666
- Once permissions are set refresh your browser and the script should set everything else up.
- You can them login with username:1 and password whatever you entered previously.
