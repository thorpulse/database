var search_data = {
    'index': {
        'searchIndex': ["icecave","icecave\\recoil","icecave\\recoil\\database","icecave\\recoil\\database\\exception","icecave\\recoil\\database\\connectionfactory","icecave\\recoil\\database\\connectionfactoryinterface","icecave\\recoil\\database\\connectioninterface","icecave\\recoil\\database\\exception\\databaseexception","icecave\\recoil\\database\\packageinfo","icecave\\recoil\\database\\statementinterface","icecave\\recoil\\database\\connectionfactory::__construct","icecave\\recoil\\database\\connectionfactory::connect","icecave\\recoil\\database\\connectionfactoryinterface::connect","icecave\\recoil\\database\\connectioninterface::prepare","icecave\\recoil\\database\\connectioninterface::query","icecave\\recoil\\database\\connectioninterface::exec","icecave\\recoil\\database\\connectioninterface::intransaction","icecave\\recoil\\database\\connectioninterface::begintransaction","icecave\\recoil\\database\\connectioninterface::commit","icecave\\recoil\\database\\connectioninterface::rollback","icecave\\recoil\\database\\connectioninterface::lastinsertid","icecave\\recoil\\database\\connectioninterface::errorcode","icecave\\recoil\\database\\connectioninterface::errorinfo","icecave\\recoil\\database\\connectioninterface::quote","icecave\\recoil\\database\\connectioninterface::setattribute","icecave\\recoil\\database\\connectioninterface::getattribute","icecave\\recoil\\database\\exception\\databaseexception::__construct","icecave\\recoil\\database\\statementinterface::execute","icecave\\recoil\\database\\statementinterface::setfetchmode","icecave\\recoil\\database\\statementinterface::fetch","icecave\\recoil\\database\\statementinterface::fetchobject","icecave\\recoil\\database\\statementinterface::fetchall","icecave\\recoil\\database\\statementinterface::fetchcolumn","icecave\\recoil\\database\\statementinterface::bindvalue","icecave\\recoil\\database\\statementinterface::bindparam","icecave\\recoil\\database\\statementinterface::bindcolumn","icecave\\recoil\\database\\statementinterface::columncount","icecave\\recoil\\database\\statementinterface::rowcount","icecave\\recoil\\database\\statementinterface::getcolumnmeta","icecave\\recoil\\database\\statementinterface::nextrowset","icecave\\recoil\\database\\statementinterface::closecursor","icecave\\recoil\\database\\statementinterface::setattribute","icecave\\recoil\\database\\statementinterface::getattribute","icecave\\recoil\\database\\statementinterface::errorcode","icecave\\recoil\\database\\statementinterface::errorinfo","icecave\\recoil\\database\\statementinterface::querystring","icecave\\recoil\\database\\statementinterface::debugdumpparams"],
        'info': [["Icecave","","Icecave.html","","",3],["Icecave\\Recoil","","Icecave\/Recoil.html","","",3],["Icecave\\Recoil\\Database","","Icecave\/Recoil\/Database.html","","",3],["Icecave\\Recoil\\Database\\Exception","","Icecave\/Recoil\/Database\/Exception.html","","",3],["ConnectionFactory","Icecave\\Recoil\\Database","Icecave\/Recoil\/Database\/ConnectionFactory.html","","Creates new connections by spawning sub-processes to",1],["ConnectionFactoryInterface","Icecave\\Recoil\\Database","Icecave\/Recoil\/Database\/ConnectionFactoryInterface.html","","Interface for database connection factories.",1],["ConnectionInterface","Icecave\\Recoil\\Database","Icecave\/Recoil\/Database\/ConnectionInterface.html","","An asynchronous PDO-like database connection.",1],["DatabaseException","Icecave\\Recoil\\Database\\Exception","Icecave\/Recoil\/Database\/Exception\/DatabaseException.html"," < PDOException","",1],["PackageInfo","Icecave\\Recoil\\Database","Icecave\/Recoil\/Database\/PackageInfo.html","","",1],["StatementInterface","Icecave\\Recoil\\Database","Icecave\/Recoil\/Database\/StatementInterface.html","","",1],["ConnectionFactory::__construct","Icecave\\Recoil\\Database\\ConnectionFactory","Icecave\/Recoil\/Database\/ConnectionFactory.html#method___construct","(string|null $commandLine = null)","",2],["ConnectionFactory::connect","Icecave\\Recoil\\Database\\ConnectionFactory","Icecave\/Recoil\/Database\/ConnectionFactory.html#method_connect","(string $dsn, string|null $username = null, string|null $password = null, array $driverOptions = array())","[COROUTINE] Establish a database connection.",2],["ConnectionFactoryInterface::connect","Icecave\\Recoil\\Database\\ConnectionFactoryInterface","Icecave\/Recoil\/Database\/ConnectionFactoryInterface.html#method_connect","(string $dsn, string|null $username = null, string|null $password = null, array $driverOptions = array())","[COROUTINE] Establish a database connection.",2],["ConnectionInterface::prepare","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_prepare","(string $statement, <abbr title=\"Icecave\\Recoil\\Database\\array&lt;integer,mixed&gt;\">array&lt;integer,mixed&gt;<\/abbr> $attributes = array())","[COROUTINE] Prepare an SQL statement to be executed.",2],["ConnectionInterface::query","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_query","(string $statement, integer|null $mode = null, string|object|null $fetchArgument = null, array $constructorArguments = null)","[COROUTINE] Execute an SQL statement and return the",2],["ConnectionInterface::exec","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_exec","(string $statement)","[COROUTINE] Execute an SQL statement and return the",2],["ConnectionInterface::inTransaction","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_inTransaction","()","[COROUTINE] Returns true if there is an active transaction.",2],["ConnectionInterface::beginTransaction","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_beginTransaction","()","[COROUTINE] Start a transation.",2],["ConnectionInterface::commit","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_commit","()","[COROUTINE] Commit the active transaction.",2],["ConnectionInterface::rollBack","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_rollBack","()","[COROUTINE] Roll back the active transaction.",2],["ConnectionInterface::lastInsertId","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_lastInsertId","(string|null $name = null)","[COROUTINE] Get the ID of the last inserted row.",2],["ConnectionInterface::errorCode","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_errorCode","()","[COROUTINE] Get the most recent status code for this",2],["ConnectionInterface::errorInfo","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_errorInfo","()","[COROUTINE] Get status information about the last operation",2],["ConnectionInterface::quote","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_quote","(string $string, integer $parameterType = PDO::PARAM_STR)","[COROUTINE] Quotes a string using an appropriate quoting",2],["ConnectionInterface::setAttribute","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_setAttribute","(integer $attribute, mixed $value)","[COROUTINE] Set the value of an attribute.",2],["ConnectionInterface::getAttribute","Icecave\\Recoil\\Database\\ConnectionInterface","Icecave\/Recoil\/Database\/ConnectionInterface.html#method_getAttribute","(integer $attribute)","[COROUTINE] Get the value of an attribute.",2],["DatabaseException::__construct","Icecave\\Recoil\\Database\\Exception\\DatabaseException","Icecave\/Recoil\/Database\/Exception\/DatabaseException.html#method___construct","(string $message, string|null $code = null, array|null $errorInfo = null)","",2],["StatementInterface::execute","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_execute","(array $inputParameters = array())","[COROUTINE] Execute the prepared statement.",2],["StatementInterface::setFetchMode","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_setFetchMode","(integer $mode, string|object|null $fetchArgument = null, array $constructorArguments = null)","[COROUTINE] Set the default fetch mode for this statement.",2],["StatementInterface::fetch","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_fetch","(integer|null $mode = null, integer $cursorOrientation = PDO::FETCH_ORI_NEXT, integer $cursorOffset)","[COROUTINE] Fetch the next result in the rowset.",2],["StatementInterface::fetchObject","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_fetchObject","(string $className = &#039;stdClass&#039;, array $constructorArguments = array())","[COROUTINE] Fetch the next result in the rowset as",2],["StatementInterface::fetchAll","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_fetchAll","(integer|null $mode = null, string|object|null $fetchArgument = null, array $constructorArguments = null)","[COROUTINE] Fetch all remaining results.",2],["StatementInterface::fetchColumn","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_fetchColumn","(integer $columnIndex)","[COROUTINE] Return a single column from the next row",2],["StatementInterface::bindValue","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_bindValue","(integer|string $parameter, mixed $value, integer $dataType = PDO::PARAM_STR)","[COROUTINE] Bind a value to a named or positional placeholder.",2],["StatementInterface::bindParam","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_bindParam","(integer|string $parameter, mixed $value, integer $dataType = PDO::PARAM_STR, integer|null $length = null, mixed $driverOptions = null)","[COROUTINE] Bind a variable reference to a named or",2],["StatementInterface::bindColumn","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_bindColumn","(string|integer $column, mixed $value, integer $dataType = null, integer|null $length = null, mixed $driverOptions = null)","[COROUTINE] Bind a column to a variable reference.",2],["StatementInterface::columnCount","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_columnCount","()","[COROUTINE] Fetch the number of columns in the rowset.",2],["StatementInterface::rowCount","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_rowCount","()","[COROUTINE] Fetch the number of rows affected by the",2],["StatementInterface::getColumnMeta","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_getColumnMeta","(integer $columnIndex)","[COROUTINE] Retrieve meta-data about a column in the",2],["StatementInterface::nextRowset","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_nextRowset","()","[COROUTINE] Advance to the next rowset in a multi-rowset",2],["StatementInterface::closeCursor","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_closeCursor","()","[COROUTINE] Close the cursor, allowing the statement",2],["StatementInterface::setAttribute","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_setAttribute","(integer $attribute, mixed $value)","[COROUTINE] Set the value of an attribute.",2],["StatementInterface::getAttribute","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_getAttribute","(integer $attribute)","[COROUTINE] Get the value of an attribute.",2],["StatementInterface::errorCode","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_errorCode","()","[COROUTINE] Get the most recent status code for this",2],["StatementInterface::errorInfo","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_errorInfo","()","[COROUTINE] Get status information about the last operation",2],["StatementInterface::queryString","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_queryString","()","[COROUTINE] Fetch the query string used by the statement.",2],["StatementInterface::debugDumpParams","Icecave\\Recoil\\Database\\StatementInterface","Icecave\/Recoil\/Database\/StatementInterface.html#method_debugDumpParams","()","[COROUTINE] Dump debug information about the prepared",2]]
    }
}
search_data['index']['longSearchIndex'] = search_data['index']['searchIndex']