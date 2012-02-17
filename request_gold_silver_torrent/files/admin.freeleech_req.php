<?php
/////////////////////////////////////////////////////////////////////////////////////
// xbtit - Bittorrent tracker/frontend
//
// Copyright (C) 2004 - 2009  Btiteam
//
//    This file is part of xbtit.
//
// org by Petr1fied Dec 2007 converted to xbtit by DiemThuy Feb 2009
//
// Redistribution and use in source and binary forms, with or without modification,
// are permitted provided that the following conditions are met:
//
//   1. Redistributions of source code must retain the above copyright notice,
//      this list of conditions and the following disclaimer.
//   2. Redistributions in binary form must reproduce the above copyright notice,
//      this list of conditions and the following disclaimer in the documentation
//      and/or other materials provided with the distribution.
//   3. The name of the author may not be used to endorse or promote products
//      derived from this software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED
// WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
// MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
// IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
// SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
// TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
// PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
// NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
// EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
////////////////////////////////////////////////////////////////////////////////////

if (!defined("IN_BTIT"))
      die("non direct access!");

if (!defined("IN_ACP"))
      die("non direct access!");

require_once("include/functions.php");

$home="index.php?page=admin&amp;user=".$CURUSER["uid"]."&amp;code=".$CURUSER["random"]."&amp;do=freeleech_req";

(isset($_GET["act"])?$act=$_GET["act"]:$act="");
(isset($_GET["id"])?$id=mysql_real_escape_string($_GET["id"]):$id="");

if($act=="approve" && $id!="")
{
    @mysql_query("UPDATE {$TABLE_PREFIX}files SET gold='2' WHERE info_hash='$id'");
    @mysql_query("UPDATE {$TABLE_PREFIX}free_leech_req SET approved='yes' WHERE info_hash='$id'");
}
elseif($act=="decline")
{
    @mysql_query("UPDATE {$TABLE_PREFIX}files SET gold='0' WHERE info_hash='$id'");
    @mysql_query("UPDATE {$TABLE_PREFIX}free_leech_req SET approved='no' WHERE info_hash='$id'");
}
elseif($act=="nuke")
{
    @mysql_query("UPDATE {$TABLE_PREFIX}files SET gold='0' WHERE info_hash='$id'");
    @mysql_query("DELETE FROM {$TABLE_PREFIX}free_leech_req WHERE info_hash='$id'");
}



$query ="SELECT flr.*, f.filename, UNIX_TIMESTAMP(f.data) added, f.size, ";
$query.="f.uploader, u.username uploader_username, f.seeds, f.leechers, ";
$query.="f.finished, f.category, c.name, c.image, ul.prefixcolor, ul.suffixcolor ";
$query.="FROM {$TABLE_PREFIX}free_leech_req flr ";
$query.="LEFT JOIN {$TABLE_PREFIX}files f ON flr.info_hash=f.info_hash ";
$query.="LEFT JOIN {$TABLE_PREFIX}users u ON f.uploader=u.id ";
$query.="LEFT JOIN {$TABLE_PREFIX}categories c ON f.category=c.id ";
$query.="LEFT JOIN {$TABLE_PREFIX}users_level ul ON u.id_level=ul.id ";
$query.="ORDER BY added DESC";

$res=mysql_query($query);


    $flr=array();
    $i=0;

$admintpl->set("language",$language);
 if(mysql_num_rows($res)>0)
{

    while($row=mysql_fetch_assoc($res))
    {
        if($row["approved"]=="undecided")
        {
            $table["undecided"][]="<tr><td class='lista' align='center'><center><a href='index.php?page=torrents&category=".$row["category"]."'><img border=0 src='style/xbtit_default/images/categories/".$row["image"]."'></a></td><td class='lista' align='center'><center><a href=\"".(($GLOBALS["usepopup"])?"javascript:popdetails('index.php?page=details&id=".$row["info_hash"]."');":"index.php?page=details&id=".$row["info_hash"])."\">".$row["filename"]."</a></td><td class='lista' align='center'><center>".date("d/m/Y",$row["added"])."</td><td class='lista' align='center'><center>".makesize($row["size"])."</td><td class='lista' align='center'><center><a href='index.php?page=userdetails&id=".$row["uploader"]."'>".stripslashes($row[prefixcolor]).$row["uploader_username"].stripslashes($row[suffixcolor])."</a></td><td class='lista' align='center'><center><a href=\"".(($GLOBALS["usepopup"])?"javascript:poppeer('index.php?page=peers&id=".
            $row["info_hash"]."');":"index.php?page=peers&id=".$row["info_hash"])."\"><font color=green>".$row["seeds"]."</font></a></td><td class='lista' align='center'><center><a href=\"".(($GLOBALS["usepopup"])?"javascript:poppeer('index.php?page=peers&id=".$row["info_hash"]."');":"index.php?page=peers&id=".$row["info_hash"])."\"><font color=red>".$row["leechers"]."</font></a></td><td class='lista' align='center'><center>".(($row["finished>0"])?"<a href=\"".(($GLOBALS["usepopup"])?"javascript:poppeer('index.php?page=torrent_history&id=".$row["info_hash"]."');":"index.php?page=torrent_history&id=".$row["info_hash"])."\">".$row["finished"]."</a>":"---")."</td><td class='lista' align='center'><center>".$row["count"]."</td><td class='lista' align='center'><center><a onclick='return confirm(\"Are you sure you want to approve this torrent for Free Leech?\")' href='".$home."&act=approve&amp;id=".$row["info_hash"]."'><img border=0 src='images/smilies/thumbsup.gif' alt='Approve request'></a></td><td class='lista' align='center'><center><a onclick='return confirm(\"Are you sure you want to decline this torrent for Free Leech?\")' href='".$home."&act=decline&amp;id="
            .$row["info_hash"]."'><img border=0 src='images/smilies/thumbsdown.gif' alt='Decline request'></a></td><td class='lista' align='center'><center><a onclick='return confirm(\"Are you sure you want to nuke (clear all votes) for this torrent?\")' href='".$home."&act=nuke&amp;id=".$row["info_hash"]."'><img border=0 src='images/smilies/nuke.gif' alt='Nuke request'></a></td></tr>";
        }
        elseif($row["approved"]=="yes")
        {
            $table["approved"][]="<tr><td class='lista' align='center'><center><a href='index.php?page=torrents&category=".$row["category"]."'><img border=0 src='style/xbtit_default/images/categories/".$row["image"]."'></a></td><td class='lista' align='center'><center><a href=\"".(($GLOBALS["usepopup"])?"javascript:popdetails('index.php?page=details&id=".$row["info_hash"]."');":"index.php?page=details&id=".$row["info_hash"])."\">"
            .$row["filename"]."</a></td><td class='lista' align='center'><center>".date("d/m/Y",$row["added"])."</td><td class='lista' align='center'><center>".makesize($row["size"])."</td><td class='lista' align='center'><center><a href='index.php?page=userdetails&id=".$row["uploader"]."'>".stripslashes($row[prefixcolor]).$row["uploader_username"].stripslashes($row[suffixcolor])."</a></td><td class='lista' align='center'><center><a href=\"".(($GLOBALS["usepopup"])
            ?"javascript:poppeer('index.php?page=peers&id=".$row["info_hash"]."');":"index.php?page=peers&id=".$row["info_hash"])."\"><font color=green>".$row["seeds"]."</font></a></td><td class='lista' align='center'><center><a href=\"".(($GLOBALS["usepopup"])?"javascript:poppeer('index.php?page=peers&id=".$row["info_hash"]."');":"index.php?page=peers.php?id=".$row["info_hash"])."\"><font color=red>".$row["leechers"]."</font></a></td><td class='lista' align='center'><center>".(($row["finished>0"])?"<a href=\"".(($GLOBALS["usepopup"])?"javascript:poppeer('index.php?page=torrent_history&id=".$row["info_hash"]."');":"index.php?page=torrent_history&id=".$row["info_hash"])."\">".$row["finished"]."</a>":"---")."</td><td class='lista' align='center'><center>".$row["count"]."</td><td class='lista' align='center'><center><a onclick='return confirm(\"Are you sure you want to nuke (clear all votes) for this torrent? **WARNING** doing this on an approved torrent will also set the torrent back to a regular torrent\")
            ' href='".$home."&act=nuke&amp;id=".$row["info_hash"]."'><img border=0 src='images/smilies/nuke.gif' alt='Nuke request'></a></td></tr>";
        }
        elseif($row["approved"]=="no")
        {
            $table["declined"][]="<tr><td class='lista' align='center'><center><a href='index.php?page=torrents&category=".$row["category"]."'><img border=0 src='style/xbtit_default/images/categories/".$row["image"]."'></a></td><td class='lista' align='center'><center><a href=\"".(($GLOBALS["usepopup"])?"javascript:popdetails('index.php?page=details&id=".$row["info_hash"]."');":"index.php?page=details&id=".$row["info_hash"])."\">".$row["filename"]."</a></td><td class='lista' align='center'><center>".date("d/m/Y",$row["added"])."</td><td class='lista' align='center'><center>".makesize($row["size"])."</td><td class='lista' align='center'><center><a href='index.php?page=userdetails&id=".$row["uploader"]."'>".stripslashes($row[prefixcolor]).$row["uploader_username"].stripslashes($row[suffixcolor])."</a></td><td class='lista' align='center'><center><a href=\"".(($GLOBALS["usepopup"])?"javascript:poppeer('index.php?page=peers&id=".$row["info_hash"]."');":"
            index.php?page=peers&id=".$row["info_hash"])."\"><font color=green>".$row["seeds"]."</font></a></td><td class='lista' align='center'><center><a href=\"".(($GLOBALS["usepopup"])?"javascript:poppeer('index.php?page=peers&id=".$row["info_hash"]."');":"index.php?page=peers.php?id=".$row["info_hash"])."\"><font color=red>".$row["leechers"]."</font></a></td>
            <td class='lista' align='center'><center>".(($row["finished>0"])?"<a href=\"".(($GLOBALS["usepopup"])?"javascript:poppeer('index.php?page=torrent_history&id=".$row["info_hash"]."');":"index.php?page=torrent_history&id=".$row["info_hash"])."\">".$row["finished"]."</a>":"---")."</td><td class='lista' align='center'><center>".$row["count"]."</td><td class='lista' align='center'><center><a onclick='return confirm(\"Are you sure you want to approve this torrent for Free Leech?\")' href='".$home."&act=approve&amp;id=".$row["info_hash"]."'><img border=0 src='images/smilies/thumbsup.gif' alt='Approve request'></a></td><td class='lista' align='center'><center><a onclick='return confirm(\"Are you sure you want to nuke (clear all votes) for this torrent?\")' href='".$home."&act=nuke&amp;id=".$row["info_hash"]."'><img border=0 src='images/smilies/nuke.gif' alt='Nuke request'></a></td></tr>";
        }
    }
    if(isset($table["undecided"]))
    {
$admintpl->set("af1","<br /><center><strong><u><font size=3>Undecided</font></u></strong></center><br />");
$admintpl->set("af2","<table align='center' width='90%'><tr>");
$admintpl->set("af3","<td class='header' align='center'>Category</td>");
$admintpl->set("af4","<td class='header' align='center'>File</td>");
$admintpl->set("af5","<td class='header' align='center'>Added</td>");
$admintpl->set("af6","<td class='header' align='center'>Size</td>");
$admintpl->set("af7","<td class='header' align='center'>Uploader</td>");
$admintpl->set("af8","<td class='header' align='center'>S</td>");
$admintpl->set("af9","<td class='header' align='center'>L</td>");
        $admintpl->set("af10","<td class='header' align='center'>C</td>");
        $admintpl->set("af11","<td class='header' align='center'>Votes</td>");
        $admintpl->set("af12","<td class='header' align='center'>Approve</td>");
        $admintpl->set("af13","<td class='header' align='center'>Decline</td>");
        $admintpl->set("af14","<td class='header' align='center'>Nuke</td></tr>");

        foreach($table["undecided"] as $v)
        {

         $flr[$i]["af15"]= $v;
         $i++;
         $admintpl->set("flr",$flr);
        }
    $admintpl->set("af16","</table>");
    }
    if(isset($table["approved"]))
    {
        $admintpl->set("af17","<br /><center><strong><u><font size=3>Approved</font></u></strong></center><br />");
        $admintpl->set("af18","<table align='center' width='90%'><tr>");
        $admintpl->set("af19","<td class='header' align='center'>Category</td>");
        $admintpl->set("af20","<td class='header' align='center'>File</td>");
        $admintpl->set("af21","<td class='header' align='center'>Added</td>");
        $admintpl->set("af22","<td class='header' align='center'>Size</td>");
        $admintpl->set("af23","<td class='header' align='center'>Uploader</td>");
        $admintpl->set("af24","<td class='header' align='center'>S</td>");
        $admintpl->set("af25","<td class='header' align='center'>L</td>");
        $admintpl->set("af26","<td class='header' align='center'>C</td>");
        $admintpl->set("af27","<td class='header' align='center'>Votes</td>");
        $admintpl->set("af28","<td class='header' align='center'>Nuke</td></tr>");

        foreach($table["approved"] as $v)
        {
         $flr[$i]["af29"]= $v;
         $i++;
         $admintpl->set("flr1",$flr);
        }
        $admintpl->set("af30","</table>");
    }
    if(isset($table["declined"]))
    {
        $admintpl->set("af31","<br /><center><strong><u><font size=3>Declined</font></u></strong></center><br />");
        $admintpl->set("af32","<table align='center' width='90%'><tr>");
        $admintpl->set("af33","<td class='header' align='center'>Category</td>");
        $admintpl->set("af34","<td class='header' align='center'>File</td>");
        $admintpl->set("af35","<td class='header' align='center'>Added</td>");
        $admintpl->set("af36","<td class='header' align='center'>Size</td>");
        $admintpl->set("af37","<td class='header' align='center'>Uploader</td>");
        $admintpl->set("af38","<td class='header' align='center'>S</td>");
        $admintpl->set("af39","<td class='header' align='center'>L</td>");
        $admintpl->set("af40","<td class='header' align='center'>C</td>");
        $admintpl->set("af41","<td class='header' align='center'>Votes</td>");
        $admintpl->set("af42","<td class='header' align='center'>Approve</td>");
        $admintpl->set("af43","<td class='header' align='center'>Nuke</td></tr>");

        foreach($table["declined"] as $v)
        {
         $flr[$i]["af44"]= $v;
         $i++;
         $admintpl->set("flr2",$flr);
        }
        $admintpl->set("af45","</table>");
    }
    $admintpl->set("af46","<br />");


}
 else
{

    stderr("ERROR","No torrents have been voted for yet.");

    exit();
}

?>
