<?php 
ob_start();
echo '<!DOCTYPE html>
<html lang="en">
 <head>
    <meta charset="utf-8">
    <title>Easy Draw</title>
    <meta name="description" content="Easy Draw">
    <meta name="theme-color" content="#475d82"/>
    <link rel="icon" type="image/png" sizes="32x32" href="./favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicon-16x16.png">
	<link rel="stylesheet" type="text/css" href="./view.css" media="all">
	<script type="text/javascript" src="./view.js"></script>

 </head>';
 

require_once(__DIR__ . '/config.php');

if(isset($_GET["draw"])) $draw_id = $_GET["draw"];
if(empty($draw_id))
{
	  if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$nameErr = "";
		$titlex = $_POST["titlex"];
	  if (empty($_POST["names_list"])) {
		$nameErr = "* Names list is required!<p>\n\r";
	  } else {
		$names_list = $_POST["names_list"];
		$names_list = clean_r_n($names_list);
		$rows = explode("\n",$names_list);
	  }
		
	  if (empty($_POST["num_res"])) {
	   $nameErr .= "** Number of drawn is required!<p>\n\r";
	  } else {
		$num_res = intval($_POST["num_res"]);
		if($num_res == 0)  $nameErr .= "** Number of drawn is required!<p>\n\r";
		if(count($rows) < $num_res)  $nameErr .= "*** The number of names in the list must be greater than the number of results to draw.<p>\n\r";
	  }
	  
	  if(!empty($nameErr))
		{	 
			echo $nameErr."<p>\n\r<p>\n\r";
			 print_form();
		} else {
			//estrazione
			$cnt = 0;
			while($num_res > $cnt )
			{
				$estratto = rand(0,count($rows)-1);
				$from_map = $rows[$estratto];
				unset($rows[$estratto]);
				sort($rows);
				$cnt++;
				$estratti[] = $from_map;
			}
			//Save file		
			$draw_id = generateRandomString();
			$id_file = './draws/'.$draw_id;
			$d = new DateTime('', new DateTimeZone('Europe/Rome')); 
			$DateNow = $d->format('d-m-Y H:i:s');
			
			$risultato['draw_id'] = $draw_id;
			$risultato['num_res'] = $num_res;
			$risultato['titlex'] = $titlex;
			$risultato['rows'] = explode("\n",$names_list);
			$risultato['estratti'] = $estratti;
			$risultato['names_list'] = $names_list;
			$risultato['date'] = $DateNow;
			
			file_put_contents($id_file, json_encode($risultato));
			ob_start();
			header('location:'.WEBSITE."?draw=".$draw_id);
			exit();
			ob_end_flush();
		}
	}	else {
		print_form();
	}
} else {
		//print draw result from file.
		$id_file = './draws/'.$draw_id;
		if (file_exists($id_file) && filesize($id_file) > 0) {
			$content = is_file($id_file) ? file_get_contents($id_file) : null;
			$content = json_decode($content,true);
			
			print_form_2($content);
		} else {
			echo "Wrong ID";
		}
		
}
function clean_r_n($text){
	$text = rtrim($text,"\r\n");
	$text = ltrim($text,"\r\n");
	
	while(strpos($text,"\r",0)  || strpos($text,"\r ",0) || strpos($text,"\n ",0) || strpos($text,"\n\n",0) || strpos($text,"\r\n\r\n",0))
	{
		$a = array("\r\n\r\n","\n\n","\r\r","\n ","\r ","\r");
		$b =  array("\r\n","\n","\r","\n","\r","");
		$text = str_replace($a,$b,$text);
	}
	return $text;
}

function print_form()
{
	echo '<body id="main_body" >
	<img id="top" src="./top.png" alt="">
	<div id="form_container">
	
		<h1><a>Easy Draw</a></h1>
		<form id="form_12258" class="appnitro"  method="post" action="">
		<div class="form_description">
			<h2>Easy Draw</h2>
			<p>Fill in the fields to draw names.</p>
		</div>						
			<ul >
			
			<li id="li_2" >
		<label class="description" for="titlex">Draw Title: </label>
		<div>
			<input id="titlex" name="titlex" class="element text medium" type="text" maxlength="255" value=""/> 
		</div><p class="guidelines" id="guide_2"><small>Optional field.</small></p> 
		</li>		<li id="li_1" >
		<label class="description" for="num_res">Number of drawn: </label>
		<div>
			<input id="num_res" name="num_res" class="element text small" type="text" maxlength="255" value=""/> 
		</div><p class="guidelines" id="guide_1"><small>How many results?</small></p> 
		</li>		<li id="li_3" >
		<label class="description" for="names_list">Names List: </label>
		<div>
			<textarea id="names_list" name="names_list" class="element textarea medium"></textarea> 
		</div><p class="guidelines" id="guide_3"><small>Each newline is valid for a single entity.</small></p> 
		</li>
					<li class="buttons">
			    <input type="hidden" name="form_id" value="12258" />
				<input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
		</li>
			</ul>
		</form>	

	</div>
	<img id="bottom" src="./bottom.png" alt="">
	</body>';
}

function print_form_2($content)
{
	$num_res = $content['num_res'];
	$titlex = $content['titlex'];
	$rows = $content['rows'];
	$estratti = $content['estratti'];
	$date = $content['date'];
	$draw_id = $content['draw_id'];
	//debug($content['names_list'],true);
	
	$p_titlex = (empty($titlex)) ? "" : '<p style="font-size:large; text-align:center; font-family:courier;">'.$titlex.'</p>';
	$url_post = '<a href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">'.$draw_id.'</a>';
	$new_draw = '<a href="'.WEBSITE.'"> New draw </a>';
	
	$partecipants = "";
	foreach($rows as $row)
	{
		$partecipants .= "-".$row."<p>\n\r";
	}
	
	
	$elaborato = "";
	$cntr_1 = 1;
	foreach($estratti as $row)
	{
		$elaborato .= "<tr><td>".$cntr_1.". ".$row."</td></tr>";
		$cntr_1++;
	}
	
$tab1 = '
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
th, td {
  padding: 15px;
  text-align: center;
}
#t01 {
  width: 100%;    
  background-color: #f1f1c1;
}
</style>
<font size="5" face="Verdana" >
<table id="t01">
  '.$elaborato.'
</table>
</font>
';
		$d = new DateTime('', new DateTimeZone('Europe/Riga')); 
	$DateNow = $d->format('Y-m-d H:i:s');
	echo '
	<body id="main_body" >
	
	<img id="top" src="./top.png" alt="">
	
	<div id="form_container">
		<center>[ID:'.$draw_id.']</center><h1><a>Result of the draw:</a></h1>
		<form id="form_12258" class="appnitro"  method="post" action="">
		<div class="form_description">
			<h2><p style="text-align:center">Result of the draw:</p></h2>
			<p style="text-align:center">Draw <b>'.$num_res.'</b> names out of <b>'.count($rows).'</b>.</p>
			<p style="color:Gray; text-align:center"><small>Date: '.$date.'</small></p>
		</div>	
			'.$p_titlex.'	
			<ul >
			<div class="form_description">
			'.$tab1.'
			</div>
			<div class="poste">
						<div>
						<p>
							<p><strong><em>Input values:</em></strong></p>
								'.$partecipants.'
						</div>
					</div>
			</ul>
			<p><p>Link to the draw: ' .$url_post. '</p>
			<p>
			<p>'.$new_draw.'
		</form>	

	</div>
	
	<img id="bottom" src="./bottom.png" alt="">
	</body>';
}


function debug($val, $encode = false)
{
	 echo (!$encode) ?  $val . "<p>\n\r" : json_encode($val,JSON_PRETTY_PRINT) . "<p>\n\r" ;
}


function generateRandomString($length = 20) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
echo "</html>";
ob_end_flush();
?>


