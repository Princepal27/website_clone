<?php
class copy
{
     function custom_copy($src, $dst,$db,$ndb) 
    {
        
        // open the source directory
        $dir = opendir($src);
    
        // Make the destination directory if not exist
        @mkdir($dst);
    
        // Loop through the files in source directory
        while( $file = readdir($dir) ) {
    
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) )
                {
    
                    // Recursively calling custom copy function
                    // for sub directory
                    $this->custom_copy($src . '/' . $file, $dst . '/' . $file,$db,$ndb);
    
                }
                else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                    $path_to_file = $dst . '/' . $file;
                    $file_contents = file_get_contents($path_to_file);
                    $file_contents = str_replace($db, $ndb, $file_contents);
                    file_put_contents($path_to_file, $file_contents);
                }
            }
        }
    
        closedir($dir);
    
    }

    public function EXPORT_DATABASE($host,$user,$pass,$db,$srrc,$dsst,$dstimg, $tables=false, $backup_name=false)
    {
        set_time_limit(3000);
        $mysqli = new mysqli($host,$user,$pass,$db);
        $mysqli->select_db($db); 
        $mysqli->query("SET NAMES 'utf8'");
        $queryTables = $mysqli->query('SHOW TABLES');
        while($row = $queryTables->fetch_row())
        {
            $target_tables[] = $row[0];
        }
        if($tables !== false)
        { 
            $target_tables = array_intersect( $target_tables, $tables); 
        }
        $content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `".$db."`\r\n--\r\n\r\n\r\n";
    
        foreach($target_tables as $table)
        {
            if (empty($table))
            { 
                continue; 
            } 
            $result	= $mysqli->query('SELECT * FROM `'.$table.'`'); 
            $fields_amount=$result->field_count;  
            $rows_num=$mysqli->affected_rows; 	
            $res = $mysqli->query('SHOW CREATE TABLE '.$table);	
            $TableMLine=$res->fetch_row();
            $content .= "\n\n".$TableMLine[1].";\n\n";   
            $TableMLine[1]=str_ireplace('CREATE TABLE `','CREATE TABLE IF NOT EXISTS `',$TableMLine[1]);
    
            for ($i=0; $i < $fields_amount; $i++)
            {
                while($row = $result->fetch_row())
                {
                    $content .= "\nINSERT INTO ".$table." VALUES(";
                    for($j=0; $j<$fields_amount; $j++)
                    {
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) );
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ;
                        }
                        else
                        {
                            $content .= '""';
                        }
                        if ($j<($fields_amount-1))
                        {
                            $content.= ',';
                        }
                       
                    }
                    $content .=");\n"; 
                }
                
            }
            $content .="\n\n\n";
        }
        
        $handle = fopen('backup.sql', 'w+');
        fwrite($handle, $content);
        fclose($handle);
        $path_to_file = "backup.sql";
        $file_contents = file_get_contents($path_to_file);
        $file_contents = str_replace($srrc, $dsst, $file_contents);
        $file_contents = str_ireplace($db.".png",$dstimg, $file_contents);
        file_put_contents($path_to_file, $file_contents);
        
    }

    public function IMPORT_TABLES($host,$user,$pass,$ndb, $sql_file_OR_content)
    {
        set_time_limit(3000);
        $SQL_CONTENT = (strlen($sql_file_OR_content) > 300 ?  $sql_file_OR_content : file_get_contents($sql_file_OR_content)  );  
        $allLines = explode("\n",$SQL_CONTENT); 
    
        $mysqli = new mysqli($host, $user, $pass);
        if(!$mysqli)
        {
            die('Could not connect: ' . $mysqli->error);
        }
        if ($mysqli ->query("CREATE DATABASE IF NOT EXISTS ".$ndb))
        {
            $mysqli->select_db($ndb);
            $zzzzzz = $mysqli->query('SET foreign_key_checks = 0');	        
            preg_match_all("/\nCREATE TABLE(.*?)\`(.*?)\`/si", "\n". $SQL_CONTENT, $target_tables); 
            foreach ($target_tables[2] as $table)
            {
                $mysqli->query('DROP TABLE IF EXISTS '.$table);
            }         
            $zzzzzz = $mysqli->query('SET foreign_key_checks = 1');    
            $mysqli->query("SET NAMES 'utf8'");	
            $templine = '';	// Temporary variable, used to store current query
            foreach ($allLines as $line)	
            {											// Loop through each line
                if (substr($line, 0, 2) != '--' && $line != '') 
                {
                    $templine .= $line; 	// (if it is not a comment..) Add this line to the current segment
                    if (substr(trim($line), -1, 1) == ';')
                    {		// If it has a semicolon at the end, it's the end of the query
                        if(!$mysqli->query($templine))
                        { 
                            print('Error performing query \'<strong>' . $templine . '\': ' . $mysqli->error . '<br /><br />');  
                        }  
                        $templine = ''; // set variable to empty, to start picking up the lines after ";"
                    }
                }
            }
            
            
        }
        else
        {
            echo "Error creating database: " . $mysqli->error;
        }
    } 
}

?>