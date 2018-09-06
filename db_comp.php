<?php
// Configuration settings
$server1 = "";
$user1 = "";
$password1 = "";
$dbname1 = "";

$server2 = "";
$user2 = "";
$password2 = "";
$dbname2 = "";


// Class for table format  start
class ConsoleTable
{
    const HEADER_INDEX = -1;
    const HR = 'HR';

    /** @var array Array of table data */
    protected $data = array();
    /** @var boolean Border shown or not */
    protected $border = true;
    /** @var boolean All borders shown or not */
    protected $allBorders = false;
    /** @var integer Table padding */
    protected $padding = 1;
    /** @var integer Table left margin */
    protected $indent = 0;
    /** @var integer */
    private $rowIndex = -1;
    /** @var array */
    private $columnWidths = array();

   
    /**
     * Set headers for the columns in one-line
     * @param  array  Array of header cell content
     * @return object LucidFrame\Console\ConsoleTable
     */
    public function setHeaders(array $content)
    {
        $this->data[self::HEADER_INDEX] = $content;

        return $this;
    }

   

    /**
     * Adds a row to the table
     * @param  array  $data The row data to add
     * @return object LucidFrame\Console\ConsoleTable
     */
    public function addRow(array $data = null)
    {
        $this->rowIndex++;

        if (is_array($data)) {
            foreach ($data as $col => $content) {
                $this->data[$this->rowIndex][$col] = $content;
            }
        }

        return $this;
    }

   

    /**
     * Set padding for each cell
     * @param  integer $value The integer value, defaults to 1
     * @return object LucidFrame\Console\ConsoleTable
     */
    public function setPadding($value = 1)
    {
        $this->padding = $value;

        return $this;
    }

    /**
     * Set left indentation for the table
     * @param  integer $value The integer value, defaults to 1
     * @return object LucidFrame\Console\ConsoleTable
     */
    public function setIndent($value = 0)
    {
        $this->indent = $value;

        return $this;
    }

    

    /**
     * Print the table
     * @return void
     */
    public function display()
    {
        echo $this->getTable();
    }

    /**
     * Get the printable table content
     * @return string
     */
    public function getTable()
    {
        $this->calculateColumnWidth();

        $output = $this->border ? $this->getBorderLine() : '';
        foreach ($this->data as $y => $row) {
            if ($row === self::HR) {
                if (!$this->allBorders) {
                    $output .= $this->getBorderLine();
                    unset($this->data[$y]);
                }

                continue;
            }

            foreach ($row as $x => $cell) {
                $output .= $this->getCellOutput($x, $row);
            }
            $output .= PHP_EOL;

            if ($y === self::HEADER_INDEX) {
                $output .= $this->getBorderLine();
            } else {
                if ($this->allBorders) {
                    $output .= $this->getBorderLine();
                }
            }
        }

        if (!$this->allBorders) {
            $output .= $this->border ? $this->getBorderLine() : '';
        }

        if (PHP_SAPI !== 'cli') {
            $output = '<pre>'.$output.'</pre>';
        }

        return $output;
    }

    /**
     * Get the printable border line
     * @return string
     */
    private function getBorderLine()
    {
        $output = '';
        $columnCount = count($this->data[0]);
        for ($col = 0; $col < $columnCount; $col++) {
            $output .= $this->getCellOutput($col);
        }

        if ($this->border) {
            $output .= '+';
        }
        $output .= PHP_EOL;

        return $output;
    }

    /**
     * Get the printable cell content
     * @param integer $index The column index
     * @param array   $row   The table row
     * @return string
     */
    private function getCellOutput($index, $row = null)
    {
        $cell       = $row ? $row[$index] : '-';
        $width      = $this->columnWidths[$index];
        $pad        = $row ? $width - strlen($cell) : $width;
        $padding    = str_repeat($row ? ' ' : '-', $this->padding);

        $output = '';

        if ($index === 0) {
            $output .= str_repeat(' ', $this->indent);
        }

        if ($this->border) {
            $output .= $row ? '|' : '+';
        }

        $output .= $padding; # left padding
        $output .= str_pad($cell, $width, $row ? ' ' : '-'); # cell content
        $output .= $padding; # right padding
        if ($row && $index == count($row)-1 && $this->border) {
            $output .= $row ? '|' : '+';
        }

        return $output;
    }

    /**
     * Calculate maximum width of each column
     * @return array
     */
    private function calculateColumnWidth()
    {
        foreach ($this->data as $y => $row) {
            if (is_array($row)) {
                foreach ($row as $x => $col) {
                    $content = preg_replace('#\x1b[[][^A-Za-z]*[A-Za-z]#', '', $col);
                    if (!isset($this->columnWidths[$x])) {
                        $this->columnWidths[$x] = strlen($content);
                    } else {
                        if (strlen($col) > $this->columnWidths[$x]) {
                            $this->columnWidths[$x] = strlen($content);
                        }
                    }
                }
            }
        }

        return $this->columnWidths;
    }
}
/********************Class ConsoleTable End******************/

/*******************DB Comparison Start**********************/


// function call for database connection..
$handle_db1 = getConnection ( $server1, $user1, $password1, $dbname1 );
$handle_db2 = getConnection ( $server2, $user2, $password2, $dbname2 );


// Fetching tables from db1
$db1_tables = array ();
$db2_tables = array ();
$sql = "SHOW TABLES FROM $dbname1";
$result = mysqli_query ( $handle_db1, $sql );
if (! $result) 
{
	die ( "There is an error in showing tables from $dbname1" );
}
while ( $table = mysqli_fetch_array ( $result ) ) 
{
	$db1_tables [] = $table [0];
}


// Fetching tables from db2
$sql1 = "SHOW TABLES FROM $dbname2";
$result1 = mysqli_query ( $handle_db2, $sql1 );
if (! $result1) 
{
	die ( "There is an error in showing tables from $dbname2" );
}
while ( $table = mysqli_fetch_array ( $result1 ) ) 
{
	$db2_tables[]=$table [0];
}


//Checking for case difference in table names
$case_insensitive= array_uintersect($db1_tables, $db2_tables, "strcasecmp");
$case_sensitive=array_intersect($db1_tables, $db2_tables);
$result_array=array_diff($case_insensitive,$case_sensitive);
$result_array = array_values ( $result_array );

if(!empty($result_array))
{
	for($k=0;$k<count($result_array);$k++)
	{
		echo "$result_array[$k] have case difference in both dbs.\n";
	}
}	



//Checking for missing tables in db2
$missing_tables = array_udiff ( $db1_tables, $db2_tables,'strcasecmp' );
$missing_tables = array_values ( $missing_tables );
if (! empty ( $missing_tables )) 
{
	for($i = 0; $i < count ( $missing_tables ); $i ++) 
	{
		echo "$missing_tables[$i]  table is missing in $dbname2\n";
	}
}

		
// finding new tables in db2
$new_tables = array_udiff ( $db2_tables, $db1_tables,'strcasecmp' );
$new_tables = array_values ( $new_tables );
if (! empty ( $new_tables )) 
{
	for($j = 0; $j < count ( $new_tables ); $j ++) 
	{
		echo "New table $new_tables[$j]   in $dbname2\n";
	}
}




// finding common tables from both db..
$common_tables = array_uintersect($db1_tables, $db2_tables, "strcasecmp");
$common_tables = array_values ( $common_tables );
$table1_fields = array ();
$table2_fields = array ();
$fieldtype1 = array ();
$fieldtype2 = array ();


if (! empty ( $common_tables ) ) 
{
	for($z = 0; $z < count ( $common_tables ); $z ++) 
	{
		//Fetching columns and types of columns of db1 tables
		for($x=0;$x<count($db1_tables);$x++)
		{
			if(strcasecmp($common_tables[$z],$db1_tables[$x])==0)
			{	
				$qry1 = "SHOW COLUMNS FROM $db1_tables[$x]";// query for showing columns from db1 tables...
				$result3 = mysqli_query ( $handle_db1, $qry1 );
		
				if (! $result3) 
				{
					die ( "There is an error in showing columns from $dbname1.$common_tables[$z]" );
				}
				while ( $row = mysqli_fetch_array ( $result3 ) ) 
				{
					$table1_fields [] = $row [0]; // assigning columns.. 
					$fieldtype1 [$row [0]] = $row [1]; // assigning type of columns...
				}
				//taking rowcount of each common table in DB1
				$rowCount=mysqli_query($handle_db1,"SELECT count(*) as total from $db1_tables[$x]");
				$db1_rows=mysqli_fetch_assoc($rowCount);
				$db1_rowCount[$common_tables[$z]]=$db1_rows['total'];
			}
		}
		
		//Fetching columns and types of columns of db2 tables
		for($m=0;$m<count($db2_tables);$m++)
		{
			if(strcasecmp($common_tables[$z],$db2_tables[$m])==0)	
			{	
				$qry2 = "SHOW COLUMNS FROM $db2_tables[$m]";  // query for showing columns from db2 tables...
				$result4 = mysqli_query ( $handle_db2, $qry2 );
		
				if (! $result4) 
				{
					die ( "There is an error in showing columns from $dbname2.$common_tables[$z]" );
				}
				while ( $row1 = mysqli_fetch_array ( $result4 ) ) 
				{
					$table2_fields [] = $row1 [0]; // assigning columns...
					$fieldtype2 [$row1 [0]] = $row1 [1]; // assigning type of columns...
				}
				//Taking rowcount of each common table in DB2
				$rowCount_db2=mysqli_query($handle_db2,"SELECT count(*) as total from $db2_tables[$m]");
				$db2_rows=mysqli_fetch_assoc($rowCount_db2);
				$db2_rowCount[$common_tables[$z]]=$db2_rows['total'];
				
			}
		}
		
				
		//Checking for case differences in column names
		$case_fields= array_uintersect($table1_fields, $table2_fields, "strcasecmp");
		$incase_fields=array_intersect($table1_fields, $table2_fields);
		$diff_fields=array_diff($case_fields,$incase_fields);
		$diff_fields=array_values($diff_fields);
		if(!empty($diff_fields))
		{
			for($n=0;$n<count($diff_fields);$n++)
			{
				echo "$diff_fields[$n] column have case difference in table $common_tables[$z]\n";
			}	
		}	
		
		
		//Checking for missing columns in db2 tables
		$missing_fields = array_udiff ( $table1_fields, $table2_fields,"strcasecmp" ); 
		$missing_fields = array_values ( $missing_fields );
		if (! empty ( $missing_fields ) ) 
		{
			for($j = 0; $j < count ( $missing_fields ); $j ++) 
			{
				echo "$missing_fields[$j] field is missing in $dbname2.$common_tables[$z]\n";
			}
		}
		
		
		//Checking for new columns in db2 tables
		$new_fields = array_udiff ( $table2_fields, $table1_fields,"strcasecmp" ); 
		$new_fields = array_values ( $new_fields );
		
		if (! empty ( $new_fields )) 
		{
			for($j = 0; $j < count ( $new_fields ); $j ++) 
			{
				echo "$new_fields[$j] field is new in $dbname2.$common_tables[$z]\n";
			}
		}
		
		
		// finding types of columns
		foreach ( $fieldtype1 as $field => $type ) 
		{
			foreach ( $fieldtype2 as $key => $value ) 
			{
				if (strcasecmp($field,$key)==0) 
				{
					
					if ($type !== $value) 
					{
						echo "Type of field $key in $dbname2.$common_tables[$z] is $value, but type of field $field in $dbname1.$common_tables[$z] is $type\n";   
					}
				}
			}
		}
		
		unset ( $qry1, $result1, $result2, $table1_fields, $table2_fields, $fieldtype1, $fieldtype2 );
	}
	
}

// Rowcount of db1 remaining tables
$db1_newTables = array_udiff ( $db1_tables, $common_tables,'strcasecmp' );
$db1_newTables = array_values ( $db1_newTables );
for($p=0;$p<count($db1_newTables);$p++)
{
	$new_tableRowCount=mysqli_query($handle_db1,"SELECT count(*) as total from $db1_newTables[$p]");
	$db1_newTableRows=mysqli_fetch_assoc($new_tableRowCount);
	$db1_rowCount[$db1_newTables[$p]]=$db1_newTableRows['total'];
}
//Rowcount of db2 remaining tables
$db2_newTables = array_udiff ( $db2_tables, $common_tables,'strcasecmp' );
$db2_newTables = array_values ( $db2_newTables );	
for($h=0;$h<count($db2_newTables);$h++)
{
	$new_db2TableRowCount=mysqli_query($handle_db2,"SELECT count(*) as total from $db2_newTables[$h]");
	$db1_newDb2TableRows=mysqli_fetch_assoc($new_db2TableRowCount);
	$db2_rowCount[$db2_newTables[$h]]=$db1_newDb2TableRows['total'];
}
	
$total=array_unique( array_merge( $db1_tables, $db2_tables ) );
$totalTables=array_intersect_key($total,array_unique(array_map("StrToLower",$total)));// merging two db's tables in to an array by avoiding duplicates
$totalTables = array_values ( $totalTables );

// Function for database connection
function getConnection($servername, $username, $password, $dbname) 
{
	$conn = mysqli_connect ( $servername, $username, $password, $dbname );
	if (! $conn) 
	{
		die ( "Connection failed:$servername " . mysqli_connect_error () );
	}
	return $conn;
}
// Creating Class object for table display
$table = new ConsoleTable();
$table->setHeaders(array('Tablename', 'DB1 RowCount','DB2 RowCount'));
for($tableCount=0;$tableCount<count($totalTables);$tableCount++)
{
	$table->addRow(array($totalTables[$tableCount],isset($db1_rowCount[$totalTables[$tableCount]]) ? $db1_rowCount[$totalTables[$tableCount]]:'Table not found',isset($db2_rowCount[$totalTables[$tableCount]]) ? $db2_rowCount[$totalTables[$tableCount]]:'Table not found'));
}
$table->setPadding(2);
$table->setIndent(4);
$table->display();
?>
