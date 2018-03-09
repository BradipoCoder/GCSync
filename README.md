TODOS(4me):
-----------
- cleanup + restructure commands move them into appropriate folder/ns
    - user: all commands called because in user-config-file
    - operations: all commands called because of queue items in db
- make selenium tunnel more robust - check for exceptions and add __shutdown function to quit browser automatically
- entity OperationQueueItem must be changed:
    - we need explicit "command name" OR "command class"(maybe better) (add column) !!! so let's use alias so we can keep compat
    - we need 5 params columns so we can add params directly (this is so we can search in them -
    like the case of scrape profile where we are adding 3/4 time the same profile to be scraped) - the names of the parameters
     should be declared on the command class ... and also the command name to use (so above let's use command class fqcn)

- MainWorker command :
    - instead of using fixed sleeptime try to distribute workload equally in the day

- USR:addNewFriend
    - do that double control on add to friends button above/below
    - make screenshots of after clicks - so we can check


Run server(Linux-64:)
---------------------
java -Xmx256m -Dwebdriver.chrome.driver=vendor/bin/chromedriver -jar vendor/se/selenium-server-standalone/bin/selenium-server-standalone.jar

-Dwebdriver.gecko.driver="vendor/bin/geckodriver"
-Dphantomjs.binary.path="vendor/bin/phantomjs"


Run server(Windows-64:)
---------------------
java -Xmx256m -Dwebdriver.chrome.driver="drivers/chromedriver.exe" -jar "vendor/se/selenium-server-standalone/bin/selenium-server-standalone.jar"



Enjoy!
------
run commands with console



Components - Manual Setup
-------------------------
- make sure to have JDK
- (cd project root)
- mkdir selenium
- cd selenium

- (check latest release of selenium server: http://selenium-release.storage.googleapis.com)
- wget http://selenium-release.storage.googleapis.com/3.9/selenium-server-standalone-3.9.1.jar

- (check latest release of gecko driver: https://github.com/mozilla/geckodriver/releases/latest)
- wget https://github.com/mozilla/geckodriver/releases/download/v0.19.1/geckodriver-v0.19.1-linux32.tar.gz
- tar -xf geckodriver-v0.11.1-linux32.tar.gz
- mv geckodriver geckodriver32
- wget https://github.com/mozilla/geckodriver/releases/download/v0.19.1/geckodriver-v0.19.1-linux64.tar.gz
- tar -xf geckodriver-v0.11.1-linux64.tar.gz
- mv geckodriver geckodriver64

- (check latest release of chromedriver: https://sites.google.com/a/chromium.org/chromedriver/downloads)
- wget https://chromedriver.storage.googleapis.com/2.35/chromedriver_linux64.zip

- (check latest release of phantomjs: https://github.com/ariya/phantomjs/releases/)
- wget https://github.com/ariya/phantomjs/releases/download/2.1.3/phantomjs