<?php
include('function.php');
set_time_limit(3000);
$obj = new copy;
$time_start = microtime(true); 


$host = 'localhost';
$user = 'root';
$pass = '';
$sql_file_OR_content = 'backup.sql';

require 'vendor/autoload.php';
$inputFileName = 'book2.xlsx';
$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
$spreadsheet = $reader->load($inputFileName);

$worksheet = $spreadsheet->getActiveSheet();
$rows = [];
foreach ($worksheet->getRowIterator() AS $row) 
{
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
    $cells = [];
    foreach ($cellIterator as $cell) 
    {
        $cells[] = $cell->getValue();
    }
    $rows[] = $cells;
}
foreach($rows as $row)
{
    $src = $row[0]; //D:/xampp/htdocs/blog.com
    $dst = $row[1]; //D:/xampp/htdocs/blog3.mpcc.in
    $imgsrc = $row[2]; 

    $srrc = basename($src); //blog.com
    $dsst = basename($dst); //blog3.mpcc.in
    $dstimg = basename($imgsrc); //blog3.mpcc.png
    $olddatabase = pathinfo($srrc, PATHINFO_FILENAME); //blog
    $olddatabasee = pathinfo($olddatabase, PATHINFO_FILENAME); //blog
    $newdatabase = pathinfo($dsst,PATHINFO_FILENAME); //blog3.mpcc
    $newdatabasee = pathinfo($newdatabase,PATHINFO_FILENAME); //blog3
    // $dstimg = pathinfo($imgsrc,PATHINFO_FILENAME);
  
    $obj->custom_copy($src, $dst,$olddatabasee,$newdatabasee);
    $obj->Export_Database($host,$user,$pass,$olddatabasee,$srrc,$dsst,$dstimg,  $tables=false, $backup_name=false );
    $obj->IMPORT_TABLES($host, $user, $pass, $newdatabasee, $sql_file_OR_content);
    echo "successfully copy at " . $dst;
    echo "<br>";
    unlink('backup.sql');
    unlink($dst.'/wp-content/uploads/'.$olddatabasee.'.png');
    $imgdes = $dst.'/wp-content/uploads/'.$dstimg;
    copy($imgsrc,$imgdes);

    
    // $content ='
    // server {
    
    //     server_name '.$dsst.';
    
    //     root '.$dst.' ;
    //     index index.php index.html;
    
    //     location / {
    //             try_files $uri $uri/ =404;
    //     }
    //     location ~ \.php$ {
    //     #       include snippets/fastcgi-php.conf;
    //     #
    //     #       # With php-fpm (or other unix sockets):
    //             fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    //     #       # With php-cgi (or other tcp sockets):
    //     #       fastcgi_pass 127.0.0.1:9000;
    //             include fastcgi_params;
    //             fastcgi_index index.php;
    //             fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
    
    //     }
    // }
    // ';
    // $file_path = "/etc/nginx/sites-available/default";
    // $current = file_get_contents($file_path);
    // $current .= "\n".$content;
    // file_put_contents($file_path, $current);
    // echo "Code added successfully";
    // exec("sudo service nginx restart");
    // exec("sudo certbot --nginx -d " .$dsst);
    // exec("sudo service nginx restart");

}
$time_end = microtime(true);
$time = ($time_end - $time_start);
$time = number_format((float)$time, 3, '.', '');
echo "Process Time: {$time} sec";



?>