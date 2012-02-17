<?php

 if (!defined("IN_BTIT"))
      die("non direct access!");

require_once ("include/functions.php");
require_once ("include/config.php");

/////////////////
$minratio=0.9; // Set this to the minimum ratio allowed to vote.
/////////////////

if(!$CURUSER || $CURUSER["id_level"]==1 || $CURUSER["can_download"]=="no")
{

    stderr("ERROR","I'm sorry, you do not have permission to access this page.");

    stdfoot(($GLOBALS["usepopup"]?false:true));
    exit();
}
else
{
    (isset($_GET["id"]) ? $id=mysql_real_escape_string($_GET["id"]) : $id="");

    if($id!="")
    {
        $query ="SELECT flr.*, IF(IF(downloaded>0, SUM(uploaded/downloaded), $minratio)>=$minratio, 'yes', 'no') can_vote ";
        $query.="FROM {$TABLE_PREFIX}free_leech_req flr ";
        $query.="INNER JOIN {$TABLE_PREFIX}users u ";
        $query.="WHERE flr.info_hash='$id' ";
        $query.="AND u.id=".$CURUSER["uid"]." ";
        $query.="GROUP BY flr.info_hash";

        $res=mysql_query($query);
        if(mysql_num_rows($res)>0)
            $row=mysql_fetch_assoc($res);
        else
        {
            $query ="SELECT IF(IF(downloaded>0, SUM(uploaded/downloaded), $minratio)>=$minratio, 'yes', 'no') can_vote ";
            $query.="FROM {$TABLE_PREFIX}users ";
            $query.="WHERE id=".$CURUSER["uid"];
            $res=mysql_query($query);
            $row=mysql_fetch_assoc($res);
        }
        if($row["can_vote"]=="no")
        {

            stderr("ERROR","You must have a ratio higher than $minratio in order to vote");

            stdfoot(($GLOBALS["usepopup"]?false:true));
            exit();
        }
        if(isset($row["requester_ids"]))
        {
            $requesters=unserialize($row["requester_ids"]);
            if(in_array($CURUSER["uid"], $requesters))
            {

                stderr("ERROR", "You may only make one request per torrent");

                stdfoot(($GLOBALS["usepopup"]?false:true));
                exit();
            }
            else
            {
                $requesters[]=$CURUSER["uid"];
                $new_requesters=serialize($requesters);
                @mysql_query("UPDATE {$TABLE_PREFIX}free_leech_req SET count=count+1, requester_ids='$new_requesters' WHERE info_hash='$id'");
            }
        }
        else
        {
                $requesters[]=$CURUSER["uid"];
                $new_requesters=serialize($requesters);
                @mysql_query("INSERT INTO {$TABLE_PREFIX}free_leech_req SET info_hash='$id', requester_ids='$new_requesters'");
        }

       information_msg("Thank you for your vote ".$CURUSER["username"]."", "it has been successfully counted");
 //       print("</div><center><a href=\"javascript: history.go(-1);\">".BACK."</a>");


        stdfoot();
        exit();

    }

}

?>
