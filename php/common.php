<?php

//This class will handle all the operations related to the database
class Db_Op
{
    //DB_HOST,DB_USER,DB_PASSWORD,DB_NAME these are contants present in the wordpress
    public $mysqli;
  
    //This method will make the database connection
    public function __contruct()
    {
        echo "É suposto entrar aqui";
      $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      var_dump(get_object_vars($this));
      if($this->mysqli->connect_errno)
      {
      	printf("Connect failed: %s\n", $mysqli->connect_error);
      	exit();
      }
    }
	
    //This method will receive a String(query) and will process it 
    //If the query received starts with 
    // SELECT, SHOW, DESCRIBE, EXPLAIN it will return a mysqli_result object
    // else it will return true and at last if something goes wrong after/during the query 
    // run the result that will be returned is false.
    public function runQuery($query)
    {
        echo $query;
        var_dump(get_object_vars($this));
    	$result = $this->mysqli->query($query);
	    if(!$result)
	    {
	    	echo "".$this->mysqli->error;
	    	exit();
	    }
	    else
	    {
	    	return $result;
	    }

    }
    
    //This method will disconnect a database connection
    public function disconnectToDb()
    {
      $this->mysqli->close();
    }
    
    //This method will receive a table name, a field name and will get all the value fron the field name. 
    public function getEnumValues($table, $field) 
    {
    	$enum_array = array();
    
    	$query = 'SHOW COLUMNS FROM `' . $table . '` LIKE "' . $field . '"';
    	$result = runQuery($query);

    		$row = $result->fetch_row();
    		//Trata a coluna onde está o enum e guarda o valor em enum_arry
    		preg_match_all("/'(.*?)'/", $row[1], $enum_array);
    
    		if(!empty($enum_array[1]))
    		{
    			// Shift array keys to match original enumerated index in MySQL (allows for use of index values instead of strings)
    
    			foreach($enum_array[1] as $mkey => $mval)
    			{
    				$enum_fields[$mkey+1] = $mval;
    			}
    			return $enum_fields;
    
    		}
    		else
    		{
    			return array(); // Return an empty array to avoid possible errors/warnings if array is passed to foreach() without first being checked with !empty().
    		}
    	}
   
}




















    //Array para a componente pesquisa dinâmica.

   function operadores()
   {
        $operadores = array(
            "menor"=>"<",
            "maior"=>">",
            "igual"=>"=",
            "diferente"=>"!="
            );
        return $operadores;
   }

	function goBack()
	{
		echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
		<noscript>
		<a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
		</noscript>";
	}


	
?>
