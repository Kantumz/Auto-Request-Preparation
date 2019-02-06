# Auto-Request-Preparation
This class has been made to prepare distant queries. You just have to pass a classical SQL query to the class and it will be automatically prepared via PDO. It's in test for the moment, please feel free to send a feedback on possible ameliorations.

# Usage
1) Include the class in your file
2) Create constant "HOST", "USER", "PSWD" to connect your DB
3) Initialize the class by : 
    $cnx = new Requester();
    $cnx->setDB('psql', *DB_NAME*);
    
4) Then, launch queries you want to execute : $cnx->xQuery('SELECT * FROM animal WHERE animal_type = author');

# Options 

a) If you execute an insert, you can return the last insert ID (if it exists) simply by doing : $id = $cnx->lastId();
b) If you want to know the number of lines affected by your query, you can do : $count = $cnx->xCount();
c) You can choose the return type of your query (ASSOC by default), by using : $cnx->returnType('obj');
