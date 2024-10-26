REM ..\lib\phpunit\bin\phpunit --bootstrap "load.php" ../damix-tests
REM ..\lib\phpunit\bin\phpunit --display-warnings --bootstrap "..\..\apps\applitest\application.init.php" "../damix-tests"
..\lib\phpunit\bin\phpunit --display-warnings --display-errors --bootstrap "load.php" "../damix-tests" --exclude-group "pgsql,monkey"
REM ..\lib\phpunit\bin\phpunit --display-warnings --bootstrap "load.php" --filter MariadbMethodTest::testMariadbFactoryBase
REM ..\lib\phpunit\bin\phpunit --coverage-html "../../phpunit/html" --display-warnings --bootstrap "..\..\apps\applitest\application.init.php" "../damix-tests"
