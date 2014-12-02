<tr>
<td style="width:auto;padding:0px">{#IFDEF:BANNED_CKEY}<b>{BANNED_CKEY}</b><br/>{#ENDIF}{#IFDEF:BANNED_CID}{BANNED_CID}<br/>{#ENDIF}{#IFDEF:BANNED_IP}{BANNED_IP}{#ENDIF}</td>
<td style="text-overflow:nowrap;overflow:nowrap;padding:0px"><a class="easymodal" href="bandetails.php?id={BAN_ID}">{BAN_DATE}</a><br>{BANNING_ADMIN}<br/>{#IFDEF:BAN_JOB}<b>{BAN_JOB}</b>{#ENDIF}{#IFNDEF:BAN_JOB}<b>Server</b>{#ENDIF}<br/>{#IFDEF:BAN_LENGTH}{BAN_LENGTH}{#ENDIF}{#IFNDEF:BAN_LENGTH}Permanent{#ENDIF}</td>
<td style="max-width:10%;padding:0px">{BAN_REASON}</td>
<td style="padding:0px"><b>{BAN_STATUS}</b><p/>{#IFDEF:UNBANNED}{UNBANNING_ADMIN}<p/>{UNBAN_TIME}{#ENDIF}{#IFNDEF:UNBANNED}{#IFDEF:EXPIRE_TIME}{EXPIRE_TIME}{#ENDIF}{#ENDIF}</td>
</tr>