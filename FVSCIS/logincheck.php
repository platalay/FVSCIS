<?php

$Officers = new Officer;
$FisherMans = new FisherMan;
$is_officer = $Officers->find_by_social_login('line',$_SESSION['user_id']);
if($is_officer == false) {
    $is_fisher_man = $FisherMans->find_by_social_logi('line',$_SESSION['user_id']);  
    if($is_fisher_man == false) {
        redirect_to(url_for('/logins2.php'));
    }
    else
    {
        //logincheck
    }
}
else
{
    //logincheck
}

session_start();
include("connect/dbconnect.php");
if(isset($_POST["username"]))
{
	$result=$conn->query("select * from user where username='".$_POST["username"]."' and password='".$_POST["password"]."' and yesno=1" );
	if($result)
	{
		if($result->rowcount()>0)
		{
			while($row=$result->fetch())
			{
				if($row["role"]=="admin")
				{
					$_SESSION["username"]=$row["username"];
					$_SESSION["role"]=$row["role"];
					header("Location:user_admin.php");
				}
				elseif($row["role"]=="lihq")
				{
					$_SESSION["username"]=$row["username"];
					$_SESSION["role"]=$row["role"];
					header("Location:user_lihqi.php");
				}
				elseif($row["role"]=="pipo")
				{
					$_SESSION["username"]=$row["username"];
					$_SESSION["role"]=$row["role"];
					$_SESSION["pi_centerid"]=$row["center_id"];
					header("Location:user_pipo.php");
				}
				elseif($row["role"]=="vms")
				{
					header("Location:login.php");
				}
				elseif($row["role"]=="input")
				{
					$_SESSION["username"]=$row["username"];
					$_SESSION["role"]=$row["role"];
					header("Location:user_inputichi.php");
				}
				elseif($row["role"]=="input1")
				{
					header("Location:login.php");
				}
				elseif($row["role"]=="lihqvms")
				{
					$_SESSION["username"]=$row["username"];
					$_SESSION["role"]=$row["role"];
					header("Location:user_lihqvms.php");
				}
				elseif($row["role"]=="lihqvmshead")
				{
					$_SESSION["username"]=$row["username"];
					$_SESSION["role"]=$row["role"];
					header("Location:user_lihqvmshead.php");
				}
			}
		}
		else
		{
				header("Location:login.php");	
		}
	}
	else
	{
		header("Location:login.php");		
	}
	
}
else
{
	header("Location:login.php");		
}
?>