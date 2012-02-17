<?php
/////////////////////////////////////////////////////////////////////////////////////
// xbtit - Bittorrent tracker/frontend
//
// Copyright (C) 2004 - 2007  Btiteam
//
//    This file is part of xbtit.
//
//  Seed Wanted page by DiemThuy - April 2009
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



if (!$CURUSER || $CURUSER["view_torrents"]=="no")
   {
    // do nothing
   }
else
    {
    
global $CURUSER, $BASEURL, $STYLEPATH, $XBTT_USE;

//   $limit=10;
  if ($XBTT_USE)
     $sql = "SELECT f.info_hash as hash, f.seeds+ifnull(x.seeders,0) as seeds , f.leechers + ifnull(x.leechers,0) as leechers, dlbytes AS dwned, format(f.finished+ifnull(x.completed,0),0) as finished, filename, url, info, UNIX_TIMESTAMP(data) AS added, c.image, c.name AS cname, category AS catid, size, external, uploader FROM {$TABLE_PREFIX}files as f LEFT JOIN xbt_files x ON f.bin_hash=x.info_hash LEFT JOIN {$TABLE_PREFIX}categories as c ON c.id = f.category WHERE f.leechers + ifnull(x.leechers,0) > 0 AND f.seeds+ifnull(x.seeders,0) = 0 AND f.external='no' ORDER BY f.leechers + ifnull(x.leechers,0) DESC ";
  else
     $sql = "SELECT info_hash as hash, seeds, leechers, dlbytes AS dwned, finished, filename, url, info, UNIX_TIMESTAMP(data) AS added, c.image, c.name AS cname, category AS catid, size, external, uploader FROM {$TABLE_PREFIX}files as f LEFT JOIN {$TABLE_PREFIX}categories as c ON c.id = f.category WHERE leechers >0 AND seeds = 0 AND external='no' ORDER BY added DESC ";
      $seedwanted=array();
      $i=0;
   $row = do_sqlquery($sql,true);

   if (mysql_num_rows($row)>0)
     {
$seedwanttpl=new bTemplate();
$seedwanttpl->set("language",$language);


       $seedwanttpl->set("sw1","<table cellpadding=4 cellspacing=1 width=100%>");
       $seedwanttpl->set("sw2","<tr>");
       $seedwanttpl->set("sw2.3","<TD align= center class=header>".$language["DOWN"]."</TD>");
       $seedwanttpl->set("sw3","<td colspan=1 align=center class=header>".$language["TORRENT_FILE"]."</td>");
       $seedwanttpl->set("sw4","<td align=center class=header>".$language["CATEGORY"]."</td>");

         if (max(0,$CURUSER["WT"])>0)
       $seedwanttpl->set("sw5","<TD align= center class=header>".$language["WT"]."</TD>");

       $seedwanttpl->set("sw6","<td align=center class=header>".$language["ADDED"]."</td>");
       $seedwanttpl->set("sw7","<td align=center class=header>".$language["SIZE"]."</td>");
       $seedwanttpl->set("sw8","<td align=center class=header>".$language["SHORT_S"]."</td>");
       $seedwanttpl->set("sw9","<td align=center class=header>".$language["SHORT_L"]."</td>");
       $seedwanttpl->set("sw10","<td align=center class=header>".$language["SHORT_C"]."</td>");
       $seedwanttpl->set("sw11","</tr>");


       if ($row)
       {
           while ($data=mysql_fetch_array($row))
           {
if (strpos($CURUSER['catte'],"[c".$data[catid]."]")!== false){
}else{
     $seedwanted[$i]["sw12"]=("<tr>");

               if ( strlen($data["hash"]) > 0 )
               {
     $seedwanted[$i]["sw13"]=("<td NOWRAP align=center class=lista>");

   $ratio_checker=mysql_query("SELECT `id` FROM `{$TABLE_PREFIX}hacks` WHERE `title` LIKE 'Download Ratio Checker%'");
     $rc_count=@mysql_num_rows($ratio_checker);

     if($rc_count>0)
         $seedwanted[$i]["sw14"]=("<a href=index.php?page=downloadcheck&id=".$data["hash"]."><center><img src='images/download.gif' border='0' alt='".$language["DOWNLOAD_TORRENT"]."' title='".$language["DOWNLOAD_TORRENT"]."' /></center></a>");
     else
         $seedwanted[$i]["sw14"]=("<a href=download.php?id=".$data["hash"]."&f=".$data["filename"].".torrent><center><img src='images/download.gif' border='0' alt='".$language["DOWNLOAD_TORRENT"]."' title='".$language["DOWNLOAD_TORRENT"]."' /></center></a>");



         //waitingtime
             if (max(0,$CURUSER["WT"])>0){
             $resuser=do_sqlquery("SELECT * FROM {$TABLE_PREFIX}users WHERE id=".$CURUSER["uid"]);
             $rowuser=mysql_fetch_array($resuser);
             if (max(0,$rowuser['downloaded'])>0) $ratio=number_format($rowuser['uploaded']/$rowuser['downloaded'],2);
             else $ratio=0.0;
             $res2 =do_sqlquery("SELECT * FROM {$TABLE_PREFIX}files WHERE info_hash='".$data["hash"]."'");
             $added=mysql_fetch_array($res2);
             $vz = sql_timestamp_to_unix_timestamp($added["data"]);
             $timer = floor((time() - $vz) / 3600);
             if($ratio<1.0 && $rowuser['id']!=$added["uploader"]){
                 $wait=$CURUSER["WT"];
             }
             $wait -=$timer;
             if ($wait<=0)$wait=0;
             }
         //end waitingtime

           $seedwanted[$i]["sw15"]=("</td>");
                if ($GLOBALS["usepopup"])
                   $seedwanted[$i]["sw16"]=("<td width=60% class=\"lista\" style=\"padding-left:10px;\"><a href=\"javascript:popdetails('index.php?page=torrent-details&amp;id=" . $data['hash'] . "');\" title=\"" . $language["VIEW_DETAILS"] . ": " . $data["filename"] . "\">" . $data["filename"] . "</a></td>");
                else
                    $seedwanted[$i]["sw17"]=("<TD align=\"left\" class=\"lista\" style=\"padding-left:10px;\"><A HREF=\"index.php?page=torrent-details&amp;id=".$data["hash"]."\" title=\"".$language["VIEW_DETAILS"].": ".$data["filename"]."\">".$data["filename"]."</A></td>");
               $seedwanted[$i]["sw18"]=("<td align=\"center\" class=\"lista\" style=\"text-align: center;\"><a href=\"index.php?page=torrents&amp;category=$data[catid]\">".image_or_link(($data["image"]==""?"":"$STYLEPATH/images/categories/" . $data["image"]),"",$data["cname"])."</a></td>");
                if (max(0,$CURUSER["WT"])>0)
              $seedwanted[$i]["sw19"]=("<td align=\"center\" class=\"lista\" style=\"text-align: center;\">".$wait." h</td>");
                include("include/offset.php");
               $seedwanted[$i]["sw20"]=("<td nowrap=\"nowrap\" class=\"lista\" align=\"center\" style=\"text-align: center;\">" . date("d/m/Y", $data["added"]-$offset) . "</td>");
                $seedwanted[$i]["sw21"]=("<td nowrap=\"nowrap\" align=\"center\" class=\"lista\" style=\"text-align: center;\">" . makesize($data["size"]) . "</td>");

                if ($data["external"]=="no")
                {
                    if ($GLOBALS["usepopup"])
                    {
                       $seedwanted[$i]["sw22"]=("<td align=\"center\" class=\"".linkcolor($data["seeds"])."\" style=\"text-align: center;\"><a href=\"javascript:poppeer('index.php?page=peers&amp;id=".$data["hash"]."');\" title=\"".$language["PEERS_DETAILS"]."\">" . $data["seeds"] . "</a></td>\n");
                      $seedwanted[$i]["sw23"]=("<td align=\"center\" class=\"".linkcolor($data["leechers"])."\" style=\"text-align: center;\"><a href=\"javascript:poppeer('index.php?page=peers&amp;id=".$data["hash"]."');\" title=\"".$language["PEERS_DETAILS"]."\">" .$data["leechers"] . "</a></td>\n");
                        if ($data["finished"]>0)
                            $seedwanted[$i]["sw24"]=("<td align=\"center\" class=\"lista\" style=\"text-align: center;\"><a href=\"javascript:poppeer('index.php?page=torrent_history&amp;id=".$data["hash"]."');\" title=\"History - ".$data["filename"]."\">" . $data["finished"] . "</a></td>");
                        else
                         $seedwanted[$i]["sw24"]=("<td align=\"center\" class=\"lista\" style=\"text-align: center;\">---</td>");
                    }
                    else
                    {
                         $seedwanted[$i]["sw25"]=("<td align=\"center\" class=\"".linkcolor($data["seeds"])."\" style=\"text-align: center;\"><a href=\"index.php?page=peers&amp;id=".$data["hash"]."\" title=\"".$language["PEERS_DETAILS"]."\">" . $data["seeds"] . "</a></td>\n");
                       $seedwanted[$i]["sw26"]=("<td align=\"center\" class=\"".linkcolor($data["leechers"])."\" style=\"text-align: center;\"><a href=\"index.php?page=peers&amp;id=".$data["hash"]."\" title=\"".$language["PEERS_DETAILS"]."\">" .$data["leechers"] . "</a></td>\n");
                        if ($data["finished"]>0)
                           $seedwanted[$i]["sw27"]=("<td align=\"center\" class=\"lista\" style=\"text-align: center;\"><a href=\"index.php?page=torrent_history&amp;id=".$data["hash"]."\" title=\"History - ".$data["filename"]."\">" . $data["finished"] . "</a></td>");
                        else
                           $seedwanted[$i]["sw27"]=("<td align=\"center\" class=\"lista\" style=\"text-align: center;\">---</td>");
                    }
                }
                else
                {
                    // linkcolor
                     $seedwanted[$i]["sw28"]=("<td align=\"center\" class=\"".linkcolor($data["seeds"])."\" style=\"text-align: center;\">" . $data["seeds"] . "</td>");
                   $seedwanted[$i]["sw29"]=("<td align=\"center\" class=\"".linkcolor($data["leechers"])."\" style=\"text-align: center;\">" .$data["leechers"] . "</td>");
                    if ($data["finished"]>0)
                       $seedwanted[$i]["sw30"]=("<td align=\"center\" class=\"lista\" style=\"text-align: center;\">" . $data["finished"] . "</td>");
                    else
                    $seedwanted[$i]["sw30"]=("<td align=\"center\" class=\"lista\" style=\"text-align: center;\">---</td>");
                }
              $seedwanted[$i]["sw34"]=("</tr>\n");
                }
           }
           
            $i++;
            $seedwanttpl->set("seedwanted",$seedwanted);
       }
}
  else
  {
   $seedwanttpl->set("sw35","<tr><td class=\"lista\" colspan=\"9\" align=\"center\" style=\"text-align: center;\">" . $language["NO_TORRENTS"] . "</td></tr>");
       }

     $seedwanttpl->set("sw36","</table>");


    }
    else
     $seedwanttpl->set("sw37","<table class=\"lista\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr><td><div align=\"center\" style=\"text-align: center;\">".$language["NO_TORRENTS"]."</div></td></tr></table>");
} // end if user can view
?>
